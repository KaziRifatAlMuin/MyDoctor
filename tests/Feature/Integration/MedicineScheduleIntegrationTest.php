<?php

namespace Tests\Feature\Integration;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Medicine;
use App\Models\MedicineSchedule;
use App\Models\MedicineLog;

class MedicineScheduleIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_medicine_logs_reflect_on_overview_adherence()
    {
        $user = User::factory()->create();

        $med = Medicine::factory()->create(['user_id' => $user->id]);

        MedicineSchedule::factory()->create([
            'medicine_id' => $med->id,
            'is_active' => true,
            'start_date' => now()->subDays(10)->toDateString(),
        ]);

        MedicineLog::factory()->create([
            'user_id' => $user->id,
            'medicine_id' => $med->id,
            'date' => now()->toDateString(),
            'total_scheduled' => 3,
            'total_taken' => 2,
            'total_missed' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('health'));
        $response->assertStatus(200);

        // Check numbers appear on the page (taken / missed / total)
        $response->assertSee('2');
        $response->assertSee('1');
        $response->assertSee('3');
    }
}
