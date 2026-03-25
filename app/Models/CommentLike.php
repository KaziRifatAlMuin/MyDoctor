<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentLike extends Model
{
    use HasFactory;

    protected $fillable = ['comment_id', 'user_id'];
    public $timestamps = false; // No timestamps needed

    protected static function booted(): void
    {
        static::created(function (CommentLike $commentLike): void {
            $commentLike->loadMissing(['comment.post', 'user']);

            if (!$commentLike->comment || !$commentLike->comment->post || !$commentLike->user) {
                return;
            }

            if ($commentLike->comment->user_id === $commentLike->user_id) {
                return;
            }

            $liker = $commentLike->user;
            $comment = $commentLike->comment;
            $preview = strlen((string) $comment->comment_details) > 50
                ? substr((string) $comment->comment_details, 0, 50) . '...'
                : (string) $comment->comment_details;

            Notification::create([
                'user_id' => $comment->user_id,
                'from_user_id' => $liker->id,
                'type' => 'like',
                'notifiable_type' => Comment::class,
                'notifiable_id' => $comment->id,
                'message' => "{$liker->name} liked your comment",
                'data' => [
                    'post_id' => $comment->post->id,
                    'comment_id' => $comment->id,
                    'comment_preview' => $preview,
                    'actor_name' => $liker->name,
                    'actor_avatar' => $liker->picture ? asset('storage/' . $liker->picture) : null,
                ],
            ]);
        });
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}