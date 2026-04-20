<?php

namespace App\Listeners;

use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class LogAuthenticationActivity
{
    public function handle(object $event): void
    {
        if ($event instanceof Login) {
            ActivityLogger::log([
                'user_id' => $event->user?->id,
                'category' => 'auth',
                'action' => 'login',
                'description' => 'User logged in',
                'event' => 'login',
                'meta' => [
                    'guard' => $event->guard,
                    'remember' => $event->remember,
                ],
            ]);

            return;
        }

        if ($event instanceof Logout) {
            ActivityLogger::log([
                'user_id' => $event->user?->id,
                'category' => 'auth',
                'action' => 'logout',
                'description' => 'User logged out',
                'event' => 'logout',
                'meta' => [
                    'guard' => $event->guard,
                ],
            ]);
        }
    }
}
