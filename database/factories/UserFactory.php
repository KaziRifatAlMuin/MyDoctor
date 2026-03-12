<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;  

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'picture' => null,
            'name' => fake()->name(),
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'occupation' => fake()->jobTitle(),
            'blood_group' => fake()->randomElement(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-']),
            'email_verified_at' => now(),
            'password' => Hash::make('abcd1234'),
            'remember_token' => Str::random(10),
            'email_notifications' => fake()->boolean(),
            'push_notifications' => fake()->boolean(),
            'notification_settings' => json_encode([
                'reminders' => fake()->boolean(),
                'updates' => fake()->boolean(),
                'newsletter' => fake()->boolean(),
            ]),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}