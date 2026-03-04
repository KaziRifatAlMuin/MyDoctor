<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class TranslationSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_translation_seeder_inserts_and_backfills_diseases()
    {
        // Insert a disease that exists in config to verify backfill
        DB::table('diseases')->insert([
            'disease_name' => 'Asthma',
            'disease_name_bn' => null,
            'description' => 'Test disease',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Run the seeder
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\TranslationSeeder']);

        // translations table should now contain many rows
        $this->assertDatabaseHas('translations', ['type' => 'disease', 'key' => 'Asthma']);

        // disease row should have been backfilled with bn name from config
        $this->assertDatabaseMissing('diseases', ['disease_name' => 'Asthma', 'disease_name_bn' => null]);
    }
}
