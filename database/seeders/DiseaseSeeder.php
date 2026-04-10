<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Disease;

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

        // Measles must be stored in English in DB; remove legacy Bangla row if it exists.
        Disease::where('disease_name', 'হাম')->delete();
        
        $this->command->info('Diseases seeded successfully from config!');
    }
}