<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Support\Facades\Log;

class CommunityNotificationService
{
   
    public function postLiked($post, $liker)
    {
       
        if ($post->user_id === $liker->id) {
            return;
        }

        $message = "{$liker->name} liked your post";
        
        $preview = strlen($post->description) > 50 
            ? substr($post->description, 0, 50) . '...' 
            : $post->description;

      
        Notification::create([
            'user_id' => $post->user_id,
            'from_user_id' => $liker->id,
            'type' => 'like',
            'notifiable_type' => Post::class,
            'notifiable_id' => $post->id,
            'message' => $message,
            'data' => [
                'post_id' => $post->id,
                'post_preview' => $preview,
                'actor_name' => $liker->name,
                'actor_avatar' => $liker->picture ? asset('storage/' . $liker->picture) : null,
            ],
        ]);

      
    }

  
    public function commentAdded($comment, $post)
    {
        $commenter = $comment->user;
        
  
        if ($post->user_id === $commenter->id) {
            return;
        }

        $message = "{$commenter->name} commented on your post";
        
        $commentPreview = strlen($comment->comment_details) > 50 
            ? substr($comment->comment_details, 0, 50) . '...' 
            : $comment->comment_details;

       
        Notification::create([
            'user_id' => $post->user_id,
            'from_user_id' => $commenter->id,
            'type' => 'comment',
            'notifiable_type' => Comment::class,
            'notifiable_id' => $comment->id,
            'message' => $message,
            'data' => [
                'post_id' => $post->id,
                'comment_id' => $comment->id,
                'comment_preview' => $commentPreview,
                'actor_name' => $commenter->name,
                'actor_avatar' => $commenter->picture ? asset('storage/' . $commenter->picture) : null,
            ],
        ]);

       
    }

  
}