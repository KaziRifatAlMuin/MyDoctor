<?php

namespace App\Providers;

use App\Listeners\LogAuthenticationActivity;
use App\Listeners\LogModelActivity;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Login::class => [
            LogAuthenticationActivity::class,
        ],
        Logout::class => [
            LogAuthenticationActivity::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();

        Event::listen('eloquent.created: *', function (string $eventName, array $data): void {
            app(LogModelActivity::class)->handle($eventName, $data);
        });

        Event::listen('eloquent.updated: *', function (string $eventName, array $data): void {
            app(LogModelActivity::class)->handle($eventName, $data);
        });

        Event::listen('eloquent.deleted: *', function (string $eventName, array $data): void {
            app(LogModelActivity::class)->handle($eventName, $data);
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
