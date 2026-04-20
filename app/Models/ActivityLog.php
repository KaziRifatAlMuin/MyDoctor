<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',
        'action',
        'description',
        'method',
        'route_name',
        'url',
        'ip_address',
        'user_agent',
        'subject_type',
        'subject_id',
        'event',
        'changes',
        'meta',
    ];

    protected $casts = [
        'changes' => 'array',
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
