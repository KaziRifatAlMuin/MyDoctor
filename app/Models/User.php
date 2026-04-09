<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'picture',
        'name',
        'date_of_birth',
        'phone',
        'email',
        'role',
        'occupation',
        'blood_group',
        'gender',
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
     * Push subscriptions relationship
     */
    public function pushSubscriptions()
    {
        return $this->morphMany(\App\Models\PushSubscription::class, 'subscribable');
    }

    /**
     * Update or create a push subscription
     */
    public function updatePushSubscription(string $endpoint, ?string $publicKey = null, ?string $authToken = null, string $contentEncoding = 'aesgcm')
    {
        try {
            Log::info('Updating push subscription for user ' . $this->id, [
                'endpoint_prefix' => substr($endpoint, 0, 50) . '...',
                'has_public_key' => !is_null($publicKey),
                'has_auth_token' => !is_null($authToken)
            ]);

            // First, clean up any orphaned subscriptions with same endpoint for different users
            \App\Models\PushSubscription::where('endpoint', $endpoint)
                ->where('subscribable_id', '!=', $this->id)
                ->delete();

            // Update or create the subscription
            $subscription = $this->pushSubscriptions()->updateOrCreate(
                ['endpoint' => $endpoint],
                [
                    'public_key' => $publicKey,
                    'auth_token' => $authToken,
                    'content_encoding' => $contentEncoding,
                ]
            );

            Log::info('Push subscription saved', [
                'subscription_id' => $subscription->id,
                'user_id' => $this->id
            ]);

            return $subscription;

        } catch (\Exception $e) {
            Log::error('Failed to update push subscription', [
                'user_id' => $this->id,
                'error' => $e->getMessage(),
                'endpoint' => substr($endpoint, 0, 100) . '...'
            ]);
            
            // Try one more time with a more aggressive approach
            try {
                // Force delete any existing subscription with this endpoint
                \App\Models\PushSubscription::where('endpoint', $endpoint)->delete();
                
                // Create fresh
                $subscription = $this->pushSubscriptions()->create([
                    'endpoint' => $endpoint,
                    'public_key' => $publicKey,
                    'auth_token' => $authToken,
                    'content_encoding' => $contentEncoding,
                ]);
                
                Log::info('Push subscription created after retry', [
                    'subscription_id' => $subscription->id
                ]);
                
                return $subscription;
                
            } catch (\Exception $e2) {
                Log::error('Critical push subscription failure', [
                    'user_id' => $this->id,
                    'error' => $e2->getMessage()
                ]);
                throw $e2;
            }
        }
    }

    /**
     * Delete a push subscription by endpoint
     */
    public function deletePushSubscription(string $endpoint)
    {
        Log::info('Deleting push subscription', [
            'user_id' => $this->id,
            'endpoint_prefix' => substr($endpoint, 0, 50) . '...'
        ]);
        
        return $this->pushSubscriptions()->where('endpoint', $endpoint)->delete();
    }

    /**
     * Check if user wants email notifications
     */
    public function wantsEmailNotifications(): bool
    {
        return $this->email_notifications ?? true;
    }

    /**
     * Check if user wants push notifications
     */
    public function wantsPushNotifications(): bool
    {
        return $this->push_notifications ?? true;
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

    // Health relationships
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

    // Community relationships
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function postLikes()
    {
        return $this->hasMany(PostLike::class);
    }

    public function commentLikes()
    {
        return $this->hasMany(CommentLike::class);
    }

    public function chatMessages()
    {
        return $this->hasMany(AiChatMessage::class);
    }

    /**
     * Get user's full name
     */
    public function getNameAttribute()
    {
        return $this->attributes['name'] ?? null;
    }




// Add this with your other relationships
public function notifications()
{
    return $this->hasMany(\App\Models\Notification::class, 'user_id');
}

public function unreadNotifications()
{
    return $this->hasMany(\App\Models\Notification::class, 'user_id')->unread();
}

public function sentNotifications()
{
    return $this->hasMany(\App\Models\Notification::class, 'from_user_id');
}

/**
 * Check if user is admin
 */
public function isAdmin(): bool
{
    return $this->role === 'admin';
}

/**
 * Check if user is member
 */
public function isMember(): bool
{
    return $this->role === 'member';
}

/**
 * Check if user has specific role
 */
public function hasRole(string $role): bool
{
    return $this->role === $role;
}

}