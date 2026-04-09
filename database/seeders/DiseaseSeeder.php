<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Disease;
use Illuminate\Support\Facades\DB;

class DiseaseSeeder extends Seeder
{
    public function run(): void
    {
        $diseases = array_keys(config('health.diseases', []));
        
        foreach ($diseases as $english) {
            Disease::updateOrCreate(
                ['disease_name' => $english],
                [
                    'description' => $english . ' description',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        Disease::updateOrCreate(
            ['disease_name' => 'হাম'],
            [
                'description' => 'Measles (Bangla name)',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        
        $this->command->info('Diseases seeded successfully from config!');
    }
}