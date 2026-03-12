<?php

namespace Database\Factories;

use App\Models\Disease;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserDiseaseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'       => User::inRandomOrder()->first()?->id ?? 1,
            'disease_id'    => Disease::inRandomOrder()->first()?->id ?? Disease::factory(),
            'diagnosed_at'  => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'status'        => fake()->randomElement(['active', 'recovered', 'chronic', 'managed']),
            'notes'         => fake()->optional(0.6)->paragraph(1),
        ];
    }
}
