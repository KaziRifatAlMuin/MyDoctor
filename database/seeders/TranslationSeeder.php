<?php

namespace Database\Seeders;

use App\Models\Disease;
use App\Models\Translation;
use Illuminate\Database\Seeder;

class TranslationSeeder extends Seeder
{
    /**
     * Seed the translations table from config/health.php.
     * Also back-fills disease_name_bn on the diseases table.
     */
    public function run(): void
    {
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

            // Back-fill Bangla name on the diseases table
            Disease::where('disease_name', $en)
                ->whereNull('disease_name_bn')
                ->update(['disease_name_bn' => $bn]);
        }

        $this->command->info('TranslationSeeder: inserted/updated ' . Translation::count() . ' translations.');
    }
}
