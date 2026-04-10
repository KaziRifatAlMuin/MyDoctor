<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ChatbotBrowserFlowTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function authenticated_page_renders_chatbot_ui_and_scripts(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('suggestions'));

        $response->assertOk();
        $response->assertSee('id="chatbotModal"', false);
        $response->assertSee('id="chatInput"', false);
        $response->assertSee('function toggleChatbot()', false);
        $response->assertSee('function sendMessage()', false);
        $response->assertSee('/chatbot/message', false);
    }

    #[Test]
    public function browser_style_ajax_request_gets_chatbot_reply(): void
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
                    ['message' => ['content' => 'Hydrate and monitor your symptoms.']],
                ],
            ], 200),
        ]);

        $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->postJson(route('chatbot.message'), [
                'message' => 'What should I do for mild headache?',
                'history' => [
                    ['role' => 'assistant', 'content' => 'How long have you had it?'],
                ],
            ])
            ->assertOk()
            ->assertJson(fn ($json) => $json
                ->whereType('reply', 'string')
                ->where('reply', fn (string $reply) => str_contains($reply, 'Hydrate and monitor your symptoms.'))
                ->etc());
    }
}
