<?php

namespace Tests\Feature\Community;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Disease;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostListingTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function anyone_can_view_posts()
    {
        $this->actingAs($this->user);
        
        $disease = Disease::factory()->create([
            'disease_name' => 'Diabetes View Test'
        ]);
        
        Post::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'disease_id' => $disease->id
        ]);

        $response = $this->get('/community/forum');
        $response->assertStatus(200);
    }

    /** @test */
    public function posts_can_be_filtered_by_disease()
    {
        $this->actingAs($this->user);
        
        $disease1 = Disease::factory()->create([
            'disease_name' => 'Diabetes Filter Test 1'
        ]);
        $disease2 = Disease::factory()->create([
            'disease_name' => 'Hypertension Filter Test 2'
        ]);
        
        Post::factory()->count(3)->create([
            'disease_id' => $disease1->id, 
            'user_id' => $this->user->id
        ]);
        Post::factory()->count(2)->create([
            'disease_id' => $disease2->id, 
            'user_id' => $this->user->id
        ]);

        $response = $this->get('/community/forum?disease=' . $disease1->id);
        $response->assertStatus(200);
    }

    /** @test */
    public function posts_are_paginated()
    {
        $this->actingAs($this->user);
        
        $disease = Disease::factory()->create([
            'disease_name' => 'Diabetes Pagination Test'
        ]);
        
        Post::factory()->count(15)->create([
            'user_id' => $this->user->id,
            'disease_id' => $disease->id
        ]);

        $response = $this->get('/community/forum');
        $response->assertStatus(200);
    }

    /** @test */
    public function comments_can_be_loaded_for_a_post()
    {
        $this->actingAs($this->user);
        
        $disease = Disease::factory()->create([
            'disease_name' => 'Diabetes Comment Test'
        ]);
        
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'disease_id' => $disease->id
        ]);
        
        Comment::factory()->count(5)->create([
            'post_id' => $post->id, 
            'user_id' => $this->user->id
        ]);

        $response = $this->get("/community/posts/{$post->id}/comments");
        $response->assertStatus(200);
        $response->assertJsonCount(5, 'comments');
    }

    /** @test */
    public function disease_filter_sidebar_shows_all_diseases()
    {
        $this->actingAs($this->user);
        
        Disease::factory()->create(['disease_name' => 'Asthma Test']);
        Disease::factory()->create(['disease_name' => 'Arthritis Test']);
        Disease::factory()->create(['disease_name' => 'Cancer Test']);

        $response = $this->get('/community/forum');
        $response->assertStatus(200);
    }

    /** @test */
    public function trending_diseases_are_shown()
    {
        $this->actingAs($this->user);
        
        $disease1 = Disease::factory()->create([
            'disease_name' => 'Diabetes Trending 1'
        ]);
        $disease2 = Disease::factory()->create([
            'disease_name' => 'Hypertension Trending 2'
        ]);
        
        Post::factory()->count(5)->create([
            'disease_id' => $disease1->id, 
            'user_id' => $this->user->id
        ]);
        Post::factory()->count(3)->create([
            'disease_id' => $disease2->id, 
            'user_id' => $this->user->id
        ]);

        $response = $this->get('/community/forum');
        $response->assertStatus(200);
    }
}