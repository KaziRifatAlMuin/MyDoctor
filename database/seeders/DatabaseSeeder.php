<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\TranslationSeeder;
use Database\Seeders\DiseaseSeeder;
use Database\Seeders\HighVolumeDemoSeeder;

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
            TranslationSeeder::class,
            DiseaseSeeder::class,
            HighVolumeDemoSeeder::class,
        ]);
    }
}

