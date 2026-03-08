<?php

namespace App\Notifications;

use App\Models\MedicineReminder;
use App\Mail\MedicineReminderMail;
use Illuminate\Notifications\Notification;

class MedicineEmailNotification extends Notification
{

    protected $reminder;

    public function __construct(MedicineReminder $reminder)
    {
        $this->reminder = $reminder;
    }

    public function via($notifiable)
    {
        if (!$notifiable->wantsEmailNotifications()) {
            return [];
        }
        
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MedicineReminderMail($this->reminder))
            ->to($notifiable->email);
    }
}