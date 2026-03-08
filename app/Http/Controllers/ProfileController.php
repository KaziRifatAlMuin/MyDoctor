<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show the profile page.
     */
    public function index()
    {
        return view('profile');
    }

    /**
     * Update personal information.
     */
    public function update(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'phone'         => 'nullable|string|max:20',
            'occupation'    => 'nullable|string|max:255',
            'blood_group'   => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
        ]);

        $user = Auth::user();
        $user->name = $request->input('name');
        $user->date_of_birth = $request->input('date_of_birth');
        $user->phone = $request->input('phone');
        $user->occupation = $request->input('occupation');
        $user->blood_group = $request->input('blood_group');
        $user->save();

        return redirect()->route('profile')->with('success', 'Profile updated successfully.');
    }

    /**
     * Update profile picture.
     */
    public function updatePicture(Request $request)
    {
        $request->validate([
            'picture' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = Auth::user();

        // Delete old picture if it exists
        if ($user->picture && Storage::disk('public')->exists($user->picture)) {
            Storage::disk('public')->delete($user->picture);
        }

        $path = $request->file('picture')->store('profile-pictures', 'public');
        $user->picture = $path;
        $user->save();

        return redirect()->route('profile')->with('success', 'Profile picture updated successfully.');
    }

    /**
     * Change password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('profile')->with('success', 'Password changed successfully.');
    }

    /**
     * Delete the user account.
     */
    public function destroy(Request $request)
    {
        $request->validate(['password' => 'required']);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'The password you entered is incorrect.']);
        }

        Auth::logout();

        // Remove profile picture from storage
        if ($user->picture && Storage::disk('public')->exists($user->picture)) {
            Storage::disk('public')->delete($user->picture);
        }

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Your account has been permanently deleted.');
    }
}
