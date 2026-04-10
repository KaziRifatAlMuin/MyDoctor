<?php

namespace Database\Factories;

use App\Models\Symptom;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserSymptomFactory extends Factory
{
    public function definition(): array
    {
        $symptomId = Symptom::query()->inRandomOrder()->value('id');

        if (!$symptomId) {
            $symptoms = array_keys(config('health.symptoms'));
            $name = fake()->randomElement($symptoms);
            $symptomId = Symptom::firstOrCreate(['name' => $name])->id;
        }

        return [
            'user_id' => User::factory(),
            'symptom_id' => $symptomId,
            'severity_level' => fake()->numberBetween(1, 10),
            'note' => fake()->optional(0.6)->sentence(),
            'recorded_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
