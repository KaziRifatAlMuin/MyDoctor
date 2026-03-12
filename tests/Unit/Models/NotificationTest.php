<?php

namespace Tests\Unit\Models;

use App\Models\Notification;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $fromUser;
    private $notification;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->fromUser = User::factory()->create();
        
        $this->notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'from_user_id' => $this->fromUser->id,
            'type' => 'like',
            'notifiable_type' => Post::class,
            'notifiable_id' => 1,
            'message' => 'Test notification',
            'data' => ['key' => 'value'],
            'read_at' => null,
        ]);
    }

    /** @test */
    public function it_can_create_a_notification()
    {
        $this->assertDatabaseHas('notifications', [
            'id' => $this->notification->id,
            'user_id' => $this->user->id,
            'from_user_id' => $this->fromUser->id,
            'type' => 'like',
            'message' => 'Test notification',
        ]);

        $this->assertInstanceOf(Notification::class, $this->notification);
    }

    /** @test */
    public function it_belongs_to_user()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->assertInstanceOf(User::class, $notification->user);
        $this->assertEquals($this->user->id, $notification->user->id);
    }

    /** @test */
    public function it_belongs_to_from_user()
    {
        $notification = Notification::factory()->create([
            'from_user_id' => $this->fromUser->id
        ]);

        $this->assertInstanceOf(User::class, $notification->fromUser);
        $this->assertEquals($this->fromUser->id, $notification->fromUser->id);
    }

    /** @test */
    public function it_can_be_morphed_to_notifiable()
    {
        $post = Post::factory()->create();
        $notification = Notification::factory()->create([
            'notifiable_type' => Post::class,
            'notifiable_id' => $post->id,
        ]);

        $this->assertInstanceOf(Post::class, $notification->notifiable);
        $this->assertEquals($post->id, $notification->notifiable->id);
    }

    /** @test */
    public function it_can_scope_unread_notifications()
    {
        // Clear any existing notifications
        Notification::query()->delete();
        
        // Create exactly 3 unread notifications
        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'from_user_id' => $this->fromUser->id,
            'read_at' => null,
        ]);

        // Create 2 read notifications
        Notification::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'from_user_id' => $this->fromUser->id,
            'read_at' => now(),
        ]);

        $unreadCount = Notification::unread()->count();

        $this->assertEquals(3, $unreadCount);
    }

    /** @test */
    public function it_can_mark_as_read()
    {
        $this->assertNull($this->notification->read_at);

        $this->notification->markAsRead();
        $this->notification->refresh();

        $this->assertNotNull($this->notification->read_at);
    }

    /** @test */
    public function it_can_check_if_read()
    {
        $this->assertFalse($this->notification->isRead());

        $this->notification->markAsRead();
        $this->notification->refresh();

        $this->assertTrue($this->notification->isRead());
    }

    /** @test */
    public function it_casts_data_to_array()
    {
        $data = ['post_id' => 1, 'comment_preview' => 'Test comment'];
        
        $notification = Notification::factory()->create([
            'data' => $data,
        ]);

        $this->assertIsArray($notification->data);
        $this->assertEquals($data, $notification->data);
        $this->assertEquals(1, $notification->data['post_id']);
        $this->assertEquals('Test comment', $notification->data['comment_preview']);
    }

    /** @test */
    public function it_casts_read_at_to_datetime()
    {
        $now = now();
        
        $notification = Notification::factory()->create([
            'read_at' => $now,
        ]);

        $this->assertInstanceOf(\DateTimeInterface::class, $notification->read_at);
        $this->assertEquals($now->toDateTimeString(), $notification->read_at->toDateTimeString());
    }
}