<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Notification;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $fromUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->fromUser = User::factory()->create();
    }

#[Test]
    public function it_redirects_to_login_for_guest_index()
    {
        $response = $this->get(route('notifications.index'));

        $response->assertRedirect(route('login'));
    }

#[Test]
    public function it_returns_json_notifications_for_api_request()
    {
        Notification::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'from_user_id' => $this->fromUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('notifications.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'notifications' => [
                    '*' => [
                        'id',
                        'user_id',
                        'from_user',
                        'type',
                        'message',
                        'data',
                        'read_at',
                        'created_at',
                    ]
                ],
                'unread_count',
            ]);

        $this->assertCount(5, $response->json('notifications'));
    }

#[Test]
    public function it_limits_notifications_to_5_for_api_request()
    {
        Notification::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'from_user_id' => $this->fromUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('notifications.index', ['limit' => 5]));

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('notifications'));
    }

#[Test]
    public function it_returns_notifications_page_for_web_request()
    {
        Notification::factory()->count(15)->create([
            'user_id' => $this->user->id,
            'from_user_id' => $this->fromUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('notifications.index'));

        $response->assertStatus(200)
            ->assertViewIs('notifications.index')
            ->assertViewHas('notifications');

        $this->assertCount(15, $response->viewData('notifications'));
    }

#[Test]
    public function it_returns_unread_count()
    {
        Notification::query()->delete();
        
        // Create 2 unread and 3 read notifications
        Notification::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'read_at' => null,
        ]);

        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'read_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('notifications.unread-count'));

        $response->assertStatus(200)
            ->assertJson(['count' => 2]);
    }

#[Test]
    public function it_returns_unauthorized_for_guest_unread_count()
    {
        $response = $this->getJson(route('notifications.unread-count'));

        $response->assertStatus(401);
    }

#[Test]
    public function it_can_mark_notification_as_read()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'read_at' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('notifications.read', $notification->id));

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Check that read_at is not null without comparing exact timestamp
        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id,
            'read_at' => null,
        ]);
    }

#[Test]
    public function it_returns_not_found_when_marking_others_notification_as_read()
    {
        $otherUser = User::factory()->create();
        
        $notification = Notification::factory()->create([
            'user_id' => $otherUser->id,
            'from_user_id' => $this->fromUser->id,
            'read_at' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('notifications.read', $notification->id));

        // Your controller might return 403 or 404 - update this to match your implementation
        $response->assertStatus(404); // or 404, depending on your controller
    }

#[Test]
    public function it_can_mark_all_notifications_as_read()
    {
        Notification::query()->delete();
        
        Notification::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'read_at' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('notifications.mark-all-read'));

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals(
            0,
            Notification::where('user_id', $this->user->id)->unread()->count()
        );
    }

#[Test]
    public function it_can_delete_a_notification()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson(route('notifications.delete', $notification->id));

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id,
        ]);
    }

#[Test]
    public function it_returns_not_found_when_deleting_others_notification()
    {
        $otherUser = User::factory()->create();
        
        $notification = Notification::factory()->create([
            'user_id' => $otherUser->id,
            'from_user_id' => $this->fromUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson(route('notifications.delete', $notification->id));

        // Your controller might return 403 or 404 - update this to match your implementation
        $response->assertStatus(404); // or 404, depending on your controller
    }

#[Test]
    public function it_can_clear_all_notifications()
    {
        Notification::query()->delete();
        
        Notification::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'from_user_id' => $this->fromUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson(route('notifications.clear-all'));

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals(
            0,
            Notification::where('user_id', $this->user->id)->count()
        );
    }

#[Test]
    public function it_returns_not_found_when_marking_nonexistent_notification()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('notifications.read', 99999));

        // Your controller might return 404 or 500 - update this to match your implementation
        $response->assertStatus(404);
    }

#[Test]
    public function it_returns_not_found_when_deleting_nonexistent_notification()
    {
        $response = $this->actingAs($this->user)
            ->deleteJson(route('notifications.delete', 99999));

        // Your controller might return 404 or 500 - update this to match your implementation
        $response->assertStatus(404);
    }

#[Test]
    public function it_returns_unauthorized_for_guest_when_marking_read()
    {
        $notification = Notification::factory()->create();

        $response = $this->postJson(route('notifications.read', $notification->id));

        $response->assertStatus(401);
    }

#[Test]
    public function it_returns_unauthorized_for_guest_when_clearing_all()
    {
        $response = $this->deleteJson(route('notifications.clear-all'));

        $response->assertStatus(401);
    }

#[Test]
    public function it_updates_unread_count_after_marking_read()
    {
        Notification::query()->delete();
        
        Notification::factory()->count(4)->create([
            'user_id' => $this->user->id,
            'read_at' => null,
        ]);

        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'read_at' => null,
        ]);

        $this->actingAs($this->user)
            ->postJson(route('notifications.read', $notification->id));

        $response = $this->actingAs($this->user)
            ->getJson(route('notifications.unread-count'));

        $response->assertJson(['count' => 4]);
    }

#[Test]
    public function it_handles_notifications_with_valid_from_user()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'from_user_id' => $this->fromUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('notifications.index'));

        $response->assertStatus(200);
        $this->assertNotNull($response->json('notifications.0.from_user'));
        $this->assertEquals($this->fromUser->name, $response->json('notifications.0.from_user.name'));
    }

#[Test]
    public function it_correctly_formats_notification_data_for_api()
    {
        $post = Post::factory()->create();
        
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'from_user_id' => $this->fromUser->id,
            'type' => 'like',
            'notifiable_type' => Post::class,
            'notifiable_id' => $post->id,
            'data' => [
                'post_id' => $post->id,
                'post_preview' => 'Test preview',
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('notifications.index'));

        $response->assertStatus(200);
        
        $notificationData = $response->json('notifications.0');
        $this->assertEquals($notification->id, $notificationData['id']);
        $this->assertEquals('like', $notificationData['type']);
        $this->assertEquals($this->fromUser->name, $notificationData['from_user']['name']);
        $this->assertEquals($post->id, $notificationData['data']['post_id']);
    }
}