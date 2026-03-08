<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Medicine;
use App\Models\MedicineSchedule;
use App\Models\MedicineReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicineReminderIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_reminders_page()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get(route('medicine.reminders'));

        $response->assertStatus(200);
        $response->assertViewIs('medicine.reminders');
    }

    public function test_user_can_mark_reminder_as_taken()
    {
        $user = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user->id]);
        $schedule = MedicineSchedule::factory()->create(['medicine_id' => $medicine->id]);
        $reminder = MedicineReminder::factory()->pending()->create(['schedule_id' => $schedule->id]);

        $response = $this->actingAs($user)->post(route('medicine.reminders.taken', $reminder->id));

        $response->assertRedirect();
        $this->assertEquals('taken', $reminder->fresh()->status);
        $this->assertNotNull($reminder->fresh()->taken_at);
    }

    public function test_user_cannot_mark_other_users_reminder_as_taken()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user2->id]);
        $schedule = MedicineSchedule::factory()->create(['medicine_id' => $medicine->id]);
        $reminder = MedicineReminder::factory()->create(['schedule_id' => $schedule->id]);

        $response = $this->actingAs($user1)->post(route('medicine.reminders.taken', $reminder->id));

        $response->assertStatus(404);
    }
}