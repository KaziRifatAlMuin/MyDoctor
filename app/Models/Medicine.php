<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $table = 'medicines';
    protected $primaryKey = 'MedicineID';
    public $timestamps = false;

    protected $fillable = [
        'UserID',
        'MedicineName',
        'Type',
        'ValuePerDose',
        'Unit',
        'Rule',
        'DoseLimit',
    ];

    protected $casts = [
        'ValuePerDose' => 'decimal:2',
        'CreatedAt' => 'datetime',
    ];

    /**
     * Get the user that owns the medicine.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    /**
     * Get the schedules for this medicine.
     */
    public function schedules()
    {
        return $this->hasMany(MedicineSchedule::class, 'MedicineID', 'MedicineID');
    }

    /**
     * Get the active schedule for this medicine.
     */
    public function activeSchedule()
    {
        return $this->hasOne(MedicineSchedule::class, 'MedicineID', 'MedicineID')
            ->where('IsActive', true)
            ->where(function($q) {
                $q->whereNull('EndDate')
                  ->orWhere('EndDate', '>=', now()->toDateString());
            });
    }

    /**
     * Get the logs for this medicine.
     */
    public function logs()
    {
        return $this->hasMany(MedicineLog::class, 'MedicineID', 'MedicineID');
    }

    /**
     * Get today's log for this medicine.
     */
    public function todayLog()
    {
        return $this->hasOne(MedicineLog::class, 'MedicineID', 'MedicineID')
            ->where('Date', now()->toDateString());
    }

    /**
     * Get all reminders for this medicine through schedules.
     */
    public function reminders()
    {
        return $this->hasManyThrough(
            MedicineReminder::class,
            MedicineSchedule::class,
            'MedicineID',
            'ScheduleID',
            'MedicineID',
            'ScheduleID'
        );
    }

    /**
     * Get pending reminders for this medicine.
     */
    public function pendingReminders()
    {
        return $this->reminders()
            ->where('Status', 'pending')
            ->where('ReminderDateTime', '<=', now())
            ->orderBy('ReminderDateTime');
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
            'inhaler' => 'ইনহেলার',
            'other' => 'অন্যান্য'
        ];
        return $labels[$this->Type] ?? ucfirst($this->Type);
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
        return $labels[$this->Rule] ?? ucfirst(str_replace('_', ' ', $this->Rule));
    }

    /**
     * Get the unit label.
     */
    public function getUnitLabelAttribute()
    {
        return strtoupper($this->Unit);
    }
}