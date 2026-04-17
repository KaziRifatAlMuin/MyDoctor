<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSymptom extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'symptom_id',
        'severity_level',
        'note',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function symptom()
    {
        return $this->belongsTo(Symptom::class);
    }

    public function getSymptomNameAttribute(): ?string
    {
        return $this->symptom?->name;
    }

    public function getSymptomDisplayNameAttribute(): ?string
    {
        if ($this->symptom?->display_name) {
            return $this->symptom->display_name;
        }

        $name = trim((string) ($this->symptom?->name ?? ''));
        if ($name === '') {
            return null;
        }

        $bangla = Translation::banglaFor(Translation::TYPE_SYMPTOM, $name, '');
        if ($bangla !== '') {
            return $name . ' (' . $bangla . ')';
        }

        return $name;
    }
}
