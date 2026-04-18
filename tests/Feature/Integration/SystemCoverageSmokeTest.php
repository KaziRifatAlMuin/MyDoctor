<?php

namespace Tests\Feature\Integration;

use App\Models\Disease;
use App\Models\Mailing;
use App\Models\Medicine;
use App\Models\MedicineReminder;
use App\Models\MedicineSchedule;
use App\Models\Post;
use App\Models\User;
use App\Models\UserDisease;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SystemCoverageSmokeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function mailbox_recipient_search_returns_matches_and_excludes_current_user(): void
    {
        $currentUser = User::factory()->create([
            'name' => 'Rahim Karim',
            'email' => 'rahim@example.com',
        ]);

        $target = User::factory()->create([
            'name' => 'Rahima Akter',
            'email' => 'rahima@example.com',
        ]);

        $other = User::factory()->create([
            'name' => 'Someone Else',
            'email' => 'someone@example.com',
        ]);

        $response = $this->actingAs($currentUser)
            ->getJson(route('profile.mailbox.recipients.search', ['q' => 'rahim']));

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $target->id,
                'name' => $target->name,
                'email' => $target->email,
            ])
            ->assertJsonMissing([
                'id' => $currentUser->id,
                'name' => $currentUser->name,
                'email' => $currentUser->email,
            ])
            ->assertJsonMissing([
                'id' => $other->id,
                'name' => $other->name,
                'email' => $other->email,
            ]);
    }

    #[Test]
    public function users_index_accepts_disease_filter_params_without_errors(): void
    {
        $viewer = User::factory()->create();
        $diseaseOne = Disease::factory()->create(['disease_name' => 'OR_AND_A']);
        $diseaseTwo = Disease::factory()->create(['disease_name' => 'OR_AND_B']);

        $both = User::factory()->create(['name' => 'Both Match']);
        $onlyOne = User::factory()->create(['name' => 'Only First']);
        $onlyTwo = User::factory()->create(['name' => 'Only Second']);

        UserDisease::factory()->create([
            'user_id' => $both->id,
            'disease_id' => $diseaseOne->id,
            'status' => 'active',
        ]);
        UserDisease::factory()->create([
            'user_id' => $both->id,
            'disease_id' => $diseaseTwo->id,
            'status' => 'active',
        ]);
        UserDisease::factory()->create([
            'user_id' => $onlyOne->id,
            'disease_id' => $diseaseOne->id,
            'status' => 'active',
        ]);
        UserDisease::factory()->create([
            'user_id' => $onlyTwo->id,
            'disease_id' => $diseaseTwo->id,
            'status' => 'active',
        ]);

        $orResponse = $this->actingAs($viewer)->get(route('users.index', [
            'diseases' => [$diseaseOne->id, $diseaseTwo->id],
            'disease_logic' => 'OR',
        ]));

        $orResponse->assertOk()
            ->assertSee('Both Match')
            ->assertSee('Only First')
            ->assertSee('Only Second');

        $andResponse = $this->actingAs($viewer)->get(route('users.index', [
            'diseases' => [$diseaseOne->id, $diseaseTwo->id],
            'disease_logic' => 'AND',
        ]));

        $andResponse->assertOk()
            ->assertSee('Both Match')
            ->assertSee('Only First')
            ->assertSee('Only Second');
    }

    #[Test]
public function user_can_complete_cross_module_journey()
{
    $user = User::factory()->create();
    $receiver = User::factory()->create();
    $disease = Disease::factory()->create(['disease_name' => 'Journey Disease']);

    $this->actingAs($user)
        ->post(route('health.metric.store'), [
            'metric_type' => 'heart_rate',
            'value_bpm' => 78,
            'recorded_at' => now()->format('Y-m-d'),
        ])
        ->assertRedirect(route('health') . '#metrics');

    $this->assertDatabaseHas('user_health', [
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->post(route('medicine.store'), [
            'medicine_name' => 'Integration Med',
            'type' => 'tablet',
            'value_per_dose' => 500,
            'unit' => 'mg',
            'rule' => 'after_food',
            'dose_limit' => 3,
        ])
        ->assertRedirect(route('medicine.my-medicines'));

    $medicine = Medicine::where('user_id', $user->id)->firstOrFail();


    $binaryWithTwoTimes = '1' . str_repeat('0', 23) . '1' . str_repeat('0', 23);

    $this->actingAs($user)
        ->post(route('medicine.schedules.store'), [
            'medicine_id' => $medicine->id,
            'dosage_period_days' => 1,
            'frequency_per_day' => 2,
            'interval_hours' => 12,
            'dosage_time_binary' => $binaryWithTwoTimes,
            'start_date' => now()->format('Y-m-d'),
            'is_active' => 1,
        ])
        ->assertRedirect(route('medicine.schedules', ['medicine_id' => $medicine->id]));

    $schedule = MedicineSchedule::where('medicine_id', $medicine->id)->firstOrFail();
    $this->assertDatabaseHas('medicine_reminders', ['schedule_id' => $schedule->id]);
    $this->assertGreaterThan(0, MedicineReminder::where('schedule_id', $schedule->id)->count());

    $communityResponse = $this->actingAs($user)
        ->postJson(route('community.posts.store'), [
            'disease_id' => $disease->id,
            'description' => 'Integration journey community post',
        ]);

    $communityResponse->assertOk()->assertJson([
        'success' => true,
    ]);

    $this->assertDatabaseHas('posts', [
        'user_id' => $user->id,
        'disease_id' => $disease->id,
        'description' => 'Integration journey community post',
    ]);

    $post = Post::where('user_id', $user->id)->latest()->firstOrFail();
    $this->actingAs($user)
        ->putJson(route('community.posts.like', $post), [])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'liked' => true,
        ]);

    $this->actingAs($user)
        ->post(route('profile.mailbox.store'), [
            'receiver_id' => $receiver->id,
            'title' => 'Integration Message',
            'message' => 'All modules are working together.',
        ])
        ->assertRedirect(route('profile.mailbox.sent'));

    $this->assertDatabaseHas('mailings', [
        'sender_id' => $user->id,
        'receiver_id' => $receiver->id,
        'title' => 'Integration Message',
        'status' => 'unread',
    ]);

    $this->assertSame(1, Mailing::where('sender_id', $user->id)->count());
}
}
