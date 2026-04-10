<?php

namespace Tests\Feature\Community;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Disease;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class PostManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $anotherUser;
    protected $post;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->anotherUser = User::factory()->create();
        $this->post = Post::factory()->create([
            'user_id' => $this->user->id,
            'description' => 'Original description'
        ]);
    }

#[Test]
    public function user_can_edit_their_own_post()
    {
        $this->actingAs($this->user);

        $response = $this->post("/community/posts/{$this->post->id}/update", [
            'description' => 'Updated description'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Post updated successfully!'
        ]);

        $this->assertDatabaseHas('posts', [
            'id' => $this->post->id,
            'description' => 'Updated description'
        ]);
    }

#[Test]
    public function user_cannot_edit_others_post()
    {
        $this->actingAs($this->anotherUser);

        $response = $this->post("/community/posts/{$this->post->id}/update", [
            'description' => 'Updated description'
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'You can only edit your own posts'
        ]);

        $this->assertDatabaseHas('posts', [
            'id' => $this->post->id,
            'description' => 'Original description'
        ]);
    }

#[Test]
    public function user_can_delete_their_own_post()
    {
        $this->actingAs($this->user);

        $response = $this->post("/community/posts/{$this->post->id}/delete");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Post deleted successfully!'
        ]);

        $this->assertDatabaseMissing('posts', [
            'id' => $this->post->id
        ]);
    }

#[Test]
    public function user_cannot_delete_others_post()
    {
        $this->actingAs($this->anotherUser);

        $response = $this->post("/community/posts/{$this->post->id}/delete");

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'You can only delete your own posts'
        ]);

        $this->assertDatabaseHas('posts', [
            'id' => $this->post->id
        ]);
    }

#[Test]
    public function user_can_edit_their_own_comment()
    {
        $this->actingAs($this->user);
        
        $comment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'post_id' => $this->post->id,
            'comment_details' => 'Original comment'
        ]);

        $response = $this->post("/community/comments/{$comment->id}/update", [
            'description' => 'Updated comment'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Comment updated successfully!'
        ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'comment_details' => 'Updated comment'
        ]);
    }

#[Test]
    public function user_cannot_edit_others_comment()
    {
        $this->actingAs($this->anotherUser);
        
        $comment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'post_id' => $this->post->id
        ]);

        $response = $this->post("/community/comments/{$comment->id}/update", [
            'description' => 'Updated comment'
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'You can only edit your own comments'
        ]);
    }

#[Test]
    public function user_can_delete_their_own_comment()
    {
        $this->actingAs($this->user);
        
        $comment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'post_id' => $this->post->id
        ]);

        $response = $this->post("/community/comments/{$comment->id}/delete");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Comment deleted successfully!'
        ]);

        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id
        ]);
    }

#[Test]
    public function user_cannot_delete_others_comment()
    {
        $this->actingAs($this->anotherUser);
        
        $comment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'post_id' => $this->post->id
        ]);

        $response = $this->post("/community/comments/{$comment->id}/delete");

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'You can only delete your own comments'
        ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id
        ]);
    }
}