<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DiseaseFactory extends Factory
{
    public function definition(): array
    {
        static $diseases = null;
        static $index = 0;

        if ($diseases === null) {
            $diseases = array_keys(config('health.diseases'));
        }

        $name = $diseases[$index % count($diseases)];
        $bangla = config('health.diseases')[$name] ?? null;
        $index++;

        return [
            'disease_name'    => $name,
            'disease_name_bn' => $bangla,
            'description'     => fake()->paragraph(2),
        ];
    }
}
