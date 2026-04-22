<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PostDeletedNotification extends Notification
{
    use Queueable;

    protected $post;
    protected $reason;
    protected $deletedBy;
    protected $systemUser;
    protected $postData;

    public function __construct(Post $post, $reason = null, $deletedBy = null)
    {
        $this->post = $post;
        $this->reason = $reason;
        $this->deletedBy = $deletedBy;
        
        // Store post data before it's deleted
        $this->postData = [
            'id' => $post->id,
            'description' => $post->description,
            'created_at' => $post->created_at->toISOString(),
        ];
        
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
            ? "\n\n**Reason for deletion:** {$this->reason}" 
            : '';

        return (new MailMessage)
            ->subject("❌ Your post (#{$this->post->id}) has been deleted")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your post (ID: {$this->post->id}) has been removed from the community by an administrator.")
            ->line("**Your Post (preview):**")
            ->line($postPreview)
            ->line($reasonText)
            ->line("If you believe this was a mistake, please contact our support team.");
    }

    public function toDatabase($notifiable)
    {
        $postPreview = strlen($this->post->description) > 100 
            ? substr($this->post->description, 0, 100) . '...' 
            : $this->post->description;

        return [
            'type' => 'post_deleted',
            'post_id' => $this->post->id,
            'post_preview' => $postPreview,
            'post_data' => $this->postData,
            'reason' => $this->reason,
            'message' => "Your post (ID: {$this->post->id}) was deleted by admin." . ($this->reason ? " Reason: {$this->reason}" : "") . ($postPreview ? " Preview: {$postPreview}" : ""),
            'action_url' => route('users.show', $notifiable->id),
            'deleted_by' => $this->deletedBy?->name,
            'deleted_at' => now()->toISOString(),
            'from_user_id' => $this->deletedBy?->id ?? $this->systemUser->id,
            'from_user_name' => $this->deletedBy?->name ?? 'System',
        ];
    }
}
