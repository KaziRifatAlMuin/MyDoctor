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
use PHPUnit\Framework\Attributes\Test;

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

#[Test]
    public function user_can_like_a_post()
    {
        $this->actingAs($this->user);

        $response = $this->put("/community/posts/{$this->post->id}/likes");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'liked' => true,
            'count' => 1
        ]);

        $this->assertDatabaseHas('user_posts', [
            'user_id' => $this->user->id,
            'post_id' => $this->post->id
        ]);
    }

#[Test]
    public function user_can_unlike_a_post()
    {
        $this->actingAs($this->user);
        
        // First like
        PostLike::create([
            'user_id' => $this->user->id,
            'post_id' => $this->post->id
        ]);

        // Then unlike
        $response = $this->put("/community/posts/{$this->post->id}/likes");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'liked' => false,
            'count' => 0
        ]);

        $this->assertDatabaseMissing('user_posts', [
            'user_id' => $this->user->id,
            'post_id' => $this->post->id
        ]);
    }

#[Test]
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

#[Test]
    public function user_can_like_a_comment()
    {
        $this->actingAs($this->user);
        
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id
        ]);

        $response = $this->put("/community/comments/{$comment->id}/likes");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'liked' => true,
            'count' => 1
        ]);

        $this->assertDatabaseHas('post_comments', [
            'user_id' => $this->user->id,
            'comment_id' => $comment->id
        ]);
    }

#[Test]
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

        $response = $this->put("/community/comments/{$comment->id}/likes");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'liked' => false,
            'count' => 0
        ]);

        $this->assertDatabaseMissing('post_comments', [
            'user_id' => $this->user->id,
            'comment_id' => $comment->id
        ]);
    }

#[Test]
    public function user_can_star_a_post(): void
    {
        $this->actingAs($this->user);

        $response = $this->put("/community/posts/{$this->post->id}/star");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'starred' => true,
            'liked' => false,
            'count' => 0,
        ]);

        $this->assertDatabaseHas('user_posts', [
            'user_id' => $this->user->id,
            'post_id' => $this->post->id,
            'is_starred' => true,
        ]);
    }

#[Test]
    public function starred_posts_page_is_accessible_and_contains_starred_post(): void
    {
        $this->actingAs($this->user);

        PostLike::create([
            'user_id' => $this->user->id,
            'post_id' => $this->post->id,
            'is_starred' => true,
        ]);

        $response = $this->get('/community/posts/starred');

        $response->assertStatus(200);
        $response->assertSee('Starred Posts');
        $response->assertViewHas('posts', function ($posts) {
            return $posts->getCollection()->contains('id', $this->post->id);
        });
    }

#[Test]
    public function user_can_star_and_unstar_disease_with_history_timestamps(): void
    {
        $this->actingAs($this->user);
        $disease = Disease::factory()->create();

        $starResponse = $this->put("/community/diseases/{$disease->id}/star");
        $starResponse->assertStatus(200)->assertJson([
            'success' => true,
            'starred' => true,
        ]);

        $userAfterStar = $this->user->fresh();
        $this->assertContains($disease->id, $userAfterStar->getStarredDiseaseIds());

        $historyAfterStar = collect($userAfterStar->getStarredDiseaseHistory())
            ->first(fn (array $entry): bool => (int) $entry['disease_id'] === (int) $disease->id && $entry['unstarred_at'] === null);

        $this->assertNotNull($historyAfterStar);
        $this->assertNotEmpty($historyAfterStar['starred_at']);

        $unstarResponse = $this->put("/community/diseases/{$disease->id}/star");
        $unstarResponse->assertStatus(200)->assertJson([
            'success' => true,
            'starred' => false,
        ]);

        $userAfterUnstar = $this->user->fresh();
        $this->assertNotContains($disease->id, $userAfterUnstar->getStarredDiseaseIds());

        $historyAfterUnstar = collect($userAfterUnstar->getStarredDiseaseHistory())
            ->last(fn (array $entry): bool => (int) $entry['disease_id'] === (int) $disease->id);

        $this->assertNotNull($historyAfterUnstar);
        $this->assertNotEmpty($historyAfterUnstar['unstarred_at']);
    }

#[Test]
    public function starred_disease_history_page_is_accessible_for_members(): void
    {
        $this->actingAs($this->user);
        $disease = Disease::factory()->create();

        $this->put("/community/diseases/{$disease->id}/star");

        $response = $this->get('/community/diseases/starred/history');

        $response->assertStatus(200);
        $response->assertSee('Starred Disease History');
        $response->assertSee($disease->display_name);
    }
}