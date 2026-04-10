<?php

namespace Tests\Feature;

use App\Models\UserHealth;
use App\Models\Medicine;
use App\Models\MedicineLog;
use App\Models\MedicineSchedule;
use App\Models\Symptom;
use App\Models\User;
use App\Models\UserSymptom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HealthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guests_are_redirected_from_health_page(): void
    {
        $this->get(route('health'))
             ->assertRedirect(route('login'));
    }

    #[Test]
    public function authenticated_user_can_access_health_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->get(route('health'))
             ->assertStatus(200);
    }

    #[Test]
    public function health_page_shows_health_metrics_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        UserHealth::factory()->count(3)->create(['user_id' => $user->id]);

        $this->actingAs($user)
             ->get(route('health'))
             ->assertStatus(200)
             ->assertViewHas('healthMetrics');
    }

    #[Test]
    public function health_page_shows_symptoms_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $catalog = Symptom::factory()->count(3)->create();
        foreach ($catalog as $symptom) {
            UserSymptom::factory()->create([
                'user_id' => $user->id,
                'symptom_id' => $symptom->id,
            ]);
        }

        $this->actingAs($user)
             ->get(route('health'))
             ->assertStatus(200)
             ->assertViewHas('symptoms');
    }

    #[Test]
    public function health_page_shows_medicines_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        Medicine::factory()->count(2)->create(['user_id' => $user->id]);

        $this->actingAs($user)
             ->get(route('health'))
             ->assertStatus(200)
             ->assertViewHas('medicines');
    }

    #[Test]
    public function health_page_shows_only_current_user_data(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        UserHealth::factory()->count(5)->create(['user_id' => $userA->id]);
        UserHealth::factory()->count(3)->create(['user_id' => $userB->id]);

        $response = $this->actingAs($userA)
                         ->get(route('health'));

        $response->assertViewHas('healthMetrics', function ($metrics) use ($userA) {
            return $metrics->every(fn($m) => $m->user_id === $userA->id);
        });
    }

    #[Test]
    public function health_page_calculates_adherence_rate_correctly(): void
    {
        $user     = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user->id]);

        // 3 scheduled, 3 taken → 100% adherence
        MedicineLog::factory()->create([
            'user_id'         => $user->id,
            'medicine_id'     => $medicine->id,
            'date'            => now()->subDays(1)->format('Y-m-d'),
            'total_scheduled' => 3,
            'total_taken'     => 3,
            'total_missed'    => 0,
        ]);

        $response = $this->actingAs($user)
                         ->get(route('health'));

        $response->assertViewHas('adherenceRate', 100);
    }

    #[Test]
    public function health_page_shows_zero_adherence_with_no_logs(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->get(route('health'))
             ->assertViewHas('adherenceRate', 0);
    }

    #[Test]
    public function health_page_groups_metrics_by_type(): void
    {
        $user = User::factory()->create();

        UserHealth::factory()->create([
            'user_id'     => $user->id,
            'metric_type' => 'heart_rate',
            'value'       => ['bpm' => 72, 'unit' => 'bpm'],
            'recorded_at' => now(),
        ]);
        UserHealth::factory()->create([
            'user_id'     => $user->id,
            'metric_type' => 'body_weight',
            'value'       => ['value' => 70, 'unit' => 'kg'],
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($user)
                         ->get(route('health'));

        $response->assertViewHas('metricsByType', function ($byType) {
            return $byType->has('heart_rate') && $byType->has('body_weight');
        });
    }

    #[Test]
    public function health_page_medicine_logs_limited_to_last_30_days(): void
    {
        $user     = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user->id]);

        // Old log (> 30 days) — should not appear
        MedicineLog::factory()->create([
            'user_id'         => $user->id,
            'medicine_id'     => $medicine->id,
            'date'            => now()->subDays(60)->format('Y-m-d'),
            'total_scheduled' => 2,
            'total_taken'     => 2,
            'total_missed'    => 0,
        ]);

        // Recent log (within 30 days) — should appear
        MedicineLog::factory()->create([
            'user_id'         => $user->id,
            'medicine_id'     => $medicine->id,
            'date'            => now()->subDays(5)->format('Y-m-d'),
            'total_scheduled' => 2,
            'total_taken'     => 1,
            'total_missed'    => 1,
        ]);

        $response = $this->actingAs($user)
                         ->get(route('health'));

        $response->assertViewHas('medicineLogs', function ($logs) {
            return $logs->count() === 1;
        });
    }
}
