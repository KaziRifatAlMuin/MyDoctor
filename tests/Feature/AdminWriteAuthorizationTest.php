<?php

namespace Tests\Feature;

use App\Models\Disease;
use App\Models\HealthMetric;
use App\Models\Symptom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWriteAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_perform_user_write_actions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'member', 'is_active' => true]);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'New Admin Managed User',
                'email' => 'new-admin-user@example.com',
                'password' => 'password123',
                'role' => 'member',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'new-admin-user@example.com',
            'role' => 'member',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $target), [
                'name' => 'Updated Name',
                'email' => 'updated-user@example.com',
                'role' => 'member',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'name' => 'Updated Name',
            'email' => 'updated-user@example.com',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.toggle-active', $target))
            ->assertRedirect(route('admin.users.index'));

        $target->refresh();
        $this->assertFalse((bool) $target->is_active);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $target))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_member_cannot_perform_user_write_actions(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $target = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)
            ->post(route('admin.users.store'), [
                'name' => 'Blocked User',
                'email' => 'blocked-user@example.com',
                'password' => 'password123',
                'role' => 'member',
            ])
            ->assertForbidden();

        $this->actingAs($member)
            ->patch(route('admin.users.update', $target), [
                'name' => 'Should Not Update',
                'email' => 'blocked-update@example.com',
                'role' => 'member',
            ])
            ->assertForbidden();

        $this->actingAs($member)
            ->patch(route('admin.users.toggle-active', $target))
            ->assertForbidden();

        $this->actingAs($member)
            ->delete(route('admin.users.destroy', $target))
            ->assertForbidden();
    }

    public function test_admin_can_perform_disease_symptom_and_metric_write_actions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $disease = Disease::factory()->create();
        $symptom = Symptom::factory()->create();
        $metric = HealthMetric::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.diseases.store'), [
                'disease_name' => 'Integration Disease',
                'description' => 'Disease description',
            ])
            ->assertRedirect(route('admin.diseases.index'));

        $this->assertDatabaseHas('diseases', ['disease_name' => 'Integration Disease']);

        $this->actingAs($admin)
            ->patch(route('admin.diseases.update', $disease), [
                'disease_name' => 'Updated Disease Name',
                'description' => 'Updated description',
            ])
            ->assertRedirect(route('admin.diseases.index'));

        $this->assertDatabaseHas('diseases', [
            'id' => $disease->id,
            'disease_name' => 'Updated Disease Name',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.symptoms.store'), [
                'name' => 'integration_symptom',
            ])
            ->assertRedirect(route('admin.symptoms.index'));

        $this->assertDatabaseHas('symptoms', ['name' => 'integration_symptom']);

        $this->actingAs($admin)
            ->patch(route('admin.symptoms.update', $symptom), [
                'name' => 'updated_symptom_name',
            ])
            ->assertRedirect(route('admin.symptoms.index'));

        $this->assertDatabaseHas('symptoms', [
            'id' => $symptom->id,
            'name' => 'updated_symptom_name',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.health.store'), [
                'metric_name' => 'integration_metric_name',
                'fields' => ['value', 'unit'],
            ])
            ->assertRedirect(route('admin.health.index'));

        $this->assertDatabaseHas('health_metrics', ['metric_name' => 'integration_metric_name']);

        $this->actingAs($admin)
            ->patch(route('admin.metrics.update', $metric), [
                'metric_name' => 'updated_metric_name',
                'fields' => ['field_a', 'field_b'],
            ])
            ->assertRedirect(route('admin.metrics.show', $metric));

        $this->assertDatabaseHas('health_metrics', [
            'id' => $metric->id,
            'metric_name' => 'updated_metric_name',
        ]);
    }

    public function test_member_cannot_perform_disease_symptom_and_metric_write_actions(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $disease = Disease::factory()->create();
        $symptom = Symptom::factory()->create();
        $metric = HealthMetric::factory()->create();

        $this->actingAs($member)
            ->post(route('admin.diseases.store'), [
                'disease_name' => 'blocked_disease',
            ])
            ->assertForbidden();

        $this->actingAs($member)
            ->patch(route('admin.diseases.update', $disease), [
                'disease_name' => 'blocked_disease_update',
            ])
            ->assertForbidden();

        $this->actingAs($member)
            ->post(route('admin.symptoms.store'), [
                'name' => 'blocked_symptom',
            ])
            ->assertForbidden();

        $this->actingAs($member)
            ->patch(route('admin.symptoms.update', $symptom), [
                'name' => 'blocked_symptom_update',
            ])
            ->assertForbidden();

        $this->actingAs($member)
            ->post(route('admin.health.store'), [
                'metric_name' => 'blocked_metric',
                'fields' => ['value'],
            ])
            ->assertForbidden();

        $this->actingAs($member)
            ->patch(route('admin.metrics.update', $metric), [
                'metric_name' => 'blocked_metric_update',
                'fields' => ['value'],
            ])
            ->assertForbidden();
    }

    public function test_guest_is_redirected_for_admin_write_routes(): void
    {
        $target = User::factory()->create();
        $disease = Disease::factory()->create();
        $symptom = Symptom::factory()->create();
        $metric = HealthMetric::factory()->create();

        $this->post(route('admin.users.store'), [
            'name' => 'Guest Blocked',
            'email' => 'guest-blocked@example.com',
            'password' => 'password123',
            'role' => 'member',
        ])->assertRedirect(route('login'));

        $this->patch(route('admin.users.update', $target), [
            'name' => 'Guest Update',
            'email' => 'guest-update@example.com',
            'role' => 'member',
        ])->assertRedirect(route('login'));

        $this->post(route('admin.diseases.store'), [
            'disease_name' => 'Guest Disease',
        ])->assertRedirect(route('login'));

        $this->post(route('admin.symptoms.store'), [
            'name' => 'Guest Symptom',
        ])->assertRedirect(route('login'));

        $this->post(route('admin.health.store'), [
            'metric_name' => 'Guest Metric',
            'fields' => ['value'],
        ])->assertRedirect(route('login'));

        $this->patch(route('admin.metrics.update', $metric), [
            'metric_name' => 'Guest Metric Update',
            'fields' => ['value'],
        ])->assertRedirect(route('login'));

        $this->delete(route('admin.users.destroy', $target))->assertRedirect(route('login'));
        $this->delete(route('admin.diseases.destroy', $disease))->assertRedirect(route('login'));
        $this->delete(route('admin.symptoms.destroy', $symptom))->assertRedirect(route('login'));
        $this->delete(route('admin.metrics.destroy', $metric))->assertRedirect(route('login'));
    }
}
