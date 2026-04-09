<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AiChatController extends Controller
{
    public function message(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'history' => ['nullable', 'array', 'max:12'],
            'history.*.role' => ['required_with:history', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:1000'],
        ]);

        $apiKey = (string) config('services.openrouter.api_key');
        if ($apiKey === '') {
            return response()->json([
                'reply' => 'AI service is not configured yet. Please set OPENROUTER_API_KEY in your .env file.',
            ], 503);
        }

        $baseUrl = rtrim((string) config('services.openrouter.base_url', 'https://openrouter.ai/api/v1'), '/');
        $primaryModel = (string) config('services.openrouter.model', 'openai/gpt-oss-20b:free');
        $fallbackModels = (array) config('services.openrouter.fallback_models', []);
        $models = collect(array_merge([$primaryModel], $fallbackModels))
            ->filter(fn ($m) => is_string($m) && trim($m) !== '')
            ->map(fn ($m) => trim($m))
            ->unique()
            ->values()
            ->all();

        if ($models === []) {
            return response()->json([
                'reply' => 'AI service models are not configured. Please set AI_CHAT_MODEL in your .env file.',
            ], 503);
        }

        $history = collect($validated['history'] ?? [])->map(function (array $item): array {
            return [
                'role' => $item['role'],
                'content' => $item['content'],
            ];
        })->values()->all();

        $messages = array_merge(
            [[
                'role' => 'system',
                'content' => 'You are MyDoctor AI health assistant. Be concise and practical. Do not provide diagnosis. Encourage consulting a licensed doctor for urgent or severe symptoms. For emergencies, advise immediate local emergency services.',
            ]],
            $history,
            [[
                'role' => 'user',
                'content' => $validated['message'],
            ]]
        );

        $enableTextToSql = (bool) config('services.openrouter.enable_text_to_sql', true);
        if ($enableTextToSql) {
            // Try text->SQL flow: generate SQL, run, then ask the LLM to craft final answer using results.
            try {
                $schemaDesc = $this->getDatabaseSchemaDescription();
                $sql = $this->generateSqlFromMessage($validated['message'], $schemaDesc, $apiKey, $primaryModel, $baseUrl);

                if ($sql !== null) {
                    $sql = $this->sanitizeSql($sql);
                    if ($sql !== null) {
                        $results = $this->executeSelectSql($sql);
                        if ($results !== null) {
                            $final = $this->askModelToSummarizeResults($validated['message'], $sql, $results, $apiKey, $primaryModel, $baseUrl);
                            if ($final !== null) {
                                return response()->json(['reply' => $final]);
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Text-to-SQL pipeline failed', ['message' => $e->getMessage()]);
                // fallthrough to normal chat flow
            }
        }

        $lastStatus = null;

        foreach ($models as $model) {
            try {
                $response = Http::timeout(35)
                    ->withToken($apiKey)
                    ->withHeaders([
                        'HTTP-Referer' => (string) config('services.openrouter.site_url', config('app.url')),
                        'X-Title' => (string) config('services.openrouter.app_name', config('app.name')),
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])
                    ->post($baseUrl . '/chat/completions', [
                        'model' => $model,
                        'messages' => $messages,
                        'temperature' => 0.5,
                        'max_tokens' => 500,
                    ]);

                if ($response->successful()) {
                    $reply = $this->extractReplyText(data_get($response->json(), 'choices.0.message.content'));
                    if ($reply !== null) {
                        return response()->json([
                            'reply' => $reply,
                        ]);
                    }

                    Log::warning('OpenRouter returned empty response', [
                        'model' => $model,
                    ]);
                    $lastStatus = 502;
                    continue;
                }

                $status = $response->status();
                $body = $response->body();
                Log::warning('OpenRouter chat request failed', [
                    'model' => $model,
                    'status' => $status,
                    'body' => $body,
                ]);

                $lastStatus = $status;

                if ($status === 401 || $status === 403) {
                    break;
                }
            } catch (\Throwable $e) {
                Log::error('AI chat exception', [
                    'model' => $model,
                    'message' => $e->getMessage(),
                ]);
                $lastStatus = 500;
            }
        }

        if (in_array($lastStatus, [401, 403], true)) {
            return response()->json([
                'reply' => 'AI service authentication failed. Please verify your API key.',
            ], 502);
        }

        return response()->json([
            'reply' => 'I could not reach the AI service right now. Please try again shortly.',
        ], 502);
    }

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

        if ($parts === []) {
            return null;
        }

        return implode("\n", $parts);
    }

    private function getDatabaseSchemaDescription(): string
    {
        try {
            $database = config('database.connections.' . config('database.default') . '.database');
            $rows = DB::select("SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = ?
                ORDER BY TABLE_NAME, ORDINAL_POSITION",
                [$database]
            );

            $tables = [];
            foreach ($rows as $r) {
                $t = $r->TABLE_NAME;
                $c = $r->COLUMN_NAME . ' (' . $r->COLUMN_TYPE . ')';
                $tables[$t][] = $c;
            }

            $parts = [];
            foreach ($tables as $table => $cols) {
                $parts[] = "Table: $table => " . implode(', ', $cols);
            }

            $schema = implode("\n", $parts);
            // keep schema reasonably sized
            return mb_strimwidth($schema, 0, 1800, '...');
        } catch (\Throwable $e) {
            Log::warning('Failed to build DB schema description', ['message' => $e->getMessage()]);
            return '';
        }
    }

    private function generateSqlFromMessage(string $message, string $schemaDesc, string $apiKey, string $model, string $baseUrl): ?string
    {
        if ($schemaDesc === '') {
            return null;
        }

        $system = 'You are a SQL generator. Based on the provided database schema, return a single-line valid SQL SELECT query (MySQL dialect) that answers the user question. Do NOT return any explanation or text. Return only the SQL. Only SELECT queries are allowed.';
        $user = "Schema:\n" . $schemaDesc . "\n\nQuestion: " . $message . "\n\nSQL Query:";

        try {
            // If a Google API key is provided in env/config, call Google Generative API directly
            $googleKey = env('GOOGLE_API_KEY') ?: config('services.google.api_key');
            $googleModel = env('GOOGLE_MODEL') ?: config('services.google.model', 'chat-bison-001');
            if (!empty($googleKey)) {
                $resp = $this->googleChatRequest($system, $user, $googleKey, $googleModel, 0.0, 300);
                if ($resp !== null) {
                    $text = trim(preg_replace('/^```\w*|```$/', '', $resp));
                    return $text !== '' ? $text : null;
                }
            }

            // Fallback to configured OpenRouter-like provider
            $response = Http::timeout(30)
                ->withToken($apiKey)
                ->withHeaders(['Content-Type' => 'application/json', 'Accept' => 'application/json'])
                ->post($baseUrl . '/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $user],
                    ],
                    'temperature' => 0.0,
                    'max_tokens' => 300,
                ]);

            if ($response->successful()) {
                $text = $this->extractReplyText(data_get($response->json(), 'choices.0.message.content'));
                if ($text !== null) {
                    $text = preg_replace('/^```\w*|```$/', '', trim($text));
                    return trim($text);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('SQL generation failed', ['message' => $e->getMessage()]);
        }

        return null;
    }

    private function sanitizeSql(string $sql): ?string
    {
        // Basic safety: allow only a single SELECT statement, no semicolons, no dangerous keywords.
        $s = trim($sql);
        $s = preg_replace('/;+/','', $s);

        // Disallow multiple statements or non-select
        if (!preg_match('/^\s*select\b/i', $s)) {
            return null;
        }

        $bad = ['insert', 'update', 'delete', 'drop', 'alter', 'create', 'truncate', 'replace', 'merge', 'call', 'grant'];
        foreach ($bad as $k) {
            if (preg_match('/\b' . preg_quote($k, '/') . '\b/i', $s)) {
                return null;
            }
        }

        // Enforce a row cap: if no LIMIT present, append a safe limit
        if (!preg_match('/\blimit\b/i', $s)) {
            $s = rtrim($s, '\\s') . ' LIMIT 200';
        }

        return $s;
    }

    private function executeSelectSql(string $sql): ?array
    {
        try {
            $conn = config('chatbot.read_connection', 'mysql_chatbot');
            $rows = DB::connection($conn)->select(DB::raw($sql));
            // convert stdClass rows to arrays and limit payload
            $arr = array_map(function ($r) {
                return (array) $r;
            }, $rows);

            return $arr;
        } catch (\Throwable $e) {
            Log::warning('SQL execution failed', ['sql' => $sql, 'message' => $e->getMessage()]);
            return null;
        }
    }

    private function askModelToSummarizeResults(string $question, string $sql, array $results, string $apiKey, string $model, string $baseUrl): ?string
    {
        // build a concise results string (json truncated)
        $json = json_encode(array_slice($results, 0, 50), JSON_UNESCAPED_UNICODE);
        $system = 'You are MyDoctor AI assistant. Use the provided SQL and query results to produce a concise, helpful answer to the user question. Do not hallucinate — only use the results. If the results are empty, say that no matching records were found.';
        $user = "Question: $question\n\nSQL used: $sql\n\nResults (JSON): $json\n\nNow provide a short natural language answer (2-6 sentences).";

        try {
            $googleKey = env('GOOGLE_API_KEY') ?: config('services.google.api_key');
            $googleModel = env('GOOGLE_MODEL') ?: config('services.google.model', 'chat-bison-001');
            if (!empty($googleKey)) {
                $resp = $this->googleChatRequest($system, $user, $googleKey, $googleModel, 0.2, 400);
                if ($resp !== null) {
                    return $this->extractReplyText($resp);
                }
            }

            $response = Http::timeout(30)
                ->withToken($apiKey)
                ->withHeaders(['Content-Type' => 'application/json', 'Accept' => 'application/json'])
                ->post($baseUrl . '/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $user],
                    ],
                    'temperature' => 0.2,
                    'max_tokens' => 400,
                ]);

            if ($response->successful()) {
                return $this->extractReplyText(data_get($response->json(), 'choices.0.message.content'));
            }
        } catch (\Throwable $e) {
            Log::warning('Summarize results failed', ['message' => $e->getMessage()]);
        }

        return null;
    }

    private function isAllowedSql(string $sql): bool
    {
        if (config('chatbot.allow_all_tables', false)) {
            return true;
        }

        $allowed = array_map('strtolower', (array) config('chatbot.allowed_tables', []));
        if ($allowed === []) {
            return false; // deny if no allowed tables configured
        }

        $tables = $this->extractTablesFromSql($sql);
        if ($tables === []) {
            return false; // if we can't detect tables, be conservative
        }

        foreach ($tables as $t) {
            if (!in_array(strtolower($t), $allowed, true)) {
                return false;
            }
        }

        return true;
    }

    private function extractTablesFromSql(string $sql): array
    {
        $sql = strtolower($sql);
        $tables = [];

        // match FROM and JOIN occurrences
        if (preg_match_all('/\bfrom\s+`?([a-z0-9_]+)`?/i', $sql, $m)) {
            foreach ($m[1] as $t) $tables[] = $t;
        }
        if (preg_match_all('/\bjoin\s+`?([a-z0-9_]+)`?/i', $sql, $m)) {
            foreach ($m[1] as $t) $tables[] = $t;
        }

        // also try simple SELECT ... table syntax (older SQL)
        if (preg_match_all('/\bselect\b[\s\S]*?\bfrom\b\s*([a-z0-9_`]+)/i', $sql, $m)) {
            foreach ($m[1] as $raw) {
                $raw = preg_replace('/[^a-z0-9_]/i', '', $raw);
                if ($raw !== '') $tables[] = $raw;
            }
        }

        return array_values(array_unique($tables));
    }

    /**
     * Call Google Generative API chat-style (generateMessage) using API key.
     * Returns plain text content or null.
     */
    private function googleChatRequest(string $system, string $user, string $apiKey, string $model, float $temperature = 0.2, int $maxTokens = 400): ?string
    {
        try {
            $url = "https://generativelanguage.googleapis.com/v1beta2/models/{$model}:generateMessage?key={$apiKey}";

            $body = [
                'messages' => [
                    ['author' => 'system', 'content' => [['type' => 'text', 'text' => $system]]],
                    ['author' => 'user', 'content' => [['type' => 'text', 'text' => $user]]],
                ],
                'temperature' => $temperature,
                'maxOutputTokens' => $maxTokens,
            ];

            $resp = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json', 'Accept' => 'application/json'])
                ->post($url, $body);

            if (!$resp->successful()) {
                Log::warning('Google Generative API returned non-success', ['status' => $resp->status(), 'body' => $resp->body()]);
                return null;
            }

            $json = $resp->json();
            // Try known response shapes
            $text = data_get($json, 'candidates.0.content.0.text')
                ?? data_get($json, 'candidates.0.content.0')
                ?? data_get($json, 'candidates.0.text')
                ?? data_get($json, 'candidates.0.message.content.0.text')
                ?? data_get($json, 'candidates.0.message.content');

            if (is_array($text)) {
                // join pieces
                $parts = [];
                array_walk_recursive($text, function ($v) use (&$parts) { if (is_string($v)) $parts[] = $v; });
                $text = implode('\n', $parts);
            }

            return is_string($text) ? trim($text) : null;
        } catch (\Throwable $e) {
            Log::warning('Google chat request failed', ['message' => $e->getMessage()]);
            return null;
        }
    }
}
