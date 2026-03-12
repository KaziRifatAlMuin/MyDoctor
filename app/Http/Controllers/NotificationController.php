<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            if ($request->wantsJson()) {
                return response()->json(['notifications' => [], 'unread_count' => 0]);
            }
            return redirect()->route('login');
        }

        if ($request->wantsJson()) {
            $limit = $request->get('limit', 5);
            $recent = $user->notifications()
                ->with('fromUser')
                ->latest()
                ->limit($limit)
                ->get()
                ->map(function($notification) {
                    return [
                        'id' => $notification->id,
                        'user_id' => $notification->user_id,
                        'from_user' => $notification->fromUser ? [
                            'id' => $notification->fromUser->id,
                            'name' => $notification->fromUser->name,
                            'avatar' => $notification->fromUser->picture ? asset('storage/' . $notification->fromUser->picture) : null,
                        ] : null,
                        'type' => $notification->type,
                        'message' => $notification->message,
                        'data' => $notification->data,
                        'read_at' => $notification->read_at,
                        'created_at' => $notification->created_at->toISOString(),
                    ];
                });
                
            return response()->json([
                'notifications' => $recent,
                'unread_count' => $user->unreadNotifications()->count(),
            ]);
        }

        $notifications = $user->notifications()
            ->with('fromUser')
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function unreadCount()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['count' => 0]);
        }
        
        return response()->json([
            'count' => $user->unreadNotifications()->count()
        ]);
    }

    public function markAsRead($id)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        try {
            $notification = $user->notifications()->findOrFail($id);
            $notification->markAsRead();

            return response()->json(['success' => true]);
        } catch (ModelNotFoundException $e) {
            // Notification doesn't exist or doesn't belong to user
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to mark as read'], 500);
        }
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        try {
            $user->unreadNotifications()->update(['read_at' => now()]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to mark all as read'], 500);
        }
    }

    /**
     * Delete a single notification
     */
    public function delete($id)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        try {
            $notification = $user->notifications()->findOrFail($id);
            $notification->delete();
            
            return response()->json(['success' => true]);
        } catch (ModelNotFoundException $e) {
            // Notification doesn't exist or doesn't belong to user
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to delete notification: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete notification'], 500);
        }
    }

    /**
     * Clear all notifications for the user
     */
    public function clearAll()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        try {
            $user->notifications()->delete();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Failed to clear notifications: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to clear notifications'], 500);
        }
    }
}