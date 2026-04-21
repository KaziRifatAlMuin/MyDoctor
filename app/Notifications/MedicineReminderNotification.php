<?php

namespace App\Notifications;

use App\Models\MedicineReminder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MedicineReminderNotification extends Notification
{
    use Queueable;

    protected $reminder;
    protected $systemUser;

    public function __construct(MedicineReminder $reminder)
    {
        $this->reminder = $reminder;
        
        // Get system user (create if doesn't exist)
        $this->systemUser = User::firstOrCreate(
            ['email' => 'system@mydoctor.com'],
            [
                'name' => 'System',
                'password' => bcrypt(uniqid()),
                'gender' => 'other',
                'role' => 'member',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }

    public function via($notifiable)
    {
        $channels = ['database'];
        
        if ($notifiable->wantsEmailNotifications()) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    public function toMail($notifiable)
    {
        $medicine = $this->reminder->schedule->medicine;
        $time = $this->reminder->reminder_at->format('h:i A');
        
        return (new MailMessage)
            ->subject("🔔 Medicine Reminder: {$medicine->medicine_name}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("It's time to take your medicine:")
            ->line("**Medicine:** {$medicine->medicine_name}")
            ->line("**Time:** {$time}")
            ->line("**Dosage:** " . ($medicine->value_per_dose ? "{$medicine->value_per_dose} {$medicine->unit}" : 'As prescribed'))
            ->line("**When:** " . ($medicine->ruleLabel ?? 'As prescribed'))
            ->action('Mark as Taken', route('medicine.reminders.taken-from-notification', $this->reminder->id))
            ->line("If you've already taken it, please mark it as taken in the app.")
            ->line("Stay healthy! 💪");
    }

    public function toDatabase($notifiable)
    {
        $medicine = $this->reminder->schedule->medicine;
        
        return [
            'type' => 'medicine_reminder',
            'reminder_id' => $this->reminder->id,
            'medicine_id' => $medicine->id,
            'medicine_name' => $medicine->medicine_name,
            'dosage' => $medicine->value_per_dose ? "{$medicine->value_per_dose} {$medicine->unit}" : null,
            'scheduled_time' => $this->reminder->reminder_at->format('h:i A'),
            'message' => "Time to take your medicine: {$medicine->medicine_name}",
            'action_url' => route('medicine.reminders'),
            'taken_url' => route('medicine.reminders.taken-from-notification', $this->reminder->id),
            'from_user_id' => $this->systemUser->id,
            'from_user_name' => 'System',
        ];
    }
}