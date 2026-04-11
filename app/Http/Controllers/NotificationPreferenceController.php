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
        $userSetting = $user->setting;

        return view('profile.setting', compact('user', 'userSetting'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'email_notifications' => 'sometimes|boolean',
            'push_notifications' => 'sometimes|boolean',
            'show_personal_info' => 'sometimes|boolean',
            'show_diseases' => 'sometimes|boolean',
            'show_chatbot' => 'sometimes|boolean',
            'show_notification_badge' => 'sometimes|boolean',
            'show_mail_badge' => 'sometimes|boolean',
            'reminder_before_minutes' => 'nullable|integer|min:0|max:120',
        ]);

        $settings = $user->setting()->firstOrCreate([]);
        $settings->email_notifications = $request->has('email_notifications');
        $settings->push_notifications = $request->has('push_notifications');
        $settings->show_personal_info = $request->has('show_personal_info');
        $settings->show_diseases = $request->has('show_diseases');
        if (!$user->isAdmin()) {
            $settings->show_chatbot = $request->has('show_chatbot');
        }
        $settings->show_notification_badge = $request->has('show_notification_badge');
        $settings->show_mail_badge = $request->has('show_mail_badge');
        $settings->save();

        $notificationSettings = is_array($user->notification_settings) ? $user->notification_settings : [];
        $notificationSettings['reminder_before_minutes'] = $validated['reminder_before_minutes'] ?? 5;
        $user->notification_settings = $notificationSettings;
        
        $user->save();

        // Persist chatbot bubble preference as a cookie (365 days)
        $chatbotCookieValue = (!$user->isAdmin() && $request->has('show_chatbot')) ? '1' : '0';
        $minutes = 60 * 24 * 365; // 1 year

        return redirect()->back()
            ->with('success', 'Settings updated successfully.')
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