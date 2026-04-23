<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'description',
        'is_anonymous',
        'approved_at',
        'is_approved',
        'is_edited',
        'is_reported',
        'file_path',
        'file_type',
        'file_name',
        'file_size',
        'files',
        'like_count',
        'comment_count',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'is_approved' => 'boolean',
        'is_edited' => 'boolean',
        'is_reported' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'like_count' => 'integer',
        'comment_count' => 'integer',
        'file_size' => 'integer',
        'files' => 'array',
    ];

    protected $appends = [
        'file_url', 
        'file_icon', 
        'formatted_file_size',
        'file_count',
        'total_file_size',
        'formatted_total_size',
        'all_files'
    ];

    protected static function booted(): void
    {
        // Global scope: Hide posts from deleted/inactive users (except for admins)
        static::addGlobalScope('withExistingUser', function ($query) {
            // Skip for admin routes - admins can see everything
            if (request()->is('admin*') || request()->routeIs('admin.*')) {
                return;
            }
            $query->whereHas('user', function ($q) {
                $q->where('is_active', true);
            });
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function diseases()
    {
        return $this->belongsToMany(Disease::class, 'post_diseases');
    }

    /**
     * Backward-compatible alias used by legacy views/controllers.
     */
    public function getDiseaseModelsAttribute(): Collection
    {
        if ($this->relationLoaded('diseases')) {
            return $this->getRelation('diseases');
        }

        return $this->diseases()->get();
    }

    /**
     * Primary disease used for legacy UI/components.
     */
    public function getPrimaryDiseaseAttribute(): ?Disease
    {
        return $this->diseases->first();
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    public function isLikedBy($user)
    {
        if (!$user) return false;
        return $this->likes()->where('user_id', $user->id)->where('is_starred', false)->exists();
    }

    /**
     * Get all files with proper formatting
     */
    public function getAllFilesAttribute()
    {
        $files = [];
        
        // Handle multiple files from JSON
        if ($this->files && is_array($this->files)) {
            foreach ($this->files as $file) {
                $files[] = [
                    'path' => $file['path'],
                    'url' => Storage::url($file['path']),
                    'type' => $file['type'],
                    'name' => $file['name'],
                    'size' => $file['size'],
                    'icon' => $this->getFileIcon($file['type']),
                    'formatted_size' => $this->formatBytes($file['size']),
                ];
            }
        } 
        // Handle single file (backward compatibility)
        elseif ($this->file_path) {
            $files[] = [
                'path' => $this->file_path,
                'url' => $this->file_url,
                'type' => $this->file_type,
                'name' => $this->file_name,
                'size' => $this->file_size,
                'icon' => $this->file_icon,
                'formatted_size' => $this->formatted_file_size,
            ];
        }
        
        return $files;
    }

    /**
     * Get file count
     */
    public function getFileCountAttribute()
    {
        if ($this->files && is_array($this->files)) {
            return count($this->files);
        }
        return $this->file_path ? 1 : 0;
    }

    /**
     * Get total file size
     */
    public function getTotalFileSizeAttribute()
    {
        $total = 0;
        
        if ($this->files && is_array($this->files)) {
            foreach ($this->files as $file) {
                $total += $file['size'] ?? 0;
            }
        } elseif ($this->file_size) {
            $total = $this->file_size;
        }
        
        return $total;
    }

    /**
     * Get formatted total size
     */
    public function getFormattedTotalSizeAttribute()
    {
        return $this->formatBytes($this->total_file_size);
    }

    /**
     * Properly construct file URL with normalized path
     */
    public function getFileUrlAttribute()
    {
        if (!$this->file_path) return null;
        
        // Normalize the path - remove 'storage/' if it exists
        $path = str_replace('storage/', '', $this->file_path);
        
        return asset('storage/' . $path);
    }

    /**
     * Get file icon based on mime type
     */
    public function getFileIconAttribute()
    {
        return $this->getFileIcon($this->file_type);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute()
    {
        return $this->formatBytes($this->file_size);
    }

    /**
     * Helper to get file icon
     */
    private function getFileIcon($mimeType)
    {
        if (!$mimeType) return 'fa-file';
        
        if (str_contains($mimeType, 'pdf')) return 'fa-file-pdf';
        if (str_contains($mimeType, 'word')) return 'fa-file-word';
        if (str_contains($mimeType, 'excel') || str_contains($mimeType, 'sheet')) return 'fa-file-excel';
        if (str_contains($mimeType, 'image')) return 'fa-file-image';
        if (str_contains($mimeType, 'video')) return 'fa-file-video';
        if (str_contains($mimeType, 'audio')) return 'fa-file-audio';
        if (str_contains($mimeType, 'text')) return 'fa-file-alt';
        
        return 'fa-file';
    }

    /**
     * Helper to format bytes
     */
    private function formatBytes($bytes)
    {
        if (!$bytes) return null;
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 1) . ' ' . $units[$i];
    }
}