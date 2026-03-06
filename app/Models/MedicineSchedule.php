<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineSchedule extends Model
{
    use HasFactory;

    protected $table = 'medicine_schedules';
    protected $primaryKey = 'ScheduleID';
    public $timestamps = false;

    protected $fillable = [
        'MedicineID',
        'DosagePeriodDays',
        'FrequencyPerDay',
        'IntervalHours',
        'DosageTimeBinary',
        'StartDate',
        'EndDate',
        'IsActive'
    ];

    protected $casts = [
        'StartDate' => 'date',
        'EndDate' => 'date',
        'IsActive' => 'boolean',
    ];

    /**
     * Get the medicine that owns the schedule.
     */
    public function medicine()
    {
        return $this->belongsTo(Medicine::class, 'MedicineID', 'MedicineID');
    }

    /**
     * Get the reminders for this schedule.
     */
    public function reminders()
    {
        return $this->hasMany(MedicineReminder::class, 'ScheduleID', 'ScheduleID');
    }

    /**
     * Get pending reminders for this schedule.
     */
    public function pendingReminders()
    {
        return $this->reminders()
            ->where('Status', 'pending')
            ->where('ReminderDateTime', '<=', now())
            ->orderBy('ReminderDateTime');
    }

    /**
     * Get upcoming reminders for this schedule.
     */
    public function upcomingReminders($limit = 5)
    {
        return $this->reminders()
            ->where('Status', 'pending')
            ->where('ReminderDateTime', '>', now())
            ->orderBy('ReminderDateTime')
            ->limit($limit);
    }

    /**
     * Get dosage times as array from binary string.
     */
    public function getDosageTimesArrayAttribute()
    {
        $binary = $this->DosageTimeBinary;
        $times = [];
        
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
        
        if (!$this->IsActive) {
            return false;
        }
        
        if ($date->lt($this->StartDate)) {
            return false;
        }
        
        if ($this->EndDate && $date->gt($this->EndDate)) {
            return false;
        }
        
        return true;
    }

    /**
     * Get period label.
     */
    public function getPeriodLabelAttribute()
    {
        if ($this->DosagePeriodDays == 0) {
            return 'প্রয়োজন অনুযায়ী';
        } elseif ($this->DosagePeriodDays == 1) {
            return 'প্রতিদিন';
        } elseif ($this->DosagePeriodDays == 7) {
            return 'সাপ্তাহিক';
        } elseif ($this->DosagePeriodDays == 30) {
            return 'মাসিক';
        } else {
            return "প্রতি {$this->DosagePeriodDays} দিন";
        }
    }
}