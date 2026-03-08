<?php

namespace Database\Factories;

use App\Models\MedicineLog;
use App\Models\Medicine;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicineLogFactory extends Factory
{
    protected $model = MedicineLog::class;

    public function definition(): array
    {
        static $dateCounter = 0;
        $dateCounter++;
        
        $scheduled = fake()->numberBetween(1, 6);
        $taken = fake()->numberBetween(0, $scheduled);
        $missed = $scheduled - $taken;

        return [
            'medicine_id' => Medicine::factory(),
            'user_id' => User::factory(),
            'date' => now()->subDays($dateCounter)->format('Y-m-d'),
            'total_scheduled' => $scheduled,
            'total_taken' => $taken,
            'total_missed' => $missed,
        ];
    }

    public function perfect(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_scheduled' => 3,
            'total_taken' => 3,
            'total_missed' => 0,
        ]);
    }

    public function poor(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_scheduled' => 3,
            'total_taken' => 1,
            'total_missed' => 2,
        ]);
    }
}
