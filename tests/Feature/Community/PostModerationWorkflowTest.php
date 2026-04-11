<?php

namespace Tests\Feature\Community;

use App\Models\Disease;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PostModerationWorkflowTest extends TestCase
{
    use RefreshDatabase;

#[Test]
    public function unapproved_posts_are_hidden_from_public_feed(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $disease = Disease::factory()->create();

        $pendingPost = Post::factory()->create([
            'user_id' => $owner->id,
            'disease_id' => $disease->id,
            'description' => 'PENDING_UNIQUE_POST_TEXT',
            'is_approved' => false,
        ]);

        $approvedPost = Post::factory()->create([
            'user_id' => $owner->id,
            'disease_id' => $disease->id,
            'description' => 'APPROVED_UNIQUE_POST_TEXT',
            'is_approved' => true,
        ]);

        $response = $this->actingAs($viewer)->get('/community/posts');

        $response->assertStatus(200);
        $response->assertViewHas('posts', function ($posts) use ($approvedPost, $pendingPost) {
            $collection = $posts->getCollection();
            return $collection->contains('id', $approvedPost->id)
                && !$collection->contains('id', $pendingPost->id);
        });
    }

#[Test]
    public function user_can_view_own_pending_posts_page(): void
    {
        $owner = User::factory()->create();
        $disease = Disease::factory()->create();

        $ownPending = Post::factory()->create([
            'user_id' => $owner->id,
            'disease_id' => $disease->id,
            'description' => 'MY_PENDING_POST_TEXT',
            'is_approved' => false,
        ]);

        Post::factory()->create([
            'disease_id' => $disease->id,
            'description' => 'SOMEONE_ELSE_PENDING_POST_TEXT',
            'is_approved' => false,
        ]);

        $response = $this->actingAs($owner)->get('/community/posts/pending');

        $response->assertStatus(200);
        $response->assertSee('Pending Posts');
        $response->assertSee('MY_PENDING_POST_TEXT');
        $response->assertDontSee('SOMEONE_ELSE_PENDING_POST_TEXT');

        $response->assertViewHas('posts', function ($posts) use ($ownPending) {
            return $posts->getCollection()->contains('id', $ownPending->id);
        });
    }

#[Test]
    public function admin_can_approve_pending_post(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $post = Post::factory()->create(['is_approved' => false]);

        $response = $this->actingAs($admin)->patch("/community/posts/{$post->id}/approve");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'approved' => true,
        ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'is_approved' => true,
        ]);
    }

#[Test]
    public function any_authenticated_user_can_report_a_post(): void
    {
        $reporter = User::factory()->create();
        $post = Post::factory()->create(['is_reported' => false]);

        $response = $this->actingAs($reporter)->post("/community/posts/{$post->id}/report");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'reported' => true,
        ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'is_reported' => true,
        ]);
    }

#[Test]
    public function editing_a_post_sets_is_edited_to_true(): void
    {
        $owner = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $owner->id,
            'description' => 'Initial text',
            'is_edited' => false,
        ]);

        $response = $this->actingAs($owner)->patch("/community/posts/{$post->id}", [
            'description' => 'Updated text',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'description' => 'Updated text',
            'is_edited' => true,
        ]);
    }
}
