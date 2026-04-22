<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PostReportedNotification extends Notification
{
    use Queueable;

    protected $post;
    protected $reportedBy;
    protected $reportReason;
    protected $systemUser;

    public function __construct(Post $post, $reportedBy = null, $reportReason = null)
    {
        $this->post = $post;
        $this->reportedBy = $reportedBy;
        $this->reportReason = $reportReason;
        
        // Get system user (create if doesn't exist)
        $this->systemUser = User::firstOrCreate(
            ['email' => 'system@mydoctor.com'],
            [
                'name' => 'System',
                'password' => bcrypt(uniqid()),
                'gender' => 'other',
                'role' => 'member',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }

    public function via($notifiable)
    {
        $channels = ['database'];
        
        if ($notifiable->wantsEmailNotifications()) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    public function toMail($notifiable)
    {
        $postPreview = strlen($this->post->description) > 100 
            ? substr($this->post->description, 0, 100) . '...' 
            : $this->post->description;

        return (new MailMessage)
            ->subject("⚠️ Your post has been reported")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your post has been reported by a community member for potentially violating our guidelines.")
            ->line("**Your Post:**")
            ->line($postPreview)
            ->line("Our moderation team will review your post. If it violates guidelines, it may be removed.")
            ->line("If you believe this was a mistake, please contact support.");
    }

    public function toDatabase($notifiable)
    {
        $postPreview = strlen($this->post->description) > 100 
            ? substr($this->post->description, 0, 100) . '...' 
            : $this->post->description;

        return [
            'type' => 'post_reported',
            'post_id' => $this->post->id,
            'post_preview' => $postPreview,
            'reported_by' => $this->reportedBy?->name,
            'report_reason' => $this->reportReason,
            'message' => "Your post has been reported and is under review by moderators.",
            'action_url' => route('community.posts.show', $this->post),
            'reported_at' => now()->toISOString(),
            'from_user_id' => $this->reportedBy?->id ?? $this->systemUser->id,
            'from_user_name' => $this->reportedBy?->name ?? 'System',
        ];
    }
}