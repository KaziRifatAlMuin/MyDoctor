<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Where to redirect users after registration.
     */
    protected $redirectTo = '/login'; // Redirect to login after registration

    /**
     * Show the application registration form.
     * Available to ALL users (no guest middleware in constructor)
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request.
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        // Optional: Log out current user if you want to switch to new account
        // auth()->logout();
        
        // Redirect to login page with success message
        return redirect()->route('login')->with('success', 'Registration successful! Please login with your new account.');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'Name' => ['required', 'string', 'max:255'],
            'Email' => ['required', 'string', 'email', 'max:255', 'unique:users,Email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'Phone' => ['nullable', 'string', 'max:20'],
            'DateOfBirth' => ['nullable', 'date'],
            'Occupation' => ['nullable', 'string', 'max:255'],
            'BloodGroup' => ['nullable', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'Name' => $data['Name'],
            'Email' => $data['Email'],
            'password' => Hash::make($data['password']),
            'Phone' => $data['Phone'] ?? null,
            'DateOfBirth' => $data['DateOfBirth'] ?? null,
            'Occupation' => $data['Occupation'] ?? null,
            'BloodGroup' => $data['BloodGroup'] ?? null,
            'CreatedAt' => now(),
        ]);
    }
}