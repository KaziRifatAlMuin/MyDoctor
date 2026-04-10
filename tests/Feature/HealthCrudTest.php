<?php

namespace Tests\Feature;

use App\Models\Disease;
use App\Models\UserHealth;
use App\Models\Symptom;
use App\Models\User;
use App\Models\UserDisease;
use App\Models\UserSymptom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tests for HealthController CRUD operations:
 *  – Guest redirect from every mutation endpoint
 *  – Ownership enforcement (HTTP 403 for wrong user)
 *  – Store validation (invalid types, severity bounds, duplicate disease)
 *  – Happy-path store / update / delete for the authenticated owner
 */
class HealthCrudTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────
    // Guest redirect tests (all mutation endpoints)
    // ──────────────────────────────────────────────────

    #[Test]
    public function guest_is_redirected_from_store_metric(): void
    {
        $this->post(route('health.metric.store'))
             ->assertRedirect(route('login'));
    }

    #[Test]
    public function guest_is_redirected_from_store_symptom(): void
    {
        $this->post(route('health.symptom.store'))
             ->assertRedirect(route('login'));
    }

    #[Test]
    public function guest_is_redirected_from_store_disease(): void
    {
        $this->post(route('health.disease.store'))
             ->assertRedirect(route('login'));
    }

    #[Test]
    public function guest_is_redirected_from_update_metric(): void
    {
        $metric = UserHealth::factory()->create();

        $this->put(route('health.metric.update', $metric->id))
             ->assertRedirect(route('login'));
    }

    #[Test]
    public function guest_is_redirected_from_destroy_metric(): void
    {
        $metric = UserHealth::factory()->create();

        $this->delete(route('health.metric.destroy', $metric->id))
             ->assertRedirect(route('login'));
    }

    #[Test]
    public function guest_is_redirected_from_destroy_symptom(): void
    {
        $symptom = UserSymptom::factory()->create();

        $this->delete(route('health.symptom.destroy', $symptom->id))
             ->assertRedirect(route('login'));
    }

    // ──────────────────────────────────────────────────
    // Happy-path store tests
    // ──────────────────────────────────────────────────

    #[Test]
    public function authenticated_user_can_store_a_health_metric(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('health.metric.store'), [
                 'metric_type' => 'heart_rate',
                 'value_bpm'   => 75,
                 'recorded_at' => now()->format('Y-m-d'),
             ])
             ->assertRedirect(route('health') . '#metrics')
             ->assertSessionHas('success');

        $this->assertDatabaseHas('user_health', [
            'user_id' => $user->id,
        ]);
    }

    #[Test]
    public function authenticated_user_can_store_a_symptom(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('health.symptom.store'), [
                 'symptom_name'   => 'Headache',
                 'severity_level' => 5,
                 'recorded_at'    => now()->format('Y-m-d'),
             ])
             ->assertRedirect(route('health') . '#symptomsPane')
             ->assertSessionHas('success');

        $catalogSymptom = Symptom::query()->where('name', 'Headache')->first();
        $this->assertNotNull($catalogSymptom);

        $this->assertDatabaseHas('user_symptoms', [
            'user_id'    => $user->id,
            'symptom_id' => $catalogSymptom->id,
        ]);
    }

    #[Test]
    public function authenticated_user_can_delete_own_metric(): void
    {
        $user   = User::factory()->create();
        $metric = UserHealth::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
             ->delete(route('health.metric.destroy', $metric->id))
             ->assertRedirect(route('health') . '#metrics')
             ->assertSessionHas('success');

        $this->assertDatabaseMissing('user_health', ['id' => $metric->id]);
    }

    #[Test]
    public function authenticated_user_can_delete_own_symptom(): void
    {
        $user    = User::factory()->create();
        $symptom = UserSymptom::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
             ->delete(route('health.symptom.destroy', $symptom->id))
             ->assertRedirect(route('health') . '#symptomsPane')
             ->assertSessionHas('success');

        $this->assertDatabaseMissing('user_symptoms', ['id' => $symptom->id]);
    }

    // ──────────────────────────────────────────────────
    // Validation error tests — metric store
    // ──────────────────────────────────────────────────

    #[Test]
    public function store_metric_with_invalid_metric_type_returns_validation_error(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('health.metric.store'), [
                 'metric_type' => 'totally_fake_metric',
                 'recorded_at' => now()->format('Y-m-d'),
             ])
             ->assertSessionHasErrors('metric_type');
    }

    #[Test]
    public function store_metric_without_metric_type_returns_validation_error(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('health.metric.store'), [
                 'recorded_at' => now()->format('Y-m-d'),
             ])
             ->assertSessionHasErrors('metric_type');
    }

    #[Test]
    public function store_metric_without_recorded_at_returns_validation_error(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('health.metric.store'), [
                 'metric_type' => 'heart_rate',
             ])
             ->assertSessionHasErrors('recorded_at');
    }

    // ──────────────────────────────────────────────────
    // Validation error tests — symptom store
    // ──────────────────────────────────────────────────

    #[Test]
    public function store_symptom_with_severity_above_10_returns_validation_error(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('health.symptom.store'), [
                 'symptom_name'   => 'Extreme Pain',
                 'severity_level' => 11,
                 'recorded_at'    => now()->format('Y-m-d'),
             ])
             ->assertSessionHasErrors('severity_level');
    }

    #[Test]
    public function store_symptom_with_severity_of_zero_returns_validation_error(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('health.symptom.store'), [
                 'symptom_name'   => 'Mild Ache',
                 'severity_level' => 0,
                 'recorded_at'    => now()->format('Y-m-d'),
             ])
             ->assertSessionHasErrors('severity_level');
    }

    #[Test]
    public function store_symptom_with_severity_of_10_succeeds(): void  // max boundary
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('health.symptom.store'), [
                 'symptom_name'   => 'Maximum Pain',
                 'severity_level' => 10,
                 'recorded_at'    => now()->format('Y-m-d'),
             ])
             ->assertRedirect(route('health') . '#symptomsPane');

        $this->assertDatabaseHas('user_symptoms', ['user_id' => $user->id, 'severity_level' => 10]);
    }

    #[Test]
    public function store_symptom_with_severity_of_1_succeeds(): void   // min boundary
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('health.symptom.store'), [
                 'symptom_name'   => 'Minimal Pain',
                 'severity_level' => 1,
                 'recorded_at'    => now()->format('Y-m-d'),
             ])
             ->assertRedirect(route('health') . '#symptomsPane');

        $this->assertDatabaseHas('user_symptoms', ['user_id' => $user->id, 'severity_level' => 1]);
    }

    #[Test]
    public function store_symptom_without_name_returns_validation_error(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('health.symptom.store'), [
                 'severity_level' => 5,
                 'recorded_at'    => now()->format('Y-m-d'),
             ])
             ->assertSessionHasErrors('symptom_name');
    }

    // ──────────────────────────────────────────────────
    // Validation error tests — disease store
    // ──────────────────────────────────────────────────

    #[Test]
    public function store_disease_with_nonexistent_disease_id_returns_validation_error(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('health.disease.store'), [
                 'disease_id' => 99999,
                 'status'     => 'active',
             ])
             ->assertSessionHasErrors('disease_id');
    }

    #[Test]
    public function store_disease_with_invalid_status_returns_validation_error(): void
    {
        $user    = User::factory()->create();
        $disease = Disease::factory()->create();

        $this->actingAs($user)
             ->post(route('health.disease.store'), [
                 'disease_id' => $disease->id,
                 'status'     => 'cured',   // not in allowed list
             ])
             ->assertSessionHasErrors('status');
    }

    #[Test]
    public function store_duplicate_disease_returns_error_flash(): void
    {
        $user    = User::factory()->create();
        $disease = Disease::factory()->create();

        UserDisease::create([
            'user_id'    => $user->id,
            'disease_id' => $disease->id,
            'status'     => 'active',
        ]);

        $this->actingAs($user)
             ->from(route('health'))
             ->post(route('health.disease.store'), [
                 'disease_id' => $disease->id,
                 'status'     => 'active',
             ])
             ->assertRedirect(route('health'))
             ->assertSessionHas('error');
    }

    #[Test]
    public function store_disease_with_all_valid_statuses_succeeds(): void
    {
        $user     = User::factory()->create();
        $diseases = Disease::factory()->count(4)->create();
        $statuses = ['active', 'recovered', 'chronic', 'managed'];

        foreach ($statuses as $index => $status) {
            $this->actingAs($user)
                 ->post(route('health.disease.store'), [
                     'disease_id' => $diseases[$index]->id,
                     'status'     => $status,
                 ])
                 ->assertRedirect(route('health') . '#diseasesPane');
        }

        $this->assertCount(4, UserDisease::where('user_id', $user->id)->get());
    }

    // ──────────────────────────────────────────────────
    // Ownership / authorization tests (403)
    // ──────────────────────────────────────────────────

    #[Test]
    public function user_cannot_delete_another_users_metric(): void
    {
        $owner  = User::factory()->create();
        $other  = User::factory()->create();
        $metric = UserHealth::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)
             ->delete(route('health.metric.destroy', $metric->id))
             ->assertStatus(403);

        $this->assertDatabaseHas('user_health', ['id' => $metric->id]);
    }

    #[Test]
    public function user_cannot_update_another_users_metric(): void
    {
        $owner  = User::factory()->create();
        $other  = User::factory()->create();
        $metric = UserHealth::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)
             ->put(route('health.metric.update', $metric->id), [
                 'metric_type' => 'heart_rate',
                 'value_bpm'   => 80,
                 'recorded_at' => now()->format('Y-m-d'),
             ])
             ->assertStatus(403);
    }

    #[Test]
    public function user_cannot_delete_another_users_symptom(): void
    {
        $owner   = User::factory()->create();
        $other   = User::factory()->create();
        $symptom = UserSymptom::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)
             ->delete(route('health.symptom.destroy', $symptom->id))
             ->assertStatus(403);

        $this->assertDatabaseHas('user_symptoms', ['id' => $symptom->id]);
    }

    #[Test]
    public function user_cannot_update_another_users_symptom(): void
    {
        $owner   = User::factory()->create();
        $other   = User::factory()->create();
        $symptom = UserSymptom::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)
             ->put(route('health.symptom.update', $symptom->id), [
                 'symptom_name'   => 'Tampered',
                 'severity_level' => 3,
                 'recorded_at'    => now()->format('Y-m-d'),
             ])
             ->assertStatus(403);
    }

    #[Test]
    public function user_cannot_delete_another_users_disease_record(): void
    {
        $owner      = User::factory()->create();
        $other      = User::factory()->create();
        $disease    = Disease::factory()->create();
        $userDisease = UserDisease::create([
            'user_id'    => $owner->id,
            'disease_id' => $disease->id,
            'status'     => 'active',
        ]);

        $this->actingAs($other)
             ->delete(route('health.disease.destroy', $userDisease->id))
             ->assertStatus(403);

        $this->assertDatabaseHas('user_diseases', ['id' => $userDisease->id]);
    }

    // ──────────────────────────────────────────────────
    // Update happy-path
    // ──────────────────────────────────────────────────

    #[Test]
    public function owner_can_update_own_metric(): void
    {
        $user   = User::factory()->create();
        $metric = UserHealth::factory()->create([
            'user_id'     => $user->id,
            'value'       => ['bpm' => 70],
        ]);

        $this->actingAs($user)
             ->put(route('health.metric.update', $metric->id), [
                 'metric_type' => 'heart_rate',
                 'value_bpm'   => 90,
                 'recorded_at' => now()->format('Y-m-d'),
             ])
             ->assertRedirect(route('health') . '#metrics')
             ->assertSessionHas('success');
    }

    #[Test]
    public function owner_can_update_own_symptom(): void
    {
        $user    = User::factory()->create();
        $symptom = UserSymptom::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
             ->put(route('health.symptom.update', $symptom->id), [
                 'symptom_name'   => 'Updated Symptom',
                 'severity_level' => 4,
                 'recorded_at'    => now()->format('Y-m-d'),
             ])
             ->assertRedirect(route('health') . '#symptomsPane')
             ->assertSessionHas('success');

        $updatedCatalogSymptom = Symptom::query()->where('name', 'Updated Symptom')->first();
        $this->assertNotNull($updatedCatalogSymptom);

        $this->assertDatabaseHas('user_symptoms', [
            'id'         => $symptom->id,
            'symptom_id' => $updatedCatalogSymptom->id,
            'severity_level' => 4,
        ]);
    }
}
