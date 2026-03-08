<?php

namespace App\Http\Controllers;

use App\Models\MedicineReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function markTaken($id)
    {
        $reminder = MedicineReminder::whereHas('schedule.medicine', function($q) {
            $q->where('user_id', Auth::id());
        })->findOrFail($id);

        $reminder->markAsTaken();

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Medicine marked as taken']);
        }

        return redirect()->back()->with('success', 'Medicine marked as taken.');
    }

    public function markMissed($id)
    {
        $reminder = MedicineReminder::whereHas('schedule.medicine', function($q) {
            $q->where('user_id', Auth::id());
        })->findOrFail($id);

        $reminder->markAsMissed();

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Medicine marked as missed']);
        }

        return redirect()->back()->with('success', 'Medicine marked as missed.');
    }

    /**
     * Mark reminder as taken from notification action
     */
    public function markTakenFromNotification($id)
    {
        $reminder = MedicineReminder::findOrFail($id);

        $reminder->markAsTaken();

        return response()->json(['success' => true, 'message' => 'Medicine marked as taken']);
    }

    /**
     * Snooze reminder from notification action
     */
    public function snooze(Request $request, $id)
    {
        $reminder = MedicineReminder::findOrFail($id);

        $minutes = $request->get('minutes', 5);
        
        $newReminder = MedicineReminder::create([
            'schedule_id' => $reminder->schedule_id,
            'reminder_at' => now()->addMinutes($minutes),
            'status' => 'pending'
        ]);

        $reminder->update(['status' => 'snoozed']);

        return response()->json(['success' => true, 'new_reminder_id' => $newReminder->id]);
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
                // Log error but continue
            }
        }

        return response()->json(['success' => true, 'count' => $count]);
    }
}