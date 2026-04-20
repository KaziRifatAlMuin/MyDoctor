<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_records_login_activity_for_user(): void
    {
        $user = User::factory()->create([
            'email' => 'activity-login@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $this->post(route('login'), [
            'email' => 'activity-login@example.com',
            'password' => 'password',
        ])->assertRedirect('/');

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'category' => 'auth',
            'action' => 'login',
        ]);
    }

    #[Test]
    public function admin_activity_log_page_is_accessible_and_paginated(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        for ($i = 0; $i < 55; $i++) {
            \DB::table('activity_logs')->insert([
                'user_id' => $admin->id,
                'category' => 'test',
                'action' => 'seeded',
                'description' => 'seeded log',
                'context' => json_encode(['seeded' => true]),
                'created_at' => now(),
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.logs.index'));
        $response->assertOk();
        $response->assertViewHas('logs');

        $logs = $response->viewData('logs');
        $this->assertSame(100, $logs->perPage());
        $this->assertGreaterThanOrEqual(55, $logs->total());
    }

    #[Test]
    public function admin_logs_show_clickable_user_name_for_user_subject(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $targetUser = User::factory()->create([
            'name' => 'Target Person',
            'email_verified_at' => now(),
        ]);

        \DB::table('activity_logs')->insert([
            'user_id' => $admin->id,
            'category' => 'account',
            'action' => 'model_updated',
            'description' => 'Updated user profile fields.',
            'subject_type' => User::class,
            'subject_id' => $targetUser->id,
            'context' => json_encode(['changed_fields' => ['name', 'phone']]),
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.logs.index'));
        $response->assertOk();
        $response->assertSee('Target Person');
        $response->assertSee(route('admin.users.show', $targetUser), false);
    }

    #[Test]
    public function profile_logs_show_only_current_users_activities(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $otherUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        \DB::table('activity_logs')->insert([
            'user_id' => $user->id,
            'category' => 'health',
            'action' => 'model_updated',
            'description' => 'Updated own health metric.',
            'context' => json_encode(['changed_fields' => ['value']]),
            'created_at' => now(),
        ]);

        \DB::table('activity_logs')->insert([
            'user_id' => $otherUser->id,
            'category' => 'health',
            'action' => 'model_updated',
            'description' => 'Should not be visible to current user.',
            'context' => json_encode(['changed_fields' => ['value']]),
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('profile.logs'));
        $response->assertOk();
        $response->assertSee('Updated own health metric.');
        $response->assertDontSee('Should not be visible to current user.');
    }
}
