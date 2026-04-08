<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class TranslationSeeder extends Seeder
{
    /**
     * Seed the translations table from config/health.php.
     */
    public function run(): void
    {
        if (!Schema::hasTable('translations')) {
            $this->command?->warn('TranslationSeeder skipped: translations table does not exist.');
            return;
        }

        // ─── Symptoms ────────────────────────────────────────────────────────
        foreach (config('health.symptoms', []) as $en => $bn) {
            Translation::updateOrCreate(
                ['type' => Translation::TYPE_SYMPTOM, 'key' => $en],
                ['value' => $bn]
            );
        }

        // ─── Metric Types ─────────────────────────────────────────────────────
        foreach (config('health.metric_types', []) as $key => $cfg) {
            Translation::updateOrCreate(
                ['type' => Translation::TYPE_METRIC, 'key' => $key],
                ['value' => $cfg['bn'] ?? $cfg['en']]
            );
        }

        // ─── Diseases ─────────────────────────────────────────────────────────
        foreach (config('health.diseases', []) as $en => $bn) {
            Translation::updateOrCreate(
                ['type' => Translation::TYPE_DISEASE, 'key' => $en],
                ['value' => $bn]
            );
        }

        $this->command->info('TranslationSeeder: inserted/updated ' . Translation::count() . ' translations.');
    }
}
