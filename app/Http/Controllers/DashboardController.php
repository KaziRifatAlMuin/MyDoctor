<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\HealthMetric;
use App\Models\UserHealth;
use App\Models\MedicineReminder;
use App\Models\UserSymptom;
use App\Models\Medicine;
use App\Models\MedicineLog;
use App\Models\UserDisease;
use App\Models\Upload;
use App\Services\LiveEnvironmentService;

class DashboardController extends Controller
{
    public function index(LiveEnvironmentService $liveEnvironmentService)
    {
        $user = Auth::user();

        if ($user?->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $metricDefinitions = $this->ensureMetricDefinitions();
        $metricConfig = $this->buildMetricConfig($metricDefinitions);
        $liveEnvironment = $liveEnvironmentService->forUser($user);

        // Health metrics summary
        $healthMetrics = UserHealth::with('healthMetric')
            ->where('user_id', $user->id)
            ->orderByDesc('recorded_at')
            ->limit(50)
            ->get()
            ->filter(fn(UserHealth $record) => $record->healthMetric !== null)
            ->values();

        $metricsByType  = $healthMetrics->groupBy(fn(UserHealth $record) => $record->metric_type ?? 'unknown');
        $latestMetrics  = $metricsByType->map(fn($group) => $group->first());

        // Recent symptoms
        $symptoms = UserSymptom::where('user_id', $user->id)
            ->with('symptom')
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
        $recentMetricsCount  = UserHealth::where('user_id', $user->id)
            ->where('recorded_at', '>=', now()->subDays(7))->count();
        $recentSymptomsCount = UserSymptom::where('user_id', $user->id)
            ->where('recorded_at', '>=', now()->subDays(7))->count();

        // ── 30-day adherence breakdown for sparkline ──
        $adherenceByDay = [];
        $logsByDate = $medicineLogs->groupBy(fn($log) => $log->date->format('Y-m-d'));
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayLogs = $logsByDate[$date] ?? collect();
            $dayScheduled = $dayLogs->sum('total_scheduled');
            $adherenceByDay[] = [
                'date'  => now()->subDays($i)->format('M d'),
                'rate'  => $dayScheduled > 0 ? round(($dayLogs->sum('total_taken') / $dayScheduled) * 100) : null,
            ];
        }

        // ── Today's pending reminders ──
        $todayReminders = MedicineReminder::whereHas('schedule.medicine', fn($q) => $q->where('user_id', $user->id))
            ->where('status', 'pending')
            ->whereDate('reminder_at', today())
            ->with('schedule.medicine')
            ->orderBy('reminder_at')
            ->limit(8)
            ->get();

        // ── Recent uploads ──
        $recentUploads = Upload::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // ── Metric trends (last 7 readings per type) ──
        $metricTrends = [];
        foreach ($metricsByType->take(4) as $type => $metrics) {
            $recent = $metrics->sortBy('recorded_at')->take(7);
            $metricTrends[$type] = $recent->map(function ($m) {
                $val = is_array($m->value)
                    ? (float) collect($m->value)->reject(fn($v, $k) => $k === 'unit')->first()
                    : (float) $m->value;
                return ['date' => $m->recorded_at->format('M d'), 'value' => $val];
            })->values()->toArray();
        }

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
            'recentSymptomsCount',
            'adherenceByDay',
            'todayReminders',
            'recentUploads',
            'metricTrends',
            'liveEnvironment'
        ));
    }

    private function ensureMetricDefinitions()
    {
        HealthMetric::seedDefaults();
        return HealthMetric::query()->orderBy('metric_name')->get();
    }

    private function buildMetricConfig($definitions): array
    {
        $config = [];
        foreach ($definitions as $definition) {
            $metricName = (string) $definition->metric_name;
            $fields = array_values((array) $definition->fields);
            $config[$metricName] = [
                'en' => ucwords(str_replace('_', ' ', $metricName)),
                'bn' => '',
                'unit' => '',
                'fields' => $fields,
            ];
        }

        return $config;
    }
}