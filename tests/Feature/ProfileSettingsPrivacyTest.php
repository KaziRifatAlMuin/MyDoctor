<?php

namespace Tests\Feature;

use App\Models\Disease;
use App\Models\User;
use App\Models\UserDisease;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileSettingsPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_update_persists_public_visibility_permissions(): void
    {
        $user = User::factory()->create();
        
        // Ensure setting exists first
        $setting = $user->setting()->firstOrCreate([]);
        $setting->update([
            'email_notifications' => false,
            'show_personal_info' => false,
            'show_diseases' => false,
            'show_chatbot' => false,
            'show_notification_badge' => false,
            'show_mail_badge' => false,
        ]);

        $response = $this->actingAs($user)->put(route('profile.setting.update'), [
            'email_notifications' => '1',
            'show_personal_info' => '1',
            // show_diseases intentionally omitted to verify false handling
            'show_chatbot' => '1',
            'show_notification_badge' => '1',
            'show_mail_badge' => '1',
            'reminder_before_minutes' => 15,
        ]);

        $response->assertRedirect();
        $response->assertCookie('chatbot_bubble_enabled', '1');

        $user->refresh();
        $user->load('setting');

        // Assert all values
        $this->assertTrue($user->setting->email_notifications, 'email_notifications should be true');
        $this->assertTrue($user->setting->show_personal_info, 'show_personal_info should be true');
        $this->assertFalse($user->setting->show_diseases, 'show_diseases should be false');
        $this->assertTrue($user->setting->show_chatbot, 'show_chatbot should be true');
        $this->assertTrue($user->setting->show_notification_badge, 'show_notification_badge should be true');
        $this->assertTrue($user->setting->show_mail_badge, 'show_mail_badge should be true');
        
        // Check notification_settings separately
        $notificationSettings = $user->notification_settings ?? [];
        $this->assertSame(15, (int) ($notificationSettings['reminder_before_minutes'] ?? 0), 'reminder_before_minutes should be 15');
    }

    public function test_public_profile_respects_privacy_permissions(): void
    {
        $disease = Disease::factory()->create(['disease_name' => 'Diabetes']);

        $user = User::factory()->create([
            'occupation' => 'Software Engineer',
            'blood_group' => 'B+',
        ]);
        $user->address()->updateOrCreate([], [
            'district' => 'Sunamganj',
            'upazila' => 'Sadar',
            'street' => 'Nona Fall',
            'house' => '2878',
        ]);
        $user->setting()->update([
            'show_personal_info' => false,
            'show_diseases' => false,
        ]);

        UserDisease::factory()->create([
            'user_id' => $user->id,
            'disease_id' => $disease->id,
            'status' => 'managed',
        ]);

        $viewer = User::factory()->create();

        $hiddenResponse = $this->actingAs($viewer)->get(route('users.show', $user));
        $hiddenResponse->assertOk();
        $hiddenResponse->assertDontSee('Personal Information');
        $hiddenResponse->assertSee('has not granted permission to display disease information publicly', false);
        $hiddenResponse->assertDontSee('Software Engineer');
        $hiddenResponse->assertDontSee('Sadar');
        $hiddenResponse->assertDontSee('Sunamganj');
        $hiddenResponse->assertDontSee('Diabetes');

        $user->setting()->update([
            'show_personal_info' => true,
            'show_diseases' => true,
        ]);

        $visibleResponse = $this->actingAs($viewer)->get(route('users.show', $user));
        $visibleResponse->assertOk();
        $visibleResponse->assertSee('Software Engineer');
        $visibleResponse->assertSee('B+');
        $visibleResponse->assertSee('Sadar');
        $visibleResponse->assertSee('Sunamganj');
        $visibleResponse->assertDontSee('Nona Fall');
        $visibleResponse->assertDontSee('2878');
        $visibleResponse->assertSee('Diabetes');
        $visibleResponse->assertSee('(Managed)');
    }
}