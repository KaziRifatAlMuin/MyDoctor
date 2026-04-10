<?php

namespace Database\Factories;

use App\Models\HealthMetric;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserHealthFactory extends Factory
{
    public function definition(): array
    {
        $definition = HealthMetric::query()->inRandomOrder()->first();

        if (!$definition) {
            $definition = HealthMetric::factory()->create([
                'metric_name' => 'blood_pressure',
                'fields' => ['systolic', 'diastolic'],
            ]);
        }

        $values = [];
        foreach ((array) $definition->fields as $field) {
            $values[$field] = fake()->randomFloat(1, 40, 220);
        }

        return [
            'user_id' => User::factory(),
            'health_metric_id' => $definition->id,
            'value' => $values,
            'recorded_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
