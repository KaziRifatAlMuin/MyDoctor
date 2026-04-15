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
}