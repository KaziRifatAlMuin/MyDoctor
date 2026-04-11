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

class AiChatAboutMeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guests_cannot_access_about_me_endpoint(): void
    {
        $this->postJson(route('chatbot.about_me'))
            ->assertUnauthorized();
    }

    #[Test]
    public function returns_local_summary_when_ai_keys_are_missing(): void
    {
        $user = User::factory()->create();

        config([
            'services.openrouter.api_key' => '',
            'services.google.api_key' => '',
        ]);

        Http::fake();

        $response = $this->actingAs($user)
            ->postJson(route('chatbot.about_me'))
            ->assertOk();

        $reply = (string) $response->json('reply');

        $this->assertStringContainsString('**স্মার্ট পরামর্শ**', $reply);
        $this->assertStringContainsString('আপনার রেকর্ডে এখনো কোনো রোগ', $reply);
        Http::assertNothingSent();
    }

    #[Test]
    public function about_me_prompt_contains_only_authenticated_users_health_records(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownDisease = Disease::factory()->create(['disease_name' => 'Own Disease Marker']);
        $otherDisease = Disease::factory()->create(['disease_name' => 'Other Disease Marker']);

        $ownSymptom = Symptom::factory()->create(['name' => 'own_symptom_marker']);
        $otherSymptom = Symptom::factory()->create(['name' => 'other_symptom_marker']);

        UserDisease::factory()->create([
            'user_id' => $user->id,
            'disease_id' => $ownDisease->id,
            'status' => 'active',
        ]);

        UserDisease::factory()->create([
            'user_id' => $otherUser->id,
            'disease_id' => $otherDisease->id,
            'status' => 'active',
        ]);

        UserSymptom::factory()->create([
            'user_id' => $user->id,
            'symptom_id' => $ownSymptom->id,
            'severity_level' => 6,
            'recorded_at' => now(),
        ]);

        UserSymptom::factory()->create([
            'user_id' => $otherUser->id,
            'symptom_id' => $otherSymptom->id,
            'severity_level' => 8,
            'recorded_at' => now(),
        ]);

        config([
            'services.openrouter.api_key' => 'test-key',
            'services.google.api_key' => '',
            'services.openrouter.model' => 'primary-model',
            'services.openrouter.fallback_models' => [],
            'chatbot.read_connection' => config('database.default'),
        ]);

        $capturedPrompt = '';

        Http::fake(function (HttpClientRequest $request) use (&$capturedPrompt) {
            $capturedPrompt = (string) data_get($request->data(), 'messages.1.content', '');

            return Http::response([
                'choices' => [
                    ['message' => ['content' => 'About me summary']],
                ],
            ], 200);
        });

        $response = $this->actingAs($user)
            ->postJson(route('chatbot.about_me'))
            ->assertOk()
            ->assertJson(fn($json) => $json->whereType('reply', 'string'));

        $reply = (string) $response->json('reply');

        $this->assertStringContainsString('About me summary', $reply);

        $this->assertStringContainsString('Own Disease Marker', $capturedPrompt);
        $this->assertStringContainsString('own_symptom_marker', $capturedPrompt);
        $this->assertStringNotContainsString('Other Disease Marker', $capturedPrompt);
        $this->assertStringNotContainsString('other_symptom_marker', $capturedPrompt);
    }

    #[Test]
    public function falls_back_to_local_summary_when_ai_provider_fails(): void
    {
        $user = User::factory()->create();

        $disease = Disease::factory()->create(['disease_name' => 'Fallback Disease']);
        $symptom = Symptom::factory()->create(['name' => 'fallback_symptom']);

        UserDisease::factory()->create([
            'user_id' => $user->id,
            'disease_id' => $disease->id,
            'status' => 'active',
        ]);

        UserSymptom::factory()->create([
            'user_id' => $user->id,
            'symptom_id' => $symptom->id,
            'severity_level' => 7,
            'recorded_at' => now(),
        ]);

        config([
            'services.openrouter.api_key' => 'test-key',
            'services.google.api_key' => '',
            'services.openrouter.model' => 'primary-model',
            'services.openrouter.fallback_models' => [],
            'chatbot.read_connection' => config('database.default'),
        ]);

        Http::fake([
            '*' => Http::response(['error' => 'provider unavailable'], 500),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('chatbot.about_me'))
            ->assertOk();

        $reply = (string) $response->json('reply');

        $this->assertStringContainsString('**রোগের অবস্থা:**', $reply);
        $this->assertStringContainsString('**স্মার্ট পরামর্শ**', $reply);
        $this->assertStringContainsString('Fallback Disease', $reply);
        $this->assertStringContainsString('fallback_symptom', $reply);
    }

    #[Test]
    public function cleans_instruction_echo_lines_from_about_me_model_output(): void
    {
        $user = User::factory()->create();

        config([
            'services.openrouter.api_key' => 'test-key',
            'services.google.api_key' => '',
            'services.openrouter.model' => 'primary-model',
            'services.openrouter.fallback_models' => [],
            'chatbot.read_connection' => config('database.default'),
        ]);

        Http::fake([
            '*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => "Strict format required: do this\nCurrent user id: 123\n## My Health\n- Keep tracking trends",
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('chatbot.about_me'))
            ->assertOk();

        $reply = (string) $response->json('reply');

        $this->assertStringContainsString('## My Health', $reply);
        $this->assertStringContainsString('Keep tracking trends', $reply);
        $this->assertStringNotContainsString('Strict format required', $reply);
        $this->assertStringNotContainsString('Current user id:', $reply);
    }
}
