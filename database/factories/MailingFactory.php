<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Mailing;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mailing>
 */
class MailingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Mailing>
     */
    protected $model = Mailing::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sender_id' => User::factory(),
            'receiver_id' => User::factory(),
            'title' => $this->faker->sentence(),
            'message' => $this->faker->paragraph(3),
            'is_read' => false,
            'is_starred' => false,
            'status' => 'unread',
        ];
    }

    /**
     * Indicate that the mailing has been sent.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'is_read' => true,
        ]);
    }

    /**
     * Indicate that the mailing has failed.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'receiver_id' => null,
            'is_read' => false,
        ]);
    }

    /**
     * Indicate that the mailing is pending.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
            'is_read' => true,
        ]);
    }
}
