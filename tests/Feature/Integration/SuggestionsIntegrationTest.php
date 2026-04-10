<?php

namespace Tests\Feature\Integration;

use App\Models\Disease;
use App\Models\UserHealth;
use App\Models\Medicine;
use App\Models\MedicineLog;
use App\Models\Symptom;
use App\Models\User;
use App\Models\UserDisease;
use App\Models\UserSymptom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Integration tests for the suggestions feature.
 *
 * These tests cover complete end-to-end flows: a user has real database
 * records, the controller fetches and processes them, and the view receives
 * fully personalised suggestion arrays.  Everything passes through the
 * actual HTTP stack (middleware → controller → Eloquent → view).
 */
class SuggestionsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────
    // Full happy-path flows
    // ──────────────────────────────────────────────────

    #[Test]
    public function user_with_high_bp_and_low_adherence_gets_both_suggestions(): void
    {
        $user     = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user->id]);

        // High blood pressure
        UserHealth::factory()->create([
            'user_id'     => $user->id,
            'metric_type' => 'blood_pressure',
            'value'       => ['systolic' => 160, 'diastolic' => 100],
            'recorded_at' => now(),
        ]);

        // Very low adherence (2/10 = 20%)
        MedicineLog::factory()->create([
            'user_id'         => $user->id,
            'medicine_id'     => $medicine->id,
            'date'            => now()->subDays(5)->format('Y-m-d'),
            'total_scheduled' => 10,
            'total_taken'     => 2,
            'total_missed'    => 8,
        ]);

        $response = $this->actingAs($user)
                         ->get(route('suggestions'))
                         ->assertOk();

        $response->assertViewHas('suggestions', function (array $suggestions) {
            $titles = array_column($suggestions, 'title');
            return in_array('High Blood Pressure Detected',   $titles)
                && in_array('Very Low Medicine Adherence',    $titles);
        });
    }

    #[Test]
    public function user_with_diabetes_condition_gets_condition_specific_tip(): void
    {
        $user    = User::factory()->create();
        $disease = Disease::factory()->create(['disease_name' => 'Type 2 Diabetes']);

        UserDisease::factory()->create([
            'user_id'    => $user->id,
            'disease_id' => $disease->id,
            'status'     => 'active',
        ]);

        $response = $this->actingAs($user)
                         ->get(route('suggestions'))
                         ->assertOk();

        $response->assertViewHas('suggestions', function (array $suggestions) {
            return in_array('Diabetes Management Tips', array_column($suggestions, 'title'));
        });
    }

    #[Test]
    public function user_with_asthma_condition_gets_asthma_care_tip(): void
    {
        $user    = User::factory()->create();
        $disease = Disease::factory()->create(['disease_name' => 'Bronchial Asthma']);

        UserDisease::factory()->create([
            'user_id'    => $user->id,
            'disease_id' => $disease->id,
            'status'     => 'chronic',
        ]);

        $response = $this->actingAs($user)
                         ->get(route('suggestions'))
                         ->assertOk();

        $response->assertViewHas('suggestions', function (array $suggestions) {
            return in_array('Asthma Care Tips', array_column($suggestions, 'title'));
        });
    }

    #[Test]
    public function user_with_multiple_active_conditions_gets_all_related_tips(): void
    {
        $user = User::factory()->create();

        $diabetes     = Disease::factory()->create(['disease_name' => 'Diabetes Mellitus']);
        $hypertension = Disease::factory()->create(['disease_name' => 'Hypertension']);

        UserDisease::factory()->create(['user_id' => $user->id, 'disease_id' => $diabetes->id,     'status' => 'active']);
        UserDisease::factory()->create(['user_id' => $user->id, 'disease_id' => $hypertension->id, 'status' => 'managed']);

        $response = $this->actingAs($user)
                         ->get(route('suggestions'))
                         ->assertOk();

        $response->assertViewHas('suggestions', function (array $suggestions) {
            $titles = array_column($suggestions, 'title');
            return in_array('Diabetes Management Tips', $titles)
                && in_array('Hypertension Management',  $titles);
        });
    }

    // ──────────────────────────────────────────────────
    // Adherence calculation
    // ──────────────────────────────────────────────────

    #[Test]
    public function adherence_is_correctly_calculated_across_multiple_log_entries(): void
    {
        $user     = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user->id]);

        // 3 logs in last 30 days: 4+3+3 = 10 taken out of 4+5+6 = 15 scheduled → 67%
        $logs = [
            ['scheduled' => 4, 'taken' => 4, 'daysAgo' => 2],
            ['scheduled' => 5, 'taken' => 3, 'daysAgo' => 7],
            ['scheduled' => 6, 'taken' => 3, 'daysAgo' => 15],
        ];

        foreach ($logs as $log) {
            MedicineLog::factory()->create([
                'user_id'         => $user->id,
                'medicine_id'     => $medicine->id,
                'date'            => now()->subDays($log['daysAgo'])->format('Y-m-d'),
                'total_scheduled' => $log['scheduled'],
                'total_taken'     => $log['taken'],
                'total_missed'    => $log['scheduled'] - $log['taken'],
            ]);
        }

        $expected = (int) round((10 / 15) * 100); // 67

        $this->actingAs($user)
             ->get(route('suggestions'))
             ->assertViewHas('adherenceRate', $expected);
    }

    #[Test]
    public function perfect_adherence_generates_excellent_suggestion(): void
    {
        $user     = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user->id]);

        MedicineLog::factory()->count(10)->create([
            'user_id'         => $user->id,
            'medicine_id'     => $medicine->id,
            'total_scheduled' => 3,
            'total_taken'     => 3,
            'total_missed'    => 0,
        ]);

        $response = $this->actingAs($user)
                         ->get(route('suggestions'))
                         ->assertOk();

        $response->assertViewHas('adherenceRate', 100);
        $response->assertViewHas('suggestions', function (array $suggestions) {
            return in_array('Excellent Adherence!', array_column($suggestions, 'title'));
        });
    }

    // ──────────────────────────────────────────────────
    // Symptom alerting
    // ──────────────────────────────────────────────────

    #[Test]
    public function user_with_severe_recent_symptoms_gets_urgent_suggestion(): void
    {
        $user = User::factory()->create();

        $symptom = Symptom::factory()->create(['name' => 'chest_pain']);

        UserSymptom::factory()->create([
            'user_id'        => $user->id,
            'symptom_id'     => $symptom->id,
            'severity_level' => 9,
            'recorded_at'    => now()->subDays(2),
        ]);

        $response = $this->actingAs($user)
                         ->get(route('suggestions'))
                         ->assertOk();

        $response->assertViewHas('suggestions', function (array $suggestions) {
            return in_array('Severe Symptoms Reported', array_column($suggestions, 'title'));
        });
    }

    #[Test]
    public function severe_symptoms_older_than_14_days_do_not_trigger_alert(): void
    {
        $user = User::factory()->create();

        $symptom = Symptom::factory()->create(['name' => 'chest_pain']);

        UserSymptom::factory()->create([
            'user_id'        => $user->id,
            'symptom_id'     => $symptom->id,
            'severity_level' => 10,
            'recorded_at'    => now()->subDays(20),  // outside window
        ]);

        $response = $this->actingAs($user)
                         ->get(route('suggestions'))
                         ->assertOk();

        $response->assertViewHas('suggestions', function (array $suggestions) {
            return !in_array('Severe Symptoms Reported', array_column($suggestions, 'title'));
        });
    }

    // ──────────────────────────────────────────────────
    // Getting started prompts
    // ──────────────────────────────────────────────────

    #[Test]
    public function brand_new_user_sees_both_getting_started_suggestions(): void
    {
        $user = User::factory()->create(); // no data at all

        $response = $this->actingAs($user)
                         ->get(route('suggestions'))
                         ->assertOk();

        $response->assertViewHas('suggestions', function (array $suggestions) {
            $titles = array_column($suggestions, 'title');
            return in_array('Start Tracking Medicines', $titles)
                && in_array('Record Health Metrics',    $titles);
        });
    }

    #[Test]
    public function user_who_added_medicines_does_not_see_medicines_getting_started(): void
    {
        $user = User::factory()->create();
        Medicine::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
                         ->get(route('suggestions'))
                         ->assertOk();

        $response->assertViewHas('suggestions', function (array $suggestions) {
            return !in_array('Start Tracking Medicines', array_column($suggestions, 'title'));
        });
    }

    // ──────────────────────────────────────────────────
    // Multiple metric types
    // ──────────────────────────────────────────────────

    #[Test]
    public function multiple_abnormal_metrics_generate_multiple_metric_alert_suggestions(): void
    {
        $user = User::factory()->create();

        UserHealth::factory()->create([
            'user_id' => $user->id, 'metric_type' => 'blood_pressure',
            'value'   => ['systolic' => 155, 'diastolic' => 100], 'recorded_at' => now(),
        ]);
        UserHealth::factory()->create([
            'user_id' => $user->id, 'metric_type' => 'blood_glucose',
            'value'   => ['value' => 220], 'recorded_at' => now(),
        ]);
        UserHealth::factory()->create([
            'user_id' => $user->id, 'metric_type' => 'temperature',
            'value'   => ['value' => 38.8], 'recorded_at' => now(),
        ]);

        $response = $this->actingAs($user)
                         ->get(route('suggestions'))
                         ->assertOk();

        $response->assertViewHas('suggestions', function (array $suggestions) {
            $titles = array_column($suggestions, 'title');
            return in_array('High Blood Pressure Detected', $titles)
                && in_array('High Blood Sugar',             $titles)
                && in_array('Fever Detected',               $titles);
        });
    }

    // ──────────────────────────────────────────────────
    // User isolation (cross-user data never leaks)
    // ──────────────────────────────────────────────────

    #[Test]
    public function user_a_does_not_see_user_b_health_data(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // UserB has high BP — userA should NOT get that suggestion
        UserHealth::factory()->create([
            'user_id'     => $userB->id,
            'metric_type' => 'blood_pressure',
            'value'       => ['systolic' => 170, 'diastolic' => 110],
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($userA)
                         ->get(route('suggestions'))
                         ->assertOk();

        // UserA has no BP data → no high-BP suggestion should appear for userA
        $response->assertViewHas('suggestions', function (array $suggestions) {
            return !in_array('High Blood Pressure Detected', array_column($suggestions, 'title'));
        });

        // UserA's latestMetrics should be empty
        $response->assertViewHas('latestMetrics', fn($m) => $m->isEmpty());
    }

    #[Test]
    public function user_a_adherence_is_not_contaminated_by_user_b_logs(): void
    {
        $userA    = User::factory()->create();
        $userB    = User::factory()->create();
        $medA     = Medicine::factory()->create(['user_id' => $userA->id]);
        $medB     = Medicine::factory()->create(['user_id' => $userB->id]);

        // UserA: 5/10 = 50%
        MedicineLog::factory()->create([
            'user_id'         => $userA->id,
            'medicine_id'     => $medA->id,
            'date'            => now()->subDays(3)->format('Y-m-d'),
            'total_scheduled' => 10,
            'total_taken'     => 5,
            'total_missed'    => 5,
        ]);

        // UserB: perfectly 100%
        MedicineLog::factory()->count(5)->create([
            'user_id'         => $userB->id,
            'medicine_id'     => $medB->id,
            'total_scheduled' => 4,
            'total_taken'     => 4,
            'total_missed'    => 0,
        ]);

        $this->actingAs($userA)
             ->get(route('suggestions'))
             ->assertViewHas('adherenceRate', 50);
    }
}
