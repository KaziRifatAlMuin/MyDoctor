<?php
// app/Http/Controllers/Auth/RegisterController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/email/verify';

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

        // Send email verification notification
        event(new Registered($user));

        // Log the user in so they can see the verification notice page
        Auth::login($user);

        return redirect()->route('verification.notice')
            ->with('status', 'Registration successful! A verification link has been sent to ' . $user->email . '. Please verify your email to continue.');
    }

    /**
     * Get a validator for an incoming registration request.
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'Name' => ['required', 'string', 'max:255'],
            'Email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:4', 'confirmed'], // Simple: min 4 characters, no complexity
            'redirect' => ['nullable', 'string', 'max:2048'],
            'Phone' => ['nullable', 'string', 'max:20'],
            'DateOfBirth' => ['nullable', 'date'],
            'Occupation' => ['nullable', 'string', 'max:255'],
            'BloodGroup' => ['nullable', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'Gender' => ['required', 'in:male,female,other'],
            'DivisionId' => ['required', 'integer'],
            'Division' => ['required', 'string', 'max:255'],
            'DivisionBn' => ['nullable', 'string', 'max:255'],
            'DistrictId' => ['required', 'integer'],
            'District' => ['required', 'string', 'max:255'],
            'DistrictBn' => ['nullable', 'string', 'max:255'],
            'UpazilaId' => ['required', 'integer'],
            'Upazila' => ['required', 'string', 'max:255'],
            'UpazilaBn' => ['nullable', 'string', 'max:255'],
            'Street' => ['nullable', 'string', 'max:255'],
            'House' => ['nullable', 'string', 'max:255'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['Name'],
            'email' => $data['Email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['Phone'] ?? null,
            'date_of_birth' => $data['DateOfBirth'] ?? null,
            'occupation' => $data['Occupation'] ?? null,
            'blood_group' => $data['BloodGroup'] ?? null,
            'gender' => $data['Gender'] ?? null,
        ]);

        $user->address()->updateOrCreate([], [
            'division_id' => $data['DivisionId'] ?? null,
            'division' => $data['Division'] ?? null,
            'division_bn' => $data['DivisionBn'] ?? null,
            'district_id' => $data['DistrictId'] ?? null,
            'district' => $data['District'],
            'district_bn' => $data['DistrictBn'] ?? null,
            'upazila_id' => $data['UpazilaId'] ?? null,
            'upazila' => $data['Upazila'],
            'upazila_bn' => $data['UpazilaBn'] ?? null,
            'street' => $data['Street'] ?? null,
            'house' => $data['House'] ?? null,
        ]);

        return $user;
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