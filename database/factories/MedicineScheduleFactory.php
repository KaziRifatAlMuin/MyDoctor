<?php

namespace Database\Factories;

use App\Models\Medicine;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicineScheduleFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-3 months', 'now');
        $end   = fake()->dateTimeBetween($start, '+3 months');

        return [
            'medicine_id'         => Medicine::inRandomOrder()->first()?->id ?? Medicine::factory(),
            'dosage_period_days'  => fake()->numberBetween(7, 90),
            'frequency_per_day'   => fake()->randomElement([1, 2, 3]),
            'interval_hours'      => fake()->randomElement([8, 12, 24]),
            'dosage_time_binary'  => fake()->regexify('[01]{3}'),
            'start_date'          => $start->format('Y-m-d'),
            'end_date'            => $end->format('Y-m-d'),
            'is_active'           => fake()->boolean(70),
        ];
    }
}
