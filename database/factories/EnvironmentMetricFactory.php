<?php

namespace Database\Factories;

use App\Models\Environment;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnvironmentMetricFactory extends Factory
{
    public function definition(): array
    {
        $metrics = [
            ['type' => 'aqi',          'min' => 0,   'max' => 500, 'unit' => 'AQI'],
            ['type' => 'pm2_5',        'min' => 0,   'max' => 250, 'unit' => 'µg/m³'],
            ['type' => 'pm10',         'min' => 0,   'max' => 600, 'unit' => 'µg/m³'],
            ['type' => 'humidity',     'min' => 10,  'max' => 100, 'unit' => '%'],
            ['type' => 'temperature',  'min' => 10,  'max' => 45,  'unit' => '°C'],
            ['type' => 'uv_index',     'min' => 0,   'max' => 11,  'unit' => 'UV'],
            ['type' => 'wind_speed',   'min' => 0,   'max' => 120, 'unit' => 'km/h'],
            ['type' => 'co2',          'min' => 350, 'max' => 2000,'unit' => 'ppm'],
        ];

        $metric = fake()->randomElement($metrics);

        return [
            'environment_id' => Environment::inRandomOrder()->first()?->id ?? Environment::factory(),
            'metric_type'    => $metric['type'],
            'value'          => fake()->randomFloat(2, $metric['min'], $metric['max']),
            'unit'           => $metric['unit'],
        ];
    }
}
