<?php

namespace App\Models;

use NotificationChannels\WebPush\PushSubscription as WebPushSubscription;

class PushSubscription extends WebPushSubscription
{
    protected $table = 'push_subscriptions';

    protected $fillable = [
        'subscribable_type',
        'subscribable_id',
        'endpoint',
        'public_key',
        'auth_token',
        'content_encoding',
    ];

    public function subscribable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
}
