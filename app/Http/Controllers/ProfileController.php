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
            'Name'        => 'required|string|max:255',
            'DateOfBirth' => 'nullable|date|before:today',
            'Phone'       => 'nullable|string|max:20',
            'Occupation'  => 'nullable|string|max:255',
            'BloodGroup'  => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
        ]);

        $user = Auth::user();
        $user->Name        = $request->Name;
        $user->DateOfBirth = $request->DateOfBirth;
        $user->Phone       = $request->Phone;
        $user->Occupation  = $request->Occupation;
        $user->BloodGroup  = $request->BloodGroup;
        $user->save();

        return redirect()->route('profile')->with('success', 'Profile updated successfully.');
    }

    /**
     * Update profile picture.
     */
    public function updatePicture(Request $request)
    {
        $request->validate([
            'Picture' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = Auth::user();

        // Delete old picture if it exists
        if ($user->Picture && Storage::disk('public')->exists($user->Picture)) {
            Storage::disk('public')->delete($user->Picture);
        }

        $path = $request->file('Picture')->store('profile-pictures', 'public');
        $user->Picture = $path;
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
        if ($user->Picture && Storage::disk('public')->exists($user->Picture)) {
            Storage::disk('public')->delete($user->Picture);
        }

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Your account has been permanently deleted.');
    }
}
