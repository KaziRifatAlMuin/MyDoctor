<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'from_user_id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'message',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Global scope: Hide notifications from deleted/inactive users (except for admins)
        static::addGlobalScope('withExistingUser', function ($query) {
            if (request()->is('admin*') || request()->routeIs('admin.*')) {
                return;
            }
            $query->whereHas('user', function ($q) {
                $q->where('is_active', true);
            });
        });

        // Global scope: Hide notifications from deleted/inactive from_users (except for admins)
        static::addGlobalScope('withExistingFromUser', function ($query) {
            if (request()->is('admin*') || request()->routeIs('admin.*')) {
                return;
            }
            $query->whereHas('fromUser', function ($q) {
                $q->where('is_active', true);
            });
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function notifiable()
    {
        return $this->morphTo();
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for starred notifications
     */
    public function scopeStarred($query)
    {
        return $query->where('data->starred', true);
    }

    /**
     * Scope for unstarred notifications
     */
    public function scopeUnstarred($query)
    {
        return $query->where(function($q) {
            $q->whereNull('data')
              ->orWhere('data->starred', false)
              ->orWhereRaw('JSON_EXTRACT(data, "$.starred") IS NULL')
              ->orWhereRaw('JSON_EXTRACT(data, "$.starred") = false');
        });
    }

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function isRead()
    {
        return !is_null($this->read_at);
    }

    /**
     * Check if notification is starred
     */
    public function isStarred(): bool
    {
        return isset($this->data['starred']) && $this->data['starred'] === true;
    }

    /**
     * Toggle star status
     */
    public function toggleStar(): bool
    {
        $data = $this->data ?? [];
        $data['starred'] = !($data['starred'] ?? false);
        $this->data = $data;
        $this->saveQuietly(); // Use saveQuietly to avoid triggering extra events
        
        return $data['starred'];
    }

    /**
     * Mark as starred
     */
    public function markAsStarred(): void
    {
        $data = $this->data ?? [];
        $data['starred'] = true;
        $this->data = $data;
        $this->saveQuietly();
    }

    /**
     * Mark as unstarred
     */
    public function markAsUnstarred(): void
    {
        $data = $this->data ?? [];
        $data['starred'] = false;
        $this->data = $data;
        $this->saveQuietly();
    }

    /**
     * Get the from_user_id from data if column is null
     */
    public function getFromUserIdAttribute($value)
    {
        if ($value) {
            return $value;
        }
        
        // If from_user_id is null, try to get it from data array
        $data = $this->data ?? [];
        return $data['from_user_id'] ?? null;
    }

    /**
     * Get the from_user_name from data
     */
    public function getFromUserNameAttribute()
    {
        $data = $this->data ?? [];
        return $data['from_user_name'] ?? 'System';
    }

    /**
     * Get notification type label
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            'like' => 'Like',
            'comment' => 'Comment',
            'reply' => 'Reply',
            'post_approved' => 'Post Approved',
            'post_rejected' => 'Post Rejected',
            'post_reported' => 'Post Reported',
            'starred_disease_post' => 'Starred Disease Update',
            'medicine_reminder' => 'Medicine Reminder',
        ];
        
        $type = $this->type;
        return $labels[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }

    /**
     * Get notification icon class
     */
    public function getIconClassAttribute()
    {
        $icons = [
            'like' => 'fa-heart',
            'comment' => 'fa-comment',
            'reply' => 'fa-reply',
            'post_approved' => 'fa-check-circle',
            'post_rejected' => 'fa-times-circle',
            'post_reported' => 'fa-flag',
            'starred_disease_post' => 'fa-star',
            'medicine_reminder' => 'fa-pills',
        ];
        
        $type = $this->type;
        return $icons[$type] ?? 'fa-bell';
    }

    /**
     * Get notification color class
     */
    public function getColorClassAttribute()
    {
        $colors = [
            'like' => 'danger',
            'comment' => 'primary',
            'reply' => 'info',
            'post_approved' => 'success',
            'post_rejected' => 'danger',
            'post_reported' => 'warning',
            'starred_disease_post' => 'warning',
            'medicine_reminder' => 'success',
        ];
        
        $type = $this->type;
        return $colors[$type] ?? 'secondary';
    }

    /**
     * Get action URL for the notification
     */
    public function getActionUrlAttribute()
    {
        $data = $this->data ?? [];
        
        // Check if action_url is directly provided in data
        if (isset($data['action_url'])) {
            return $data['action_url'];
        }
        
        // Generate URL based on notification type
        switch ($this->type) {
            case 'post_approved':
            case 'post_rejected':
            case 'post_reported':
            case 'starred_disease_post':
                if (isset($data['post_id'])) {
                    return route('community.posts.show', $data['post_id']);
                }
                break;
                
            case 'medicine_reminder':
                return route('medicine.reminders');
                
            case 'like':
            case 'comment':
            case 'reply':
                if (isset($data['post_id'])) {
                    return route('community.posts.show', $data['post_id']);
                }
                break;
        }
        
        return '#';
    }

    /**
     * Get preview text for the notification
     */
    public function getPreviewTextAttribute()
    {
        $data = $this->data ?? [];
        
        if (isset($data['post_preview'])) {
            return $data['post_preview'];
        }
        
        if (isset($data['comment_preview'])) {
            return $data['comment_preview'];
        }
        
        return null;
    }
}