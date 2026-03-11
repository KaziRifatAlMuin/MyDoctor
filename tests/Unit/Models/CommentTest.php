<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
use App\Models\CommentLike;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_comment_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $comment->user);
        $this->assertEquals($user->id, $comment->user->id);
    }

    /** @test */
    public function a_comment_belongs_to_a_post()
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id]);

        $this->assertInstanceOf(Post::class, $comment->post);
        $this->assertEquals($post->id, $comment->post->id);
    }

    /** @test */
    public function a_comment_has_many_likes()
    {
        $comment = Comment::factory()->create();
        $likes = CommentLike::factory()->count(3)->create(['comment_id' => $comment->id]);

        $this->assertCount(3, $comment->likes);
        $this->assertInstanceOf(CommentLike::class, $comment->likes->first());
    }

    /** @test */
    public function it_can_check_if_a_comment_is_liked_by_a_user()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();
        
        $this->assertFalse($comment->isLikedBy($user));
        
        CommentLike::factory()->create([
            'user_id' => $user->id,
            'comment_id' => $comment->id
        ]);
        
        $this->assertTrue($comment->isLikedBy($user));
    }
}