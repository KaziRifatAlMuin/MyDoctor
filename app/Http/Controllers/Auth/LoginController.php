<?php
// app/Http/Controllers/Auth/LoginController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'redirect' => 'nullable|string|max:2048',
        ]);

        // Check if user exists (including deactivated users, so we can show the right message)
        $user = User::withoutGlobalScope('active_users')
            ->where('email', $request->email)
            ->first();
        $bypassVerification = $user && (
            $user->email === 'admin@mydoctor.com' || $user->isAdmin()
        );

        if ($user && !$user->hasVerifiedEmail() && ! $bypassVerification) {
            if (Hash::check($request->password, $user->password)) {
                $user->sendEmailVerificationNotification();
                Auth::login($user);

                return redirect()->route('verification.notice')
                    ->with('status', 'Verification link sent to ' . $user->email . '. Please verify your email before logging in.');
            }

            return back()->withErrors([
                'email' => 'Your email address is not verified yet. Enter the correct password to resend verification.',
            ])->onlyInput('email');
        }

        // Check if user is active
        if ($user && !$user->is_active) {
            return back()->withErrors([
                'email' => 'Your account has been deactivated. Please contact support.',
            ])->onlyInput('email');
        }

        // Attempt login with lowercase 'email' to match database column
        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ], $request->filled('remember'))) {
            
            $request->session()->regenerate();

            return redirect()->to($this->resolveRedirectPath($request, $this->redirectTo));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        // Invalidate session and regenerate CSRF token to be safe
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Also clear the session cookie to help browsers forget session id
        $cookie = cookie()->forget(config('session.cookie'));

        return redirect('/')->withCookies([$cookie]);
    }

    private function resolveRedirectPath(Request $request, string $fallback = '/'): string
    {
        $redirect = (string) ($request->input('redirect') ?? $request->query('redirect') ?? '');
        if ($redirect === '') {
            return $fallback;
        }

        if (str_starts_with($redirect, '/')) {
            return $redirect;
        }

        $parts = parse_url($redirect);
        if (!is_array($parts)) {
            return $fallback;
        }

        if (isset($parts['host']) && strcasecmp((string) $parts['host'], $request->getHost()) !== 0) {
            return $fallback;
        }

        $path = (string) ($parts['path'] ?? '/');
        if (isset($parts['query'])) {
            $path .= '?' . $parts['query'];
        }
        if (isset($parts['fragment'])) {
            $path .= '#' . $parts['fragment'];
        }

        return $path !== '' ? $path : $fallback;
    }
}