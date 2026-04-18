<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Route;

class RedirectIfEmailNotVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->hasVerifiedEmail() || $user->email === 'admin@mydoctor.com' || $user->isAdmin()) {
            return $next($request);
        }

        $routeName = optional($request->route())->getName();
        $allowedRouteNames = [
            'verification.notice',
            'verification.verify',
            'verification.resend',
            'logout',
            'password.request',
            'password.email',
            'password.reset',
            'password.update',
            'login',
        ];

        if ($routeName && in_array($routeName, $allowedRouteNames, true)) {
            return $next($request);
        }

        $user->sendEmailVerificationNotification();

        if (Route::has('verification.notice')) {
            return redirect()->route('verification.notice')
                ->with('status', 'Verification link sent to ' . $user->email . '. Please verify your email before continuing.');
        }

        return redirect()->route('home')
            ->with('status', 'Verification link sent to ' . $user->email . '. Please verify your email before continuing.');
    }
}
