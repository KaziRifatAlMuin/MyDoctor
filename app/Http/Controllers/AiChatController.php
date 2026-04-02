<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
}
