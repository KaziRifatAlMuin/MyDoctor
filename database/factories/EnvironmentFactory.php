<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnvironmentFactory extends Factory
{
    public function definition(): array
    {
        $locations = [
            ['name' => 'Dhaka',       'lat' => 23.8103, 'lon' => 90.4125],
            ['name' => 'Chittagong',  'lat' => 22.3569, 'lon' => 91.7832],
            ['name' => 'Rajshahi',    'lat' => 24.3636, 'lon' => 88.6241],
            ['name' => 'Khulna',      'lat' => 22.8456, 'lon' => 89.5403],
            ['name' => 'Sylhet',      'lat' => 24.8949, 'lon' => 91.8687],
            ['name' => 'Barishal',    'lat' => 22.7010, 'lon' => 90.3535],
            ['name' => 'Rangpur',     'lat' => 25.7439, 'lon' => 89.2752],
            ['name' => 'Mymensingh',  'lat' => 24.7471, 'lon' => 90.4203],
        ];

        $loc = fake()->randomElement($locations);

        return [
            'user_id'           => User::factory(),
            'location_name'     => $loc['name'],
            'latitude'          => $loc['lat'] + fake()->randomFloat(4, -0.05, 0.05),
            'longitude'         => $loc['lon'] + fake()->randomFloat(4, -0.05, 0.05),
            'recorded_at'       => fake()->dateTimeBetween('-3 months', 'now'),
            'weather_condition' => fake()->randomElement(['Sunny', 'Cloudy', 'Rainy', 'Foggy', 'Humid', 'Windy', 'Stormy']),
        ];
    }
}
