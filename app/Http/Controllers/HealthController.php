<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\HealthMetric;
use App\Models\Symptom;
use App\Models\Medicine;
use App\Models\MedicineLog;

class HealthController extends Controller
{
    /**
     * Display the main health dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // Health Metrics — latest 20, grouped by type
        $healthMetrics = HealthMetric::where('user_id', $user->id)
            ->orderByDesc('recorded_at')
            ->limit(50)
            ->get();

        $metricsByType = $healthMetrics->groupBy('metric_type');

        // Latest value per metric type for summary cards
        $latestMetrics = $healthMetrics->groupBy('metric_type')->map(function ($group) {
            return $group->first();
        });

        // Symptoms — latest 20
        $symptoms = Symptom::where('user_id', $user->id)
            ->orderByDesc('recorded_at')
            ->limit(20)
            ->get();

        // Medicines with schedules
        $medicines = Medicine::where('user_id', $user->id)
            ->with(['schedules' => function ($q) {
                $q->orderByDesc('start_date');
            }])
            ->get();

        // Medicine logs — last 30 days
        $medicineLogs = MedicineLog::where('user_id', $user->id)
            ->where('date', '>=', now()->subDays(30))
            ->with('medicine')
            ->orderByDesc('date')
            ->get();

        // Adherence stats
        $totalScheduled = $medicineLogs->sum('total_scheduled');
        $totalTaken     = $medicineLogs->sum('total_taken');
        $totalMissed    = $medicineLogs->sum('total_missed');
        $adherenceRate  = $totalScheduled > 0 ? round(($totalTaken / $totalScheduled) * 100) : 0;

        // Symptom severity distribution
        $severityDistribution = $symptoms->groupBy('severity_level')->map->count();

        return view('health.index', compact(
            'user',
            'healthMetrics',
            'metricsByType',
            'latestMetrics',
            'symptoms',
            'medicines',
            'medicineLogs',
            'totalScheduled',
            'totalTaken',
            'totalMissed',
            'adherenceRate',
            'severityDistribution'
        ));
    }
}
