<?php

namespace Database\Factories;

use App\Models\MedicineReminder;
use App\Models\MedicineSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicineReminderFactory extends Factory
{
    protected $model = MedicineReminder::class;

    public function definition(): array
    {
        $status = fake()->randomElement(['pending', 'taken', 'missed']);
        $takenAt = $status === 'taken' ? fake()->dateTimeBetween('-1 day', 'now') : null;

        return [
            'schedule_id' => MedicineSchedule::factory(),
            'reminder_at' => fake()->dateTimeBetween('-1 week', '+1 week'),
            'status' => $status,
            'taken_at' => $takenAt,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'taken_at' => null,
        ]);
    }

    public function taken(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'taken',
            'taken_at' => now(),
        ]);
    }

    public function missed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'missed',
            'taken_at' => null,
        ]);
    }
}
