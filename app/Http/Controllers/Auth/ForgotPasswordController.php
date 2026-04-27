<?php
// app/Http/Controllers/Auth/ForgotPasswordController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;

class ForgotPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $maintenance = Cache::get('maintenance_mode', config('app.maintenance_mode', false));
        if ($maintenance === true) {
            return redirect()->route('maintenance');
        }

        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Log the request for debugging
        \Log::info('Password reset request received', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $key = 'password-reset:' . $request->email;
        
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            \Log::warning('Rate limit exceeded for password reset', [
                'email' => $request->email,
                'seconds_remaining' => $seconds
            ]);
            return back()->withErrors([
                'email' => "Too many reset attempts. Please try again in {$seconds} seconds."
            ]);
        }

        RateLimiter::hit($key, 300);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        \Log::info('Password reset link sent', [
            'email' => $request->email,
            'status' => $status
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        \Log::error('Password reset failed', [
            'email' => $request->email,
            'status' => $status
        ]);

        return back()->withErrors(['email' => __($status)]);
    }
}