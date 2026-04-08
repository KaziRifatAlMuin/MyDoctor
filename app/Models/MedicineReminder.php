<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'reminder_at',
        'status',
        'taken_at'
    ];

    protected $casts = [
        'reminder_at' => 'datetime',
        'taken_at' => 'datetime',
    ];

    /**
     * Get the schedule that owns the reminder.
     */
    public function schedule()
    {
        return $this->belongsTo(MedicineSchedule::class, 'schedule_id');
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
            'status' => 'taken',
            'taken_at' => now()
        ]);

        $this->updateLog('taken');
        
        return $this;
    }

    /**
     * Mark reminder as missed.
     */
    public function markAsMissed()
    {
        $this->update(['status' => 'missed']);
        $this->updateLog('missed');
        
        return $this;
    }

    /**
     * Update medicine log.
     */
    private function updateLog($action)
    {
        $date = $this->reminder_at->toDateString();
        $medicineId = $this->schedule->medicine_id;
        $userId = $this->schedule->medicine->user_id;
        
        $log = MedicineLog::firstOrCreate(
            [
                'medicine_id' => $medicineId,
                'user_id' => $userId,
                'date' => $date
            ],
            [
                'total_scheduled' => 0,
                'total_taken' => 0,
                'total_missed' => 0
            ]
        );

        $totalScheduled = MedicineReminder::whereHas('schedule', function($q) use ($medicineId) {
                $q->where('medicine_id', $medicineId);
            })
            ->whereDate('reminder_at', $date)
            ->count();
        
        $log->total_scheduled = $totalScheduled;
        
        if ($action === 'taken') {
            $log->total_taken += 1;
        } else {
            $log->total_missed += 1;
        }
        
        $log->save();
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute()
    {
        $key = "ui.medicine.{$this->status}";
        $translated = __($key);

        if ($translated === $key) {
            return ucfirst($this->status);
        }

        return $translated;
    }

    /**
     * Get notification message.
     */
    public function getNotificationMessage(): string
    {
        $medicineName = $this->schedule->medicine->medicine_name;
        $time = $this->reminder_at->format('h:i A');

        return __('ui.medicine.time_to_take', [
            'medicine' => $medicineName,
            'time' => $time,
        ]);
    }

    /**
     * Get notification title.
     */
    public function getNotificationTitle(): string
    {
        return '💊 ' . $this->schedule->medicine->medicine_name;
    }

    /**
     * Get notification data array.
     */
    public function getNotificationData(): array
    {
        return [
            'reminder_id' => $this->id,
            'medicine_id' => $this->schedule->medicine->id,
            'medicine_name' => $this->schedule->medicine->medicine_name,
            'time' => $this->reminder_at->format('h:i A'),
            'url' => route('medicine.reminders'),
        ];
    }
}