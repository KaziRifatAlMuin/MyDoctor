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

        $allowed = [
            'home',
            'help',
            'login',
            'register',
            'password.request',
            'language.switch',
        ];

        if ($request->route() && in_array($request->route()->getName(), $allowed, true)) {
            return $next($request);
        }

        return redirect()->route('home');
    }
}
