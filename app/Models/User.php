<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable implements MustVerifyEmail
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
        'starred_disease_ids',
        'starred_disease_history',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'notification_settings' => 'array',
        'starred_disease_ids' => 'array',
        'starred_disease_history' => 'array',
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
                'show_personal_info' => false,
                'show_diseases' => false,
                'show_chatbot' => true,
                'show_notification_badge' => true,
                'show_mail_badge' => true,
            ]);

            $user->address()->firstOrCreate([], [
                'division_id' => 0,
                'division' => 'Not set',
                'division_bn' => null,
                'district_id' => 0,
                'district' => 'Not set',
                'district_bn' => null,
                'upazila_id' => 0,
                'upazila' => 'Not set',
                'upazila_bn' => null,
                'street' => null,
                'house' => null,
            ]);
        });
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\CustomVerifyEmail());
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }

    /**
     * Check if user wants email notifications
     * FIXED: Added null safety check for setting relationship
     */
    public function wantsEmailNotifications(): bool
    {
        $setting = $this->setting;
        
        // If setting relationship exists and has email_notifications property
        if ($setting && isset($setting->email_notifications)) {
            return (bool) $setting->email_notifications;
        }
        
        // Default to true if setting doesn't exist yet
        return true;
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

    public function setting()
    {
        return $this->hasOne(UserSetting::class)->withDefault([
            'email_notifications' => true,
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
            'division_id' => 0,
            'division' => 'Not set',
            'division_bn' => null,
            'district_id' => 0,
            'district' => 'Not set',
            'district_bn' => null,
            'upazila_id' => 0,
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
        $ids = $this->getStarredDiseaseIds();

        if ($ids === []) {
            return collect();
        }

        return Disease::query()
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(function (Disease $disease) use ($ids) {
                $index = array_search((int) $disease->id, $ids, true);

                return $index === false ? PHP_INT_MAX : $index;
            })
            ->values();
    }

    public function getStarredDiseaseIds(): array
    {
        $ids = $this->starred_disease_ids ?? [];

        if (!is_array($ids)) {
            $decoded = json_decode((string) $ids, true);
            $ids = is_array($decoded) ? $decoded : [];
        }

        $normalized = array_map('intval', $ids);
        $normalized = array_values(array_unique(array_filter($normalized, static fn (int $id): bool => $id > 0)));

        return $normalized;
    }

    public function getStarredDiseaseHistory(): array
    {
        $history = $this->starred_disease_history ?? [];

        if (!is_array($history)) {
            $decoded = json_decode((string) $history, true);
            $history = is_array($decoded) ? $decoded : [];
        }

        return array_values(array_filter(array_map(static function ($row): ?array {
            if (!is_array($row)) {
                return null;
            }

            $diseaseId = isset($row['disease_id']) ? (int) $row['disease_id'] : 0;
            if ($diseaseId <= 0) {
                return null;
            }

            return [
                'disease_id' => $diseaseId,
                'starred_at' => isset($row['starred_at']) ? (string) $row['starred_at'] : now()->toIso8601String(),
                'unstarred_at' => isset($row['unstarred_at']) && $row['unstarred_at'] !== null
                    ? (string) $row['unstarred_at']
                    : null,
            ];
        }, $history)));
    }

    public function isDiseaseStarred(int $diseaseId): bool
    {
        return in_array($diseaseId, $this->getStarredDiseaseIds(), true);
    }

    public function toggleDiseaseStarred(int $diseaseId): bool
    {
        $currentIds = $this->getStarredDiseaseIds();
        $history = $this->getStarredDiseaseHistory();
        $now = now()->toIso8601String();

        if (in_array($diseaseId, $currentIds, true)) {
            $currentIds = array_values(array_filter($currentIds, static fn (int $id): bool => $id !== $diseaseId));

            for ($i = count($history) - 1; $i >= 0; $i--) {
                if ((int) ($history[$i]['disease_id'] ?? 0) === $diseaseId
                    && empty($history[$i]['unstarred_at'])) {
                    $history[$i]['unstarred_at'] = $now;
                    break;
                }
            }

            $this->forceFill([
                'starred_disease_ids' => $currentIds,
                'starred_disease_history' => $history,
            ])->save();

            return false;
        }

        $currentIds[] = $diseaseId;
        $currentIds = array_values(array_unique($currentIds));

        $history[] = [
            'disease_id' => $diseaseId,
            'starred_at' => $now,
            'unstarred_at' => null,
        ];

        $this->forceFill([
            'starred_disease_ids' => $currentIds,
            'starred_disease_history' => $history,
        ])->save();

        return true;
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

    // Notification relationships
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