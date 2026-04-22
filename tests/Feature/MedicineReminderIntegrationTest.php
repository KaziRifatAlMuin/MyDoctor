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
        $reminder = MedicineReminder::factory()->create([
            'schedule_id' => $schedule->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($user)->post(route('medicine.reminders.taken', $reminder->id));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Medicine marked as taken.');
        $this->assertEquals('taken', $reminder->fresh()->status);
        $this->assertNotNull($reminder->fresh()->taken_at);
    }

    public function test_user_cannot_mark_other_users_reminder_as_taken()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user2->id]);
        $schedule = MedicineSchedule::factory()->create(['medicine_id' => $medicine->id]);
        $reminder = MedicineReminder::factory()->create([
            'schedule_id' => $schedule->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($user1)->post(route('medicine.reminders.taken', $reminder->id));

        $response->assertStatus(302);
        $this->assertEquals('pending', $reminder->fresh()->status);
    }

    public function test_user_can_mark_reminder_as_missed()
    {
        $user = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user->id]);
        $schedule = MedicineSchedule::factory()->create(['medicine_id' => $medicine->id]);
        $reminder = MedicineReminder::factory()->create([
            'schedule_id' => $schedule->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($user)->post(route('medicine.reminders.missed', $reminder->id));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Medicine marked as missed.');
        $this->assertEquals('missed', $reminder->fresh()->status);
    }

    public function test_user_cannot_mark_other_users_reminder_as_missed()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user2->id]);
        $schedule = MedicineSchedule::factory()->create(['medicine_id' => $medicine->id]);
        $reminder = MedicineReminder::factory()->create([
            'schedule_id' => $schedule->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($user1)->post(route('medicine.reminders.missed', $reminder->id));

        $response->assertStatus(302);
        $this->assertEquals('pending', $reminder->fresh()->status);
    }

    public function test_user_can_mark_reminder_as_taken_from_notification()
    {
        $user = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user->id]);
        $schedule = MedicineSchedule::factory()->create(['medicine_id' => $medicine->id]);
        $reminder = MedicineReminder::factory()->create([
            'schedule_id' => $schedule->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($user)->post(route('medicine.reminders.taken-from-notification', $reminder->id));

        $response->assertJson([
            'success' => true,
            'message' => 'Medicine marked as taken'
        ]);
        $this->assertEquals('taken', $reminder->fresh()->status);
    }

    public function test_user_cannot_mark_other_users_reminder_as_taken_from_notification()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user2->id]);
        $schedule = MedicineSchedule::factory()->create(['medicine_id' => $medicine->id]);
        $reminder = MedicineReminder::factory()->create([
            'schedule_id' => $schedule->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($user1)->post(route('medicine.reminders.taken-from-notification', $reminder->id));

        $response->assertJson([
            'success' => false,
            'message' => 'Unauthorized'
        ]);
        $response->assertStatus(403);
        $this->assertEquals('pending', $reminder->fresh()->status);
    }

    public function test_user_can_mark_reminder_as_missed_from_notification()
    {
        $user = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user->id]);
        $schedule = MedicineSchedule::factory()->create(['medicine_id' => $medicine->id]);
        $reminder = MedicineReminder::factory()->create([
            'schedule_id' => $schedule->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($user)->post(route('medicine.reminders.missed-from-notification', $reminder->id));

        $response->assertJson([
            'success' => true,
            'message' => 'Medicine marked as missed'
        ]);
        $this->assertEquals('missed', $reminder->fresh()->status);
    }

    public function test_user_cannot_mark_other_users_reminder_as_missed_from_notification()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user2->id]);
        $schedule = MedicineSchedule::factory()->create(['medicine_id' => $medicine->id]);
        $reminder = MedicineReminder::factory()->create([
            'schedule_id' => $schedule->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($user1)->post(route('medicine.reminders.missed-from-notification', $reminder->id));

        $response->assertJson([
            'success' => false,
            'message' => 'Unauthorized'
        ]);
        $response->assertStatus(403);
        $this->assertEquals('pending', $reminder->fresh()->status);
    }

    public function test_user_cannot_mark_multiple_other_users_reminders_as_taken()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $medicine1 = Medicine::factory()->create(['user_id' => $user1->id]);
        $medicine2 = Medicine::factory()->create(['user_id' => $user2->id]);
        
        $schedule1 = MedicineSchedule::factory()->create(['medicine_id' => $medicine1->id]);
        $schedule2 = MedicineSchedule::factory()->create(['medicine_id' => $medicine2->id]);
        
        $reminder1 = MedicineReminder::factory()->create([
            'schedule_id' => $schedule1->id,
            'status' => 'pending'
        ]);
        $reminder2 = MedicineReminder::factory()->create([
            'schedule_id' => $schedule2->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($user1)->post(route('medicine.reminders.mark-multiple-taken'), [
            'ids' => [$reminder1->id, $reminder2->id]
        ]);

        $response->assertJson([
            'success' => true,
            'count' => 1
        ]);
        
        $this->assertEquals('taken', $reminder1->fresh()->status);
        $this->assertEquals('pending', $reminder2->fresh()->status);
    }
}