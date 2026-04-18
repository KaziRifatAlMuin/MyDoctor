<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Symptom extends Model
{
    use HasFactory;

    private static ?bool $hasBanglaNameColumn = null;
    private static ?bool $hasLegacyBanglaNameColumn = null;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'name_bn',
        'bangla_name',
    ];

    protected $appends = [
        'display_name',
        'name_bn',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $symptom): void {
            $rawName = trim((string) $symptom->name);
            if ($rawName === '') {
                return;
            }

            $inlineBangla = null;
            if (preg_match('/^(.*?)\s*\(([^)]*[\x{0980}-\x{09FF}][^)]*)\)\s*$/u', $rawName, $m) === 1) {
                $baseName = trim((string) $m[1]);
                $inlineBangla = trim((string) $m[2]);
                $symptom->name = $baseName !== '' ? $baseName : $rawName;
            }

            $manualBangla = trim((string) ($symptom->attributes['bangla_name'] ?? ''));
            if ($manualBangla === '') {
                $manualBangla = trim((string) ($symptom->attributes['name_bn'] ?? ''));
            }

            $resolvedBangla = $manualBangla !== ''
                ? $manualBangla
                : trim((string) ($inlineBangla ?? ''));
            if ($resolvedBangla === '') {
                $resolvedBangla = trim((string) Translation::banglaFor(
                    Translation::TYPE_SYMPTOM,
                    (string) $symptom->name,
                    (string) $symptom->name
                ));
            }

            if (self::supportsBanglaNameColumn()) {
                $symptom->attributes['bangla_name'] = $resolvedBangla;
            }

            if (self::supportsLegacyBanglaNameColumn()) {
                $symptom->attributes['name_bn'] = $resolvedBangla;
            }
        });
    }

    private static function supportsBanglaNameColumn(): bool
    {
        if (self::$hasBanglaNameColumn !== null) {
            return self::$hasBanglaNameColumn;
        }

        try {
            self::$hasBanglaNameColumn = Schema::hasColumn('symptoms', 'bangla_name');
        } catch (\Throwable $e) {
            self::$hasBanglaNameColumn = false;
        }

        return self::$hasBanglaNameColumn;
    }

    private static function supportsLegacyBanglaNameColumn(): bool
    {
        if (self::$hasLegacyBanglaNameColumn !== null) {
            return self::$hasLegacyBanglaNameColumn;
        }

        try {
            self::$hasLegacyBanglaNameColumn = Schema::hasColumn('symptoms', 'name_bn');
        } catch (\Throwable $e) {
            self::$hasLegacyBanglaNameColumn = false;
        }

        return self::$hasLegacyBanglaNameColumn;
    }

    public function userSymptoms()
    {
        return $this->hasMany(UserSymptom::class);
    }

    public function diseases()
    {
        return $this->belongsToMany(Disease::class, 'disease_symptoms')
            ->withTimestamps();
    }

    public function getBanglaNameAttribute($value): ?string
    {
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }

        $legacy = trim((string) ($this->attributes['name_bn'] ?? ''));
        if ($legacy !== '') {
            return $legacy;
        }

        $name = trim((string) ($this->attributes['name'] ?? ''));
        if ($name === '') {
            return null;
        }

        if (preg_match('/\(([^)]*[\x{0980}-\x{09FF}][^)]*)\)/u', $name, $matches) === 1) {
            return trim($matches[1]);
        }

        return Translation::banglaFor(Translation::TYPE_SYMPTOM, $name, $name);
    }

    public function getDisplayNameAttribute(): string
    {
        $name = trim((string) ($this->attributes['name'] ?? ''));
        $bangla = trim((string) ($this->bangla_name ?? ''));

        if ($name === '' || $bangla === '') {
            return $name;
        }

        return $name . ' (' . $bangla . ')';
    }

    public function getNameBnAttribute(): ?string
    {
        return $this->bangla_name;
    }

    public function setNameBnAttribute(?string $value): void
    {
        $normalized = $value !== null ? trim($value) : null;

        if (self::supportsBanglaNameColumn()) {
            $this->attributes['bangla_name'] = $normalized;
        }

        if (self::supportsLegacyBanglaNameColumn()) {
            $this->attributes['name_bn'] = $normalized;
        }
    }
}
