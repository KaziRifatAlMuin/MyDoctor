<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_all_admin_view_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create();

        $routes = [
            route('admin.dashboard'),
            route('admin.users.index'),
            route('admin.diseases.index'),
            route('admin.symptoms.index'),
            route('admin.health.index'),
            route('admin.community.posts.index'),
            route('admin.community.posts.pending'),
            route('admin.users.show', $targetUser),
            route('admin.medical.index'),
            route('admin.analytics'),
            route('admin.settings'),
        ];

        foreach ($routes as $uri) {
            $response = $this->actingAs($admin)->get($uri);

            $this->assertContains(
                $response->getStatusCode(),
                [200, 302],
                "Admin route [$uri] should be reachable by admin users."
            );
        }
    }

    public function test_regular_user_cannot_access_any_admin_view_route(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $targetUser = User::factory()->create();

        $routes = [
            route('admin.dashboard'),
            route('admin.users.index'),
            route('admin.diseases.index'),
            route('admin.symptoms.index'),
            route('admin.health.index'),
            route('admin.community.posts.index'),
            route('admin.community.posts.pending'),
            route('admin.users.show', $targetUser),
            route('admin.medical.index'),
            route('admin.analytics'),
            route('admin.settings'),
        ];

        foreach ($routes as $uri) {
            $this->actingAs($member)
                ->get($uri)
                ->assertForbidden();
        }
    }

    public function test_guest_is_redirected_to_login_for_admin_routes(): void
    {
        $targetUser = User::factory()->create();

        $routes = [
            route('admin.dashboard'),
            route('admin.users.index'),
            route('admin.users.show', $targetUser),
        ];

        foreach ($routes as $uri) {
            $this->get($uri)
                ->assertRedirect(route('login'));
        }
    }

    public function test_admin_can_access_admin_user_api_endpoints(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create();

        $this->actingAs($admin)
            ->getJson('/api/users/' . $targetUser->id)
            ->assertOk()
            ->assertJsonPath('id', $targetUser->id);

        $this->actingAs($admin)
            ->getJson('/api/users/' . $targetUser->id . '/medical')
            ->assertOk()
            ->assertJsonStructure(['medicines', 'diseases', 'metrics']);
    }

    public function test_regular_user_cannot_access_admin_user_api_endpoints(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $targetUser = User::factory()->create();

        $this->actingAs($member)
            ->getJson('/api/users/' . $targetUser->id)
            ->assertForbidden();

        $this->actingAs($member)
            ->getJson('/api/users/' . $targetUser->id . '/medical')
            ->assertForbidden();
    }
}
