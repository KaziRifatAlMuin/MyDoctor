<?php

use Illuminate\Support\Facades\Route;
use App\Models\MedicineReminder;

Route::middleware('auth:sanctum')->get('/pending-reminders', function () {
    $reminders = MedicineReminder::with('schedule.medicine')
        ->whereHas('schedule.medicine', function($q) {
            $q->where('user_id', auth()->id());
        })
        ->where('status', 'pending')
        ->whereBetween('reminder_at', [now(), now()->addMinutes(5)])
        ->get()
        ->map(function($reminder) {
            return [
                'id' => $reminder->id,
                'medicine_name' => $reminder->schedule->medicine->medicine_name,
                'dosage' => $reminder->schedule->medicine->value_per_dose . ' ' . $reminder->schedule->medicine->unit,
                'time' => $reminder->reminder_at->format('h:i A'),
            ];
        });
    
    return response()->json(['reminders' => $reminders]);
});