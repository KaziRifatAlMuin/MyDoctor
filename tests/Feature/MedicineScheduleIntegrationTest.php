<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Medicine;
use App\Models\MedicineSchedule;
use App\Models\MedicineReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicineScheduleIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_schedule_and_generate_reminders()
    {
        $user = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user->id]);

        // Binary string with 2 times selected (00:00 and 12:00)
        // 48 bits: 1 at position 0 (00:00) and 1 at position 24 (12:00)
        $binaryWithTwoTimes = '1' . str_repeat('0', 23) . '1' . str_repeat('0', 23);

        $response = $this->actingAs($user)->post(route('medicine.schedules.store'), [
            'medicine_id' => $medicine->id,
            'dosage_period_days' => 1,
            'frequency_per_day' => 2,
            'interval_hours' => 12,
            'dosage_time_binary' => $binaryWithTwoTimes,
            'start_date' => now()->format('Y-m-d'),
            'is_active' => 1
        ]);

        $response->assertRedirect(route('medicine.schedules', ['medicine_id' => $medicine->id]));
        
        $this->assertDatabaseHas('medicine_schedules', [
            'medicine_id' => $medicine->id,
            'is_active' => 1
        ]);

        $schedule = MedicineSchedule::where('medicine_id', $medicine->id)->first();
        $this->assertNotNull($schedule);
        
        $reminders = MedicineReminder::where('schedule_id', $schedule->id)->get();
        $this->assertGreaterThan(0, $reminders->count());
    }

    public function test_user_cannot_create_schedule_for_other_users_medicine()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user2->id]);

        $binaryWithOneTime = '1' . str_repeat('0', 47);

        $response = $this->actingAs($user1)->post(route('medicine.schedules.store'), [
            'medicine_id' => $medicine->id,
            'dosage_period_days' => 1,
            'frequency_per_day' => 1,
            'dosage_time_binary' => $binaryWithOneTime,
            'start_date' => now()->format('Y-m-d'),
            'is_active' => 1
        ]);

        $response->assertStatus(404);
    }
}