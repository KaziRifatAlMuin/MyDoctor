<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if maintenance mode is enabled (cached value takes precedence)
        $maintenanceEnabled = Cache::get('maintenance_mode', config('app.maintenance_mode', false));

        if ($maintenanceEnabled === true) {
            // Allow admin users to bypass maintenance mode
            if (auth()->check() && auth()->user()->isAdmin()) {
                return $next($request);
            }

            // Redirect to maintenance page
            return redirect()->route('maintenance');
        }

        return $next($request);
    }
}
