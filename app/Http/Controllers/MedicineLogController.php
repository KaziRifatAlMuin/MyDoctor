<?php

namespace App\Http\Controllers;

use App\Models\MedicineLog;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicineLogController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of logs.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $medicineId = $request->get('medicine_id');
        $days = $request->get('days', 30);
        
        $query = MedicineLog::where('user_id', $userId)
            ->with('medicine')
            ->orderBy('date', 'desc');
        
        if ($medicineId) {
            $query->where('medicine_id', $medicineId);
        }
        
        if ($days) {
            $query->where('date', '>=', now()->subDays($days)->toDateString());
        }
        
        $logs = $query->paginate(20);
        
        // Calculate statistics
        $totalScheduled = $logs->sum('total_scheduled');
        $totalTaken = $logs->sum('total_taken');
        $totalMissed = $logs->sum('total_missed');
        $overallAdherence = $totalScheduled > 0 ? round(($totalTaken / $totalScheduled) * 100, 2) : 0;
        
        // Get medicines for filter dropdown
        $medicines = Medicine::where('user_id', $userId)->orderBy('medicine_name')->get();
        
        return view('medicine.logs', compact('logs', 'medicines', 'medicineId', 'days', 
            'totalScheduled', 'totalTaken', 'totalMissed', 'overallAdherence'));
    }

    /**
     * Export logs as CSV.
     */
    public function export(Request $request)
    {
        $userId = Auth::id();
        $medicineId = $request->get('medicine_id');
        $startDate = $request->get('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());
        
        $query = MedicineLog::where('user_id', $userId)
            ->with('medicine')
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date');
        
        if ($medicineId) {
            $query->where('medicine_id', $medicineId);
        }
        
        $logs = $query->get();
        
        $filename = "medicine-logs-{$startDate}-to-{$endDate}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, ['Medicine', 'Date', 'Scheduled', 'Taken', 'Missed', 'Adherence %']);
            
            // Data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->medicine->medicine_name,
                    $log->date->format('Y-m-d'),
                    $log->total_scheduled,
                    $log->total_taken,
                    $log->total_missed,
                    $log->adherenceRate . '%'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}