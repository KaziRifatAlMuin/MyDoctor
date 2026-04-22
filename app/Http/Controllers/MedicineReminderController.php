<?php

namespace App\Http\Controllers;

use App\Models\MedicineReminder;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MedicineReminderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $userId = Auth::id();
        
        $todayReminders = MedicineReminder::whereHas('schedule.medicine', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->whereDate('reminder_at', now()->toDateString())
            ->with('schedule.medicine')
            ->orderBy('reminder_at')
            ->get();

        $upcomingReminders = MedicineReminder::whereHas('schedule.medicine', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->where('reminder_at', '>', now())
            ->whereDate('reminder_at', '<=', now()->addDays(3)->toDateString())
            ->with('schedule.medicine')
            ->orderBy('reminder_at')
            ->limit(20)
            ->get();

        $missedReminders = MedicineReminder::whereHas('schedule.medicine', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->where('status', 'pending')
            ->where('reminder_at', '<', now())
            ->with('schedule.medicine')
            ->orderBy('reminder_at')
            ->get();

        return view('medicine.reminders', compact('todayReminders', 'upcomingReminders', 'missedReminders'));
    }

    /**
     * Mark reminder as taken
     */
    public function markTaken($id)
    {
        try {
            $reminder = MedicineReminder::whereHas('schedule.medicine', function($q) {
                $q->where('user_id', Auth::id());
            })->findOrFail($id);

            $reminder->markAsTaken();

            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Medicine marked as taken']);
            }

            return redirect()->back()->with('success', 'Medicine marked as taken.');
        } catch (\Exception $e) {
            Log::error('Error marking reminder as taken: ' . $e->getMessage());
            
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to mark as taken'], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to mark as taken');
        }
    }

    /**
     * Mark reminder as missed
     */
    public function markMissed($id)
    {
        try {
            $reminder = MedicineReminder::whereHas('schedule.medicine', function($q) {
                $q->where('user_id', Auth::id());
            })->findOrFail($id);

            $reminder->markAsMissed();

            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Medicine marked as missed']);
            }

            return redirect()->back()->with('success', 'Medicine marked as missed.');
        } catch (\Exception $e) {
            Log::error('Error marking reminder as missed: ' . $e->getMessage());
            
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to mark as missed'], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to mark as missed');
        }
    }

    /**
     * Mark reminder as taken from notification action
     */
    public function markTakenFromNotification($id)
    {
        try {
            $reminder = MedicineReminder::findOrFail($id);
            
            // Verify ownership
            if ($reminder->schedule->medicine->user_id !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $reminder->markAsTaken();

            return response()->json(['success' => true, 'message' => 'Medicine marked as taken']);
        } catch (\Exception $e) {
            Log::error('Error marking reminder as taken from notification: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to mark as taken'], 500);
        }
    }

    /**
     * Mark reminder as missed from notification action
     */
    public function markMissedFromNotification($id)
    {
        try {
            $reminder = MedicineReminder::findOrFail($id);
            
            // Verify ownership
            if ($reminder->schedule->medicine->user_id !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $reminder->markAsMissed();

            return response()->json(['success' => true, 'message' => 'Medicine marked as missed']);
        } catch (\Exception $e) {
            Log::error('Error marking reminder as missed from notification: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to mark as missed'], 500);
        }
    }

    public function markMultipleTaken(Request $request)
    {
        $ids = $request->ids;
        
        if (!is_array($ids)) {
            return response()->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $count = 0;
        foreach ($ids as $id) {
            try {
                $reminder = MedicineReminder::whereHas('schedule.medicine', function($q) {
                    $q->where('user_id', Auth::id());
                })->find($id);
                
                if ($reminder && $reminder->status === 'pending') {
                    $reminder->markAsTaken();
                    $count++;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to mark reminder as taken in bulk: ' . $e->getMessage());
            }
        }

        return response()->json(['success' => true, 'count' => $count]);
    }

    /**
     * Get medicine reminder notifications for the user
     */
    public function notifications(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                if ($request->wantsJson()) {
                    return response()->json(['notifications' => [], 'unread_count' => 0]);
                }
                return redirect()->route('login');
            }

            $limit = $request->get('limit', 20);
            
            // Get medicine reminder notifications from the notifications table
            $notifications = Notification::where('user_id', $user->id)
                ->where('type', 'medicine_reminder')
                ->latest()
                ->limit($limit)
                ->get()
                ->map(function($notification) {
                    $data = $notification->data;
                    return [
                        'id' => $notification->id,
                        'type' => 'medicine_reminder',
                        'reminder_id' => $data['reminder_id'] ?? null,
                        'medicine_id' => $data['medicine_id'] ?? null,
                        'medicine_name' => $data['medicine_name'] ?? 'Medicine',
                        'dosage' => $data['dosage'] ?? null,
                        'scheduled_time' => $data['scheduled_time'] ?? '',
                        'message' => $data['message'] ?? 'Time to take your medicine',
                        'read_at' => $notification->read_at,
                        'created_at' => $notification->created_at->toISOString(),
                        'action_url' => $data['action_url'] ?? route('medicine.reminders'),
                        'taken_url' => $data['taken_url'] ?? null,
                    ];
                });

            $unreadCount = Notification::where('user_id', $user->id)
                ->where('type', 'medicine_reminder')
                ->whereNull('read_at')
                ->count();

            if ($request->wantsJson()) {
                return response()->json([
                    'notifications' => $notifications,
                    'unread_count' => $unreadCount,
                    'total' => $notifications->count(),
                ]);
            }
            
            return view('medicine.reminder-notifications', compact('notifications'));
        } catch (\Exception $e) {
            Log::error('Error fetching medicine reminders: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json(['notifications' => [], 'unread_count' => 0]);
            }
            
            return view('medicine.reminder-notifications', ['notifications' => collect()]);
        }
    }

    /**
     * Get unread count for medicine reminders
     */
    public function unreadCount(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['count' => 0]);
            }
            
            $count = Notification::where('user_id', $user->id)
                ->where('type', 'medicine_reminder')
                ->whereNull('read_at')
                ->count();
            
            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            Log::error('Error getting unread count: ' . $e->getMessage());
            return response()->json(['count' => 0]);
        }
    }

    /**
     * Mark a reminder notification as read
     */
    public function markNotificationRead($id)
    {
        try {
            $user = Auth::user();
            $notification = Notification::where('user_id', $user->id)
                ->where('type', 'medicine_reminder')
                ->findOrFail($id);
            
            $notification->update(['read_at' => now()]);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to mark as read'], 500);
        }
    }

    /**
     * Mark all reminder notifications as read
     */
    public function markAllRead()
    {
        try {
            $user = Auth::user();
            Notification::where('user_id', $user->id)
                ->where('type', 'medicine_reminder')
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error marking all as read: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to mark all as read'], 500);
        }
    }

    /**
     * Delete a reminder notification
     */
    public function deleteNotification($id)
    {
        try {
            $user = Auth::user();
            $notification = Notification::where('user_id', $user->id)
                ->where('type', 'medicine_reminder')
                ->findOrFail($id);
            
            $notification->delete();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error deleting notification: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete notification'], 500);
        }
    }

    /**
     * Show medicine reminder modal
     */
    public function modal($id)
    {
        try {
            $reminder = MedicineReminder::whereHas('schedule.medicine', function($q) {
                $q->where('user_id', Auth::id());
            })->with(['schedule.medicine'])->findOrFail($id);
            
            return view('medicine.partials.reminder-modal', compact('reminder'));
        } catch (\Exception $e) {
            Log::error('Medicine Reminder Modal Error: ' . $e->getMessage());
            abort(404, 'Reminder not found');
        }
    }
}