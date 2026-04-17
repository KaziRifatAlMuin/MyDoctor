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
        $normalizedKey = trim($key);
        if ($normalizedKey === '') {
            return static::ensureBanglaScript($fallback, $fallback);
        }

        // Prefer config dictionary first so app-level curated translations always win.
        $map = match ($type) {
            self::TYPE_SYMPTOM => config('health.symptoms', []),
            self::TYPE_DISEASE => config('health.diseases', []),
            default => [],
        };

        if (isset($map[$normalizedKey]) && trim((string) $map[$normalizedKey]) !== '') {
            return static::ensureBanglaScript((string) $map[$normalizedKey], $normalizedKey);
        }

        foreach ($map as $en => $bn) {
            if (mb_strtolower(trim((string) $en)) === mb_strtolower($normalizedKey) && trim((string) $bn) !== '') {
                return static::ensureBanglaScript((string) $bn, $normalizedKey);
            }
        }

        if (Schema::hasTable('translations')) {
            $exact = static::where('type', $type)->where('key', $normalizedKey)->value('value');
            if (is_string($exact) && trim($exact) !== '') {
                return static::ensureBanglaScript($exact, $normalizedKey);
            }

            $ci = static::where('type', $type)
                ->whereRaw('LOWER(`key`) = ?', [mb_strtolower($normalizedKey)])
                ->value('value');

            if (is_string($ci) && trim($ci) !== '') {
                return static::ensureBanglaScript($ci, $normalizedKey);
            }
        }

        return static::ensureBanglaScript($fallback, $normalizedKey);
    }

    private static function ensureBanglaScript(string $value, string $source): string
    {
        $normalized = trim($value);
        if ($normalized !== '' && preg_match('/[\x{0980}-\x{09FF}]/u', $normalized) === 1) {
            return $normalized;
        }

        $fallbackSource = trim($source);
        if ($fallbackSource === '') {
            return 'বাংলা নাম';
        }

        return static::banglaFromEnglish($fallbackSource);
    }

    private static function banglaFromEnglish(string $source): string
    {
        $normalized = trim($source);
        if ($normalized === '') {
            return 'বাংলা নাম';
        }

        $exactMap = [
            'Body Heaviness' => 'শরীর ভারীভাব',
            'Breast Tenderness' => 'স্তনে ব্যথা',
            'Breathlessness at Rest' => 'বিশ্রামে শ্বাসকষ্ট',
            'Breathlessness on Exertion' => 'পরিশ্রমে শ্বাসকষ্ট',
        ];

        if (isset($exactMap[$normalized])) {
            return $exactMap[$normalized];
        }

        $wordMap = [
            'body' => 'শরীর',
            'heaviness' => 'ভারীভাব',
            'breast' => 'স্তন',
            'tenderness' => 'ব্যথা',
            'breathlessness' => 'শ্বাসকষ্ট',
            'at' => 'এ',
            'rest' => 'বিশ্রাম',
            'on' => 'এ',
            'exertion' => 'পরিশ্রম',
            'pain' => 'ব্যথা',
            'burning' => 'জ্বালাপোড়া',
            'stomach' => 'পেট',
            'feet' => 'পা',
        ];

        $tokens = preg_split('/\s+/', $normalized) ?: [];
        $parts = [];
        foreach ($tokens as $token) {
            $clean = preg_replace('/[^A-Za-z]/', '', $token) ?? '';
            if ($clean === '') {
                continue;
            }

            $lower = strtolower($clean);
            if (isset($wordMap[$lower])) {
                $parts[] = $wordMap[$lower];
                continue;
            }

            $parts[] = static::phoneticBangla($lower);
        }

        $result = trim(implode(' ', array_filter($parts)));
        if ($result === '') {
            return 'বাংলা নাম';
        }

        return $result;
    }

    private static function phoneticBangla(string $word): string
    {
        $pairs = [
            'ch' => 'চ', 'sh' => 'শ', 'ph' => 'ফ', 'th' => 'থ', 'dh' => 'ধ',
            'kh' => 'খ', 'gh' => 'ঘ', 'ng' => 'ং', 'oo' => 'ু', 'ee' => 'ি',
            'ai' => 'ৈ', 'ay' => 'ে', 'oi' => 'ৈ', 'ou' => 'ৌ', 'ow' => 'াও',
        ];

        $single = [
            'a' => 'আ', 'b' => 'ব', 'c' => 'ক', 'd' => 'ড', 'e' => 'এ',
            'f' => 'ফ', 'g' => 'গ', 'h' => 'হ', 'i' => 'ই', 'j' => 'জ',
            'k' => 'ক', 'l' => 'ল', 'm' => 'ম', 'n' => 'ন', 'o' => 'ও',
            'p' => 'প', 'q' => 'ক', 'r' => 'র', 's' => 'স', 't' => 'ত',
            'u' => 'উ', 'v' => 'ভ', 'w' => 'ও', 'x' => 'ক্স', 'y' => 'ই', 'z' => 'জ',
        ];

        $out = '';
        $i = 0;
        while ($i < strlen($word)) {
            $pair = substr($word, $i, 2);
            if (isset($pairs[$pair])) {
                $out .= $pairs[$pair];
                $i += 2;
                continue;
            }

            $char = $word[$i];
            $out .= $single[$char] ?? '';
            $i++;
        }

        return $out !== '' ? $out : 'বাংলা';
    }
}
