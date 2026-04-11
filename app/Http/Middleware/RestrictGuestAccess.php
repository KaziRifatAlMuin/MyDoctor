<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictGuestAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            return $next($request);
        }

        $allowedRouteNames = [
            'home',
            'help',
            'login',
            'register',
            'password.request',
            'language.switch',
            'privacy.policy',
            'terms.service',
            'cookie.policy',
            'sitemap',
            'public.diseases.index',
            'public.symptoms.index',
            'public.disease.show',
            'public.symptoms.show',
        ];

        if ($request->route() && in_array($request->route()->getName(), $allowedRouteNames, true)) {
            return $next($request);
        }

        // Allow guest access by path, including POST auth actions and geo API endpoints.
        $allowedPaths = [
            '/',
            'login',
            'register',
            'forgot-password',
            'language/*',
            'geo/v2.0/*',
            'help',
            'privacy-policy',
            'terms-of-service',
            'cookie-policy',
            'sitemap',
            'diseases',
            'diseases/*',
            'symptoms',
            'symptoms/*',
            'disease/*',
            'appointments',
            'pharmacy/nearby',
            'emergency',
        ];

        if ($request->is($allowedPaths)) {
            return $next($request);
        }

        return redirect()->route('home');
    }
}
