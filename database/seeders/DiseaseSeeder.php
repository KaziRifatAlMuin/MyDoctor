<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Disease;
use App\Models\Translation;
use Illuminate\Support\Facades\Schema;

class DiseaseSeeder extends Seeder
{
    public function run(): void
    {
        $diseases = array_slice(array_keys(config('health.diseases', [])), 0, 50);
        
        foreach ($diseases as $english) {
            $bangla = Translation::banglaFor(Translation::TYPE_DISEASE, $english, $english);

            $payload = [
                'description' => $english . ' description',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('diseases', 'bangla_name')) {
                $payload['bangla_name'] = $bangla;
            }
            if (Schema::hasColumn('diseases', 'disease_name_bn')) {
                $payload['disease_name_bn'] = $bangla;
            }

            Disease::updateOrCreate(
                ['disease_name' => $english],
                $payload
            );
        }

        // Keep catalog focused for demo seeding: at most 50 diseases.
        Disease::query()->whereNotIn('disease_name', $diseases)->delete();

        // Measles must be stored in English in DB; remove legacy Bangla row if it exists.
        Disease::where('disease_name', 'হাম')->delete();
        
        $this->command->info('Diseases seeded successfully from config!');
    }
}