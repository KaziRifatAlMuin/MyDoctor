<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\Disease;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function configure(): static
    {
        return $this->afterCreating(function (Post $post): void {
            $diseaseIds = Disease::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $post->diseases()->attach($diseaseIds);
        });
    }

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'description' => $this->faker->paragraphs(3, true),
            'is_anonymous' => false,
            'is_approved' => true,
            'is_edited' => false,
            'is_reported' => false,
            'file_path' => null,
            'file_type' => null,
            'file_name' => null,
            'file_size' => null,
            'files' => null,
            'like_count' => 0,
            'comment_count' => 0,
        ];
    }

    public function withImage()
    {
        return $this->state(function (array $attributes) {
            return [
                'file_path' => 'community/posts/test-image.jpg',
                'file_type' => 'image/jpeg',
                'file_name' => 'test-image.jpg',
                'file_size' => 1024 * 50, // 50KB
            ];
        });
    }

    public function withMultipleFiles()
    {
        return $this->state(function (array $attributes) {
            return [
                'files' => [
                    [
                        'path' => 'community/posts/file1.jpg',
                        'type' => 'image/jpeg',
                        'name' => 'file1.jpg',
                        'size' => 1024 * 50,
                    ],
                    [
                        'path' => 'community/posts/file2.pdf',
                        'type' => 'application/pdf',
                        'name' => 'file2.pdf',
                        'size' => 1024 * 100,
                    ],
                ],
                'file_path' => 'community/posts/file1.jpg',
                'file_type' => 'image/jpeg',
                'file_name' => 'file1.jpg',
                'file_size' => 1024 * 50,
            ];
        });
    }
}