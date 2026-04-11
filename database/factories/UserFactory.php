<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserAddress;
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
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'is_active' => true,
            'email_verified_at' => now(),
            'password' => Hash::make('abcd1234'),
            'remember_token' => Str::random(10),
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

            UserAddress::query()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'division_id' => fake()->numberBetween(1, 8),
                    'division' => fake()->randomElement(['Dhaka', 'Chattogram', 'Rajshahi', 'Khulna', 'Barishal', 'Sylhet', 'Rangpur', 'Mymensingh']),
                    'district' => fake()->randomElement(['Dhaka', 'Chattogram', 'Rajshahi', 'Khulna', 'Sylhet', 'Barishal', 'Rangpur', 'Mymensingh']),
                    'district_id' => fake()->numberBetween(1, 64),
                    'upazila' => fake()->randomElement(['Mirpur', 'Uttara', 'Gulshan', 'Dhanmondi', 'Kotwali', 'Pahartali']),
                    'upazila_id' => fake()->numberBetween(1, 500),
                    'street' => fake()->streetName(),
                    'house' => fake()->buildingNumber(),
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