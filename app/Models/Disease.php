<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Disease extends Model
{
    use HasFactory;

    private static ?bool $hasBanglaNameColumn = null;
    private static ?bool $hasLegacyBanglaNameColumn = null;

    protected $fillable = [
        'disease_name',
        'disease_name_bn',
        'bangla_name',
        'description',
    ];

    protected $appends = [
        'display_name',
        'disease_name_bn',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $disease): void {
            $rawName = trim((string) $disease->disease_name);
            if ($rawName === '') {
                return;
            }

            $inlineBangla = null;
            if (preg_match('/^(.*?)\s*\(([^)]*[\x{0980}-\x{09FF}][^)]*)\)\s*$/u', $rawName, $m) === 1) {
                $baseName = trim((string) $m[1]);
                $inlineBangla = trim((string) $m[2]);
                $disease->disease_name = $baseName !== '' ? $baseName : $rawName;
            }

            $manualBangla = trim((string) ($disease->attributes['bangla_name'] ?? ''));
            if ($manualBangla === '') {
                $manualBangla = trim((string) ($disease->attributes['disease_name_bn'] ?? ''));
            }

            $resolvedBangla = $manualBangla !== ''
                ? $manualBangla
                : trim((string) ($inlineBangla ?? ''));
            if ($resolvedBangla === '') {
                $resolvedBangla = trim((string) Translation::banglaFor(
                    Translation::TYPE_DISEASE,
                    (string) $disease->disease_name,
                    (string) $disease->disease_name
                ));
            }

            if (self::supportsBanglaNameColumn()) {
                $disease->attributes['bangla_name'] = $resolvedBangla;
            }

            if (self::supportsLegacyBanglaNameColumn()) {
                $disease->attributes['disease_name_bn'] = $resolvedBangla;
            }
        });
    }

    private static function supportsBanglaNameColumn(): bool
    {
        if (self::$hasBanglaNameColumn !== null) {
            return self::$hasBanglaNameColumn;
        }

        try {
            self::$hasBanglaNameColumn = Schema::hasColumn('diseases', 'bangla_name');
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
            self::$hasLegacyBanglaNameColumn = Schema::hasColumn('diseases', 'disease_name_bn');
        } catch (\Throwable $e) {
            self::$hasLegacyBanglaNameColumn = false;
        }

        return self::$hasLegacyBanglaNameColumn;
    }

    /**
     * Get the posts for this disease
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the users who have this disease
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_diseases')
                    ->withPivot('diagnosed_at', 'status', 'notes')
                    ->withTimestamps();
    }

    /**
     * Get the user diseases pivot records
     */
    public function userDiseases()
    {
        return $this->hasMany(UserDisease::class);
    }

    public function starredByUsers()
    {
        return User::query()
            ->get()
            ->filter(function (User $user): bool {
                return in_array((int) $this->id, $user->getStarredDiseaseIds(), true);
            })
            ->values();
    }

    public function symptoms()
    {
        return $this->belongsToMany(Symptom::class, 'disease_symptoms')
            ->withTimestamps();
    }

    public function getBanglaNameAttribute($value): ?string
    {
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }

        $legacy = trim((string) ($this->attributes['disease_name_bn'] ?? ''));
        if ($legacy !== '') {
            return $legacy;
        }

        $name = trim((string) ($this->attributes['disease_name'] ?? ''));
        if ($name === '') {
            return null;
        }

        if (preg_match('/\(([^)]*[\x{0980}-\x{09FF}][^)]*)\)/u', $name, $matches) === 1) {
            return trim($matches[1]);
        }

        return Translation::banglaFor(Translation::TYPE_DISEASE, $name, $name);
    }

    public function getDisplayNameAttribute(): string
    {
        $name = trim((string) ($this->attributes['disease_name'] ?? ''));
        $bangla = trim((string) ($this->bangla_name ?? ''));

        if ($name === '' || $bangla === '') {
            return $name;
        }

        return $name . ' (' . $bangla . ')';
    }

    public function getDiseaseNameBnAttribute(): ?string
    {
        return $this->bangla_name;
    }

    public function setDiseaseNameBnAttribute(?string $value): void
    {
        $normalized = $value !== null ? trim($value) : null;

        if (self::supportsBanglaNameColumn()) {
            $this->attributes['bangla_name'] = $normalized;
        }

        if (self::supportsLegacyBanglaNameColumn()) {
            $this->attributes['disease_name_bn'] = $normalized;
        }
    }
}