<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'dosage_period_days',
        'frequency_per_day',
        'interval_hours',
        'dosage_time_binary',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function reminders()
    {
        return $this->hasMany(MedicineReminder::class, 'schedule_id');
    }
}
