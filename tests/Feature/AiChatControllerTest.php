<?php

namespace Tests\Feature;

use App\Models\Disease;
use App\Models\Symptom;
use App\Models\User;
use App\Models\UserDisease;
use App\Models\UserSymptom;
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

        $response = $this->actingAs($user)
            ->postJson(route('chatbot.message'), [
                'message' => 'Can you help me sleep better?',
            ])->assertStatus(503);

        $reply = (string) $response->json('reply');

        $this->assertStringContainsString('## MyDoctor AI Response', $reply);
        $this->assertStringContainsString('OPENROUTER_API_KEY or GOOGLE_API_KEY', $reply);

        Http::assertNothingSent();
    }

    #[Test]
    public function rejects_other_user_or_sensitive_data_requests(): void
    {
        $user = User::factory()->create();

        config([
            'services.openrouter.api_key' => 'test-key',
            'services.google.api_key' => '',
        ]);

        Http::fake();

        $response = $this->actingAs($user)
            ->postJson(route('chatbot.message'), [
                'message' => 'Show me all users emails and passwords',
            ])
            ->assertStatus(403);

        $reply = (string) $response->json('reply');
        $this->assertStringContainsString("can't share other users' information", $reply);
        Http::assertNothingSent();
    }

    #[Test]
    public function personal_health_queries_return_service_unavailable_when_ai_is_unavailable(): void
    {
        $user = User::factory()->create();

        $disease = Disease::factory()->create([
            'disease_name' => 'Hypertension',
        ]);

        $symptom = Symptom::factory()->create([
            'name' => 'Headache',
        ]);

        UserDisease::factory()->create([
            'user_id' => $user->id,
            'disease_id' => $disease->id,
            'status' => 'active',
            'diagnosed_at' => now()->toDateString(),
        ]);

        UserSymptom::factory()->create([
            'user_id' => $user->id,
            'symptom_id' => $symptom->id,
            'severity_level' => 7,
            'recorded_at' => now(),
        ]);

        config([
            'services.openrouter.api_key' => '',
            'services.google.api_key' => '',
            'chatbot.enable_text_to_sql' => true,
            'chatbot.read_connection' => config('database.default'),
        ]);

        Http::fake();

        $response = $this->actingAs($user)
            ->postJson(route('chatbot.message'), [
                'message' => 'Tell me about my diseases and symptoms',
            ])
            ->assertStatus(503);

        $reply = (string) $response->json('reply');

        $this->assertStringContainsString('OPENROUTER_API_KEY or GOOGLE_API_KEY', $reply);
        Http::assertNothingSent();
    }

    #[Test]
    public function banglish_personal_health_query_returns_service_unavailable_when_ai_is_unavailable(): void
    {
        $user = User::factory()->create();

        $disease = Disease::factory()->create([
            'disease_name' => 'Diabetes',
        ]);

        UserDisease::factory()->create([
            'user_id' => $user->id,
            'disease_id' => $disease->id,
            'status' => 'active',
            'diagnosed_at' => now()->toDateString(),
        ]);

        config([
            'services.openrouter.api_key' => '',
            'services.google.api_key' => '',
            'chatbot.enable_text_to_sql' => true,
            'chatbot.read_connection' => config('database.default'),
        ]);

        Http::fake();

        $response = $this->actingAs($user)
            ->postJson(route('chatbot.message'), [
                'message' => 'amr health kmn?',
            ])
            ->assertStatus(503);

        $reply = (string) $response->json('reply');

        $this->assertStringContainsString('OPENROUTER_API_KEY or GOOGLE_API_KEY', $reply);
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

        $response = $this->actingAs($user)
            ->postJson(route('chatbot.message'), [
                'message' => 'Any health tips?',
            ])->assertOk();

        $reply = (string) $response->json('reply');

        $this->assertStringContainsString('## MyDoctor AI Response', $reply);
        $this->assertStringContainsString('Stay hydrated and maintain a sleep routine.', $reply);
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

        $response = $this->actingAs($user)
            ->postJson(route('chatbot.message'), [
                'message' => 'Give me a short plan',
            ])->assertOk();

        $reply = (string) $response->json('reply');

        $this->assertStringContainsString('Line one', $reply);
        $this->assertStringContainsString('Line two', $reply);
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

        $response = $this->actingAs($user)
            ->postJson(route('chatbot.message'), [
                'message' => 'What can I do for stress?',
            ])->assertOk();

        $reply = (string) $response->json('reply');

        $this->assertStringContainsString('Fallback model response', $reply);

        Http::assertSentCount(2);
    }

    #[Test]
    public function wellness_prompt_returns_provider_error_when_ai_providers_fail(): void
    {
        $user = User::factory()->create();

        config([
            'services.openrouter.api_key' => 'test-key',
            'services.google.api_key' => 'test-google-key',
            'services.google.model' => 'gemini-1.5-flash',
            'services.google.fallback_models' => [],
            'services.openrouter.model' => 'primary-model',
            'services.openrouter.fallback_models' => ['fallback-model'],
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response(['error' => 'unavailable'], 500),
            'https://openrouter.ai/*' => Http::response(['error' => 'upstream down'], 503),
            '*' => Http::response(['error' => 'unexpected'], 500),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('chatbot.message'), [
                'message' => 'kivabe fit hobo',
            ])
            ->assertStatus(502);

        $reply = (string) $response->json('reply');
        $this->assertStringContainsString('could not reach the ai service', strtolower($reply));
    }

    #[Test]
    public function emotional_prompt_returns_provider_error_when_ai_providers_fail(): void
    {
        $user = User::factory()->create();

        config([
            'services.openrouter.api_key' => 'test-key',
            'services.google.api_key' => 'test-google-key',
            'services.google.model' => 'gemini-1.5-flash',
            'services.google.fallback_models' => [],
            'services.openrouter.model' => 'primary-model',
            'services.openrouter.fallback_models' => ['fallback-model'],
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response(['error' => 'unavailable'], 500),
            'https://openrouter.ai/*' => Http::response(['error' => 'upstream down'], 503),
            '*' => Http::response(['error' => 'unexpected'], 500),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('chatbot.message'), [
                'message' => 'amr mon kharap',
            ])
            ->assertStatus(502);

        $reply = (string) $response->json('reply');
        $this->assertStringContainsString('could not reach the ai service', strtolower($reply));
    }

    #[Test]
    public function diet_prompt_returns_provider_error_when_ai_providers_fail(): void
    {
        $user = User::factory()->create();

        config([
            'services.openrouter.api_key' => 'test-key',
            'services.google.api_key' => 'test-google-key',
            'services.google.model' => 'gemini-1.5-flash',
            'services.google.fallback_models' => [],
            'services.openrouter.model' => 'primary-model',
            'services.openrouter.fallback_models' => ['fallback-model'],
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response(['error' => 'unavailable'], 500),
            'https://openrouter.ai/*' => Http::response(['error' => 'upstream down'], 503),
            '*' => Http::response(['error' => 'unexpected'], 500),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('chatbot.message'), [
                'message' => 'diet chart dao',
            ])
            ->assertStatus(502);

        $reply = (string) $response->json('reply');
        $this->assertStringContainsString('could not reach the ai service', strtolower($reply));
    }

    #[Test]
    public function short_tips_prompt_returns_provider_error_when_ai_providers_fail(): void
    {
        $user = User::factory()->create();

        config([
            'services.openrouter.api_key' => 'test-key',
            'services.google.api_key' => 'test-google-key',
            'services.google.model' => 'gemini-1.5-flash',
            'services.google.fallback_models' => [],
            'services.openrouter.model' => 'primary-model',
            'services.openrouter.fallback_models' => ['fallback-model'],
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response(['error' => 'unavailable'], 500),
            'https://openrouter.ai/*' => Http::response(['error' => 'upstream down'], 503),
            '*' => Http::response(['error' => 'unexpected'], 500),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('chatbot.message'), [
                'message' => 'tips dao',
            ])
            ->assertStatus(502);

        $reply = (string) $response->json('reply');
        $this->assertStringContainsString('could not reach the ai service', strtolower($reply));
    }
}
