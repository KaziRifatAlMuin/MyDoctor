<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostLike extends Model
{
    use HasFactory;

    protected $table = 'user_posts';

    protected $fillable = ['post_id', 'user_id', 'is_starred'];

    protected $casts = [
        'is_starred' => 'boolean',
    ];

    public $timestamps = false; // No timestamps needed

    protected static function booted(): void
    {
        static::created(function (PostLike $postLike): void {
            if ($postLike->is_starred) {
                return;
            }

            $postLike->loadMissing(['post', 'user']);

            if (!$postLike->post || !$postLike->user) {
                return;
            }

            if ($postLike->post->user_id === $postLike->user_id) {
                return;
            }

            $post = $postLike->post;
            $liker = $postLike->user;
            $preview = strlen((string) $post->description) > 50
                ? substr((string) $post->description, 0, 50) . '...'
                : (string) $post->description;

            Notification::create([
                'user_id' => $post->user_id,
                'from_user_id' => $liker->id,
                'type' => 'like',
                'notifiable_type' => Post::class,
                'notifiable_id' => $post->id,
                'message' => "{$liker->name} liked your post",
                'data' => [
                    'post_id' => $post->id,
                    'post_preview' => $preview,
                    'actor_name' => $liker->name,
                    'actor_avatar' => $liker->picture ? asset('storage/' . $liker->picture) : null,
                ],
            ]);

            $starFollowerIds = PostLike::query()
                ->where('post_id', $post->id)
                ->where('is_starred', true)
                ->where('user_id', '!=', $liker->id)
                ->pluck('user_id')
                ->unique()
                ->values();

            foreach ($starFollowerIds as $followerId) {
                if ((int) $followerId === (int) $post->user_id) {
                    continue;
                }

                Notification::create([
                    'user_id' => $followerId,
                    'from_user_id' => $liker->id,
                    'type' => 'starred_post_update',
                    'notifiable_type' => Post::class,
                    'notifiable_id' => $post->id,
                    'message' => "{$liker->name} reacted on a post you starred",
                    'data' => [
                        'post_id' => $post->id,
                        'post_preview' => $preview,
                        'actor_name' => $liker->name,
                        'actor_avatar' => $liker->picture ? asset('storage/' . $liker->picture) : null,
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
}