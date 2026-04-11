<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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
            'email' => 'required|email',  // Changed from 'Email' to 'email'
            'password' => 'required|string',
            'redirect' => 'nullable|string|max:2048',
        ]);

        // Attempt login with lowercase 'email' to match database column
        if (Auth::attempt([
            'email' => $request->email,  // Changed from 'Email' to 'email'
            'password' => $request->password
        ], $request->filled('remember'))) {
            
            $request->session()->regenerate();
            
            return redirect()->to($this->resolveRedirectPath($request, $this->redirectTo));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',  // Changed from 'Email' to 'email'
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
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