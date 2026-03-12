<?php

namespace Database\Factories;

use App\Models\Medicine;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicineFactory extends Factory
{
    protected $model = Medicine::class;

    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? 1,
            'medicine_name' => fake()->word() . ' ' . fake()->randomElement(['Tablet', 'Capsule', 'Syrup']),
            'type' => fake()->randomElement(['tablet', 'capsule', 'syrup', 'injection', 'drops', 'cream', 'inhaler', 'other']),
            'value_per_dose' => fake()->randomFloat(2, 0.1, 1000),
            'unit' => fake()->randomElement(['mg', 'ml', 'mcg', 'g', 'IU', 'tablet', 'capsule', 'drop', 'puff']),
            'rule' => fake()->randomElement(['before_food', 'after_food', 'with_food', 'before_sleep', 'anytime']),
            'dose_limit' => fake()->numberBetween(1, 10),
        ];
    }
}