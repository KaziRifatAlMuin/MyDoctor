<?php

namespace Database\Factories;

use App\Models\CommentLike;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentLikeFactory extends Factory
{
    protected $model = CommentLike::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'comment_id' => Comment::factory(),
        ];
    }
}