<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PostApprovedNotification extends Notification
{
    use Queueable;

    protected $post;
    protected $approvedBy;
    protected $systemUser;

    public function __construct(Post $post, $approvedBy = null)
    {
        $this->post = $post;
        $this->approvedBy = $approvedBy;
        
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
            ->subject("✅ Your post has been approved!")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Great news! Your post has been approved and is now visible to the community.")
            ->line("**Post Preview:**")
            ->line($postPreview)
            ->action('View Your Post', route('community.posts.show', $this->post))
            ->line("Thank you for contributing to our community!");
    }

    public function toDatabase($notifiable)
    {
        $postPreview = strlen($this->post->description) > 100 
            ? substr($this->post->description, 0, 100) . '...' 
            : $this->post->description;

        return [
            'type' => 'post_approved',
            'post_id' => $this->post->id,
            'post_preview' => $postPreview,
            'disease_name' => $this->post->disease?->display_name,
            'message' => "Your post has been approved and is now visible to the community!",
            'action_url' => route('community.posts.show', $this->post),
            'approved_by' => $this->approvedBy?->name,
            'approved_at' => now()->toISOString(),
            'from_user_id' => $this->approvedBy?->id ?? $this->systemUser->id,
            'from_user_name' => $this->approvedBy?->name ?? 'System',
        ];
    }
}