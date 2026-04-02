<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpClientRequest;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AiChatControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guests_cannot_send_chatbot_messages(): void
    {
        $this->postJson(route('chatbot.message'), [
            'message' => 'Hello',
        ])->assertUnauthorized();
    }

    #[Test]
    public function returns_service_unavailable_when_api_key_missing(): void
    {
        $user = User::factory()->create();

        config(['services.openrouter.api_key' => '']);

        Http::fake();

        $this->actingAs($user)
            ->postJson(route('chatbot.message'), [
                'message' => 'Can you help me sleep better?',
            ])
            ->assertStatus(503)
            ->assertJsonPath('reply', 'AI service is not configured yet. Please set OPENROUTER_API_KEY in your .env file.');

        Http::assertNothingSent();
    }

    #[Test]
    public function returns_ai_reply_when_primary_model_succeeds(): void
    {
        $user = User::factory()->create();

        config([
            'services.openrouter.api_key' => 'test-key',
            'services.openrouter.model' => 'primary-model',
            'services.openrouter.fallback_models' => [],
        ]);

        Http::fake([
            '*' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'Stay hydrated and maintain a sleep routine.']],
                ],
            ], 200),
        ]);

        $this->actingAs($user)
            ->postJson(route('chatbot.message'), [
                'message' => 'Any health tips?',
            ])
            ->assertOk()
            ->assertJsonPath('reply', 'Stay hydrated and maintain a sleep routine.');
    }

    #[Test]
    public function accepts_structured_content_arrays_from_ai_provider(): void
    {
        $user = User::factory()->create();

        config([
            'services.openrouter.api_key' => 'test-key',
            'services.openrouter.model' => 'primary-model',
            'services.openrouter.fallback_models' => [],
        ]);

        Http::fake([
            '*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => [
                                ['type' => 'text', 'text' => 'Line one'],
                                ['type' => 'text', 'text' => 'Line two'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->actingAs($user)
            ->postJson(route('chatbot.message'), [
                'message' => 'Give me a short plan',
            ])
            ->assertOk()
            ->assertJsonPath('reply', "Line one\nLine two");
    }

    #[Test]
    public function retries_with_fallback_model_after_primary_failure(): void
    {
        $user = User::factory()->create();

        config([
            'services.openrouter.api_key' => 'test-key',
            'services.openrouter.model' => 'primary-model',
            'services.openrouter.fallback_models' => ['fallback-model'],
        ]);

        Http::fake(function (HttpClientRequest $request) {
            $model = (string) data_get($request->data(), 'model');

            if ($model === 'primary-model') {
                return Http::response(['error' => 'rate_limited'], 429);
            }

            if ($model === 'fallback-model') {
                return Http::response([
                    'choices' => [
                        ['message' => ['content' => 'Fallback model response']],
                    ],
                ], 200);
            }

            return Http::response(['error' => 'unexpected model'], 500);
        });

        $this->actingAs($user)
            ->postJson(route('chatbot.message'), [
                'message' => 'What can I do for stress?',
            ])
            ->assertOk()
            ->assertJsonPath('reply', 'Fallback model response');

        Http::assertSentCount(2);
    }
}
