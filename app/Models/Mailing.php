<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mailing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recipient_email',
        'subject',
        'body',
        'mailable_type',
        'mailable_id',
        'status',
        'error_message',
        'retry_count',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user associated with this mailing.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark the mailing as sent.
     */
    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark the mailing as failed.
     */
    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    /**
     * Check if the mailing can be retried.
     */
    public function canRetry($maxRetries = 3)
    {
        return $this->status === 'failed' && $this->retry_count < $maxRetries;
    }
}
