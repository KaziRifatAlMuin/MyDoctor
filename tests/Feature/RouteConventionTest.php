<?php

namespace Tests\Feature;

use App\Models\Disease;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteConventionTest extends TestCase
{
    use RefreshDatabase;

    public function test_canonical_user_and_disease_routes_use_plural_resource_paths(): void
    {
        $user = User::factory()->create();
        $disease = Disease::factory()->create();

        $this->assertStringEndsWith('/users/' . $user->id, route('users.show', $user));
        $this->assertStringEndsWith('/diseases/' . $disease->id, route('public.disease.show', $disease));
    }

    public function test_admin_user_routes_stay_under_admin_prefix(): void
    {
        $user = User::factory()->create();

        $this->assertStringEndsWith('/admin/users/' . $user->id, route('admin.users.show', $user));
        $this->assertStringEndsWith('/admin/users/' . $user->id, route('admin.users.update', $user));
    }

    public function test_legacy_singular_public_routes_redirect_to_canonical_plural_paths(): void
    {
        $viewer = User::factory()->create();
        $user = User::factory()->create();
        $disease = Disease::factory()->create();

        $this->actingAs($viewer)
            ->get('/user/' . $user->id)
            ->assertNotFound();

        $this->actingAs($viewer)
            ->get('/disease/' . $disease->id)
            ->assertNotFound();
    }

    public function test_all_admin_named_routes_are_under_admin_uri_prefix(): void
    {
        $adminNamedRoutes = collect(Route::getRoutes()->getRoutes())
            ->filter(function ($route) {
                $name = $route->getName();
                return is_string($name) && str_starts_with($name, 'admin.');
            });

        foreach ($adminNamedRoutes as $route) {
            $this->assertTrue(
                str_starts_with('/' . ltrim($route->uri(), '/'), '/admin/'),
                'Admin route [' . $route->getName() . '] must use /admin prefix, got [' . $route->uri() . ']'
            );
        }
    }
}
