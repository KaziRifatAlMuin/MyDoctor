<?php

namespace App\Notifications;

use App\Models\MedicineReminder;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;
use Illuminate\Support\Facades\Log;

class MedicinePushNotification extends Notification
{

    protected $reminder;

    public function __construct(MedicineReminder $reminder)
    {
        $this->reminder = $reminder;
    }

    public function via($notifiable)
    {
        if (!$notifiable->wantsPushNotifications()) {
            return [];
        }
        
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        $medicineName = $this->reminder->schedule->medicine->medicine_name;
        $time = $this->reminder->reminder_at->format('h:i A');
        
        // Get the base URL from config
        $baseUrl = config('app.url');
        
        // Generate CSRF token for the notification
        $csrfToken = csrf_token();

        Log::info('🎯 Preparing push for: ' . $medicineName . ' at ' . $time);

        return (new WebPushMessage)
            ->title('💊 ' . $medicineName)
            ->icon('/images/logos/applogo.jpg')
            ->body("Time to take your medicine at {$time}")
            ->action('✓ Mark Taken', 'mark_taken')
            ->action('⏰ Snooze 5 min', 'snooze')
            ->data([
                'reminder_id' => $this->reminder->id,
                'medicine' => $medicineName,
                'time' => $time,
                'url' => $baseUrl . '/medicine/reminders',
                'base_url' => $baseUrl,
                'csrf_token' => $csrfToken,
            ])
            ->options(['TTL' => 86400]);
    }
}