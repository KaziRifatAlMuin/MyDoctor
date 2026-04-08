<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationPreferenceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $chatbotBubbleEnabled = request()->cookie('chatbot_bubble_enabled', '1') === '1';
        return view('profile.setting', compact('user', 'chatbotBubbleEnabled'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'email_notifications' => 'sometimes|boolean',
            'push_notifications' => 'sometimes|boolean',
            'show_personal_info' => 'sometimes|boolean',
            'show_diseases' => 'sometimes|boolean',
            'reminder_before_minutes' => 'nullable|integer|min:0|max:120',
        ]);

        $user->email_notifications = $request->has('email_notifications');
        $user->push_notifications = $request->has('push_notifications');
        $user->show_personal_info = $request->has('show_personal_info');
        $user->show_diseases = $request->has('show_diseases');

        $settings = is_array($user->notification_settings) ? $user->notification_settings : [];
        $settings['reminder_before_minutes'] = $validated['reminder_before_minutes'] ?? 5;
        $user->notification_settings = $settings;
        
        $user->save();

        // Persist chatbot bubble preference as a cookie (365 days)
        $chatbotCookieValue = $request->has('chatbot_bubble') ? '1' : '0';
        $minutes = 60 * 24 * 365; // 1 year

        return redirect()->back()
            ->with('success', 'Notification preferences updated successfully.')
            ->withCookie(cookie('chatbot_bubble_enabled', $chatbotCookieValue, $minutes));
    }

    public function toggleEmail(Request $request)
    {
        $user = Auth::user();
        $newState = $user->toggleEmailNotifications();
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'email_notifications' => $newState
            ]);
        }
        
        return redirect()->back()->with('success', 'Email notifications ' . ($newState ? 'enabled' : 'disabled'));
    }

    public function togglePush(Request $request)
    {
        $user = Auth::user();
        $newState = $user->togglePushNotifications();
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'push_notifications' => $newState
            ]);
        }
        
        return redirect()->back()->with('success', 'Push notifications ' . ($newState ? 'enabled' : 'disabled'));
    }
}