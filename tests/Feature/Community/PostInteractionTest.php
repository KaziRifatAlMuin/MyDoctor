<?php

namespace Tests\Feature\Community;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Disease;
use App\Models\PostLike;
use App\Models\CommentLike;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostInteractionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $post;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->post = Post::factory()->create([
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function user_can_like_a_post()
    {
        $this->actingAs($this->user);

        $response = $this->post("/community/posts/{$this->post->id}/like");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'liked' => true,
            'count' => 1
        ]);

        $this->assertDatabaseHas('post_likes', [
            'user_id' => $this->user->id,
            'post_id' => $this->post->id
        ]);
    }

    /** @test */
    public function user_can_unlike_a_post()
    {
        $this->actingAs($this->user);
        
        // First like
        PostLike::create([
            'user_id' => $this->user->id,
            'post_id' => $this->post->id
        ]);

        // Then unlike
        $response = $this->post("/community/posts/{$this->post->id}/like");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'liked' => false,
            'count' => 0
        ]);

        $this->assertDatabaseMissing('post_likes', [
            'user_id' => $this->user->id,
            'post_id' => $this->post->id
        ]);
    }

    /** @test */
    public function user_can_add_comment_to_post()
    {
        $this->actingAs($this->user);

        $response = $this->post("/community/posts/{$this->post->id}/comments", [
            'comment_details' => 'This is a test comment'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Comment added successfully!'
        ]);

        $this->assertDatabaseHas('comments', [
            'user_id' => $this->user->id,
            'post_id' => $this->post->id,
            'comment_details' => 'This is a test comment'
        ]);

        // Check if post comment count increased
        $this->assertEquals(1, $this->post->fresh()->comment_count);
    }

    /** @test */
    public function user_can_like_a_comment()
    {
        $this->actingAs($this->user);
        
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id
        ]);

        $response = $this->post("/community/comments/{$comment->id}/like");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'liked' => true,
            'count' => 1
        ]);

        $this->assertDatabaseHas('comment_likes', [
            'user_id' => $this->user->id,
            'comment_id' => $comment->id
        ]);
    }

    /** @test */
    public function user_can_unlike_a_comment()
    {
        $this->actingAs($this->user);
        
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id
        ]);
        
        CommentLike::create([
            'user_id' => $this->user->id,
            'comment_id' => $comment->id
        ]);

        $response = $this->post("/community/comments/{$comment->id}/like");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'liked' => false,
            'count' => 0
        ]);

        $this->assertDatabaseMissing('comment_likes', [
            'user_id' => $this->user->id,
            'comment_id' => $comment->id
        ]);
    }
}