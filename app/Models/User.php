<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'picture',
        'name',
        'date_of_birth',
        'phone',
        'email',
        'occupation',
        'blood_group',
        'password',
        'email_notifications',
        'push_notifications',
        'notification_settings',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'email_verified_at' => 'datetime',
        'password'           => 'hashed',
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
     * Push subscriptions relationship (minimal replacement for missing package trait)
     */
    public function pushSubscriptions()
    {
        return $this->morphMany(\App\Models\PushSubscription::class, 'subscribable');
    }

    /**
     * Create or update a push subscription
     */
    public function updatePushSubscription(string $endpoint, ?string $publicKey = null, ?string $authToken = null)
    {
        return $this->pushSubscriptions()->updateOrCreate(
            ['endpoint' => $endpoint],
            ['public_key' => $publicKey, 'auth_token' => $authToken]
        );
    }

    /**
     * Delete a push subscription by endpoint
     */
    public function deletePushSubscription(string $endpoint)
    {
        return $this->pushSubscriptions()->where('endpoint', $endpoint)->delete();
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
public function healthMetrics()
    {
        return $this->hasMany(HealthMetric::class);
    }

    public function symptoms()
    {
        return $this->hasMany(Symptom::class);
    }

    public function medicines()
    {
        return $this->hasMany(Medicine::class);
    }

    public function medicineLogs()
    {
        return $this->hasMany(MedicineLog::class);
    }

    public function uploads()
    {
        return $this->hasMany(Upload::class);
    }

    public function userDiseases()
    {
        return $this->hasMany(UserDisease::class);
    }

    public function diseases()
    {
        return $this->belongsToMany(Disease::class, 'user_diseases')
                    ->withPivot('diagnosed_at', 'status', 'notes')
                    ->withTimestamps();
    }
    /**
     * Get user's full name
     */
public function getNameAttribute()
{
    return $this->attributes['name'] ?? null;
}
}