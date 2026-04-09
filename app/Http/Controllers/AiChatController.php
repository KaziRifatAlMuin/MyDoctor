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
        'environments',
        'environment_metrics',
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
        'health_metrics',
        'environments',
        'environment_metrics',
        'user_symptoms',
        'user_diseases',
        'uploads',
        'notifications',
    ];

    public function message(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message'           => ['required', 'string', 'max:1000'],
            'history'           => ['nullable', 'array', 'max:12'],
            'history.*.role'    => ['required_with:history', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:1000'],
        ]);

        $authUserId = $request->user()?->id;

        if ($this->isUnauthorizedDataRequest($validated['message'])) {
            return response()->json([
                'reply' => $this->formatStructuredReply(
                    "I can't share other users' information or sensitive account data. I can only discuss your own records."
                ),
            ], 403);
        }

        $apiKey    = (string) config('services.openrouter.api_key', '');
        $googleKey = (string) (env('GOOGLE_API_KEY', '') ?: config('services.google.api_key', ''));

        // Personal health queries must still work from DB even when external AI is down.
        if ((bool) config('chatbot.enable_text_to_sql', true)) {
            try {
                if ($authUserId !== null && $this->isPersonalHealthIntent($validated['message'])) {
                    $snapshot = $this->getPersonalHealthSnapshot($authUserId);
                    if ($snapshot !== null) {
                        $directReply = $this->buildPersonalHealthReply($snapshot);
                        if ($directReply !== null) {
                            return response()->json(['reply' => $this->formatStructuredReply($directReply)]);
                        }

                        // Rich data: try LLM summary if possible, otherwise always fall back to local summary.
                        if ($apiKey !== '' || $googleKey !== '') {
                            $baseUrl = rtrim((string) config('services.openrouter.base_url', 'https://openrouter.ai/api/v1'), '/');
                            $primaryModel = (string) config('services.openrouter.model', 'qwen/qwen3-6b-instruct:free');
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

                            if ($models !== []) {
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
                                    return response()->json(['reply' => $this->formatStructuredReply($final)]);
                                }
                            }
                        }

                        return response()->json(['reply' => $this->formatStructuredReply($this->buildPersonalHealthReply($snapshot, true))]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Personal health fast-path failed', [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);
            }
        }

        if ($apiKey === '' && $googleKey === '') {
            return response()->json([
                'reply' => $this->formatStructuredReply('AI service is not configured yet. Please set OPENROUTER_API_KEY or GOOGLE_API_KEY in your .env file.'),
            ], 503);
        }

        $baseUrl      = rtrim((string) config('services.openrouter.base_url', 'https://openrouter.ai/api/v1'), '/');
        $primaryModel = (string) config('services.openrouter.model', 'qwen/qwen3-6b-instruct:free');
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

        if ($models === []) {
            return response()->json([
                'reply' => $this->formatStructuredReply('AI service models are not configured.'),
            ], 503);
        }

        // ── Text-to-SQL / RAG pipeline ────────────────────────────────────────
        if ((bool) config('chatbot.enable_text_to_sql', true)) {
            try {
                // Fast-path: deterministic personal health snapshot
                if ($authUserId !== null && $this->isPersonalHealthIntent($validated['message'])) {
                    $snapshot = $this->getPersonalHealthSnapshot($authUserId);
                    if ($snapshot !== null) {
                        // If data is simple enough, reply directly without LLM
                        $directReply = $this->buildPersonalHealthReply($snapshot);
                        if ($directReply !== null) {
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
                                $final = $this->askModelToSummarizeResults(
                                    $validated['message'],
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

        // Try Google Gemini first if key is present
        if ($googleKey !== '') {
            $googleModel = (string) (env('GOOGLE_MODEL', '') ?: config('services.google.model', 'gemini-1.5-flash'));
            $resp = $this->googleChatRequest(
                $systemPrompt,
                $validated['message'],
                $googleKey,
                $googleModel,
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
            '/\b(my\s+health|health\s+condition|my\s+condition|health\s+status|my\s+symptoms?|my\s+diseases?|my\s+medicines?|my\s+metrics?|tell\s+me\s+about\s+my|what\s+(are|is)\s+my)\b/i',
            $message
        );
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

                // health_metrics — value is a JSON column, decode it
                $metrics = DB::connection($conn)
                    ->table('health_metrics')
                    ->where('user_id', $userId)
                    ->orderByDesc('recorded_at')
                    ->limit(20)
                    ->get(['metric_type', 'value', 'recorded_at'])
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
            return 'I checked your records and could not find any saved diseases, symptoms, health metrics, or medicines yet. Please add health entries in the app first, then ask again.';
        }

        // For rich data, let the LLM compose a better narrative unless deterministic fallback is requested.
        if ($total > 6 && !$forceDetailed) {
            return null;
        }

        $parts = [];

        if ($diseases !== []) {
            $labels = array_map(function (array $d): string {
                $name   = (string) ($d['disease'] ?? 'Unknown disease');
                $status = (string) ($d['status'] ?? 'unknown');
                $date   = !empty($d['diagnosed_at']) ? ', diagnosed ' . $d['diagnosed_at'] : '';
                return "{$name} ({$status}{$date})";
            }, array_slice($diseases, 0, 3));
            $parts[] = '**Diseases:** ' . implode(', ', $labels) . '.';
        }

        if ($symptoms !== []) {
            $labels = array_map(function (array $s): string {
                $name = (string) ($s['symptom'] ?? 'Unknown symptom');
                $sev  = $s['severity_level'] ?? null;
                return $sev !== null ? "{$name} (severity {$sev}/10)" : $name;
            }, array_slice($symptoms, 0, 5));
            $parts[] = '**Recent symptoms:** ' . implode(', ', $labels) . '.';
        }

        if ($medicines !== []) {
            $labels = array_map(fn(array $m): string => (string) ($m['medicine_name'] ?? 'Unknown'), array_slice($medicines, 0, 5));
            $parts[] = '**Medicines:** ' . implode(', ', $labels) . '.';
        }

        if ($metrics !== []) {
            $labels = array_map(function (array $m): string {
                $type  = (string) ($m['metric_type'] ?? 'metric');
                $value = $m['value'] ?? null;
                if (is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }
                $display = (is_string($value) || is_numeric($value)) ? (string) $value : 'recorded';
                return "{$type}: {$display}";
            }, array_slice($metrics, 0, 5));
            $parts[] = '**Health metrics:** ' . implode('; ', $labels) . '.';
        }

        if ($forceDetailed) {
            $parts[] = sprintf(
                '**Summary:** %d disease record(s), %d symptom record(s), %d medicine record(s), and %d health metric record(s) found.',
                count($diseases),
                count($symptoms),
                count($medicines),
                count($metrics)
            );
        }

        $parts[] = "\n*This is based on your saved records and is not a medical diagnosis. Please consult a licensed doctor for clinical advice.*";

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

        // Try Google Gemini first
        if ($googleKey !== '') {
            $googleModel = (string) (env('GOOGLE_MODEL', '') ?: config('services.google.model', 'gemini-1.5-flash'));
            $resp = $this->googleChatRequest($system, $user, $googleKey, $googleModel, 0.0, 300);
            if ($resp !== null) {
                $text = trim(preg_replace('/^```\w*\s*|```\s*$/m', '', $resp));
                return $text !== '' ? $text : null;
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
                . 'If the JSON is empty, say no matching records were found. '
                . 'Be concise (2–6 sentences), friendly, and remind the user this is not a medical diagnosis.';
        $user   = "Question: {$question}\n\nData source: {$sql}\n\nData:\n{$json}\n\nAnswer:";

        // Try Google Gemini first
        if ($googleKey !== '') {
            $googleModel = (string) (env('GOOGLE_MODEL', '') ?: config('services.google.model', 'gemini-1.5-flash'));
            $resp = $this->googleChatRequest($system, $user, $googleKey, $googleModel, 0.2, 500);
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

        $sentences = array_values(array_filter(array_map('trim', preg_split('/(?<=[.!?])\s+/', $text) ?: [])));
        if ($sentences === []) {
            $sentences = [$text];
        }

        $summary = array_shift($sentences) ?? $text;
        $detailItems = array_slice($sentences, 0, 4);
        $suggestions = $this->buildDefaultSuggestions($text);
        $tips = $this->buildDefaultTips($text);

        $output = [
            '## MyDoctor AI Response',
            '',
            '**Summary**',
            '- ' . $summary,
        ];

        if ($detailItems !== []) {
            $output[] = '';
            $output[] = '**Details**';
            foreach ($detailItems as $item) {
                $output[] = '- ' . $item;
            }
        }

        $output[] = '';
        $output[] = '**Suggestions**';
        foreach ($suggestions as $suggestion) {
            $output[] = '- ' . $suggestion;
        }

        $output[] = '';
        $output[] = '**Tips**';
        foreach ($tips as $tip) {
            $output[] = '- ' . $tip;
        }

        return implode("\n", $output);
    }

    private function buildDefaultSuggestions(string $context): array
    {
        $suggestions = [
            'Review your latest health entries and keep them updated so recommendations stay accurate.',
            'Share persistent or worsening symptoms with a licensed doctor for proper evaluation.',
            'Use your medicine reminders consistently to improve treatment adherence and routine tracking.',
            'Ask follow-up questions about trends in your records so you can monitor changes over time.',
        ];

        if (preg_match('/\b(emergency|chest pain|shortness of breath|severe|faint|stroke)\b/i', $context)) {
            $suggestions[0] = 'Seek urgent medical care immediately if severe or emergency symptoms are present.';
        }

        return array_slice($suggestions, 0, 4);
    }

    private function buildDefaultTips(string $context): array
    {
        $tips = [
            'Tip 1: Stay hydrated and aim for regular sleep to support recovery and overall health.',
            'Tip 2: Record symptoms with time and severity so changes can be tracked accurately.',
            'Tip 3: Follow prescribed medicines exactly and avoid skipping doses without medical advice.',
            'Tip 4: Seek immediate emergency help if you develop severe or rapidly worsening symptoms.',
        ];

        if (!preg_match('/\b(emergency|severe|worsening)\b/i', $context)) {
            $tips[3] = 'Tip 4: Schedule routine check-ups to review progress and adjust plans safely.';
        }

        return array_slice($tips, 0, 4);
    }
}
