<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // Add policies here later if needed
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}