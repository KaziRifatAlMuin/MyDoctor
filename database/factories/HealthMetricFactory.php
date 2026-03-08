<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HealthMetricFactory extends Factory
{
    public function definition(): array
    {
        $types = [
            'blood_pressure'    => fn() => ['systolic' => fake()->numberBetween(90, 180),  'diastolic' => fake()->numberBetween(60, 120), 'unit' => 'mmHg'],
            'blood_glucose'     => fn() => ['value' => fake()->randomFloat(1, 70, 300),    'unit' => 'mg/dL'],
            'heart_rate'        => fn() => ['bpm' => fake()->numberBetween(50, 120),        'unit' => 'bpm'],
            'body_weight'       => fn() => ['value' => fake()->randomFloat(1, 40, 150),     'unit' => 'kg'],
            'bmi'               => fn() => ['value' => fake()->randomFloat(1, 15, 45),      'unit' => 'kg/m²'],
            'oxygen_saturation' => fn() => ['value' => fake()->numberBetween(90, 100),      'unit' => '%'],
            'temperature'       => fn() => ['value' => fake()->randomFloat(1, 36.0, 40.0),  'unit' => '°C'],
            'cholesterol'       => fn() => ['total' => fake()->numberBetween(120, 300), 'hdl' => fake()->numberBetween(30, 90), 'ldl' => fake()->numberBetween(50, 200), 'unit' => 'mg/dL'],
            'hemoglobin'        => fn() => ['value' => fake()->randomFloat(1, 8.0, 18.0),   'unit' => 'g/dL'],
            'creatinine'        => fn() => ['value' => fake()->randomFloat(1, 0.5, 5.0),    'unit' => 'mg/dL'],
            'respiratory_rate'  => fn() => ['value' => fake()->numberBetween(10, 30),        'unit' => 'breaths/min'],
            'blood_urea'        => fn() => ['value' => fake()->numberBetween(7, 40),         'unit' => 'mg/dL'],
            'uric_acid'         => fn() => ['value' => fake()->randomFloat(1, 2.0, 10.0),   'unit' => 'mg/dL'],
            'waist_circumference' => fn() => ['value' => fake()->randomFloat(1, 60, 120),   'unit' => 'cm'],
            'body_fat'          => fn() => ['value' => fake()->randomFloat(1, 8, 45),        'unit' => '%'],
        ];

        $type = fake()->randomElement(array_keys($types));

        return [
            'user_id'     => User::inRandomOrder()->first()?->id ?? User::factory(),
            'metric_type' => $type,
            'recorded_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'value'       => $types[$type](),
        ];
    }
}
