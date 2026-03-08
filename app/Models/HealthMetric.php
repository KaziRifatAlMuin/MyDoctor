<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'metric_type',
        'recorded_at',
        'value',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'value'       => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
