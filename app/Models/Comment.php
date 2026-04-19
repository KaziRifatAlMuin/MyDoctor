<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'comment_details',
        'file_path',
        'file_type',
        'file_name',
        'file_size',
        'like_count',
    ];

    protected $casts = [
        'like_count' => 'integer',
        'file_size' => 'integer',
    ];

    protected $appends = ['file_url', 'file_icon', 'formatted_file_size'];

    protected static function booted(): void
    {
        // Global scope: Hide comments from deleted/inactive users (except for admins)
        static::addGlobalScope('withExistingUser', function ($query) {
            // Skip for admin routes - admins can see everything
            if (request()->is('admin*') || request()->routeIs('admin.*')) {
                return;
            }
            $query->whereHas('user', function ($q) {
                $q->where('is_active', true);
            });
        });

        static::created(function (Comment $comment): void {
            $comment->loadMissing(['post', 'user']);

            if (!$comment->post || !$comment->user) {
                return;
            }

            if ($comment->post->user_id === $comment->user_id) {
                return;
            }

            $commenter = $comment->user;
            $preview = strlen((string) $comment->comment_details) > 50
                ? substr((string) $comment->comment_details, 0, 50) . '...'
                : (string) $comment->comment_details;

            Notification::create([
                'user_id' => $comment->post->user_id,
                'from_user_id' => $commenter->id,
                'type' => 'comment',
                'notifiable_type' => self::class,
                'notifiable_id' => $comment->id,
                'message' => "{$commenter->name} commented on your post",
                'data' => [
                    'post_id' => $comment->post->id,
                    'comment_id' => $comment->id,
                    'comment_preview' => $preview,
                    'actor_name' => $commenter->name,
                    'actor_avatar' => $commenter->picture ? asset('storage/' . $commenter->picture) : null,
                ],
            ]);

            $starFollowerIds = PostLike::query()
                ->where('post_id', $comment->post->id)
                ->where('is_starred', true)
                ->where('user_id', '!=', $commenter->id)
                ->pluck('user_id')
                ->unique()
                ->values();

            foreach ($starFollowerIds as $followerId) {
                if ((int) $followerId === (int) $comment->post->user_id) {
                    continue;
                }

                Notification::create([
                    'user_id' => $followerId,
                    'from_user_id' => $commenter->id,
                    'type' => 'starred_post_update',
                    'notifiable_type' => self::class,
                    'notifiable_id' => $comment->id,
                    'message' => "{$commenter->name} commented on a post you starred",
                    'data' => [
                        'post_id' => $comment->post->id,
                        'comment_id' => $comment->id,
                        'comment_preview' => $preview,
                        'actor_name' => $commenter->name,
                        'actor_avatar' => $commenter->picture ? asset('storage/' . $commenter->picture) : null,
                    ],
                ]);
            }
        });
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->hasMany(CommentLike::class);
    }

    public function isLikedBy($user)
    {
        if (!$user) return false;
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    /**
     * FIXED #2: Properly construct file URL with normalized path
     */
    public function getFileUrlAttribute()
    {
        if (!$this->file_path) return null;
        
        // Normalize the path - remove 'storage/' if it exists
        $path = str_replace('storage/', '', $this->file_path);
        
        return asset('storage/' . $path);
    }

    public function getFileIconAttribute()
    {
        if (!$this->file_type) return 'fa-file';
        
        $type = explode('/', $this->file_type)[0];
        return match($type) {
            'image' => 'fa-file-image',
            'video' => 'fa-file-video',
            'audio' => 'fa-file-audio',
            'application' => match($this->file_type) {
                'application/pdf' => 'fa-file-pdf',
                'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'fa-file-word',
                'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'fa-file-excel',
                default => 'fa-file',
            },
            default => 'fa-file',
        };
    }

    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size) return null;
        
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}