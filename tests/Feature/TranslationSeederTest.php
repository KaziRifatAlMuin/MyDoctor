<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class TranslationSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_translation_seeder_inserts_disease_translations(): void
    {
        // Insert a disease that exists in config.
        DB::table('diseases')->insert([
            'disease_name' => 'Asthma',
            'description' => 'Test disease',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Run the seeder
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\TranslationSeeder']);

        // translations table should now contain rows for disease keys.
        $this->assertDatabaseHas('translations', ['type' => 'disease', 'key' => 'Asthma']);
    }
}
