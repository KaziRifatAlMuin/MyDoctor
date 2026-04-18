<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicineSchedule;
use App\Models\MedicineReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MedicineScheduleController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of schedules for a medicine.
     */
    public function index(Request $request)
    {
        $medicineId = $request->get('medicine_id');
        
        if ($medicineId) {
            $medicine = Medicine::where('user_id', Auth::id())->findOrFail($medicineId);
            $schedules = $medicine->schedules()->orderBy('start_date', 'desc')->get();
            
            return view('medicine.schedules', compact('medicine', 'schedules'));
        }
        
        // If no medicine_id, show all schedules for the user
        $schedules = MedicineSchedule::whereHas('medicine', function($q) {
            $q->where('user_id', Auth::id());
        })->with('medicine')->orderBy('start_date', 'desc')->get();
        
        return view('medicine.schedules', compact('schedules'));
    }

    /**
     * Show the form for creating a new schedule.
     */
    public function create(Request $request)
    {
        $medicineId = $request->get('medicine_id');
        $medicine = Medicine::where('user_id', Auth::id())->findOrFail($medicineId);
        
        return view('medicine.schedule-create', compact('medicine'));
    }

    /**
     * Store a newly created schedule in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'dosage_period_days' => 'nullable|integer|min:0',
            'frequency_per_day' => 'nullable|integer|min:1|max:24',
            'interval_hours' => 'nullable|integer|min:1|max:24',
            'dosage_time_binary' => 'nullable|string|size:48',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'sometimes|boolean',
            'times' => 'nullable|array',
        ]);

        // Verify medicine belongs to user
        $medicine = Medicine::where('user_id', Auth::id())->findOrFail($validated['medicine_id']);
        
        // Get selected times from request
        $selectedTimes = $request->input('times', []);
        $expectedCount = isset($validated['frequency_per_day']) ? (int) $validated['frequency_per_day'] : null;
        
        // Validate selected times count matches frequency_per_day
        if ($expectedCount !== null && !empty($selectedTimes)) {
            $selectedCount = count($selectedTimes);
            
            if ($selectedCount != $expectedCount) {
                return back()->withErrors([
                    'frequency_per_day' => "You selected {$selectedCount} time(s) but specified frequency of {$expectedCount} time(s) per day. Please select exactly {$expectedCount} time(s)."
                ])->withInput();
            }
        }
        
        // Also validate via binary string if times array is empty but binary is provided
        if ($expectedCount !== null && empty($selectedTimes) && isset($validated['dosage_time_binary'])) {
            $binaryCount = substr_count($validated['dosage_time_binary'], '1');
            
            if ($binaryCount != $expectedCount) {
                return back()->withErrors([
                    'frequency_per_day' => "The binary time selection has {$binaryCount} time(s) but you specified frequency of {$expectedCount} time(s) per day. Please select exactly {$expectedCount} time(s)."
                ])->withInput();
            }
        }
        
        $validated['is_active'] = $request->has('is_active');
        
        $schedule = MedicineSchedule::create($validated);

        // Generate initial reminders
        $this->generateRemindersForSchedule($schedule);

        return redirect()->route('medicine.schedules', ['medicine_id' => $medicine->id])
            ->with('success', 'Schedule created and reminders generated successfully.');
    }

    /**
     * Display the specified schedule.
     */
    public function show($id)
    {
        $schedule = MedicineSchedule::whereHas('medicine', function($q) {
            $q->where('user_id', Auth::id());
        })->with('medicine', 'reminders')->findOrFail($id);
        
        return view('medicine.schedule-show', compact('schedule'));
    }

    /**
     * Show the form for editing the specified schedule.
     */
    public function edit($id)
    {
        $schedule = MedicineSchedule::whereHas('medicine', function($q) {
            $q->where('user_id', Auth::id());
        })->with('medicine')->findOrFail($id);
        
        return view('medicine.schedule-edit', compact('schedule'));
    }

    /**
     * Update the specified schedule in storage.
     */
    public function update(Request $request, $id)
    {
        $schedule = MedicineSchedule::whereHas('medicine', function($q) {
            $q->where('user_id', Auth::id());
        })->findOrFail($id);

        $validated = $request->validate([
            'dosage_period_days' => 'nullable|integer|min:0',
            'frequency_per_day' => 'nullable|integer|min:1|max:24',
            'interval_hours' => 'nullable|integer|min:1|max:24',
            'dosage_time_binary' => 'nullable|string|size:48',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'sometimes|boolean',
            'times' => 'nullable|array',
        ]);

        // Get selected times from request
        $selectedTimes = $request->input('times', []);
        $expectedCount = isset($validated['frequency_per_day']) ? (int) $validated['frequency_per_day'] : null;
        
        // Validate selected times count matches frequency_per_day
        if ($expectedCount !== null && !empty($selectedTimes)) {
            $selectedCount = count($selectedTimes);
            
            if ($selectedCount != $expectedCount) {
                return back()->withErrors([
                    'frequency_per_day' => "You selected {$selectedCount} time(s) but specified frequency of {$expectedCount} time(s) per day. Please select exactly {$expectedCount} time(s)."
                ])->withInput();
            }
        }
        
        // Also validate via binary string
        if ($expectedCount !== null && empty($selectedTimes) && isset($validated['dosage_time_binary'])) {
            $binaryCount = substr_count($validated['dosage_time_binary'], '1');
            
            if ($binaryCount != $expectedCount) {
                return back()->withErrors([
                    'frequency_per_day' => "The binary time selection has {$binaryCount} time(s) but you specified frequency of {$expectedCount} time(s) per day. Please select exactly {$expectedCount} time(s)."
                ])->withInput();
            }
        }

        $validated['is_active'] = $request->has('is_active');
        
        $schedule->update($validated);

        // Regenerate reminders if dates or times changed
        if ($schedule->wasChanged(['start_date', 'end_date', 'dosage_time_binary', 'dosage_period_days'])) {
            // Delete future reminders
            $schedule->reminders()
                ->where('reminder_at', '>=', now())
                ->delete();
            
            // Generate new reminders
            $this->generateRemindersForSchedule($schedule);
        }

        return redirect()->route('medicine.schedules', ['medicine_id' => $schedule->medicine_id])
            ->with('success', 'Schedule updated successfully.');
    }

    /**
     * Remove the specified schedule from storage.
     */
    public function destroy($id)
    {
        $schedule = MedicineSchedule::whereHas('medicine', function($q) {
            $q->where('user_id', Auth::id());
        })->findOrFail($id);
        
        $medicineId = $schedule->medicine_id;
        $schedule->delete();

        return redirect()->route('medicine.schedules', ['medicine_id' => $medicineId])
            ->with('success', 'Schedule deleted successfully.');
    }

    /**
     * Generate reminders for a schedule.
     */
    public function generateReminders($id)
    {
        $schedule = MedicineSchedule::whereHas('medicine', function($q) {
            $q->where('user_id', Auth::id());
        })->findOrFail($id);
        
        // Delete future reminders
        $schedule->reminders()
            ->where('reminder_at', '>=', now())
            ->delete();
        
        // Generate new reminders
        $count = $this->generateRemindersForSchedule($schedule);
        
        return redirect()->back()->with('success', "{$count} reminders generated successfully.");
    }

    /**
     * Generate reminders for a schedule (internal method).
     */
    private function generateRemindersForSchedule($schedule)
    {
        try {
            $startDate = max(now(), \Carbon\Carbon::parse($schedule->start_date));
            $endDate = $schedule->end_date ? \Carbon\Carbon::parse($schedule->end_date) : now()->addMonths(3);
            $dosageTimes = $schedule->dosageTimesArray;
            
            if (empty($dosageTimes)) {
                return 0;
            }

            $count = 0;
            $currentDate = clone $startDate;
            
            // Ensure dosage_period_days is integer
            $periodDays = (int)($schedule->dosage_period_days ?? 1);
            
            while ($currentDate <= $endDate && $count < 100) { // Limit to 100 reminders
                foreach ($dosageTimes as $time) {
                    try {
                        $reminderDateTime = \Carbon\Carbon::parse($currentDate->toDateString() . ' ' . $time);
                        
                        if ($reminderDateTime >= now()) {
                            MedicineReminder::firstOrCreate([
                                'schedule_id' => $schedule->id,
                                'reminder_at' => $reminderDateTime
                            ], [
                                'status' => 'pending'
                            ]);
                            $count++;
                        }
                    } catch (\Exception $e) {
                        // Skip invalid time format
                        Log::warning('Invalid time format: ' . $time);
                        continue;
                    }
                }
                
                if ($periodDays > 0) {
                    $currentDate->addDays($periodDays);
                } else {
                    break; // As needed - only generate for start date
                }
            }
            
            return $count;
            
        } catch (\Exception $e) {
            Log::error('Error generating reminders: ' . $e->getMessage());
            return 0;
        }
    }
}