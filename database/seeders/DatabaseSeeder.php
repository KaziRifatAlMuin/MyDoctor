<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\DiseaseSeeder;
use Database\Seeders\SymptomSeeder;
use Database\Seeders\DiseaseSymptomSeeder;
use Database\Seeders\HighVolumeDemoSeeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * Deterministic high-volume demo data.
     */
    public function run(): void
    {
        $this->call([
            DiseaseSeeder::class,
            SymptomSeeder::class,
            DiseaseSymptomSeeder::class,
        ]);

        if (!User::query()->exists()) {
            $this->call([
                HighVolumeDemoSeeder::class,
            ]);
        }
    }
}

