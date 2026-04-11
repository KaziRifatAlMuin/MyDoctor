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

    public static function defaultDefinitions(): array
    {
        return [
            'blood_pressure' => ['Systolic (mmHg)', 'Diastolic (mmHg)'],
            'blood_glucose' => ['Glucose Level (mg/dL)'],
            'heart_rate' => ['Heart Rate (bpm)'],
            'body_weight' => ['Body Weight (kg)'],
            'bmi' => ['BMI (kg/m2)'],
            'oxygen_saturation' => ['Oxygen Saturation (%)'],
            'temperature' => ['Body Temperature (C)'],
            'cholesterol' => ['Total Cholesterol (mg/dL)', 'HDL (mg/dL)', 'LDL (mg/dL)'],
            'hemoglobin' => ['Hemoglobin (g/dL)'],
            'creatinine' => ['Serum Creatinine (mg/dL)'],
            'respiratory_rate' => ['Respiratory Rate (breaths/min)'],
        ];
    }

    public static function defaultFieldLabels(string $metricName): array
    {
        return self::defaultDefinitions()[$metricName] ?? ['Value'];
    }

    public static function seedDefaults(): void
    {
        foreach (self::defaultDefinitions() as $metricName => $fields) {
            self::query()->firstOrCreate(
                ['metric_name' => $metricName],
                ['fields' => $fields]
            );
        }
    }
}
