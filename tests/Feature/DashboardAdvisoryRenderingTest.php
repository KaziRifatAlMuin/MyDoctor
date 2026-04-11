<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardAdvisoryRenderingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function dashboard_includes_live_advisory_hook_and_renderer(): void
    {
        $user = User::factory()->create();

        // Ensure address present so dashboard shows live environment section
        $user->address()->update([
            'division' => 'Khulna',
            'district' => 'Khulna',
            'upazila' => 'Fultola',
        ]);

        $response = $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();

        $content = (string) $response->getContent();

        $this->assertStringContainsString('id="liveEnvAdvisory"', $content);
        $this->assertStringContainsString('function renderChatbotMarkup', $content);
        $this->assertStringContainsString('renderChatbotMarkup(', $content);
    }
}
