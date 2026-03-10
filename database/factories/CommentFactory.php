<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
            'comment_details' => $this->faker->paragraph,
            'file_path' => null,
            'file_type' => null,
            'file_name' => null,
            'file_size' => null,
            'like_count' => 0,
        ];
    }

    public function withImage()
    {
        return $this->state(function (array $attributes) {
            return [
                'file_path' => 'community/comments/test-image.jpg',
                'file_type' => 'image/jpeg',
                'file_name' => 'test-image.jpg',
                'file_size' => 1024 * 30, // 30KB
            ];
        });
    }
}