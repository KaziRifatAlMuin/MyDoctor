<?php

namespace Database\Factories;

use App\Models\MedicineSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicineReminderFactory extends Factory
{
    public function definition(): array
    {
        $status  = fake()->randomElement(['pending', 'taken', 'missed']);
        $takenAt = $status === 'taken'
            ? fake()->dateTimeBetween('-1 month', 'now')
            : null;

        return [
            'schedule_id' => MedicineSchedule::inRandomOrder()->first()?->id ?? MedicineSchedule::factory(),
            'reminder_at' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'status'      => $status,
            'taken_at'    => $takenAt,
        ];
    }
}
