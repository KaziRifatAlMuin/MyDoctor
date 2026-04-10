<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SymptomFactory extends Factory
{
    public function definition(): array
    {
        $symptoms = array_keys(config('health.symptoms'));

        return [
            'name' => fake()->unique()->randomElement($symptoms),
        ];
    }
}
