<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Translation extends Model
{
    protected $fillable = ['type', 'key', 'value'];

    /* ─ Type constants ─ */
    const TYPE_DISEASE  = 'disease';
    const TYPE_SYMPTOM  = 'symptom';
    const TYPE_METRIC   = 'metric';

    /**
     * Get all translations of a given type keyed by English name.
     * Falls back to the config array if the DB returns nothing.
     *
     * @return array<string,string>  ['English' => 'বাংলা', ...]
     */
    public static function allOfType(string $type): array
    {
        if (!Schema::hasTable('translations')) {
            return match ($type) {
                self::TYPE_SYMPTOM => config('health.symptoms', []),
                self::TYPE_DISEASE => config('health.diseases', []),
                self::TYPE_METRIC  => collect(config('health.metric_types', []))
                                        ->map(fn($c) => $c['bn'] ?? '')
                                        ->toArray(),
                default            => [],
            };
        }

        $rows = static::where('type', $type)->pluck('value', 'key')->toArray();

        if (!empty($rows)) {
            return $rows;
        }

        // Config fallback
        return match ($type) {
            self::TYPE_SYMPTOM => config('health.symptoms', []),
            self::TYPE_DISEASE => config('health.diseases', []),
            self::TYPE_METRIC  => collect(config('health.metric_types', []))
                                    ->map(fn($c) => $c['bn'] ?? '')
                                    ->toArray(),
            default            => [],
        };
    }

    /**
     * Get a single Bangla translation.
     */
    public static function banglaFor(string $type, string $key, string $fallback = ''): string
    {
        if (!Schema::hasTable('translations')) {
            return $fallback;
        }

        return static::where('type', $type)->where('key', $key)->value('value') ?? $fallback;
    }
}
