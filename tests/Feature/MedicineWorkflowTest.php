<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Medicine;
use App\Models\MedicineSchedule;
use App\Models\MedicineReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicineWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_medicine_workflow()
    {
        $user = User::factory()->create();
        
        // Step 1: Create a medicine
        $medicineData = [
            'medicine_name' => 'Aspirin',
            'type' => 'tablet',
            'value_per_dose' => 100,
            'unit' => 'mg',
            'rule' => 'after_food',
            'dose_limit' => 4
        ];

        $response = $this->actingAs($user)->post(route('medicine.store'), $medicineData);
        $response->assertRedirect(route('medicine.my-medicines'));

        $medicine = Medicine::where('user_id', $user->id)->first();
        $this->assertNotNull($medicine);

        // Step 2: Create a schedule for the medicine
        $scheduleData = [
            'medicine_id' => $medicine->id,
            'dosage_period_days' => 1,
            'frequency_per_day' => 2,
            'interval_hours' => 12,
            'dosage_time_binary' => '100000000000000000000000000000000000000000000000',
            'start_date' => now()->format('Y-m-d'),
            'is_active' => 1
        ];

        $response = $this->actingAs($user)->post(route('medicine.schedules.store'), $scheduleData);
        $response->assertRedirect(route('medicine.schedules', ['medicine_id' => $medicine->id]));

        $schedule = MedicineSchedule::where('medicine_id', $medicine->id)->first();
        $this->assertNotNull($schedule);

        // Step 3: Check that reminders were generated
        $reminders = MedicineReminder::where('schedule_id', $schedule->id)->get();
        $this->assertGreaterThan(0, $reminders->count());

        // Step 4: View reminders page
        $response = $this->actingAs($user)->get(route('medicine.reminders'));
        $response->assertStatus(200);

        // Step 5: Mark a reminder as taken
        $reminder = $reminders->first();
        $response = $this->actingAs($user)->post(route('medicine.reminders.taken', $reminder->id));
        $response->assertRedirect();

        // Step 6: Check logs were updated
        $response = $this->actingAs($user)->get(route('medicine.logs'));
        $response->assertStatus(200);

        // Step 7: Delete the medicine (should cascade delete schedules and reminders)
        $response = $this->actingAs($user)->delete(route('medicine.destroy', $medicine->id));
        $response->assertRedirect(route('medicine.my-medicines'));

        $this->assertDatabaseMissing('medicines', ['id' => $medicine->id]);
        $this->assertDatabaseMissing('medicine_schedules', ['medicine_id' => $medicine->id]);
        $this->assertDatabaseMissing('medicine_reminders', ['schedule_id' => $schedule->id]);
    }
}