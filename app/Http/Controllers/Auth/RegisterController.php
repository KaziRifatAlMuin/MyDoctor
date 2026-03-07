<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the application registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request for the application.
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        // Auto login after registration (optional - remove if you want manual login)
        auth()->login($user);

        return redirect($this->redirectTo);
    }

    /**
     * Get a validator for an incoming registration request.
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'Name' => ['required', 'string', 'max:255'],
            'Email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'Phone' => ['nullable', 'string', 'max:20'],
            'DateOfBirth' => ['nullable', 'date'],
            'Occupation' => ['nullable', 'string', 'max:255'],
            'BloodGroup' => ['nullable', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['Name'],
            'email' => $data['Email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['Phone'] ?? null,
            'date_of_birth' => $data['DateOfBirth'] ?? null,
            'occupation' => $data['Occupation'] ?? null,
            'blood_group' => $data['BloodGroup'] ?? null,
            // 'email_notifications' => true, // Default to true
            // 'push_notifications' => true,  // Default to true
        ]);
    }
}