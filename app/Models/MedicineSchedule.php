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
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'dosage_period_days' => 'integer',
        'frequency_per_day' => 'integer',
        'interval_hours' => 'integer',
    ];

    /**
     * Get the medicine that owns the schedule.
     */
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    /**
     * Get the reminders for this schedule.
     */
    public function reminders()
    {
        return $this->hasMany(MedicineReminder::class, 'schedule_id');
    }

    /**
     * Get pending reminders for this schedule.
     */
    public function pendingReminders()
    {
        return $this->reminders()
            ->where('status', 'pending')
            ->where('reminder_at', '<=', now())
            ->orderBy('reminder_at');
    }

    /**
     * Get upcoming reminders for this schedule.
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
     * Get dosage times as array from binary string.
     */
    public function getDosageTimesArrayAttribute()
    {
        $binary = $this->dosage_time_binary;
        $times = [];
        
        if (!$binary) {
            return $times;
        }
        
        // Binary is 48 bits (24 hours * 2 for half-hour intervals)
        for ($i = 0; $i < 48; $i++) {
            if (isset($binary[$i]) && $binary[$i] === '1') {
                $hour = floor($i / 2);
                $minute = ($i % 2) * 30;
                $times[] = sprintf('%02d:%02d', $hour, $minute);
            }
        }
        
        return $times;
    }

    /**
     * Get dosage times formatted for display.
     */
    public function getFormattedDosageTimesAttribute()
    {
        $times = $this->dosageTimesArray;
        return implode(', ', $times);
    }

    /**
     * Check if schedule is active for a given date.
     */
    public function isActiveForDate($date)
    {
        $date = is_string($date) ? \Carbon\Carbon::parse($date) : $date;
        
        if (!$this->is_active) {
            return false;
        }
        
        if ($date->lt($this->start_date)) {
            return false;
        }
        
        if ($this->end_date && $date->gt($this->end_date)) {
            return false;
        }
        
        return true;
    }

    /**
     * Get period label.
     */
    public function getPeriodLabelAttribute()
    {
        if ($this->dosage_period_days == 0) {
            return 'প্রয়োজন অনুযায়ী';
        } elseif ($this->dosage_period_days == 1) {
            return 'প্রতিদিন';
        } elseif ($this->dosage_period_days == 7) {
            return 'সাপ্তাহিক';
        } elseif ($this->dosage_period_days == 30) {
            return 'মাসিক';
        } else {
            return "প্রতি {$this->dosage_period_days} দিন";
        }
    }
}