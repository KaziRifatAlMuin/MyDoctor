<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SymptomFactory extends Factory
{
    public function definition(): array
    {
        $symptoms = array_keys(config('health.symptoms'));

        return [
            'user_id'        => User::inRandomOrder()->first()?->id ?? 1,
            'symptom_name'   => fake()->randomElement($symptoms),
            'severity_level' => fake()->numberBetween(1, 10),
            'note'           => fake()->optional(0.6)->sentence(),
            'recorded_at'    => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
