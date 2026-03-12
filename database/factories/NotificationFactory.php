<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition()
    {
        $fromUser = User::factory()->create();
        $post = Post::factory()->create();

        return [
            'user_id' => User::factory(),
            'from_user_id' => $fromUser->id,
            'type' => $this->faker->randomElement(['like', 'comment', 'reply']),
            'notifiable_type' => Post::class,
            'notifiable_id' => $post->id,
            'message' => $this->faker->sentence,
            'data' => [
                'post_id' => $post->id,
                'post_preview' => $this->faker->sentence,
                'actor_name' => $fromUser->name,
            ],
            'read_at' => $this->faker->optional(0.7)->dateTime(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the notification is unread.
     */
    public function unread()
    {
        return $this->state(function (array $attributes) {
            return [
                'read_at' => null,
            ];
        });
    }

    /**
     * Indicate that the notification is read.
     */
    public function read()
    {
        return $this->state(function (array $attributes) {
            return [
                'read_at' => now(),
            ];
        });
    }

    /**
     * Indicate that the notification is a like.
     */
    public function like()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'like',
            ];
        });
    }

    /**
     * Indicate that the notification is a comment.
     */
    public function comment()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'comment',
            ];
        });
    }
}