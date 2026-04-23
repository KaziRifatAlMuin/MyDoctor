<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Models\Disease;
use App\Models\Comment;
use App\Models\PostLike;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;

class PostTest extends TestCase
{
    use RefreshDatabase;

#[Test]
    public function a_post_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $post->user);
        $this->assertEquals($user->id, $post->user->id);
    }

#[Test]
    public function a_post_belongs_to_a_disease()
    {
        $disease = Disease::factory()->create();
        $post = Post::factory()->create();
        $post->diseases()->detach();
        $post->diseases()->attach([$disease->id]);

        $this->assertInstanceOf(Disease::class, $post->primary_disease);
        $this->assertEquals($disease->id, $post->primary_disease->id);
    }

#[Test]
    public function it_normalizes_disease_ids_and_resolves_disease_models()
    {
        $diseaseA = Disease::factory()->create();
        $diseaseB = Disease::factory()->create();

        $post = Post::factory()->create();
        $post->diseases()->detach();
        $post->diseases()->attach([$diseaseA->id, $diseaseB->id]);

        $this->assertSame([$diseaseA->id, $diseaseB->id], $post->diseases->pluck('id')->sort()->values()->toArray());
        $this->assertCount(2, $post->diseases);
        $this->assertSame($diseaseA->id, $post->primary_disease?->id);
    }

#[Test]
    public function a_post_has_many_comments()
    {
        $post = Post::factory()->create();
        $comments = Comment::factory()->count(3)->create(['post_id' => $post->id]);

        $this->assertCount(3, $post->comments);
        $this->assertInstanceOf(Comment::class, $post->comments->first());
    }

#[Test]
    public function a_post_has_many_likes()
    {
        $post = Post::factory()->create();
        $likes = PostLike::factory()->count(3)->create(['post_id' => $post->id]);

        $this->assertCount(3, $post->likes);
        $this->assertInstanceOf(PostLike::class, $post->likes->first());
    }

#[Test]
    public function it_can_check_if_a_post_is_liked_by_a_user()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        
        // Should return false when not liked
        $this->assertFalse($post->isLikedBy($user));
        
        // Create a like
        PostLike::factory()->create([
            'user_id' => $user->id,
            'post_id' => $post->id
        ]);
        
        // Should return true after liking
        $this->assertTrue($post->isLikedBy($user));
    }

#[Test]
    public function it_returns_correct_file_url()
    {
        $post = Post::factory()->create([
            'file_path' => 'community/posts/test-image.jpg'
        ]);

        $this->assertEquals(
            asset('storage/community/posts/test-image.jpg'),
            $post->file_url
        );
    }

#[Test]
    public function it_returns_null_file_url_when_no_file()
    {
        $post = Post::factory()->create(['file_path' => null]);
        $this->assertNull($post->file_url);
    }

#[Test]
    public function it_returns_correct_file_icon_for_different_file_types()
    {
        $testCases = [
            ['type' => 'image/jpeg', 'expected' => 'fa-file-image'],
            ['type' => 'video/mp4', 'expected' => 'fa-file-video'],
            ['type' => 'audio/mp3', 'expected' => 'fa-file-audio'],
            ['type' => 'application/pdf', 'expected' => 'fa-file-pdf'],
            ['type' => 'application/msword', 'expected' => 'fa-file-word'],
            ['type' => 'application/vnd.ms-excel', 'expected' => 'fa-file-excel'],
            ['type' => 'text/plain', 'expected' => 'fa-file-alt'],
            ['type' => 'unknown/type', 'expected' => 'fa-file'],
        ];

        foreach ($testCases as $testCase) {
            $post = Post::factory()->create(['file_type' => $testCase['type']]);
            $this->assertEquals($testCase['expected'], $post->file_icon);
        }
    }

#[Test]
    public function it_formats_file_size_correctly()
    {
        $testCases = [
            ['size' => 500, 'expected' => '500 B'],
            ['size' => 1024, 'expected' => '1 KB'],
            ['size' => 1536, 'expected' => '1.5 KB'],
            ['size' => 1048576, 'expected' => '1 MB'],
            ['size' => 1572864, 'expected' => '1.5 MB'],
        ];

        foreach ($testCases as $testCase) {
            $post = Post::factory()->create(['file_size' => $testCase['size']]);
            $this->assertEquals($testCase['expected'], $post->formatted_file_size);
        }
    }

#[Test]
    public function it_handles_multiple_files_correctly()
    {
        $post = Post::factory()->withMultipleFiles()->create();
        
        $this->assertEquals(2, $post->file_count);
        $this->assertEquals(153600, $post->total_file_size); // 50KB + 100KB = 150KB
        $this->assertEquals('150 KB', $post->formatted_total_size);
        
        $allFiles = $post->all_files;
        $this->assertCount(2, $allFiles);
        $this->assertEquals('file1.jpg', $allFiles[0]['name']);
        $this->assertEquals('file2.pdf', $allFiles[1]['name']);
    }
}