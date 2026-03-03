<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicineFactory extends Factory
{
    public function definition(): array
    {
        $medicines = [
            ['name' => 'Metformin',     'type' => 'Tablet', 'unit' => 'mg'],
            ['name' => 'Amlodipine',    'type' => 'Tablet', 'unit' => 'mg'],
            ['name' => 'Atorvastatin',  'type' => 'Tablet', 'unit' => 'mg'],
            ['name' => 'Omeprazole',    'type' => 'Capsule','unit' => 'mg'],
            ['name' => 'Losartan',      'type' => 'Tablet', 'unit' => 'mg'],
            ['name' => 'Salbutamol',    'type' => 'Inhaler','unit' => 'mcg'],
            ['name' => 'Paracetamol',   'type' => 'Tablet', 'unit' => 'mg'],
            ['name' => 'Cetirizine',    'type' => 'Tablet', 'unit' => 'mg'],
            ['name' => 'Amoxicillin',   'type' => 'Capsule','unit' => 'mg'],
            ['name' => 'Insulin',       'type' => 'Injection','unit' => 'IU'],
        ];

        $med = fake()->randomElement($medicines);

        return [
            'user_id'       => User::inRandomOrder()->first()?->id ?? User::factory(),
            'medicine_name' => $med['name'],
            'type'          => $med['type'],
            'value_per_dose'=> fake()->randomFloat(2, 5, 1000),
            'unit'          => $med['unit'],
            'rule'          => fake()->randomElement(['After meal', 'Before meal', 'With water', 'At bedtime']),
            'dose_limit'    => fake()->numberBetween(1, 4),
        ];
    }
}
