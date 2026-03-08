<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Environment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'location_name',
        'latitude',
        'longitude',
        'recorded_at',
        'weather_condition',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'latitude'    => 'float',
        'longitude'   => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function metrics()
    {
        return $this->hasMany(EnvironmentMetric::class);
    }
}
