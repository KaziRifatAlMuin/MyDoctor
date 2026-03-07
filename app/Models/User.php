<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasPushSubscriptions;

    protected $fillable = [
        'name',
        'email',
        'password',
        'picture',
        'phone',
        'date_of_birth',
        'occupation',
        'blood_group',
        'email_notifications',
        'push_notifications',
        'notification_settings',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'email_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'notification_settings' => 'array',
    ];



    /**
     * Route notifications for Web Push
     */
    public function routeNotificationForWebPush()
    {
        return $this->pushSubscriptions;
    }

    /**
     * Check if user wants email notifications
     */
    public function wantsEmailNotifications(): bool
    {
        return $this->email_notifications;
    }

    /**
     * Check if user wants push notifications
     */
    public function wantsPushNotifications(): bool
    {
        return $this->push_notifications;
    }

    /**
     * Get specific notification setting
     */
    public function getNotificationSetting(string $key, $default = null)
    {
        return $this->notification_settings[$key] ?? $default;
    }

    /**
     * Update notification settings
     */
    public function updateNotificationSettings(array $settings): void
    {
        $currentSettings = $this->notification_settings ?? [];
        $this->notification_settings = array_merge($currentSettings, $settings);
        $this->save();
    }

    /**
     * Toggle email notifications
     */
    public function toggleEmailNotifications(): bool
    {
        $this->email_notifications = !$this->email_notifications;
        $this->save();
        return $this->email_notifications;
    }

    /**
     * Toggle push notifications
     */
    public function togglePushNotifications(): bool
    {
        $this->push_notifications = !$this->push_notifications;
        $this->save();
        return $this->push_notifications;
    }

    /**
     * Get user's full name
     */
public function getNameAttribute()
{
    return $this->attributes['name'] ?? null;
}
}