<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthMetric extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'metric_name',
        'fields',
    ];

    protected $casts = [
        'fields' => 'array',
    ];

    public function userHealthRecords()
    {
        return $this->hasMany(UserHealth::class);
    }
}
