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
            'tablet' => ['en' => 'Tablet', 'bn' => 'ট্যাবলেট'],
            'capsule' => ['en' => 'Capsule', 'bn' => 'ক্যাপসুল'],
            'syrup' => ['en' => 'Syrup', 'bn' => 'সিরাপ'],
            'injection' => ['en' => 'Injection', 'bn' => 'ইনজেকশন'],
            'drops' => ['en' => 'Drops', 'bn' => 'ড্রপস'],
            'cream' => ['en' => 'Cream', 'bn' => 'ক্রিম'],
            'inhaler' => ['en' => 'Inhaler', 'bn' => 'ইনহালার'],
            'other' => ['en' => 'Other', 'bn' => 'অন্যান্য'],
        ];

        $locale = app()->getLocale() === 'bn' ? 'bn' : 'en';

        return $labels[$this->type][$locale] ?? ucfirst($this->type);
    }

    /**
     * Get the rule label in Bengali/English.
     */
    public function getRuleLabelAttribute()
    {
        $labels = [
            'before_food' => ['en' => 'Before Food', 'bn' => 'খাবারের আগে'],
            'after_food' => ['en' => 'After Food', 'bn' => 'খাবারের পরে'],
            'with_food' => ['en' => 'With Food', 'bn' => 'খাবারের সাথে'],
            'before_sleep' => ['en' => 'Before Sleep', 'bn' => 'ঘুমানোর আগে'],
            'anytime' => ['en' => 'Anytime', 'bn' => 'যেকোনো সময়'],
        ];

        $locale = app()->getLocale() === 'bn' ? 'bn' : 'en';

        return $labels[$this->rule][$locale] ?? ucfirst(str_replace('_', ' ', $this->rule));
    }

    /**
     * Get the unit label.
     */
    public function getUnitLabelAttribute()
    {
        return strtoupper($this->unit);
    }
}