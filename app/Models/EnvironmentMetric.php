<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvironmentMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'environment_id',
        'metric_type',
        'value',
        'unit',
    ];

    public function environment()
    {
        return $this->belongsTo(Environment::class);
    }
}
