<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AiChatController extends Controller
{
    /**
     * Allowed tables for text-to-SQL queries.
     * Sensitive tables (push_subscriptions, mailings) are excluded.
     * 'users' is included but only non-sensitive columns are exposed via schema description.
     */
    private const ALLOWED_TABLES = [
        'users',
        'medicines',
        'medicine_schedules',
        'medicine_reminders',
        'medicine_logs',
        'health_metrics',
        'user_health',
        'symptoms',
        'user_symptoms',
        'diseases',
        'disease_symptoms',
        'user_diseases',
        'uploads',
        'posts',
        'comments',
        'notifications',
    ];

    /**
     * Columns stripped from schema hints sent to the LLM to prevent leaking sensitive fields.
     */
    private const SCHEMA_BLOCKED_COLUMNS = [
        'password', 'remember_token', 'auth_token', 'public_key',
        'endpoint', 'email', 'phone', 'notification_settings',
    ];

    /**
     * Tables that must always be filtered by the authenticated user's user_id.
     */
    private const USER_SCOPED_TABLES = [
        'medicines',
        'medicine_logs',
        'medicine_reminders',
        'medicine_schedules',
        'user_health',
        'user_symptoms',
        'user_diseases',
        'uploads',
        'notifications',
    ];

    public function message(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message'           => ['required', 'string', 'max:5000'],
            'history'           => ['nullable', 'array', 'max:12'],
            'history.*.role'    => ['required_with:history', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:5000'],
        ]);

        $authUserId = $request->user()?->id;

        if ($request->user()?->isAdmin()) {
            return response()->json([
                'reply' => 'Chatbot is disabled for admin accounts.',
            ], 403);
        }

        if ($this->isUnauthorizedDataRequest($validated['message'])) {
            return response()->json([
                'reply' => $this->formatStructuredReply(
                    "I can't share other users' information or sensitive account data. I can only discuss your own records."
                ),
            ], 403);
        }

        $apiKey    = (string) config('services.openrouter.api_key', '');
        $googleKey = (string) config('services.google.api_key', '');

        Log::info('aboutMe called', [
            'user_id' => $authUserId,
            'api_key_present' => $apiKey !== '',
            'google_key_present' => $googleKey !== '',
        ]);

        if ($apiKey === '' && $googleKey === '') {
            return response()->json([
                'reply' => $this->formatStructuredReply('AI service is not configured yet. Please set OPENROUTER_API_KEY or GOOGLE_API_KEY in your .env file.'),
            ], 503);
        }

        $baseUrl      = rtrim((string) config('services.openrouter.base_url', 'https://openrouter.ai/api/v1'), '/');
        $primaryModel = (string) config('services.openrouter.model', 'google/gemini-2.0-flash-001');
        $fallbackModels = array_filter(array_merge(
            (array) config('services.openrouter.fallback_models', []),
            [
            config('services.openrouter.fallback_model_1'),
            config('services.openrouter.fallback_model_2'),
            config('services.openrouter.fallback_model_3'),
            ]
        ));

        $models = collect(array_merge([$primaryModel], $fallbackModels))
            ->filter(fn($m) => is_string($m) && trim($m) !== '')
            ->map(fn($m) => trim($m))
            ->unique()
            ->values()
            ->all();
        $models = $this->sanitizeOpenRouterModels($models);

        if ($models === []) {
            return response()->json([
                'reply' => $this->formatStructuredReply('AI service models are not configured.'),
            ], 503);
        }

        // ── Text-to-SQL / RAG pipeline ────────────────────────────────────────
        // Skip the heavy text-to-SQL/RAG flow for short conversational or
        // emotional messages so they are handled by the normal LLM chat flow.
        $skipTextToSql = $this->isShortChatIntent($validated['message'])
            || $this->isEmotionalDistressIntent($validated['message'])
            || $this->isDietIntent($validated['message'])
            || $this->isGeneralWellnessIntent($validated['message']);

        if ((bool) config('chatbot.enable_text_to_sql', true) && !$skipTextToSql) {
            try {
                // Fast-path: deterministic personal health snapshot
                if ($authUserId !== null && $this->isPersonalHealthIntent($validated['message'])) {
                    $snapshot = $this->getPersonalHealthSnapshot($authUserId);
                    if ($snapshot !== null) {
                        // If data is simple enough, reply directly without LLM
                        $directReply = $this->buildPersonalHealthReply($snapshot);
                        if ($directReply !== null && $apiKey === '' && $googleKey === '') {
                            return response()->json(['reply' => $directReply]);
                        }
                        // Rich data: let LLM summarise from the snapshot
                        $final = $this->askModelToSummarizeResults(
                            $validated['message'],
                            'PERSONAL_HEALTH_SNAPSHOT',
                            $snapshot,
                            $apiKey,
                            $primaryModel,
                            $baseUrl,
                            $models,
                            $googleKey
                        );
                        if ($final !== null) {
                            return response()->json(['reply' => $final]);
                        }
                    }
                }

                // General text-to-SQL path
                $schemaDesc = $this->getDatabaseSchemaDescription();
                $sql = $this->generateSqlFromMessage(
                    $validated['message'],
                    $schemaDesc,
                    $apiKey,
                    $primaryModel,
                    $baseUrl,
                    $models,
                    $googleKey,
                    $authUserId
                );

                if ($sql !== null) {
                    $sql = $this->sanitizeSql($sql);
                    if ($sql !== null) {
                        if (!$this->isAllowedSql($sql)) {
                            Log::warning('Generated SQL references disallowed tables', ['sql' => $sql]);
                            } elseif (!$this->isSqlAuthorizedForUser($sql, $authUserId)) {
                                Log::warning('Generated SQL failed user scope authorization', [
                                    'sql'         => $sql,
                                    'auth_user_id'=> $authUserId,
                                ]);
                                return response()->json([
                                    'reply' => $this->formatStructuredReply("I can't share other users' information or sensitive account data. I can only discuss your own records."),
                                ], 403);
                        } else {
                            $results = $this->executeSelectSql($sql);
                            if ($results !== null) {
                                $questionForModel = $validated['message'];
                                if ($this->isRiskIntent($validated['message'])) {
                                    $questionForModel .= "\n\nPlease include a concise assessment of risks related to the user's diseases and symptoms, and provide 2-3 actionable suggestions.";
                                }

                                $final = $this->askModelToSummarizeResults(
                                    $questionForModel,
                                    $sql,
                                    $results,
                                    $apiKey,
                                    $primaryModel,
                                    $baseUrl,
                                    $models,
                                    $googleKey
                                );
                                if ($final !== null) {
                                    return response()->json(['reply' => $this->formatStructuredReply($final)]);
                                }
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Text-to-SQL pipeline failed, falling through to normal chat', [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);
            }
        }

        // ── Normal LLM chat flow ──────────────────────────────────────────────
        $history = collect($validated['history'] ?? [])->map(fn(array $item): array => [
            'role'    => $item['role'],
            'content' => $item['content'],
        ])->values()->all();

        // Build context-aware system prompt
        $systemPrompt = $this->buildSystemPrompt($authUserId);

        $messages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $history,
            [['role' => 'user', 'content' => $validated['message']]]
        );

        // Try Google Gemini first (with model fallback) if key is present
        if ($googleKey !== '') {
            $resp = $this->googleChatWithFallback(
                $systemPrompt,
                $validated['message'],
                $googleKey,
                0.5,
                500
            );
            if ($resp !== null) {
                return response()->json(['reply' => $this->formatStructuredReply($resp)]);
            }
        }

        // Try OpenRouter models in order
        $lastStatus = null;
        foreach ($models as $model) {
            try {
                $response = Http::timeout(35)
                    ->withToken($apiKey)
                    ->withHeaders([
                        'HTTP-Referer' => (string) config('services.openrouter.site_url', config('app.url')),
                        'X-Title'      => (string) config('services.openrouter.app_name', config('app.name')),
                        'Content-Type' => 'application/json',
                        'Accept'       => 'application/json',
                    ])
                    ->post($baseUrl . '/chat/completions', [
                        'model'       => $model,
                        'messages'    => $messages,
                        'temperature' => 0.5,
                        'max_tokens'  => 500,
                    ]);

                if ($response->successful()) {
                    $reply = $this->extractReplyText(data_get($response->json(), 'choices.0.message.content'));
                    if ($reply !== null) {
                        return response()->json(['reply' => $this->formatStructuredReply($reply)]);
                    }
                    Log::warning('OpenRouter returned empty content', ['model' => $model]);
                    $lastStatus = 502;
                    continue;
                }

                $lastStatus = $response->status();
                Log::warning('OpenRouter request failed', [
                    'model'  => $model,
                    'status' => $lastStatus,
                    'body'   => $response->body(),
                ]);

                if ($lastStatus === 401 || $lastStatus === 403) {
                    break;
                }
            } catch (\Throwable $e) {
                Log::error('AI chat exception', ['model' => $model, 'message' => $e->getMessage()]);
                $lastStatus = 500;
            }
        }

        if (in_array($lastStatus, [401, 403], true)) {
            return response()->json(['reply' => $this->formatStructuredReply('AI service authentication failed. Please verify your API key.')], 502);
        }

        return response()->json(['reply' => $this->formatStructuredReply('I could not reach the AI service right now. Please try again shortly.')], 502);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // System prompt builder
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Build a context-aware system prompt.
     * When a user is authenticated, the prompt tells the LLM it has access to
     * their real health records — preventing it from asking clarifying questions
     * that the DB already answers.
     */
    private function buildSystemPrompt(?int $authUserId): string
    {
        $base = 'You are MyDoctor AI, a personal health assistant embedded in the MyDoctor app. '
              . 'The user is already logged in and their health records (diseases, symptoms, medicines, metrics) are stored in the database. '
              . 'When the user asks about their health, diseases, symptoms, or medicines, answer using the data retrieved from their records — do NOT ask them to repeat information the system already has. '
              . 'Never disclose other users\' data, sensitive credentials, tokens, passwords, phone numbers, or emails. If asked, refuse briefly. '
              . 'Always format responses with a short title, bold section labels, and bullet points where useful. '
              . 'When giving tips, use natural bullet points and avoid labels like Tip 1, Tip 2, etc. '
              . 'Be concise, friendly, and practical. '
              . 'Never provide a clinical diagnosis. '
              . 'For emergencies or severe symptoms, advise contacting local emergency services immediately.';

        if ($authUserId !== null) {
            $base .= ' The authenticated user ID is ' . $authUserId . '.';
        }

        return $base;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Intent detection
    // ──────────────────────────────────────────────────────────────────────────

    private function isPersonalHealthIntent(string $message): bool
    {
        return (bool) preg_match(
            '/\b(my\s+health|health\s+condition|my\s+condition|health\s+status|my\s+symptoms?|my\s+diseases?|my\s+medicines?|my\s+metrics?|tell\s+me\s+about\s+my|what\s+(are|is)\s+my|how\s+is\s+my\s+health|am\s+i\s+fit|amr\s+health|amar\s+health|amar\s+shorir|shorir\s+kemon|health\s+kmn|kemon\s+achi)\b/i',
            $message
        );
    }

    /**
     * Detect simple emotional/distress expressions (English and common Banglish phrases).
     * Provides a lightweight local fallback when the AI service is unavailable.
     */
    private function isEmotionalDistressIntent(string $message): bool
    {
        return (bool) preg_match(
            '/\b(sad|depress(ed)?|i feel (bad|sad|lonely|down)|unhappy|suicid(e|al)|amr kharap|amar kharap|khara?p|dukhi|mon kharap|amar mon kharap)\b/i',
            $message
        );
    }

    /**
     * Detect short, conversational messages that don't request DB access
     * (e.g., greetings, small talk, brief Banglish phrases).
     */
    private function isShortChatIntent(string $message): bool
    {
        $trim = trim($message);
        if ($trim === '') {
            return false;
        }

        // Avoid classifying explicit personal-health intents as short chat
        if ($this->isPersonalHealthIntent($message)) {
            return false;
        }

        // Short messages under 120 chars and not containing question words
        if (mb_strlen($trim) <= 120 && !preg_match('/\b(what|why|how|when|where|who|which|do you|should i|tell me|can you|could you)\b/i', $trim)) {
            return true;
        }

        return false;
    }

    /**
     * Detect generic fitness/wellness prompts that should be answered as
     * normal LLM conversation rather than DB-centric text-to-SQL.
     */
    private function isGeneralWellnessIntent(string $message): bool
    {
        return (bool) preg_match(
            '/\b(how\s+to\s+be\s+fit|how\s+can\s+i\s+be\s+fit|be\s+fit|stay\s+fit|fit\s+hobo|fit\s+thakbo|kivabe\s+fit\s+hobo|ki\s+vabe\s+fit\s+hobo|healthy\s+lifestyle|fitness\s+tips)\b/i',
            trim($message)
        );
    }

    private function isDietIntent(string $message): bool
    {
        return (bool) preg_match(
            '/\b(diet\s+chart|diet\s+plan|meal\s+plan|food\s+plan|nutrition\s+plan|diet\s+dao|diet\s+chart\s+dao|khabar\s+chart|khabar\s+plan)\b/i',
            trim($message)
        );
    }

    /**
     * Local fallback text for wellness prompts when external LLM is down.
     */
    private function buildWellnessFallbackReply(string $message): string
    {
        $tips = [
            'Build a simple daily routine: 30 minutes of walking, consistent sleep, and regular hydration.',
            'Keep your meals balanced with vegetables, protein, and fewer sugary drinks or processed snacks.',
            'Track 1-2 weekly goals (for example, steps and sleep hours) so progress is measurable and realistic.',
        ];

        if (preg_match('/\b(kivabe|ki\s+vabe|hobo|thakbo|fit)\b/i', $message)) {
            $tips[] = 'If you want, share your current routine and I can help make a personalized 7-day fitness plan.';
        } else {
            $tips[] = 'Share your age, activity level, and current routine, and I can suggest a personalized fitness plan.';
        }

        return implode(' ', $tips);
    }

    private function buildDietFallbackReply(string $message): string
    {
        $isBanglish = (bool) preg_match('/\b(dao|khabar|chart|plan|amar|amr|kivabe|ki\s+vabe)\b/i', $message);

        $core = [
            '**Morning:** 1 glass water, oats or whole-grain bread with egg/lean protein, plus one fruit.',
            '**Lunch:** half plate vegetables, quarter plate protein (fish/chicken/lentils), quarter plate brown rice/roti.',
            '**Evening snack:** nuts or yogurt; avoid sugary drinks and deep-fried snacks.',
            '**Dinner:** lighter than lunch, include vegetables and protein; finish 2-3 hours before sleep.',
            '**Hydration:** 2-3 liters water daily and keep a fixed meal schedule.',
        ];

        if ($isBanglish) {
            $core[] = 'Chaile apnar weight, activity level, ar health condition dile ami personalized 7-day diet chart baniye dite pari.';
        } else {
            $core[] = 'Share your age, weight, activity level, and health conditions for a personalized 7-day diet chart.';
        }

        return implode(' ', $core);
    }

    /**
     * Lightweight local reply generator for short conversational messages.
     * Used as a graceful fallback when external AI is unavailable.
     */
    private function localChatFallback(string $message): string
    {
        $lower = strtolower($message);

        if ($this->isEmotionalDistressIntent($message)) {
            return "I'm sorry you're feeling this way. I'm here to listen. If you want, tell me what happened and I will try to support you step by step.";
        }

        if ($this->isDietIntent($message)) {
            return $this->buildDietFallbackReply($message);
        }

        if ($this->isGeneralWellnessIntent($message)) {
            return $this->buildWellnessFallbackReply($message);
        }

        // Simple Banglish/Bengali-friendly replies
        if (preg_match('/\b(amr|amar|amake|amar\s+mon|mon\s+kharap|dukhi|khara?p|amr\s+khara?p)\b/i', $lower)) {
            return "Ami dukkhito je apni eivabe feel korchen. Ami shuntey asi — bolte chan keno? If this is urgent, please contact local emergency services or a trusted person.";
        }

        if (preg_match('/\b(tips?\s*dao|tip\s*dao|tips?|advice|suggestion|ki\s*holo|what\s*happened)\b/i', $lower)) {
            return 'Here are quick tips: stay hydrated, sleep at a fixed time, do 20-30 minutes of daily movement, and keep meals balanced. If you want, I can make a personalized 7-day plan for you.';
        }

        if (preg_match('/\b(hi|hello|hey|thanks|thank you|bye)\b/i', $lower)) {
            if (preg_match('/\b(thanks|thank you)\b/i', $lower)) {
                return 'You\'re welcome — glad I could help.';
            }
            return 'Hello! How can I help you today?';
        }

        // Default: echo with an offer to help
        if (mb_strlen($message) < 60) {
            return 'I hear you. Tell me a bit more, and I\'ll do my best to help.';
        }

        return 'Thanks for sharing that. Could you say a little more about what you mean?';
    }

    private function isRiskIntent(string $message): bool
    {
        if (trim($message) === '') {
            return false;
        }

        $m = strtolower($message);
        $hasRisk = (bool) preg_match('/\b(risk|risks|probability|chance|likely|severity|complication|complications)\b/i', $m);
        $hasHealthTerm = (bool) preg_match('/\b(disease|diseases|symptom|symptoms|condition|health|status)\b/i', $m);

        return $hasRisk && $hasHealthTerm;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Personal health snapshot
    // ──────────────────────────────────────────────────────────────────────────

    private function getPersonalHealthSnapshot(int $userId): ?array
    {
        $connections = array_unique([
            (string) config('chatbot.read_connection', 'mysql_chatbot'),
            (string) config('database.default', 'mysql'),
        ]);

        foreach ($connections as $conn) {
            try {
                // diseases — correct column is `disease_name`
                $diseases = DB::connection($conn)
                    ->table('user_diseases as ud')
                    ->leftJoin('diseases as d', 'd.id', '=', 'ud.disease_id')
                    ->where('ud.user_id', $userId)
                    ->orderByDesc('ud.id')
                    ->limit(20)
                    ->get([
                        'd.disease_name as disease',
                        'ud.status',
                        'ud.diagnosed_at',
                        'ud.notes',
                    ])
                    ->map(fn($r) => (array) $r)
                    ->all();

                // symptoms
                $symptoms = DB::connection($conn)
                    ->table('user_symptoms as us')
                    ->leftJoin('symptoms as s', 's.id', '=', 'us.symptom_id')
                    ->where('us.user_id', $userId)
                    ->orderByDesc('us.recorded_at')
                    ->limit(20)
                    ->get([
                        's.name as symptom',
                        'us.severity_level',
                        'us.note',
                        'us.recorded_at',
                    ])
                    ->map(fn($r) => (array) $r)
                    ->all();

                // user_health + health_metrics (definition) joined
                $metrics = DB::connection($conn)
                    ->table('user_health as uh')
                    ->leftJoin('health_metrics as hm', 'hm.id', '=', 'uh.health_metric_id')
                    ->where('uh.user_id', $userId)
                    ->orderByDesc('uh.recorded_at')
                    ->limit(20)
                    ->get(['hm.metric_name as metric_type', 'uh.value', 'uh.recorded_at'])
                    ->map(function ($r): array {
                        $row = (array) $r;
                        if (is_string($row['value'])) {
                            $decoded = json_decode($row['value'], true);
                            $row['value'] = $decoded ?? $row['value'];
                        }
                        return $row;
                    })
                    ->all();

                // recent medicines
                $medicines = DB::connection($conn)
                    ->table('medicines')
                    ->where('user_id', $userId)
                    ->orderByDesc('id')
                    ->limit(10)
                    ->get(['medicine_name', 'type', 'rule', 'unit'])
                    ->map(fn($r) => (array) $r)
                    ->all();

                Log::info('Personal health snapshot fetched', [
                    'connection'     => $conn,
                    'user_id'        => $userId,
                    'diseases'       => count($diseases),
                    'symptoms'       => count($symptoms),
                    'metrics'        => count($metrics),
                    'medicines'      => count($medicines),
                ]);

                return [
                    'user_id'        => $userId,
                    'diseases'       => $diseases,
                    'symptoms'       => $symptoms,
                    'health_metrics' => $metrics,
                    'medicines'      => $medicines,
                ];
            } catch (\Throwable $e) {
                Log::warning('Personal health snapshot query failed', [
                    'connection' => $conn,
                    'user_id'    => $userId,
                    'message'    => $e->getMessage(),
                ]);
            }
        }

        return null;
    }

    /**
     * Build a direct reply when data is simple (≤6 entries total).
     * Returns null to hand off to LLM when data is richer.
     */
    private function buildPersonalHealthReply(array $snapshot, bool $forceDetailed = false): ?string
    {
        $diseases  = (array) ($snapshot['diseases'] ?? []);
        $symptoms  = (array) ($snapshot['symptoms'] ?? []);
        $metrics   = (array) ($snapshot['health_metrics'] ?? []);
        $medicines = (array) ($snapshot['medicines'] ?? []);

        $total = count($diseases) + count($symptoms) + count($metrics) + count($medicines);

        if ($total === 0) {
            return 'আপনার রেকর্ডে এখনো কোনো রোগ, উপসর্গ, স্বাস্থ্য মেট্রিক বা ওষুধের তথ্য পাওয়া যায়নি। আগে অ্যাপে স্বাস্থ্য তথ্য যোগ করুন, তারপর আবার জিজ্ঞেস করুন।';
        }

        // For rich data, let the LLM compose a better narrative unless deterministic fallback is requested.
        if ($total > 6 && !$forceDetailed) {
            return null;
        }

        $parts = [];

        if ($diseases !== []) {
            $labels = array_map(function (array $d): string {
                $nameRaw = (string) ($d['disease'] ?? 'Unknown disease');
                $name = $this->medicalNameBnWithEn($nameRaw);
                $status = (string) ($d['status'] ?? 'unknown');
                $date   = !empty($d['diagnosed_at']) ? ', diagnosed ' . $d['diagnosed_at'] : '';
                return "**{$name}** ({$status}{$date})";
            }, array_slice($diseases, 0, 3));
            $parts[] = '**রোগের অবস্থা:** ' . implode(', ', $labels) . '।';
        }

        if ($symptoms !== []) {
            $labels = array_map(function (array $s): string {
                $nameRaw = (string) ($s['symptom'] ?? 'Unknown symptom');
                $name = $this->medicalNameBnWithEn($nameRaw);
                $sev  = $s['severity_level'] ?? null;
                return $sev !== null ? "**{$name}** (তীব্রতা {$sev}/10)" : "**{$name}**";
            }, array_slice($symptoms, 0, 5));
            $parts[] = '**সাম্প্রতিক উপসর্গ:** ' . implode(', ', $labels) . '।';
        }

        if ($medicines !== []) {
            $labels = array_map(fn(array $m): string => (string) ($m['medicine_name'] ?? 'Unknown'), array_slice($medicines, 0, 5));
            $parts[] = '**ওষুধ:** ' . implode(', ', $labels) . '।';
        }

        if ($metrics !== []) {
            $labels = array_map(function (array $m): string {
                $typeRaw  = (string) ($m['metric_type'] ?? 'metric');
                $type = $this->metricNameBn($typeRaw);
                $value = $m['value'] ?? null;
                if (is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }
                $display = (is_string($value) || is_numeric($value)) ? (string) $value : 'রেকর্ড করা আছে';
                return "**{$type}**: **{$display}**";
            }, array_slice($metrics, 0, 5));
            $parts[] = '**স্বাস্থ্য মেট্রিক:** ' . implode('; ', $labels) . '।';
        }

        if ($forceDetailed) {
            $parts[] = sprintf(
                '**সারাংশ:** %dটি রোগের রেকর্ড, %dটি উপসর্গের রেকর্ড, %dটি ওষুধের রেকর্ড এবং %dটি স্বাস্থ্য মেট্রিক রেকর্ড পাওয়া গেছে।',
                count($diseases),
                count($symptoms),
                count($medicines),
                count($metrics)
            );
        }

        $parts[] = "\n*এটি আপনার সংরক্ষিত তথ্যভিত্তিক সারাংশ, চিকিৎসা নির্ণয় নয়। চিকিৎসা পরামর্শের জন্য নিবন্ধিত চিকিৎসকের সাথে যোগাযোগ করুন।*";

        return implode("\n", $parts);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Schema description — scoped to ALLOWED_TABLES, blocks sensitive columns
    // ──────────────────────────────────────────────────────────────────────────

    private function getDatabaseSchemaDescription(): string
    {
        try {
            $database = config('database.connections.' . config('database.default') . '.database');
            $tableList = implode("','", self::ALLOWED_TABLES);

            $rows = DB::select(
                "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = ?
                   AND TABLE_NAME IN ('{$tableList}')
                 ORDER BY TABLE_NAME, ORDINAL_POSITION",
                [$database]
            );

            $blocked = self::SCHEMA_BLOCKED_COLUMNS;
            $tables  = [];
            foreach ($rows as $r) {
                if (in_array(strtolower($r->COLUMN_NAME), $blocked, true)) {
                    continue;
                }
                $tables[$r->TABLE_NAME][] = $r->COLUMN_NAME . ' (' . $r->COLUMN_TYPE . ')';
            }

            $parts = [];
            foreach ($tables as $table => $cols) {
                $parts[] = "Table: {$table} => " . implode(', ', $cols);
            }

            return mb_strimwidth(implode("\n", $parts), 0, 3500, '...');
        } catch (\Throwable $e) {
            Log::warning('Failed to build DB schema description', ['message' => $e->getMessage()]);
            return '';
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // SQL generation
    // ──────────────────────────────────────────────────────────────────────────

    private function generateSqlFromMessage(
        string  $message,
        string  $schemaDesc,
        string  $apiKey,
        string  $primaryModel,
        string  $baseUrl,
        array   $models,
        string  $googleKey,
        ?int    $authUserId = null
    ): ?string {
        if ($schemaDesc === '') {
            return null;
        }

        $authContext = $authUserId !== null
            ? "Authenticated user id: {$authUserId}. Always filter personal queries using this user_id (e.g. WHERE user_id = {$authUserId})."
            : 'No authenticated user. Do not expose any user-specific data without a WHERE clause.';

        $system = 'You are a SQL generator for MySQL. Return ONLY a single valid SQL SELECT query with no explanation, no markdown, no backticks. '
                . 'Only SELECT is allowed — never INSERT, UPDATE, DELETE, DROP, ALTER, TRUNCATE. '
                . 'Always add LIMIT 50 unless the question is an aggregate (COUNT, SUM, AVG, etc.).';

        $user = "Schema:\n{$schemaDesc}\n\n{$authContext}\n\nQuestion: {$message}\n\nSQL Query:";

        // Try Google Gemini first (with model fallback)
        if ($googleKey !== '') {
            $resp = $this->googleChatWithFallback($system, $user, $googleKey, 0.0, 300);
            if ($resp !== null) {
                $text = trim(preg_replace('/^```\w*\s*|```\s*$/m', '', $resp));
                return $text !== '' ? $text : null;
            }
        }

        // Try OpenRouter models (bounded attempts to avoid long request chains).
        $attempted = 0;
        foreach ($models as $model) {
            if ($attempted >= 2) {
                break;
            }
            $attempted++;
            try {
                $response = Http::timeout(12)
                    ->withToken($apiKey)
                    ->withHeaders([
                        'HTTP-Referer' => (string) config('services.openrouter.site_url', config('app.url')),
                        'X-Title'      => (string) config('services.openrouter.app_name', config('app.name')),
                        'Content-Type' => 'application/json',
                        'Accept'       => 'application/json',
                    ])
                    ->post($baseUrl . '/chat/completions', [
                        'model'       => $model,
                        'messages'    => [
                            ['role' => 'system', 'content' => $system],
                            ['role' => 'user',   'content' => $user],
                        ],
                        'temperature' => 0.0,
                        'max_tokens'  => 300,
                    ]);

                if ($response->successful()) {
                    $text = $this->extractReplyText(data_get($response->json(), 'choices.0.message.content'));
                    if ($text !== null) {
                        $text = trim(preg_replace('/^```\w*\s*|```\s*$/m', '', $text));
                        return $text !== '' ? $text : null;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('SQL generation failed', ['model' => $model, 'message' => $e->getMessage()]);
            }
        }

        return null;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // SQL sanitizer
    // ──────────────────────────────────────────────────────────────────────────

    private function sanitizeSql(string $sql): ?string
    {
        // Strip markdown fences
        $s = trim(preg_replace('/^```\w*\s*|```\s*$/m', '', $sql));
        $s = rtrim(trim($s), ';'); // fix: was rtrim($s, '\\s') — a literal bug

        if ($s === '') {
            return null;
        }

        // Must be a SELECT
        if (!preg_match('/^\s*SELECT\b/i', $s)) {
            return null;
        }

        // Block write/DDL keywords
        $forbidden = [
            'insert', 'update', 'delete', 'drop', 'alter', 'create',
            'truncate', 'replace', 'merge', 'call', 'grant', 'revoke',
            'exec', 'execute', 'load_file', 'into outfile', 'into dumpfile',
        ];
        foreach ($forbidden as $kw) {
            if (preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $s)) {
                Log::warning('SQL sanitizer blocked forbidden keyword', ['keyword' => $kw]);
                return null;
            }
        }

        // Block direct references to sensitive tables
        foreach (['push_subscriptions', 'mailings'] as $t) {
            if (preg_match('/\b' . preg_quote($t, '/') . '\b/i', $s)) {
                Log::warning('SQL sanitizer blocked sensitive table reference', ['table' => $t]);
                return null;
            }
        }

        // Enforce row cap
        if (!preg_match('/\bLIMIT\b/i', $s)) {
            $s .= ' LIMIT 200';
        }

        return $s;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // SQL allow-list check
    // ──────────────────────────────────────────────────────────────────────────

    private function isAllowedSql(string $sql): bool
    {
        // Config can override; fall back to class constant
        $configured = (array) config('chatbot.allowed_tables', []);
        $allowed    = array_map('strtolower',
            $configured !== [] ? $configured : self::ALLOWED_TABLES
        );

        $tables = $this->extractTablesFromSql($sql);
        if ($tables === []) {
            return false;
        }

        foreach ($tables as $t) {
            if (!in_array(strtolower($t), $allowed, true)) {
                Log::warning('isAllowedSql: table not in allow-list', ['table' => $t]);
                return false;
            }
        }

        return true;
    }

    private function isSqlAuthorizedForUser(string $sql, ?int $authUserId): bool
    {
        $tables = $this->extractTablesFromSql($sql);
        $needsUserScope = collect($tables)
            ->map(fn(string $t) => strtolower($t))
            ->contains(fn(string $t) => in_array($t, self::USER_SCOPED_TABLES, true));

        if (!$needsUserScope) {
            return true;
        }

        if ($authUserId === null) {
            return false;
        }

        return (bool) preg_match(
            '/(?:\\b[a-z_][a-z0-9_]*\\.)?`?user_id`?\\s*=\\s*' . preg_quote((string) $authUserId, '/') . '\\b/i',
            $sql
        );
    }

    private function extractTablesFromSql(string $sql): array
    {
        $sql    = strtolower($sql);
        $tables = [];

        if (preg_match_all('/\bfrom\s+`?([a-z0-9_]+)`?/i', $sql, $m)) {
            foreach ($m[1] as $t) {
                $tables[] = $t;
            }
        }
        if (preg_match_all('/\bjoin\s+`?([a-z0-9_]+)`?/i', $sql, $m)) {
            foreach ($m[1] as $t) {
                $tables[] = $t;
            }
        }

        return array_values(array_unique($tables));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // SQL execution — tries read-only connection then falls back to default
    // ──────────────────────────────────────────────────────────────────────────

    private function executeSelectSql(string $sql): ?array
    {
        $preferred = (string) config('chatbot.read_connection', 'mysql_chatbot');
        $fallback  = (string) config('database.default', 'mysql');

        foreach (array_unique([$preferred, $fallback]) as $conn) {
            try {
                $rows = DB::connection($conn)->select(DB::raw($sql));
                return array_map(static fn($r) => (array) $r, $rows);
            } catch (\Throwable $e) {
                Log::warning('SQL execution failed', [
                    'connection' => $conn,
                    'sql'        => $sql,
                    'message'    => $e->getMessage(),
                ]);
            }
        }

        return null;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // LLM result summarisation
    // ──────────────────────────────────────────────────────────────────────────

    private function askModelToSummarizeResults(
        string $question,
        string $sql,
        array  $results,
        string $apiKey,
        string $primaryModel,
        string $baseUrl,
        array  $models,
        string $googleKey = ''
    ): ?string {
        $json   = json_encode(array_slice($results, 0, 50), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $system = 'You are MyDoctor AI assistant. The user has asked about their own health data. '
                . 'Use ONLY the provided JSON data to answer. Do not ask clarifying questions — the data is already here. '
            . 'Do not reveal or infer data about any other users. '
            . 'Format output with a short title, bold labels, and bullet points when appropriate. '
                . 'Do not write numbered tip labels like Tip 1/Tip 2; use natural bullet suggestions. '
                . 'Provide personalized suggestions and tips based on the data, and vary phrasing/points between requests so responses are not identical each time. '
                . 'If the JSON is empty, say no matching records were found. '
                . 'Be concise, friendly, and remind the user this is not a medical diagnosis.';
        $user   = "Question: {$question}\n\nData source: {$sql}\n\nData:\n{$json}\n\nAnswer:";

        // Try Google Gemini first (with model fallback)
        if ($googleKey !== '') {
            $resp = $this->googleChatWithFallback($system, $user, $googleKey, 0.2, 500);
            if ($resp !== null) {
                return $this->extractReplyText($resp);
            }
        }

        // Try OpenRouter models
        foreach ($models as $model) {
            try {
                $response = Http::timeout(30)
                    ->withToken($apiKey)
                    ->withHeaders([
                        'HTTP-Referer' => (string) config('services.openrouter.site_url', config('app.url')),
                        'X-Title'      => (string) config('services.openrouter.app_name', config('app.name')),
                        'Content-Type' => 'application/json',
                        'Accept'       => 'application/json',
                    ])
                    ->post($baseUrl . '/chat/completions', [
                        'model'       => $model,
                        'messages'    => [
                            ['role' => 'system', 'content' => $system],
                            ['role' => 'user',   'content' => $user],
                        ],
                        'temperature' => 0.2,
                        'max_tokens'  => 500,
                    ]);

                if ($response->successful()) {
                    $reply = $this->extractReplyText(data_get($response->json(), 'choices.0.message.content'));
                    if ($reply !== null) {
                        return $reply;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Summarize results failed', ['model' => $model, 'message' => $e->getMessage()]);
            }
        }

        return null;
    }

    /**
     * Generate 4-5 personalized smart suggestions from user records.
     */
    public function smartSuggestions(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json(['message' => 'Authentication required.'], 401);
        }

        if ($user->isAdmin()) {
            return response()->json(['message' => 'Chatbot is disabled for admin accounts.'], 403);
        }

        $snapshot = $this->getPersonalHealthSnapshot((int) $user->id);
        if ($snapshot === null) {
            return response()->json(['message' => 'Could not access user records right now.'], 502);
        }

        $apiKey    = (string) config('services.openrouter.api_key', '');
        $googleKey = (string) config('services.google.api_key', '');

        if ($apiKey === '' && $googleKey === '') {
            return response()->json(['message' => 'AI service is not configured yet.'], 503);
        }

        $baseUrl      = rtrim((string) config('services.openrouter.base_url', 'https://openrouter.ai/api/v1'), '/');
        $primaryModel = (string) config('services.openrouter.model', 'google/gemini-2.0-flash-001');
        $fallbackModels = array_filter(array_merge(
            (array) config('services.openrouter.fallback_models', []),
            [
                config('services.openrouter.fallback_model_1'),
                config('services.openrouter.fallback_model_2'),
                config('services.openrouter.fallback_model_3'),
            ]
        ));

        $models = collect(array_merge([$primaryModel], $fallbackModels))
            ->filter(fn($m) => is_string($m) && trim($m) !== '')
            ->map(fn($m) => trim($m))
            ->unique()
            ->values()
            ->all();
        $models = $this->sanitizeOpenRouterModels($models);

        $parsed = $this->generateLlmSmartSuggestions($snapshot, $apiKey, $googleKey, $baseUrl, $models);
        if ($parsed === []) {
            return response()->json(['message' => 'AI suggestions could not be generated right now.'], 502);
        }

        return response()->json([
            'suggestions' => $parsed,
            'source' => 'llm',
        ]);
    }

    /**
     * Gather a broad set of current user's records and ask the LLM to produce
     * an "About me" summary strictly from those records.
     */
    public function aboutMe(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json(['reply' => $this->formatStructuredReply('Authentication required.')], 401);
        }

        if ($user->isAdmin()) {
            return response()->json(['reply' => 'Chatbot is disabled for admin accounts.'], 403);
        }

        $userId = $user->id;
        $userEmail = $user->email ?? '';
        // Use the existing schema-aware snapshot function to collect personal records.
        $snapshot = $this->getPersonalHealthSnapshot($userId);
        if ($snapshot === null) {
            return response()->json(['reply' => $this->formatStructuredReply('I could not access your records right now. Please try again later.')], 502);
        }

        // Describe the representative queries used to fetch the snapshot so the LLM knows the data provenance.
        $queries = [
            "SELECT d.disease_name as disease, ud.status, ud.diagnosed_at, ud.notes FROM user_diseases ud LEFT JOIN diseases d ON d.id = ud.disease_id WHERE ud.user_id = {$userId} ORDER BY ud.id DESC LIMIT 20",
            "SELECT s.name as symptom, us.severity_level, us.note, us.recorded_at FROM user_symptoms us LEFT JOIN symptoms s ON s.id = us.symptom_id WHERE us.user_id = {$userId} ORDER BY us.recorded_at DESC LIMIT 20",
            "SELECT hm.metric_name as metric_type, uh.value, uh.recorded_at FROM user_health uh LEFT JOIN health_metrics hm ON hm.id = uh.health_metric_id WHERE uh.user_id = {$userId} ORDER BY uh.recorded_at DESC LIMIT 20",
            "SELECT medicine_name, type, rule, unit FROM medicines WHERE user_id = {$userId} ORDER BY id DESC LIMIT 10",
        ];

        $sqlSource = implode("\n", $queries);

        $question = "আমার রোগ, উপসর্গ এবং বর্তমান স্বাস্থ্য অবস্থার ঝুঁকি বিশ্লেষণসহ সংক্ষিপ্ত পরামর্শ দিন।\n"
            . "শুধু আমার authenticated রেকর্ড ব্যবহার করবেন, অন্য কারও তথ্য নয়।\n"
            . "Current user id: {$userId}\n"
            . "Current user email: {$userEmail}\n"
            . "প্রথমে আমার রোগ নিয়ে ২-৩ লাইন, তারপর উপসর্গ ও ট্রেন্ড নিয়ে ৩-৪ লাইন লিখুন।\n"
            . "এরপর আমার রোগ, উপসর্গ ও স্বাস্থ্য মেট্রিক অনুযায়ী ঠিক ৪-৫টি ব্যক্তিগত পরামর্শ দিন।";

        // Enforce strict output formatting instructions for the LLM to follow.
        $formatInstruction = "\n\nকঠোর ফরম্যাট নির্দেশনা:"
            . "\n- পুরো উত্তর অবশ্যই বাংলায় লিখতে হবে।"
            . "\n- শুরুতে একটি ছোট শিরোনাম দিন।"
            . "\n- **রোগের অবস্থা** শিরোনামে রোগভিত্তিক ২-৩টি বুলেট দিন।"
            . "\n- **উপসর্গ ও মেট্রিক প্রবণতা** শিরোনামে উপসর্গ/মেট্রিকভিত্তিক ২-৩টি বুলেট দিন।"
            . "\n- **স্মার্ট পরামর্শ** শিরোনামে ঠিক ৪ বা ৫টি বুলেট পয়েন্ট দিন।"
            . "\n- পরামর্শগুলো অবশ্যই ব্যবহারযোগ্য, ব্যক্তিগত এবং আমার ডেটা-ভিত্তিক হবে।"
            . "\n- Tip 1/Tip 2 ধরনের নম্বরিং ব্যবহার করবেন না।"
            . "\n- প্রয়োজনমতো markdown bold (**...**) ব্যবহার করতে পারেন।"
            . "\n- ভাষা সংক্ষিপ্ত, পরিষ্কার ও কার্যকর রাখুন।"
            . "\nঅন্য কোনো ব্যবহারকারীর তথ্য যুক্ত করবেন না।";

        $question = $question . $formatInstruction;

        $apiKey    = (string) config('services.openrouter.api_key', '');
        $googleKey = (string) config('services.google.api_key', '');

        // If AI keys are not configured, return a deterministic local summary
        // built from the user's snapshot and include fallback suggestions so
        // the UI still shows a helpful health summary.
        if ($apiKey === '' && $googleKey === '') {
            try {
                $local = $this->buildPersonalHealthReply($snapshot, true);
                $reply = $local ?? 'এই মুহূর্তে সারাংশ তৈরি করা যায়নি।';
                $fallback = $this->buildFallbackSmartSuggestions($snapshot);
                if (count($fallback) >= 4) {
                    $reply = $this->appendSmartSuggestionsSection($reply, array_slice($fallback, 0, 4));
                }
                return response()->json(['reply' => $reply]);
            } catch (\Throwable $e) {
                Log::warning('AboutMe local summary failed when AI keys missing', ['message' => $e->getMessage()]);
                return response()->json([ 'reply' => $this->formatStructuredReply('AI service is not configured yet. Please set OPENROUTER_API_KEY or GOOGLE_API_KEY in your .env file.') ], 503);
            }
        }

        $baseUrl      = rtrim((string) config('services.openrouter.base_url', 'https://openrouter.ai/api/v1'), '/');
        $primaryModel = (string) config('services.openrouter.model', 'google/gemini-2.0-flash-001');
        $fallbackModels = array_filter(array_merge(
            (array) config('services.openrouter.fallback_models', []),
            [
                config('services.openrouter.fallback_model_1'),
                config('services.openrouter.fallback_model_2'),
                config('services.openrouter.fallback_model_3'),
            ]
        ));

        $models = collect(array_merge([$primaryModel], $fallbackModels))
            ->filter(fn($m) => is_string($m) && trim($m) !== '')
            ->map(fn($m) => trim($m))
            ->unique()
            ->values()
            ->all();
        $models = $this->sanitizeOpenRouterModels($models);

        $final = $this->askModelToSummarizeResults(
            $question,
            $sqlSource,
            $snapshot,
            $apiKey,
            $primaryModel,
            $baseUrl,
            $models,
            $googleKey
        );

        Log::info('aboutMe LLM summary result', [
            'user_id' => $userId,
            'has_final' => $final !== null,
            'final_length' => is_string($final) ? strlen($final) : 0,
        ]);

        if ($final !== null) {
            // For Suggestions page summary, return the model output directly so
            // the requested sections are shown without generic wrappers.
            $reply = $this->normalizeBanglaSummaryHeadings($this->cleanAboutMeResponse($final));
            $reply = $this->normalizeMedicalTermsInBanglaText($reply);
            $llmSuggestions = $this->generateLlmSmartSuggestions($snapshot, $apiKey, $googleKey, $baseUrl, $models);
            if (count($llmSuggestions) < 4) {
                $llmSuggestions = $this->buildFallbackSmartSuggestions($snapshot);
            }
            $reply = $this->appendSmartSuggestionsSection($reply, array_slice($llmSuggestions, 0, 4));
            return response()->json(['reply' => $reply]);
        }

        // If the LLM failed, fall back to a deterministic local summary built
        // from the user's snapshot so the UI shows helpful information.
        try {
            $local = $this->buildPersonalHealthReply($snapshot, true);
            if ($local !== null) {
                $reply = $local;
                // Keep fallback fast/reliable when LLM summary already failed.
                $llmSuggestions = $this->buildFallbackSmartSuggestions($snapshot);
                $reply = $this->appendSmartSuggestionsSection($reply, array_slice($llmSuggestions, 0, 4));
                return response()->json(['reply' => $reply]);
            }
        } catch (\Throwable $e) {
            Log::warning('AboutMe local fallback failed', ['message' => $e->getMessage()]);
        }

        return response()->json([
            'reply' => 'AI summary could not be generated right now. Please regenerate in a moment.',
        ], 502);
    }

    /**
     * Remove prompt/instruction echoes from model output so UI shows only the
     * generated health response content.
     */
    private function cleanAboutMeResponse(string $text): string
    {
        $lines = preg_split('/\r?\n/', trim($text)) ?: [];
        $filtered = [];

        foreach ($lines as $line) {
            $t = trim($line);
            if ($t === '') {
                $filtered[] = $line;
                continue;
            }

            $lower = strtolower($t);
            if (str_starts_with($lower, 'we need to produce answer')) {
                continue;
            }
            if (str_contains($lower, 'strict format required')) {
                continue;
            }
            if (str_contains($lower, 'use only my current authenticated records')) {
                continue;
            }
            if (str_contains($lower, 'current user id:')) {
                continue;
            }
            if (str_contains($lower, 'current user email:')) {
                continue;
            }

            $filtered[] = $line;
        }

        $clean = trim(implode("\n", $filtered));
        return $clean !== '' ? $clean : trim($text);
    }

    private function parseSmartSuggestionsFromModel(string $raw): array
    {
        $text = trim($raw);

        $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', (string) $text);

        $start = strpos($text, '[');
        $end = strrpos($text, ']');
        if ($start === false || $end === false || $end <= $start) {
            return [];
        }

        $json = substr($text, $start, $end - $start + 1);
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return [];
        }

        $allowedCategories = ['Metric Alert', 'Adherence', 'Symptom', 'Condition', 'Lifestyle', 'Wellness'];
        $allowedColors = ['danger', 'warning', 'info', 'success', 'primary'];

        $normalized = [];
        foreach ($decoded as $item) {
            if (!is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            $message = trim((string) ($item['message'] ?? ''));
            $category = trim((string) ($item['category'] ?? 'Wellness'));
            $color = trim((string) ($item['color'] ?? 'primary'));
            $icon = trim((string) ($item['icon'] ?? 'fa-lightbulb'));

            if ($title === '' || $message === '') {
                continue;
            }

            if (!in_array($category, $allowedCategories, true)) {
                $category = 'Wellness';
            }
            if (!in_array($color, $allowedColors, true)) {
                $color = 'primary';
            }
            if ($icon === '') {
                $icon = 'fa-lightbulb';
            }

            $title = $this->normalizeBanglaSuggestionTitle($title);
            $message = $this->normalizeMedicalTermsInBanglaText($message);
            $message = $this->enhanceBoldingInBanglaText($message);

            $normalized[] = [
                'title' => $title,
                'message' => $message,
                'category' => $category,
                'color' => $color,
                'icon' => $icon,
            ];
        }

        if (count($normalized) < 4) {
            return [];
        }

        return array_slice($normalized, 0, 5);
    }

    private function generateLlmSmartSuggestions(
        array $snapshot,
        string $apiKey,
        string $googleKey,
        string $baseUrl,
        array $models
    ): array {
        $json = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $system = 'You are MyDoctor AI assistant for Bangla output. '
            . 'Use ONLY the provided JSON user records. '
            . 'Return STRICT JSON only: an array of 4 or 5 objects. '
            . 'Each object must include: title, message, category, color, icon. '
            . 'title and message MUST be in Bangla (Bengali). '
            . 'Medical condition names may remain in English if needed, but sentence structure must be Bangla. '
            . 'message must be practical and personalized; include markdown bold where useful using **text**. '
            . 'category must be one of: Metric Alert, Adherence, Symptom, Condition, Lifestyle, Wellness. '
            . 'color must be one of: danger, warning, info, success, primary. '
            . 'icon must be a Font Awesome icon class name like fa-heartbeat or fa-pills. '
            . 'Do NOT use numbered labels like Tip 1/Tip 2. '
            . 'Do NOT include any markdown wrapper or explanation outside JSON.';
        $userPrompt = "ব্যবহারকারীর ডেটা JSON:\n{$json}\n\nএখন শুধু JSON array রিটার্ন করুন।";

        $modelRaw = null;

        if ($googleKey !== '') {
            $resp = $this->googleChatWithFallback($system, $userPrompt, $googleKey, 0.3, 700);
            if ($resp !== null) {
                $modelRaw = $resp;
            }
        }

        if ($modelRaw === null) {
            $attempted = 0;
            foreach ($models as $model) {
                if ($attempted >= 2) {
                    break;
                }
                $attempted++;
                try {
                    $response = Http::timeout(10)
                        ->withToken($apiKey)
                        ->withHeaders([
                            'HTTP-Referer' => (string) config('services.openrouter.site_url', config('app.url')),
                            'X-Title'      => (string) config('services.openrouter.app_name', config('app.name')),
                            'Content-Type' => 'application/json',
                            'Accept'       => 'application/json',
                        ])
                        ->post($baseUrl . '/chat/completions', [
                            'model'       => $model,
                            'messages'    => [
                                ['role' => 'system', 'content' => $system],
                                ['role' => 'user', 'content' => $userPrompt],
                            ],
                            'temperature' => 0.3,
                            'max_tokens'  => 700,
                        ]);

                    if ($response->successful()) {
                        $reply = $this->extractReplyText(data_get($response->json(), 'choices.0.message.content'));
                        if ($reply !== null) {
                            $modelRaw = $reply;
                            break;
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('Smart suggestions model call failed', [
                        'model' => $model,
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        }

        if ($modelRaw === null) {
            return [];
        }

        return $this->parseSmartSuggestionsFromModel($modelRaw);
    }

    private function appendSmartSuggestionsSection(string $summaryText, array $suggestions): string
    {
        $content = trim($summaryText);
        $content = preg_replace('/\n(?:\*\*)?(?:Smart Suggestions|স্মার্ট পরামর্শ)(?:\*\*)?\s*:?\s*[\s\S]*$/iu', '', $content) ?? $content;
        $content = trim($content);

        $bullets = [];
        foreach ($suggestions as $s) {
            $title = trim((string) ($s['title'] ?? 'পরামর্শ'));
            $message = trim((string) ($s['message'] ?? ''));
            if ($title === '' || $message === '') {
                continue;
            }
            $bullets[] = "- **{$title}**: {$message}";
        }

        if (count($bullets) < 4) {
            return $content;
        }

        return $content . "\n\n**স্মার্ট পরামর্শ**\n" . implode("\n", array_slice($bullets, 0, 4));
    }

    private function normalizeBanglaSummaryHeadings(string $text): string
    {
        $normalized = trim($text);

        $map = [
            '/\*\*\s*Your Health Overview\s*\*\*/i' => '**আপনার স্বাস্থ্য প্রোফাইল**',
            '/^\s*Your Health Overview\s*:?\s*$/im' => 'আপনার স্বাস্থ্য প্রোফাইল',
            '/\*\*\s*Diseases\s*\*\*/i' => '**রোগের অবস্থা**',
            '/^\s*Diseases\s*:?\s*$/im' => 'রোগের অবস্থা',
            '/\*\*\s*Symptoms\s+and\s+Metrics\s+Trend\s*\*\*/i' => '**উপসর্গ ও মেট্রিক প্রবণতা**',
            '/^\s*Symptoms\s+and\s+Metrics\s+Trend\s*:?\s*$/im' => 'উপসর্গ ও মেট্রিক প্রবণতা',
            '/\*\*\s*Smart Suggestions\s*\*\*/i' => '**স্মার্ট পরামর্শ**',
            '/^\s*Smart Suggestions\s*:?\s*$/im' => 'স্মার্ট পরামর্শ',
            '/\*\*\s*Advice based on weather([^*]*)\*\*/i' => '**আবহাওয়া ভিত্তিক পরামর্শ$1**',
            '/^\s*Advice based on weather(.*)$/im' => 'আবহাওয়া ভিত্তিক পরামর্শ$1',
        ];

        foreach ($map as $pattern => $replacement) {
            $normalized = preg_replace($pattern, $replacement, $normalized) ?? $normalized;
        }

        return $normalized;
    }

    private function medicalNameBnWithEn(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return 'অজানা';
        }

        if (preg_match('/[\x{0980}-\x{09FF}]/u', $name)) {
            return $name;
        }

        $map = [
            "Cushing's Syndrome" => 'কুশিংস সিন্ড্রোম',
            'Atrial Fibrillation' => 'এট্রিয়াল ফাইব্রিলেশন',
            'Chickenpox' => 'চিকেনপক্স',
            'Productive Cough' => 'কফসহ কাশি',
            'Irregular Menstruation' => 'অনিয়মিত মাসিক',
            'Hair Loss' => 'চুল পড়া',
            'Dry Mouth' => 'মুখ শুকানো',
            'Spinning Sensation' => 'মাথা ঘোরা',
            'Dizziness' => 'মাথা ঘোরা',
            'Fever' => 'জ্বর',
        ];

        foreach ($map as $en => $bn) {
            if (strcasecmp($name, $en) === 0) {
                return "{$bn} ({$en})";
            }
        }

        return $name;
    }

    private function metricNameBn(string $metric): string
    {
        $metric = trim($metric);
        $map = [
            'heart_rate' => 'হার্ট রেট (heart_rate)',
            'blood_pressure' => 'রক্তচাপ (blood_pressure)',
            'blood_glucose' => 'রক্তে শর্করা (blood_glucose)',
            'body_weight' => 'ওজন (body_weight)',
            'temperature' => 'তাপমাত্রা (temperature)',
            'oxygen_saturation' => 'অক্সিজেন স্যাচুরেশন (oxygen_saturation)',
            'cholesterol' => 'কোলেস্টেরল (cholesterol)',
            'creatinine' => 'ক্রিয়েটিনিন (creatinine)',
            'hemoglobin' => 'হিমোগ্লোবিন (hemoglobin)',
        ];

        $key = strtolower($metric);
        return $map[$key] ?? $metric;
    }

    private function normalizeBanglaSuggestionTitle(string $title): string
    {
        $title = trim($title);
        $map = [
            'High Heart Rate Detected' => 'উচ্চ হার্ট রেট শনাক্ত',
            'Productive Cough Reported' => 'কফসহ কাশি রিপোর্ট হয়েছে',
            'Atrial Fibrillation Management' => 'এট্রিয়াল ফাইব্রিলেশন ব্যবস্থাপনা',
            'Medication Reminder' => 'ওষুধ গ্রহণের রিমাইন্ডার',
            'Elevated Body Temperature' => 'উচ্চ তাপমাত্রা শনাক্ত',
            'Abnormal Hemoglobin Level' => 'অস্বাভাবিক হিমোগ্লোবিন মাত্রা',
        ];

        foreach ($map as $en => $bn) {
            if (strcasecmp($title, $en) === 0) {
                return $bn;
            }
        }

        return $title;
    }

    private function normalizeMedicalTermsInBanglaText(string $text): string
    {
        $map = [
            "Cushing's Syndrome" => "কুশিংস সিন্ড্রোম (Cushing's Syndrome)",
            'Atrial Fibrillation' => 'এট্রিয়াল ফাইব্রিলেশন (Atrial Fibrillation)',
            'Chickenpox' => 'চিকেনপক্স (Chickenpox)',
            'Productive Cough' => 'কফসহ কাশি (Productive Cough)',
            'Irregular Menstruation' => 'অনিয়মিত মাসিক (Irregular Menstruation)',
            'Hair Loss' => 'চুল পড়া (Hair Loss)',
            'Dry Mouth' => 'মুখ শুকানো (Dry Mouth)',
            'Spinning Sensation' => 'মাথা ঘোরা (Spinning Sensation)',
        ];

        foreach ($map as $en => $bnEn) {
            $pattern = '/\b' . preg_quote($en, '/') . '\b/u';
            $text = preg_replace($pattern, $bnEn, $text) ?? $text;
        }

        return $text;
    }

    private function enhanceBoldingInBanglaText(string $text): string
    {
        $text = preg_replace('/\b(\d+(?:\.\d+)?)\s*(bpm|°C|mmHg|mg\/dL|g\/dL|%)\b/u', '**$1 $2**', $text) ?? $text;
        $text = preg_replace('/\b(heart_rate|blood_pressure|blood_glucose|body_weight|temperature|oxygen_saturation|cholesterol|creatinine|hemoglobin)\b/u', '**$1**', $text) ?? $text;
        return $text;
    }

    private function buildFallbackSmartSuggestions(array $snapshot): array
    {
        $suggestions = [];

        $diseases = array_slice((array) ($snapshot['diseases'] ?? []), 0, 2);
        $symptoms = array_slice((array) ($snapshot['symptoms'] ?? []), 0, 3);
        $medicines = array_slice((array) ($snapshot['medicines'] ?? []), 0, 2);
        $metrics = array_slice((array) ($snapshot['health_metrics'] ?? []), 0, 2);

        if ($diseases !== []) {
            $names = implode(', ', array_map(fn($d) => $this->medicalNameBnWithEn((string) ($d['disease'] ?? 'অজানা')), $diseases));
            $suggestions[] = [
                'title' => 'রোগভিত্তিক ফলো-আপ',
                'message' => "আপনার রেকর্ড অনুযায়ী **{$names}** নিয়মিত পর্যবেক্ষণে রাখা প্রয়োজন। উপসর্গের পরিবর্তন নোট করে পরবর্তী ফলো-আপে চিকিৎসককে জানান।",
                'category' => 'Condition',
                'color' => 'warning',
                'icon' => 'fa-notes-medical',
            ];
        }

        if ($symptoms !== []) {
            $names = implode(', ', array_map(fn($s) => $this->medicalNameBnWithEn((string) ($s['symptom'] ?? 'উপসর্গ')), $symptoms));
            $suggestions[] = [
                'title' => 'প্রতিদিন উপসর্গ ট্র্যাক করুন',
                'message' => "সাম্প্রতিক উপসর্গের মধ্যে **{$names}** রয়েছে। সময় ও তীব্রতা নিয়মিত লিখে রাখলে ট্রিগার এবং উন্নতির ধারা বোঝা সহজ হবে।",
                'category' => 'Symptom',
                'color' => 'danger',
                'icon' => 'fa-thermometer-half',
            ];
        }

        if ($medicines !== []) {
            $names = implode(', ', array_map(fn($m) => (string) ($m['medicine_name'] ?? 'ওষুধ'), $medicines));
            $suggestions[] = [
                'title' => 'ওষুধ গ্রহণে ধারাবাহিকতা',
                'message' => "আপনি **{$names}** সেবন করছেন। রিমাইন্ডার ও নির্দিষ্ট রুটিন মেনে চললে ডোজ মিস হওয়ার ঝুঁকি কমবে।",
                'category' => 'Adherence',
                'color' => 'info',
                'icon' => 'fa-pills',
            ];
        }

        if ($metrics !== []) {
            $names = implode(', ', array_map(fn($m) => (string) ($m['metric_type'] ?? 'মেট্রিক'), $metrics));
            $suggestions[] = [
                'title' => 'গুরুত্বপূর্ণ মেট্রিক পর্যবেক্ষণ',
                'message' => "**{$names}** সহ গুরুত্বপূর্ণ মেট্রিক নির্দিষ্ট সময়সূচিতে মাপলে পরিবর্তন দ্রুত ধরা যায়।",
                'category' => 'Metric Alert',
                'color' => 'primary',
                'icon' => 'fa-chart-line',
            ];
        }

        $suggestions[] = [
            'title' => 'দৈনিক পুনরুদ্ধার রুটিন',
            'message' => '**নিয়মিত ঘুম, পর্যাপ্ত পানি, হালকা ব্যায়াম ও মানসিক চাপ নিয়ন্ত্রণ** দীর্ঘমেয়াদে বেশিরভাগ অবস্থায় স্থিতি উন্নত করে।',
            'category' => 'Lifestyle',
            'color' => 'success',
            'icon' => 'fa-leaf',
        ];

        while (count($suggestions) < 4) {
            $suggestions[] = [
                'title' => 'প্রতিরোধমূলক ফলো-আপ স্মরণ',
                'message' => 'সাম্প্রতিক রেকর্ড পর্যালোচনার জন্য নিয়মিত ফলো-আপ পরিকল্পনা করুন, যাতে চিকিৎসকের সাথে নিরাপদভাবে কেয়ার প্ল্যান আপডেট করা যায়।',
                'category' => 'Wellness',
                'color' => 'primary',
                'icon' => 'fa-user-md',
            ];
        }

        return array_slice($suggestions, 0, 5);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Google Gemini v1beta REST API
    // ──────────────────────────────────────────────────────────────────────────

    private function googleChatRequest(
        string $system,
        string $user,
        string $apiKey,
        string $model = 'gemini-1.5-flash',
        float  $temperature = 0.2,
        int    $maxTokens = 500
    ): ?string {
        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $body = [
                'system_instruction' => [
                    'parts' => [['text' => $system]],
                ],
                'contents' => [
                    [
                        'role'  => 'user',
                        'parts' => [['text' => $user]],
                    ],
                ],
                'generationConfig' => [
                    'temperature'     => $temperature,
                    'maxOutputTokens' => $maxTokens,
                ],
            ];

            $resp = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json', 'Accept' => 'application/json'])
                ->post($url, $body);

            if (!$resp->successful()) {
                Log::warning('Google Gemini API error', [
                    'status' => $resp->status(),
                    'body'   => mb_strimwidth($resp->body(), 0, 500),
                ]);
                return null;
            }

            $json = $resp->json();
            $text = data_get($json, 'candidates.0.content.parts.0.text');

            if (!is_string($text) || trim($text) === '') {
                if (data_get($json, 'candidates.0.finishReason') === 'SAFETY') {
                    return 'I cannot answer that due to safety guidelines.';
                }
                Log::warning('Gemini returned empty text', ['response' => $json]);
                return null;
            }

            return trim($text);
        } catch (\Throwable $e) {
            Log::warning('Google Gemini request failed', ['message' => $e->getMessage()]);
            return null;
        }
    }

    private function googleChatWithFallback(
        string $system,
        string $user,
        string $apiKey,
        float $temperature = 0.2,
        int $maxTokens = 500
    ): ?string {
        $primary = (string) config('services.google.model', 'gemini-1.5-flash');
        $fallbacks = (array) config('services.google.fallback_models', []);

        $models = collect(array_merge([$primary], $fallbacks))
            ->filter(fn($m) => is_string($m) && trim($m) !== '')
            ->map(fn($m) => trim($m))
            ->unique()
            ->values()
            ->all();

        foreach ($models as $model) {
            $resp = $this->googleChatRequest($system, $user, $apiKey, $model, $temperature, $maxTokens);
            if ($resp !== null) {
                return $resp;
            }
        }

        return null;
    }

    /**
     * Remove known invalid/deprecated OpenRouter model IDs and ensure a safe fallback set.
     */
    private function sanitizeOpenRouterModels(array $models): array
    {
        $blocked = [
            'qwen/qwen3-6b-instruct:free',
            'qwen/qwen3.6-plus:free',
        ];

        $clean = collect($models)
            ->filter(fn($m) => is_string($m) && trim($m) !== '')
            ->map(fn($m) => trim($m))
            ->reject(fn($m) => in_array(strtolower($m), $blocked, true))
            ->reject(fn($m) => str_ends_with(strtolower($m), ':free'))
            ->unique()
            ->values()
            ->all();

        if ($clean !== []) {
            return $clean;
        }

        return [
            'google/gemini-2.0-flash-001',
            'openai/gpt-4o-mini',
            'anthropic/claude-3.5-haiku',
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Utility
    // ──────────────────────────────────────────────────────────────────────────

    private function extractReplyText(mixed $content): ?string
    {
        if (is_string($content)) {
            $text = trim($content);
            return $text !== '' ? $text : null;
        }

        if (!is_array($content)) {
            return null;
        }

        $parts = [];
        foreach ($content as $item) {
            if (is_string($item) && trim($item) !== '') {
                $parts[] = trim($item);
                continue;
            }
            if (is_array($item)) {
                $text = data_get($item, 'text');
                if (is_string($text) && trim($text) !== '') {
                    $parts[] = trim($text);
                }
            }
        }

        return $parts !== [] ? implode("\n", $parts) : null;
    }

    private function isUnauthorizedDataRequest(string $message): bool
    {
        return (bool) preg_match(
            '/\b(other users?|another user|all users?|everyone|all accounts?|passwords?|tokens?|api keys?|secret|private key|auth token|emails?|phone numbers?|addresses?)\b/i',
            $message
        );
    }

    private function formatStructuredReply(string $reply): string
    {
        $text = trim($reply);
        if ($text === '') {
            $text = 'I could not generate a response right now.';
        }

        // If the LLM already provided structured sections for Suggestions or Tips,
        // prefer the LLM output intact so suggestions/tips come from the model.
        $lower = strtolower($text);
        if (str_contains($lower, 'to do:') || str_contains($lower, 'not to do:') || str_contains($lower, '**suggestions**') || str_contains($lower, '**tips**') || str_contains($lower, 'overall condition')) {
            // Ensure there's a top-level heading for consistency
            if (!preg_match('/^##\s+MyDoctor\s+AI\s+Response/i', $text)) {
                return "## MyDoctor AI Response\n\n" . $text;
            }
            return $text;
        }

        $sentences = array_values(array_filter(array_map('trim', preg_split('/(?<=[.!?])\s+/', $text) ?: [])));
        if ($sentences === []) {
            $sentences = [$text];
        }

        $items = array_slice($sentences, 0, 6);
        if ($items === []) {
            $items = [$text];
        }

        $output = [
            '## MyDoctor AI Response',
            '',
            '**Response**',
        ];

        foreach ($items as $item) {
            $output[] = '- ' . $item;
        }

        return implode("\n", $output);
    }
}
