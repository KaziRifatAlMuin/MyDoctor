<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
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
        'is_active',
        'password',
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
        'notification_settings' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('active_users', function (Builder $builder): void {
            if (app()->runningInConsole()) {
                return;
            }

            // Avoid resolving the authenticated User model here to prevent recursion.
            if (request()->is('admin') || request()->is('admin/*')) {
                return;
            }

            $builder->where('users.is_active', true);
        });

        static::created(function (User $user): void {
            $user->setting()->firstOrCreate([], [
                'email_notifications' => true,
                'push_notifications' => true,
                'show_personal_info' => false,
                'show_diseases' => false,
                'show_chatbot' => true,
                'show_notification_badge' => true,
                'show_mail_badge' => true,
            ]);

            $user->address()->firstOrCreate([], [
                'division_id' => null,
                'division' => 'Not set',
                'division_bn' => null,
                'district_id' => null,
                'district' => 'Not set',
                'district_bn' => null,
                'upazila_id' => null,
                'upazila' => 'Not set',
                'upazila_bn' => null,
                'street' => null,
                'house' => null,
            ]);
        });
    }

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
        return (bool) $this->setting->email_notifications;
    }

    /**
     * Check if user wants push notifications
     */
    public function wantsPushNotifications(): bool
    {
        return (bool) $this->setting->push_notifications;
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
        $setting = $this->setting()->firstOrCreate([]);
        $setting->email_notifications = !$setting->email_notifications;
        $setting->save();

        return (bool) $setting->email_notifications;
    }

    /**
     * Toggle push notifications
     */
    public function togglePushNotifications(): bool
    {
        $setting = $this->setting()->firstOrCreate([]);
        $setting->push_notifications = !$setting->push_notifications;
        $setting->save();

        return (bool) $setting->push_notifications;
    }

    public function setting()
    {
        return $this->hasOne(UserSetting::class)->withDefault([
            'email_notifications' => true,
            'push_notifications' => true,
            'show_personal_info' => false,
            'show_diseases' => false,
            'show_chatbot' => true,
            'show_notification_badge' => true,
            'show_mail_badge' => true,
        ]);
    }

    public function address()
    {
        return $this->hasOne(UserAddress::class)->withDefault([
            'division_id' => null,
            'division' => 'Not set',
            'division_bn' => null,
            'district_id' => null,
            'district' => 'Not set',
            'district_bn' => null,
            'upazila_id' => null,
            'upazila' => 'Not set',
            'upazila_bn' => null,
            'street' => null,
            'house' => null,
        ]);
    }

    // Health relationships
    public function healthMetrics()
    {
        return $this->hasMany(UserHealth::class, 'user_id');
    }

    public function healthMetricDefinitions()
    {
        return $this->belongsToMany(HealthMetric::class, 'user_health', 'user_id', 'health_metric_id')
            ->withPivot('value', 'recorded_at')
            ->withTimestamps();
    }

    public function symptoms()
    {
        return $this->hasMany(UserSymptom::class);
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

    public function starredDiseases()
    {
        return $this->belongsToMany(Disease::class, 'user_starred_diseases')
            ->withTimestamps();
    }

    public function userStarredDiseases()
    {
        return $this->hasMany(UserStarredDisease::class);
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