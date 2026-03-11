<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Disease;
use Illuminate\Support\Facades\DB;

class DiseaseSeeder extends Seeder
{
    public function run(): void
    {
        $diseases = config('health.diseases');
        
        foreach ($diseases as $english => $bangla) {
            Disease::updateOrCreate(
                ['disease_name' => $english],
                [
                    'disease_name_bn' => $bangla,
                    'description' => $english . ' description',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        
        $this->command->info('Diseases seeded successfully from config!');
    }
}