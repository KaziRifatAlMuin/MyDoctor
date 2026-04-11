<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class HealthMetricFactory extends Factory
{
    public function definition(): array
    {
        $metricName = fake()->unique()->slug(2, '_');
        $fieldCount = fake()->numberBetween(1, 4);
        $fields = collect(range(1, $fieldCount))
            ->map(fn($index) => 'field_' . $index)
            ->all();

        return [
            'metric_name' => $metricName,
            'fields' => $fields,
        ];
    }
}
