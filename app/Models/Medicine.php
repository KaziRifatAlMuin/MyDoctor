<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'medicine_name',
        'type',
        'value_per_dose',
        'unit',
        'rule',
        'dose_limit'
    ];

    protected $casts = [
        'value_per_dose' => 'decimal:2',
        'dose_limit' => 'integer',
    ];

    /**
     * Get the user that owns the medicine.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the schedules for this medicine.
     */
    public function schedules()
    {
        return $this->hasMany(MedicineSchedule::class);
    }

    /**
     * Get the active schedule for this medicine.
     */
    public function activeSchedule()
    {
        return $this->hasOne(MedicineSchedule::class)
            ->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now()->toDateString());
            });
    }

    /**
     * Get the logs for this medicine.
     */
    public function logs()
    {
        return $this->hasMany(MedicineLog::class);
    }

    /**
     * Get today's log for this medicine.
     */
    public function todayLog()
    {
        return $this->hasOne(MedicineLog::class)
            ->where('date', now()->toDateString())
            ->where('user_id', $this->user_id);
    }

    /**
     * Get all reminders for this medicine through schedules.
     */
    public function reminders()
    {
        return $this->hasManyThrough(
            MedicineReminder::class,
            MedicineSchedule::class,
            'medicine_id',
            'schedule_id',
            'id',
            'id'
        );
    }

    /**
     * Get pending reminders for this medicine.
     */
    public function pendingReminders()
    {
        return $this->reminders()
            ->where('status', 'pending')
            ->where('reminder_at', '<=', now())
            ->orderBy('reminder_at');
    }

    /**
     * Get upcoming reminders for this medicine.
     */
    public function upcomingReminders($limit = 5)
    {
        return $this->reminders()
            ->where('status', 'pending')
            ->where('reminder_at', '>', now())
            ->orderBy('reminder_at')
            ->limit($limit);
    }

    /**
     * Get the type label in Bengali/English.
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            'tablet' => 'ট্যাবলেট',
            'capsule' => 'ক্যাপসুল',
            'syrup' => 'সিরাপ',
            'injection' => 'ইনজেকশন',
            'drops' => 'ড্রপস',
            'cream' => 'ক্রিম',
            'inhaler' => 'ইনহালার',
            'other' => 'অন্যান্য'
        ];
        return $labels[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Get the rule label in Bengali/English.
     */
    public function getRuleLabelAttribute()
    {
        $labels = [
            'before_food' => 'খাবারের আগে',
            'after_food' => 'খাবারের পরে',
            'with_food' => 'খাবারের সাথে',
            'before_sleep' => 'ঘুমানোর আগে',
            'anytime' => 'যেকোনো সময়'
        ];
        return $labels[$this->rule] ?? ucfirst(str_replace('_', ' ', $this->rule));
    }

    /**
     * Get the unit label.
     */
    public function getUnitLabelAttribute()
    {
        return strtoupper($this->unit);
    }
}
