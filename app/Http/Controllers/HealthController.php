<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\HealthMetric;
use App\Models\Symptom;
use App\Models\Medicine;
use App\Models\MedicineLog;
use App\Models\Disease;
use App\Models\UserDisease;
use App\Models\Upload;

class HealthController extends Controller
{
    /**
     * Display the main health dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // Health Metrics — latest 50, grouped by type
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

        // User diseases with disease info
        $userDiseases = UserDisease::where('user_id', $user->id)
            ->with('disease')
            ->orderByDesc('created_at')
            ->get();

        // All diseases for dropdown
        $allDiseases = Disease::orderBy('disease_name')->get();

        // Uploads - prescriptions and reports
        $uploads = Upload::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $prescriptionUploads = $uploads->where('type', 'prescription');
        $reportUploads = $uploads->where('type', 'report');

        // Metric type definitions for smart form
        $metricTypes = [
            'blood_pressure'    => ['fields' => ['systolic' => 'number', 'diastolic' => 'number'], 'unit' => 'mmHg'],
            'blood_glucose'     => ['fields' => ['value' => 'number'], 'unit' => 'mg/dL'],
            'heart_rate'        => ['fields' => ['bpm' => 'number'], 'unit' => 'bpm'],
            'body_weight'       => ['fields' => ['value' => 'number'], 'unit' => 'kg'],
            'bmi'               => ['fields' => ['value' => 'number'], 'unit' => 'kg/m²'],
            'oxygen_saturation' => ['fields' => ['value' => 'number'], 'unit' => '%'],
            'temperature'       => ['fields' => ['value' => 'number'], 'unit' => '°C'],
        ];

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
            'severityDistribution',
            'userDiseases',
            'allDiseases',
            'uploads',
            'prescriptionUploads',
            'reportUploads',
            'metricTypes'
        ));
    }

    /**
     * Store a new health metric.
     */
    public function storeMetric(Request $request)
    {
        $request->validate([
            'metric_type' => 'required|string|in:blood_pressure,blood_glucose,heart_rate,body_weight,bmi,oxygen_saturation,temperature',
            'recorded_at'  => 'required|date',
        ]);

        $metricType = $request->metric_type;

        // Build value JSON from dynamic fields
        $valueFields = [
            'blood_pressure'    => ['systolic', 'diastolic'],
            'blood_glucose'     => ['value'],
            'heart_rate'        => ['bpm'],
            'body_weight'       => ['value'],
            'bmi'               => ['value'],
            'oxygen_saturation' => ['value'],
            'temperature'       => ['value'],
        ];

        $units = [
            'blood_pressure' => 'mmHg', 'blood_glucose' => 'mg/dL', 'heart_rate' => 'bpm',
            'body_weight' => 'kg', 'bmi' => 'kg/m²', 'oxygen_saturation' => '%', 'temperature' => '°C',
        ];

        $value = [];
        foreach ($valueFields[$metricType] as $field) {
            $value[$field] = $request->input("value_$field", 0);
        }
        $value['unit'] = $units[$metricType];

        HealthMetric::create([
            'user_id'     => Auth::id(),
            'metric_type' => $metricType,
            'recorded_at' => $request->recorded_at,
            'value'       => $value,
        ]);

        return redirect()->route('health')->with('success', 'Health metric recorded successfully.');
    }

    /**
     * Store a new symptom.
     */
    public function storeSymptom(Request $request)
    {
        $request->validate([
            'symptom_name'   => 'required|string|max:255',
            'severity_level' => 'required|integer|min:1|max:10',
            'recorded_at'    => 'required|date',
            'note'           => 'nullable|string|max:1000',
        ]);

        Symptom::create([
            'user_id'        => Auth::id(),
            'symptom_name'   => $request->symptom_name,
            'severity_level' => $request->severity_level,
            'recorded_at'    => $request->recorded_at,
            'note'           => $request->note,
        ]);

        return redirect()->route('health')->with('success', 'Symptom recorded successfully.');
    }

    /**
     * Store a user disease association.
     */
    public function storeDisease(Request $request)
    {
        $request->validate([
            'disease_id'    => 'required|exists:diseases,id',
            'diagnosed_at'  => 'nullable|date',
            'status'        => 'required|in:active,recovered,chronic,managed',
            'notes'         => 'nullable|string|max:1000',
        ]);

        // Prevent duplicate
        $exists = UserDisease::where('user_id', Auth::id())
            ->where('disease_id', $request->disease_id)
            ->exists();

        if ($exists) {
            return redirect()->route('health')->with('error', 'This disease is already in your records.');
        }

        UserDisease::create([
            'user_id'      => Auth::id(),
            'disease_id'   => $request->disease_id,
            'diagnosed_at' => $request->diagnosed_at,
            'status'       => $request->status,
            'notes'        => $request->notes,
        ]);

        return redirect()->route('health')->with('success', 'Disease record added successfully.');
    }

    /**
     * Store an upload (prescription or report).
     */
    public function storeUpload(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'type'          => 'required|in:prescription,report',
            'file'          => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'summary'       => 'nullable|string|max:2000',
            'notes'         => 'nullable|string|max:1000',
            'doctor_name'   => 'nullable|string|max:255',
            'institution'   => 'nullable|string|max:255',
            'document_date' => 'nullable|date',
        ]);

        $path = $request->file('file')->store('uploads', 'public');

        Upload::create([
            'user_id'       => Auth::id(),
            'title'         => $request->title,
            'type'          => $request->type,
            'file_path'     => $path,
            'summary'       => $request->summary,
            'notes'         => $request->notes,
            'doctor_name'   => $request->doctor_name,
            'institution'   => $request->institution,
            'document_date' => $request->document_date,
        ]);

        return redirect()->route('health')->with('success', ucfirst($request->type) . ' uploaded successfully.');
    }

    /**
     * Delete an upload.
     */
    public function destroyUpload(Upload $upload)
    {
        if ($upload->user_id !== Auth::id()) {
            abort(403);
        }

        if (Storage::disk('public')->exists($upload->file_path)) {
            Storage::disk('public')->delete($upload->file_path);
        }

        $upload->delete();

        return redirect()->route('health')->with('success', 'Upload deleted successfully.');
    }

    /**
     * Delete a user disease record.
     */
    public function destroyDisease(UserDisease $userDisease)
    {
        if ($userDisease->user_id !== Auth::id()) {
            abort(403);
        }

        $userDisease->delete();

        return redirect()->route('health')->with('success', 'Disease record removed.');
    }
}
