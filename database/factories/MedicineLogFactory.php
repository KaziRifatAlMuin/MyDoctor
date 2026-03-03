<?php

namespace Database\Factories;

use App\Models\Medicine;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicineLogFactory extends Factory
{
    public function definition(): array
    {
        $scheduled = fake()->numberBetween(1, 4);
        $taken     = fake()->numberBetween(0, $scheduled);
        $missed    = $scheduled - $taken;

        return [
            'medicine_id'     => Medicine::inRandomOrder()->first()?->id ?? Medicine::factory(),
            'user_id'         => User::inRandomOrder()->first()?->id ?? User::factory(),
            'date'            => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'total_scheduled' => $scheduled,
            'total_taken'     => $taken,
            'total_missed'    => $missed,
        ];
    }
}
