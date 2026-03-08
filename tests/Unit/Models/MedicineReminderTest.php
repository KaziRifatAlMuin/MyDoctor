<?php

namespace Tests\Unit\Models;

use App\Models\MedicineReminder;
use App\Models\MedicineSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicineReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_reminder()
    {
        $schedule = MedicineSchedule::factory()->create();
        
        $reminder = MedicineReminder::create([
            'schedule_id' => $schedule->id,
            'reminder_at' => now(),
            'status' => 'pending'
        ]);

        $this->assertDatabaseHas('medicine_reminders', [
            'schedule_id' => $schedule->id,
            'status' => 'pending'
        ]);
    }

    public function test_reminder_belongs_to_schedule()
    {
        $schedule = MedicineSchedule::factory()->create();
        $reminder = MedicineReminder::factory()->create(['schedule_id' => $schedule->id]);

        $this->assertInstanceOf(MedicineSchedule::class, $reminder->schedule);
        $this->assertEquals($schedule->id, $reminder->schedule->id);
    }

    public function test_can_mark_reminder_as_taken()
    {
        $reminder = MedicineReminder::factory()->pending()->create();
        
        $reminder->markAsTaken();

        $this->assertEquals('taken', $reminder->fresh()->status);
        $this->assertNotNull($reminder->fresh()->taken_at);
    }

    public function test_can_mark_reminder_as_missed()
    {
        $reminder = MedicineReminder::factory()->pending()->create();
        
        $reminder->markAsMissed();

        $this->assertEquals('missed', $reminder->fresh()->status);
    }

    public function test_status_label_attribute()
    {
        $reminder = new MedicineReminder(['status' => 'taken']);
        $this->assertEquals('Taken', $reminder->status_label);
    }
}