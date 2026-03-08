<?php

namespace Database\Factories;

use App\Models\Medicine;
use App\Models\MedicineSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicineScheduleFactory extends Factory
{
    protected $model = MedicineSchedule::class;

    public function definition(): array
    {
        // Generate a binary string (48 characters of 0s and 1s)
        $binary = '';
        for ($i = 0; $i < 48; $i++) {
            $binary .= fake()->randomElement(['0', '1']);
        }

        // Handle end_date properly - either a date or null
        $endDate = null;
        if (fake()->boolean(70)) { // 70% chance of having an end date
            $endDate = fake()->dateTimeBetween('+1 month', '+3 months')->format('Y-m-d');
        }

        return [
            'medicine_id' => Medicine::factory(),
            'dosage_period_days' => fake()->randomElement([1, 7, 14, 30]),
            'frequency_per_day' => fake()->numberBetween(1, 4),
            'interval_hours' => fake()->randomElement([4, 6, 8, 12, null]),
            'dosage_time_binary' => $binary,
            'start_date' => fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'end_date' => $endDate,
            'is_active' => fake()->boolean(80), // 80% chance of being active
        ];
    }

    /**
     * Indicate that the schedule has no end date.
     */
    public function noEndDate(): static
    {
        return $this->state(fn (array $attributes) => [
            'end_date' => null,
        ]);
    }

    /**
     * Indicate that the schedule is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
