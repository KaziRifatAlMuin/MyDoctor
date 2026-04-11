<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHealth extends Model
{
    use HasFactory;

    protected $table = 'user_health';

    protected $fillable = [
        'user_id',
        'health_metric_id',
        'metric_type',
        'value',
        'recorded_at',
    ];

    protected $casts = [
        'value' => 'array',
        'recorded_at' => 'datetime',
    ];

    protected $appends = [
        'metric_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function healthMetric()
    {
        return $this->belongsTo(HealthMetric::class);
    }

    public function getMetricTypeAttribute(): ?string
    {
        return $this->healthMetric?->metric_name;
    }

    public function setMetricTypeAttribute($value): void
    {
        $metricName = trim((string) $value);
        if ($metricName === '') {
            return;
        }

        $defaultFields = HealthMetric::defaultFieldLabels($metricName);

        $definition = HealthMetric::query()->firstOrCreate(
            ['metric_name' => $metricName],
            ['fields' => $defaultFields]
        );

        $this->attributes['health_metric_id'] = $definition->id;
    }
}
