<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserSetting;
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
            'role' => 'member',
            'occupation' => fake()->jobTitle(),
            'blood_group' => fake()->randomElement(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-']),
            'email_verified_at' => now(),
            'password' => Hash::make('abcd1234'),
            'remember_token' => Str::random(10),
            'notification_settings' => [
                'reminders' => fake()->boolean(),
                'updates' => fake()->boolean(),
                'newsletter' => fake()->boolean(),
            ],
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (User $user): void {
            UserSetting::query()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'email_notifications' => true,
                    'push_notifications' => true,
                    'show_personal_info' => false,
                    'show_diseases' => false,
                    'show_chatbot' => true,
                    'show_notification_badge' => true,
                    'show_mail_badge' => true,
                ]
            );
        });
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}