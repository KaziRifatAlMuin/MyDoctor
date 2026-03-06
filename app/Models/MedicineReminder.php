<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineReminder extends Model
{
    use HasFactory;

    protected $table = 'medicine_reminders';
    protected $primaryKey = 'ReminderID';
    public $timestamps = false;

    protected $fillable = [
        'ScheduleID',
        'ReminderDateTime',
        'Status',
        'TakenAt'
    ];

    protected $casts = [
        'ReminderDateTime' => 'datetime',
        'TakenAt' => 'datetime',
    ];

    /**
     * Get the schedule that owns the reminder.
     */
    public function schedule()
    {
        return $this->belongsTo(MedicineSchedule::class, 'ScheduleID', 'ScheduleID');
    }

    /**
     * Get the medicine through schedule.
     */
    public function medicine()
    {
        return $this->schedule->medicine();
    }

    /**
     * Mark reminder as taken.
     */
    public function markAsTaken()
    {
        $this->update([
            'Status' => 'taken',
            'TakenAt' => now()
        ]);

        // Update medicine log
        $this->updateLog('taken');
        
        return $this;
    }

    /**
     * Mark reminder as missed.
     */
    public function markAsMissed()
    {
        $this->update(['Status' => 'missed']);
        $this->updateLog('missed');
        
        return $this;
    }

    /**
     * Update medicine log.
     */
    private function updateLog($action)
    {
        $date = $this->ReminderDateTime->toDateString();
        $medicineId = $this->schedule->MedicineID;
        
        $log = MedicineLog::firstOrCreate(
            [
                'MedicineID' => $medicineId,
                'Date' => $date
            ],
            [
                'UserID' => $this->schedule->medicine->UserID,
                'TotalScheduled' => 0,
                'TotalTaken' => 0,
                'TotalMissed' => 0
            ]
        );

        // Count scheduled doses for this day
        $totalScheduled = MedicineReminder::whereHas('schedule', function($q) use ($medicineId) {
                $q->where('MedicineID', $medicineId);
            })
            ->whereDate('ReminderDateTime', $date)
            ->count();
        
        $log->TotalScheduled = $totalScheduled;
        
        if ($action === 'taken') {
            $log->TotalTaken += 1;
        } else {
            $log->TotalMissed += 1;
        }
        
        $log->save();
    }

    /**
     * Get status label in Bengali.
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'অপেক্ষমান',
            'taken' => 'খাওয়া হয়েছে',
            'missed' => 'মিস হয়েছে',
            'skipped' => 'বাদ দেওয়া হয়েছে'
        ];
        return $labels[$this->Status] ?? ucfirst($this->Status);
    }

    /**
     * Get status color class.
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'taken' => 'success',
            'missed' => 'danger',
            'skipped' => 'secondary'
        ];
        return $colors[$this->Status] ?? 'secondary';
    }
}