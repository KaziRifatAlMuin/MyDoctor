<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\HealthMetric;
use App\Models\Symptom;
use App\Models\Medicine;
use App\Models\MedicineLog;
use App\Models\UserDisease;
use App\Models\Upload;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Health metrics summary
        $healthMetrics = HealthMetric::where('user_id', $user->id)
            ->orderByDesc('recorded_at')
            ->limit(50)
            ->get();

        $metricsByType  = $healthMetrics->groupBy('metric_type');
        $latestMetrics  = $metricsByType->map(fn($group) => $group->first());
        $metricConfig   = config('health.metric_types');

        // Recent symptoms
        $symptoms = Symptom::where('user_id', $user->id)
            ->orderByDesc('recorded_at')
            ->limit(10)
            ->get();

        // Medicines
        $medicines = Medicine::where('user_id', $user->id)
            ->with(['schedules' => fn($q) => $q->orderByDesc('start_date')])
            ->get();

        // Medicine logs — last 30 days
        $medicineLogs = MedicineLog::where('user_id', $user->id)
            ->where('date', '>=', now()->subDays(30))
            ->with('medicine')
            ->orderByDesc('date')
            ->get();

        $totalScheduled = $medicineLogs->sum('total_scheduled');
        $totalTaken     = $medicineLogs->sum('total_taken');
        $totalMissed    = $medicineLogs->sum('total_missed');
        $adherenceRate  = $totalScheduled > 0 ? round(($totalTaken / $totalScheduled) * 100) : 0;

        // Active conditions
        $activeConditions = UserDisease::where('user_id', $user->id)
            ->whereIn('status', ['active', 'chronic', 'managed'])
            ->with('disease')
            ->orderByDesc('created_at')
            ->get();

        // Uploads count
        $prescriptionCount = Upload::where('user_id', $user->id)->where('type', 'prescription')->count();
        $reportCount       = Upload::where('user_id', $user->id)->where('type', 'report')->count();

        // Recent activity (last 7 days)
        $recentMetricsCount  = HealthMetric::where('user_id', $user->id)
            ->where('recorded_at', '>=', now()->subDays(7))->count();
        $recentSymptomsCount = Symptom::where('user_id', $user->id)
            ->where('recorded_at', '>=', now()->subDays(7))->count();

        return view('dashboard', compact(
            'user',
            'healthMetrics',
            'metricsByType',
            'latestMetrics',
            'metricConfig',
            'symptoms',
            'medicines',
            'medicineLogs',
            'totalScheduled',
            'totalTaken',
            'totalMissed',
            'adherenceRate',
            'activeConditions',
            'prescriptionCount',
            'reportCount',
            'recentMetricsCount',
            'recentSymptomsCount'
        ));
    }
}
