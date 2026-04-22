<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PostRejectedNotification extends Notification
{
    use Queueable;

    protected $post;
    protected $reason;
    protected $rejectedBy;
    protected $systemUser;

    public function __construct(Post $post, $reason = null, $rejectedBy = null)
    {
        $this->post = $post;
        $this->reason = $reason;
        $this->rejectedBy = $rejectedBy;
        
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

        $reasonText = $this->reason 
            ? "\n\n**Reason for rejection:** {$this->reason}" 
            : '';

        return (new MailMessage)
            ->subject("❌ Your post was not approved")
            ->greeting("Hello {$notifiable->name}!")
            ->line("We've reviewed your post, but it doesn't meet our community guidelines at this time.")
            ->line("**Your Post:**")
            ->line($postPreview)
            ->line($reasonText)
            ->line("You can edit your post and resubmit it for approval.")
            ->action('Edit Your Post', route('community.posts.show', $this->post))
            ->line("If you have questions, please contact our support team.");
    }

    public function toDatabase($notifiable)
    {
        $postPreview = strlen($this->post->description) > 100 
            ? substr($this->post->description, 0, 100) . '...' 
            : $this->post->description;

        return [
            'type' => 'post_rejected',
            'post_id' => $this->post->id,
            'post_preview' => $postPreview,
            'reason' => $this->reason,
            'message' => "Your post was rejected by admin." . ($this->reason ? " Reason: {$this->reason}" : ""),
            'action_url' => route('community.posts.show', $this->post),
            'rejected_by' => $this->rejectedBy?->name,
            'rejected_at' => now()->toISOString(),
            'from_user_id' => $this->rejectedBy?->id ?? $this->systemUser->id,
            'from_user_name' => $this->rejectedBy?->name ?? 'System',
        ];
    }
}