<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Notification;
use App\Services\CommunityNotificationService;
use App\Notifications\CommunityPushNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CommunityNotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private $service;
    private $postOwner;
    private $liker;
    private $post;
    private $comment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CommunityNotificationService();
        
        $this->postOwner = User::factory()->create();
        $this->liker = User::factory()->create();
        
        $this->post = Post::factory()->create([
            'user_id' => $this->postOwner->id,
            'description' => 'This is a test post with enough content to test the preview functionality',
        ]);
        
        $this->comment = Comment::factory()->create([
            'user_id' => $this->liker->id,
            'post_id' => $this->post->id,
            'comment_details' => 'This is a test comment with enough content to test preview',
        ]);
    }

#[Test]
    public function it_creates_notification_when_post_is_liked()
    {
        NotificationFacade::fake();

        $this->service->postLiked($this->post, $this->liker);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->postOwner->id,
            'from_user_id' => $this->liker->id,
            'type' => 'like',
            'notifiable_type' => Post::class,
            'notifiable_id' => $this->post->id,
            'message' => "{$this->liker->name} liked your post",
        ]);

        $notification = Notification::where('user_id', $this->postOwner->id)
            ->where('type', 'like')
            ->first();

        $this->assertNotNull($notification);
        $this->assertEquals($this->post->id, $notification->data['post_id']);
        $this->assertEquals($this->liker->name, $notification->data['actor_name']);
        
        // Check preview is truncated correctly
        $expectedPreview = substr($this->post->description, 0, 50) . '...';
        $this->assertEquals($expectedPreview, $notification->data['post_preview']);
    }

#[Test]
    public function it_does_not_create_notification_when_user_likes_own_post()
    {
        $this->service->postLiked($this->post, $this->postOwner);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->postOwner->id,
            'from_user_id' => $this->postOwner->id,
            'type' => 'like',
        ]);
    }

#[Test]
    public function it_creates_notification_when_comment_is_added()
    {
        NotificationFacade::fake();

        $this->service->commentAdded($this->comment, $this->post);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->postOwner->id,
            'from_user_id' => $this->liker->id,
            'type' => 'comment',
            'notifiable_type' => Comment::class,
            'notifiable_id' => $this->comment->id,
            'message' => "{$this->liker->name} commented on your post",
        ]);

        $notification = Notification::where('user_id', $this->postOwner->id)
            ->where('type', 'comment')
            ->first();

        $this->assertNotNull($notification);
        $this->assertEquals($this->post->id, $notification->data['post_id']);
        $this->assertEquals($this->comment->id, $notification->data['comment_id']);
        $this->assertEquals($this->liker->name, $notification->data['actor_name']);
        
        // Check preview is truncated correctly
        $expectedPreview = substr($this->comment->comment_details, 0, 50) . '...';
        $this->assertEquals($expectedPreview, $notification->data['comment_preview']);
    }

#[Test]
    public function it_does_not_create_notification_when_user_comments_on_own_post()
    {
        $comment = Comment::factory()->create([
            'user_id' => $this->postOwner->id,
            'post_id' => $this->post->id,
        ]);

        $this->service->commentAdded($comment, $this->post);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->postOwner->id,
            'from_user_id' => $this->postOwner->id,
            'type' => 'comment',
        ]);
    }

#[Test]
    public function it_truncates_long_post_preview_correctly()
    {
        $longPost = Post::factory()->create([
            'user_id' => $this->postOwner->id,
            'description' => str_repeat('a', 100),
        ]);

        $this->service->postLiked($longPost, $this->liker);

        $notification = Notification::where('user_id', $this->postOwner->id)
            ->where('type', 'like')
            ->first();

        $expectedPreview = str_repeat('a', 50) . '...';
        $this->assertEquals($expectedPreview, $notification->data['post_preview']);
    }

#[Test]
    public function it_truncates_long_comment_preview_correctly()
    {
        $longComment = Comment::factory()->create([
            'user_id' => $this->liker->id,
            'post_id' => $this->post->id,
            'comment_details' => str_repeat('b', 100),
        ]);

        $this->service->commentAdded($longComment, $this->post);

        $notification = Notification::where('user_id', $this->postOwner->id)
            ->where('type', 'comment')
            ->first();

        $expectedPreview = str_repeat('b', 50) . '...';
        $this->assertEquals($expectedPreview, $notification->data['comment_preview']);
    }

#[Test]
    public function it_sends_push_notification_when_user_has_push_enabled()
    {
        NotificationFacade::fake();
        
        // Enable push notifications
        $this->postOwner->push_notifications = true;
        $this->postOwner->save();

        $this->service->postLiked($this->post, $this->liker);

        $notification = Notification::where('user_id', $this->postOwner->id)
            ->where('type', 'like')
            ->first();

        NotificationFacade::assertSentTo(
            $this->postOwner,
            CommunityPushNotification::class,
            function ($pushNotification, $channels) use ($notification) {
                return $pushNotification->notification->id === $notification->id;
            }
        );
    }

#[Test]
    public function it_does_not_send_push_notification_when_user_has_push_disabled()
    {
        NotificationFacade::fake();
        
        // Disable push notifications
        $this->postOwner->push_notifications = false;
        $this->postOwner->save();

        $this->service->postLiked($this->post, $this->liker);

        NotificationFacade::assertNotSentTo($this->postOwner, CommunityPushNotification::class);
    }
}