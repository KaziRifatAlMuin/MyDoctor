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