<?php

namespace Tests\Feature;

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
 * Feature tests for the /suggestions page (SuggestionsController@index).
 *
 * These tests exercise the full HTTP stack — middleware, controller,
 * Eloquent queries and view rendering — against an in-memory SQLite database.
 */
class SuggestionsPageTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────
    // Access control
    // ──────────────────────────────────────────────────

    #[Test]
    public function guests_are_redirected_from_suggestions_page(): void
    {
        $this->get(route('suggestions'))
             ->assertRedirect(route('login'));
    }

    #[Test]
    public function authenticated_user_can_access_suggestions_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->get(route('suggestions'))
             ->assertOk();
    }

    // ──────────────────────────────────────────────────
    // View and variables
    // ──────────────────────────────────────────────────

    #[Test]
    public function suggestions_page_renders_the_correct_view(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->get(route('suggestions'))
             ->assertViewIs('suggestions');
    }

    #[Test]
    public function suggestions_page_passes_required_variables_to_view(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->get(route('suggestions'))
             ->assertViewHasAll([
                 'user',
                 'suggestions',
                 'latestMetrics',
                 'recentSymptoms',
                 'activeConditions',
                 'adherenceRate',
                 'medicines',
             ]);
    }

    #[Test]
    public function suggestions_variable_is_an_array(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                         ->get(route('suggestions'));

        $response->assertViewHas('suggestions', fn($v) => is_array($v));
    }

    // ──────────────────────────────────────────────────
    // Data isolation
    // ──────────────────────────────────────────────────

    #[Test]
    public function suggestions_page_shows_only_current_user_metrics(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        UserHealth::factory()->create([
            'user_id'     => $userA->id,
            'metric_type' => 'blood_pressure',
            'value'       => ['systolic' => 120, 'diastolic' => 80],
            'recorded_at' => now(),
        ]);
        UserHealth::factory()->count(3)->create(['user_id' => $userB->id]);

        $response = $this->actingAs($userA)
                         ->get(route('suggestions'));

        $response->assertViewHas('latestMetrics', function ($metrics) {
            return $metrics->count() === 1 && $metrics->has('blood_pressure');
        });
    }

    #[Test]
    public function suggestions_page_shows_only_current_user_symptoms(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $symptomsA = Symptom::factory()->count(2)->create();
        foreach ($symptomsA as $symptom) {
            UserSymptom::factory()->create([
                'user_id' => $userA->id,
                'symptom_id' => $symptom->id,
                'recorded_at' => now()->subDays(3),
            ]);
        }

        $symptomsB = Symptom::factory()->count(5)->create();
        foreach ($symptomsB as $symptom) {
            UserSymptom::factory()->create([
                'user_id' => $userB->id,
                'symptom_id' => $symptom->id,
                'recorded_at' => now()->subDays(3),
            ]);
        }

        $response = $this->actingAs($userA)
                         ->get(route('suggestions'));

        $response->assertViewHas('recentSymptoms', fn($s) => $s->count() === 2);
    }

    #[Test]
    public function suggestions_page_shows_only_current_user_medicines(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Medicine::factory()->count(3)->create(['user_id' => $userA->id]);
        Medicine::factory()->count(6)->create(['user_id' => $userB->id]);

        $response = $this->actingAs($userA)
                         ->get(route('suggestions'));

        $response->assertViewHas('medicines', fn($m) => $m->count() === 3);
    }

    // ──────────────────────────────────────────────────
    // Suggestions content
    // ──────────────────────────────────────────────────

    #[Test]
    public function suggestions_page_includes_wellness_suggestions_by_default(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                         ->get(route('suggestions'));

        $response->assertViewHas('suggestions', function (array $suggestions) {
            $titles = array_column($suggestions, 'title');
            return in_array('Stay Hydrated', $titles) && in_array('Prioritize Sleep', $titles);
        });
    }

    #[Test]
    public function suggestions_page_includes_getting_started_when_no_medicines(): void
    {
        $user = User::factory()->create(); // no medicines

        $response = $this->actingAs($user)
                         ->get(route('suggestions'));

        $response->assertViewHas('suggestions', function (array $suggestions) {
            return in_array('Start Tracking Medicines', array_column($suggestions, 'title'));
        });
    }

    #[Test]
    public function suggestions_page_includes_getting_started_when_no_metrics(): void
    {
        $user = User::factory()->create(); // no metrics

        $response = $this->actingAs($user)
                         ->get(route('suggestions'));

        $response->assertViewHas('suggestions', function (array $suggestions) {
            return in_array('Record Health Metrics', array_column($suggestions, 'title'));
        });
    }

    #[Test]
    public function suggestions_page_detects_high_blood_pressure(): void
    {
        $user = User::factory()->create();

        UserHealth::factory()->create([
            'user_id'     => $user->id,
            'metric_type' => 'blood_pressure',
            'value'       => ['systolic' => 155, 'diastolic' => 98],
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($user)
                         ->get(route('suggestions'));

        $response->assertViewHas('suggestions', function (array $suggestions) {
            return in_array('High Blood Pressure (Stage 2)', array_column($suggestions, 'title'));
        });
    }

    #[Test]
    public function suggestions_page_detects_fever(): void
    {
        $user = User::factory()->create();

        UserHealth::factory()->create([
            'user_id'     => $user->id,
            'metric_type' => 'temperature',
            'value'       => ['value' => 39.2],
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($user)
                         ->get(route('suggestions'));

        $response->assertViewHas('suggestions', function (array $suggestions) {
            return in_array('Fever Detected', array_column($suggestions, 'title'));
        });
    }

    // ──────────────────────────────────────────────────
    // Adherence calculation
    // ──────────────────────────────────────────────────

    #[Test]
    public function adherence_rate_is_null_when_no_medicine_logs(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->get(route('suggestions'))
             ->assertViewHas('adherenceRate', null);
    }

    #[Test]
    public function adherence_rate_is_calculated_from_last_30_days(): void
    {
        $user     = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user->id]);

        // 10 taken out of 10 scheduled in window → 100%
        MedicineLog::factory()->create([
            'user_id'         => $user->id,
            'medicine_id'     => $medicine->id,
            'date'            => now()->subDays(5)->format('Y-m-d'),
            'total_scheduled' => 10,
            'total_taken'     => 10,
            'total_missed'    => 0,
        ]);

        $response = $this->actingAs($user)
                         ->get(route('suggestions'));

        $response->assertViewHas('adherenceRate', 100);
    }

    #[Test]
    public function medicine_logs_outside_30_day_window_are_excluded_from_adherence(): void
    {
        $user     = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user->id]);

        // Recent: 10/20 → 50%
        MedicineLog::factory()->create([
            'user_id'         => $user->id,
            'medicine_id'     => $medicine->id,
            'date'            => now()->subDays(10)->format('Y-m-d'),
            'total_scheduled' => 20,
            'total_taken'     => 10,
            'total_missed'    => 10,
        ]);
        // Old (outside window): perfect but should not count
        MedicineLog::factory()->create([
            'user_id'         => $user->id,
            'medicine_id'     => $medicine->id,
            'date'            => now()->subDays(60)->format('Y-m-d'),
            'total_scheduled' => 100,
            'total_taken'     => 100,
            'total_missed'    => 0,
        ]);

        $response = $this->actingAs($user)
                         ->get(route('suggestions'));

        $response->assertViewHas('adherenceRate', 50);
    }

    // ──────────────────────────────────────────────────
    // Recent symptoms window
    // ──────────────────────────────────────────────────

    #[Test]
    public function symptoms_older_than_14_days_are_excluded(): void
    {
        $user = User::factory()->create();

        $symptomWithin = Symptom::factory()->create();
        $symptomOutside = Symptom::factory()->create();

        UserSymptom::factory()->create([
            'user_id'     => $user->id,
            'symptom_id'  => $symptomWithin->id,
            'recorded_at' => now()->subDays(5),           // within window
        ]);
        UserSymptom::factory()->create([
            'user_id'     => $user->id,
            'symptom_id'  => $symptomOutside->id,
            'recorded_at' => now()->subDays(20),          // outside window
        ]);

        $response = $this->actingAs($user)
                         ->get(route('suggestions'));

        $response->assertViewHas('recentSymptoms', fn($s) => $s->count() === 1);
    }

    // ──────────────────────────────────────────────────
    // Active conditions
    // ──────────────────────────────────────────────────

    #[Test]
    public function only_active_chronic_and_managed_conditions_are_returned(): void
    {
        $user    = User::factory()->create();
        $disease = Disease::factory()->create();

        // Active condition
        UserDisease::factory()->create([
            'user_id'    => $user->id,
            'disease_id' => $disease->id,
            'status'     => 'active',
        ]);
        // Recovered — should NOT appear
        UserDisease::factory()->create([
            'user_id'    => $user->id,
            'disease_id' => Disease::factory()->create()->id,
            'status'     => 'recovered',
        ]);

        $response = $this->actingAs($user)
                         ->get(route('suggestions'));

        $response->assertViewHas('activeConditions', fn($c) => $c->count() === 1);
    }

    #[Test]
    public function active_conditions_eager_load_disease_relationship(): void
    {
        $user    = User::factory()->create();
        $disease = Disease::factory()->create(['disease_name' => 'Hypertension']);

        UserDisease::factory()->create([
            'user_id'    => $user->id,
            'disease_id' => $disease->id,
            'status'     => 'active',
        ]);

        $response = $this->actingAs($user)
                         ->get(route('suggestions'));

        $response->assertViewHas('activeConditions', function ($conditions) {
            return $conditions->first()?->relationLoaded('disease');
        });
    }

    // ──────────────────────────────────────────────────
    // Latest-per-type metric logic
    // ──────────────────────────────────────────────────

    #[Test]
    public function only_most_recent_metric_per_type_is_used(): void
    {
        $user = User::factory()->create();

        UserHealth::factory()->create([
            'user_id'     => $user->id,
            'metric_type' => 'blood_pressure',
            'value'       => ['systolic' => 180, 'diastolic' => 110], // older, high
            'recorded_at' => now()->subDays(10),
        ]);
        UserHealth::factory()->create([
            'user_id'     => $user->id,
            'metric_type' => 'blood_pressure',
            'value'       => ['systolic' => 115, 'diastolic' => 75],  // newest, normal
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($user)
                         ->get(route('suggestions'));

        // With the most recent BP being normal, NO high-BP suggestion should fire
        $response->assertViewHas('suggestions', function (array $suggestions) {
            return !in_array('High Blood Pressure (Stage 2)', array_column($suggestions, 'title'))
                && !in_array('High Blood Pressure (Stage 1)', array_column($suggestions, 'title'));
        });
    }
}