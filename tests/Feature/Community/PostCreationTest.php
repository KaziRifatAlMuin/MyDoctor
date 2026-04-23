<?php

namespace Tests\Feature\Community;

use Tests\TestCase;
use App\Models\User;
use App\Models\Disease;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;

class PostCreationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $disease;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->disease = Disease::factory()->create();
    }

#[Test]
    public function authenticated_user_can_create_a_post()
    {
        $this->actingAs($this->user);

        $response = $this->post('/community/posts', [
            'disease_ids' => [$this->disease->id],
            'description' => 'This is a test post description',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'requires_approval' => true,
            'message' => 'Post submitted for admin approval.'
        ]);

        $this->assertDatabaseHas('posts', [
            'user_id' => $this->user->id,
            'description' => 'This is a test post description',
            'is_approved' => false,
        ]);

        $post = Post::query()->where('user_id', $this->user->id)->latest('id')->first();
        $this->assertDatabaseHas('post_diseases', [
            'post_id' => $post->id,
            'disease_id' => $this->disease->id,
        ]);
        $this->assertSame([$this->disease->id], $post->diseases->pluck('id')->toArray());
    }

#[Test]
    public function unauthenticated_user_cannot_create_a_post()
    {
        $response = $this->post('/community/posts', [
            'disease_ids' => [$this->disease->id],
            'description' => 'This is a test post',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

#[Test]
    public function post_requires_at_least_description_or_file()
    {
        $this->actingAs($this->user);

        $response = $this->post('/community/posts', [
            'disease_ids' => [$this->disease->id],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Please write something or attach at least one file'
        ]);
    }

#[Test]
    public function post_requires_valid_disease_tags()
    {
        $this->actingAs($this->user);

        $response = $this->post('/community/posts', [
            'disease_ids' => [99999],
            'description' => 'Test description',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['disease_ids.0']);
    }

#[Test]
    public function authenticated_user_can_create_a_post_with_multiple_disease_tags()
    {
        $this->actingAs($this->user);
        $secondaryDisease = Disease::factory()->create();

        $response = $this->post('/community/posts', [
            'disease_ids' => [$this->disease->id, $secondaryDisease->id],
            'description' => 'Post tagged with multiple diseases',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'requires_approval' => true,
        ]);

        $post = Post::query()->where('user_id', $this->user->id)->latest('id')->firstOrFail();

        $this->assertDatabaseHas('post_diseases', [
            'post_id' => $post->id,
            'disease_id' => $this->disease->id,
        ]);

        $this->assertDatabaseHas('post_diseases', [
            'post_id' => $post->id,
            'disease_id' => $secondaryDisease->id,
        ]);

        $this->assertEqualsCanonicalizing(
            [$this->disease->id, $secondaryDisease->id],
            $post->diseases()->pluck('diseases.id')->all()
        );
    }

#[Test]
    public function user_can_upload_single_file_with_post()
    {
        Storage::fake('public');
        
        $this->actingAs($this->user);
        
        $file = UploadedFile::fake()->create('test-image.jpg', 100, 'image/jpeg');

        $response = $this->post('/community/posts', [
            'disease_ids' => [$this->disease->id],
            'description' => 'Post with image',
            'files' => [$file], // Note: 'files' array, not 'file'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('posts', [
            'user_id' => $this->user->id,
            'description' => 'Post with image',
        ]);

        // Get the post to check files
        $post = Post::where('user_id', $this->user->id)->first();
        $this->assertNotNull($post->files);
        $this->assertCount(1, $post->files);
    }

#[Test]
    public function user_can_upload_multiple_files_with_post()
    {
        Storage::fake('public');
        
        $this->actingAs($this->user);
        
        $file1 = UploadedFile::fake()->create('image1.jpg', 100, 'image/jpeg');
        $file2 = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->post('/community/posts', [
            'disease_ids' => [$this->disease->id],
            'description' => 'Post with multiple files',
            'files' => [$file1, $file2],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $post = Post::where('user_id', $this->user->id)->first();
        $this->assertNotNull($post->files);
        $this->assertCount(2, $post->files);
    }

#[Test]
    public function file_size_cannot_exceed_10mb_per_file()
    {
        Storage::fake('public');
        
        $this->actingAs($this->user);
        
        $file = UploadedFile::fake()->create('large.pdf', 11 * 1024); // 11MB

        $response = $this->post('/community/posts', [
            'disease_ids' => [$this->disease->id],
            'description' => 'Post with large file',
            'files' => [$file],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['files.0']);
    }

#[Test]
    public function total_file_size_cannot_exceed_50mb()
    {
        Storage::fake('public');
        
        $this->actingAs($this->user);
        
        $file1 = UploadedFile::fake()->create('file1.pdf', 30 * 1024); // 30MB
        $file2 = UploadedFile::fake()->create('file2.pdf', 30 * 1024); // 30MB

        $response = $this->post('/community/posts', [
            'disease_ids' => [$this->disease->id],
            'description' => 'Post with large files',
            'files' => [$file1, $file2],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['files.0', 'files.1']);
    }
}