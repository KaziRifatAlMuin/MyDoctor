<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDisease extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'disease_id',
        'diagnosed_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'diagnosed_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function disease()
    {
        return $this->belongsTo(Disease::class);
    }

    public function getStatusLabelAttribute(): string
    {
        $key = "ui.health.{$this->status}";
        $translated = __($key);

        if ($translated === $key) {
            return ucfirst((string) $this->status);
        }

        return $translated;
    }
}
