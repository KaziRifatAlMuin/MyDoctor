<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature tests for the /help page.
 *
 * The help route is public (no auth middleware), so both guests and
 * authenticated users should be able to access it.
 */
class HelpPageTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────
    // Access control
    // ──────────────────────────────────────────────────

    #[Test]
    public function help_page_is_publicly_accessible_to_guests(): void
    {
        $this->get(route('help'))
             ->assertOk();
    }

    #[Test]
    public function authenticated_user_can_access_help_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->get(route('help'))
             ->assertOk();
    }

    // ──────────────────────────────────────────────────
    // View
    // ──────────────────────────────────────────────────

    #[Test]
    public function help_page_renders_the_correct_view(): void
    {
        $this->get(route('help'))
             ->assertViewIs('help');
    }

    #[Test]
    public function help_page_returns_html_content_type(): void
    {
        $this->get(route('help'))
             ->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    // ──────────────────────────────────────────────────
    // Content presence
    // ──────────────────────────────────────────────────

    #[Test]
    public function help_page_contains_faq_section(): void
    {
        $this->get(route('help'))
             ->assertSee('FAQ', false);
    }

    #[Test]
    public function help_page_contains_getting_started_section(): void
    {
        $this->get(route('help'))
             ->assertSee('Getting Started', false);
    }

    #[Test]
    public function help_page_contains_contact_information(): void
    {
        $this->get(route('help'))
             ->assertSee('Contact', false);
    }

    #[Test]
    public function help_page_contains_link_to_health_dashboard(): void
    {
        $this->get(route('help'))
             ->assertSee(route('health'), false);
    }

    #[Test]
    public function help_page_contains_link_to_suggestions(): void
    {
        $this->get(route('help'))
             ->assertSee(route('suggestions'), false);
    }

    // ──────────────────────────────────────────────────
    // Route
    // ──────────────────────────────────────────────────

    #[Test]
    public function help_route_is_named_help(): void
    {
        $this->assertNotNull(route('help'));
        $this->assertStringEndsWith('/help', route('help'));
    }

    #[Test]
    public function help_page_does_not_redirect(): void
    {
        $this->get(route('help'))
             ->assertStatus(200);
    }
}
