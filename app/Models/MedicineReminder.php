<?php

namespace App\Models;

use App\Notifications\MedicineReminderNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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

        $this->updateLog();
        
        return $this;
    }

    /**
     * Mark reminder as missed.
     */
    public function markAsMissed()
    {
        $this->update(['status' => 'missed']);
        $this->updateLog();
        
        return $this;
    }

    /**
     * Update medicine log.
     */
    private function updateLog(): void
    {
        $this->loadMissing('schedule.medicine');

        if (!$this->schedule || !$this->schedule->medicine) {
            return;
        }

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

        $baseQuery = MedicineReminder::whereHas('schedule', function ($q) use ($medicineId) {
                $q->where('medicine_id', $medicineId);
            })
            ->whereDate('reminder_at', $date);

        $log->total_scheduled = (clone $baseQuery)->count();
        $log->total_taken = (clone $baseQuery)->where('status', 'taken')->count();
        $log->total_missed = (clone $baseQuery)->where('status', 'missed')->count();

        $log->save();
    }

    /**
     * Send notification for this reminder
     */
    public function sendNotification(): void
    {
        try {
            $user = $this->schedule->medicine->user;
            if ($user) {
                $user->notify(new MedicineReminderNotification($this));
                Log::info("Medicine reminder notification sent for reminder ID: {$this->id} to user ID: {$user->id}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to send medicine reminder notification: " . $e->getMessage());
        }
    }

    /**
     * Send notification for this reminder (alias for sendNotification)
     */
    public function notifyUser(): void
    {
        $this->sendNotification();
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
            'dosage' => $this->schedule->medicine->value_per_dose,
            'unit' => $this->schedule->medicine->unit,
            'time' => $this->reminder_at->format('h:i A'),
            'url' => route('medicine.reminders'),
            'taken_url' => route('medicine.reminders.taken-from-notification', $this->id),
        ];
    }

    /**
     * Check if reminder is due (within last 5 minutes)
     */
    public function isDue(): bool
    {
        $now = now();
        $reminderTime = $this->reminder_at;
        
        // Check if reminder time is between now and 5 minutes ago
        return $this->status === 'pending' 
            && $reminderTime->lte($now) 
            && $reminderTime->gte($now->copy()->subMinutes(5));
    }

    /**
     * Check if reminder is upcoming (within next 5 minutes)
     */
    public function isUpcoming(): bool
    {
        $now = now();
        $reminderTime = $this->reminder_at;
        
        return $this->status === 'pending' 
            && $reminderTime->gt($now) 
            && $reminderTime->lte($now->copy()->addMinutes(5));
    }

    /**
     * Scope for pending reminders
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for taken reminders
     */
    public function scopeTaken($query)
    {
        return $query->where('status', 'taken');
    }

    /**
     * Scope for missed reminders
     */
    public function scopeMissed($query)
    {
        return $query->where('status', 'missed');
    }

    /**
     * Scope for today's reminders
     */
    public function scopeToday($query)
    {
        return $query->whereDate('reminder_at', today());
    }

    /**
     * Scope for upcoming reminders (future)
     */
    public function scopeUpcoming($query)
    {
        return $query->where('reminder_at', '>', now())
            ->where('status', 'pending');
    }

    /**
     * Scope for reminders due within next X minutes
     */
    public function scopeDueWithin($query, $minutes = 5)
    {
        return $query->whereBetween('reminder_at', [
            now(),
            now()->addMinutes($minutes)
        ])->where('status', 'pending');
    }
}