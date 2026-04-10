<?php

namespace Tests\Unit\Models;

use App\Models\Medicine;
use App\Models\MedicineSchedule;
use App\Models\MedicineReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicineScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_schedule()
    {
        $medicine = Medicine::factory()->create();
        
        $schedule = MedicineSchedule::create([
            'medicine_id' => $medicine->id,
            'dosage_period_days' => 1,
            'frequency_per_day' => 2,
            'interval_hours' => 12,
            'dosage_time_binary' => '100000000000000000000000000000000000000000000000',
            'start_date' => now(),
            'is_active' => true
        ]);

        $this->assertDatabaseHas('medicine_schedules', [
            'medicine_id' => $medicine->id,
            'dosage_period_days' => 1
        ]);
    }

    public function test_schedule_belongs_to_medicine()
    {
        $medicine = Medicine::factory()->create();
        $schedule = MedicineSchedule::factory()->create(['medicine_id' => $medicine->id]);

        $this->assertInstanceOf(Medicine::class, $schedule->medicine);
        $this->assertEquals($medicine->id, $schedule->medicine->id);
    }

    public function test_schedule_has_many_reminders()
    {
        $schedule = MedicineSchedule::factory()->create();
        $reminder = MedicineReminder::factory()->create(['schedule_id' => $schedule->id]);

        $this->assertTrue($schedule->reminders->contains($reminder));
        $this->assertEquals(1, $schedule->reminders->count());
    }

    public function test_dosage_times_array_attribute()
    {
        $schedule = new MedicineSchedule([
            'dosage_time_binary' => '100000000000000000000000000000000000000000000000'
        ]);
        
        $times = $schedule->dosage_times_array;
        $this->assertContains('00:00', $times);
        $this->assertCount(1, $times);
    }

    public function test_period_label_attribute()
    {
        $schedule = new MedicineSchedule(['dosage_period_days' => 1]);
        $this->assertEquals('Daily', $schedule->period_label);
    }
}