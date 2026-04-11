<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;

    protected $table = 'user_setteings';

    protected $fillable = [
        'user_id',
        'email_notifications',
        'push_notifications',
        'show_personal_info',
        'show_diseases',
        'show_chatbot',
        'show_notification_badge',
        'show_mail_badge',
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'show_personal_info' => 'boolean',
        'show_diseases' => 'boolean',
        'show_chatbot' => 'boolean',
        'show_notification_badge' => 'boolean',
        'show_mail_badge' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
