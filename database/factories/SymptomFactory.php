<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SymptomFactory extends Factory
{
    public function definition(): array
    {
        $symptoms = [
            'Headache', 'Fever', 'Cough', 'Fatigue', 'Nausea',
            'Dizziness', 'Chest pain', 'Shortness of breath', 'Joint pain', 'Back pain',
            'Abdominal pain', 'Loss of appetite', 'Insomnia', 'Palpitations', 'Blurred vision',
            'Swollen feet', 'Dry mouth', 'Frequent urination', 'Skin rash', 'Numbness',
        ];

        return [
            'user_id'        => User::inRandomOrder()->first()?->id ?? User::factory(),
            'symptom_name'   => fake()->randomElement($symptoms),
            'severity_level' => fake()->numberBetween(1, 10),
            'note'           => fake()->optional(0.6)->sentence(),
            'recorded_at'    => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
