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
        $user->setting()->update([
            'email_notifications' => false,
            'push_notifications' => false,
            'show_personal_info' => false,
            'show_diseases' => false,
            'show_chatbot' => false,
            'show_notification_badge' => false,
            'show_mail_badge' => false,
        ]);

        $response = $this->actingAs($user)->put(route('profile.setting.update'), [
            'email_notifications' => '1',
            'push_notifications' => '1',
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

        $this->assertTrue($user->setting->email_notifications);
        $this->assertTrue($user->setting->push_notifications);
        $this->assertTrue($user->setting->show_personal_info);
        $this->assertFalse($user->setting->show_diseases);
        $this->assertTrue($user->setting->show_chatbot);
        $this->assertTrue($user->setting->show_notification_badge);
        $this->assertTrue($user->setting->show_mail_badge);
        $this->assertSame(15, (int) ($user->notification_settings['reminder_before_minutes'] ?? 0));
    }

    public function test_public_profile_respects_privacy_permissions(): void
    {
        $disease = Disease::factory()->create(['disease_name' => 'Diabetes']);

        $user = User::factory()->create([
            'occupation' => 'Software Engineer',
            'blood_group' => 'B+',
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

        $hiddenResponse = $this->get(route('users.show', $user));
        $hiddenResponse->assertOk();
        $hiddenResponse->assertSee('has not granted permission to display additional personal details publicly', false);
        $hiddenResponse->assertSee('has not granted permission to display disease information publicly', false);
        $hiddenResponse->assertDontSee('Software Engineer');
        $hiddenResponse->assertDontSee('Diabetes');

        $user->setting()->update([
            'show_personal_info' => true,
            'show_diseases' => true,
        ]);

        $visibleResponse = $this->get(route('users.show', $user));
        $visibleResponse->assertOk();
        $visibleResponse->assertSee('Software Engineer');
        $visibleResponse->assertSee('B+');
        $visibleResponse->assertSee('Diabetes');
        $visibleResponse->assertSee('(Managed)');
    }
}
