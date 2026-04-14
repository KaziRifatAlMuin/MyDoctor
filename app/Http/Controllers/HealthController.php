<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\HealthMetric;
use App\Models\UserHealth;
use App\Models\Symptom;
use App\Models\UserSymptom;
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

        $symptomsList  = config('health.symptoms', []);
        $metricDefinitions = $this->ensureMetricDefinitions();
        $metricConfig  = $this->buildMetricConfig($metricDefinitions);
        $diseasesBn    = [];

        // Health Metrics — latest 50, grouped by type
        $healthMetrics = UserHealth::with('healthMetric')
            ->where('user_id', $user->id)
            ->orderByDesc('recorded_at')
            ->limit(50)
            ->get()
            ->filter(fn(UserHealth $record) => $record->healthMetric !== null)
            ->values();

        $metricsByType = $healthMetrics->groupBy(fn(UserHealth $record) => $record->metric_type ?? 'unknown');

        // Latest value per metric type for summary cards
        $latestMetrics = $healthMetrics->groupBy('metric_type')->map(fn($group) => $group->first());

        // Symptoms — latest 30
        $symptoms = UserSymptom::where('user_id', $user->id)
            ->with('symptom')
            ->orderByDesc('recorded_at')
            ->limit(30)
            ->get();

        // Medicines with schedules
        $medicines = Medicine::where('user_id', $user->id)
            ->with(['schedules' => fn($q) => $q->orderByDesc('start_date')])
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
        $reportUploads       = $uploads->where('type', 'report');

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
            'symptomsList',
            'metricConfig',
            'diseasesBn'
        ));
    }

    /* ====================================================================
     *  STORE methods
     * ==================================================================== */

    public function storeMetric(Request $request)
    {
        $user = Auth::user();
        if (!$user) abort(401);

        // Use user_id from request if provided (admin context), otherwise use Auth::id()
        $userId = $request->input('user_id') ? (int)$request->input('user_id') : Auth::id();

        // Authorization: user can only add for themselves, admin can add for anyone
        if ($userId !== $user->id && $user->role !== 'admin') {
            abort(403);
        }

        $definitions = $this->ensureMetricDefinitions();
        $validTypes = implode(',', $definitions->pluck('metric_name')->all());

        $request->validate([
            'metric_type' => "required|string|in:$validTypes",
            'recorded_at' => 'required|date',
        ]);

        $metricType = (string) $request->metric_type;
        $definition = $definitions->firstWhere('metric_name', $metricType);

        if (!$definition) {
            return back()->with('error', 'Selected metric definition no longer exists.');
        }

        $value = $this->extractMetricValuesFromRequest($request, $metricType, (array) $definition->fields);

        UserHealth::create([
            'user_id'     => $userId,
            'health_metric_id' => $definition->id,
            'recorded_at' => $request->recorded_at,
            'value'       => $value,
        ]);

        // Redirect with fragment - to user show if admin is viewing a user, otherwise to health dashboard
        if ($request->input('user_id')) {
            // Admin is adding for a user
            return redirect(route('admin.users.show', $userId) . '#metrics')->with('success', 'Health metric recorded successfully.');
        }
        return redirect(route('health') . '#metrics')->with('success', 'Health metric recorded successfully.');
    }

    public function storeSymptom(Request $request)
    {
        $user = Auth::user();
        if (!$user) abort(401);

        // Use user_id from request if provided (admin context), otherwise use Auth::id()
        $userId = $request->input('user_id') ? (int)$request->input('user_id') : Auth::id();

        // Authorization: user can only add for themselves, admin can add for anyone
        if ($userId !== $user->id && $user->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'symptom_name'   => 'required|string|max:255',
            'severity_level' => 'required|integer|min:1|max:10',
            'recorded_at'    => 'required|date',
            'note'           => 'nullable|string|max:1000',
        ]);

        $symptom = Symptom::firstOrCreate([
            'name' => trim((string) $request->symptom_name),
        ]);

        UserSymptom::create([
            'user_id'        => $userId,
            'symptom_id'     => $symptom->id,
            'severity_level' => $request->severity_level,
            'recorded_at'    => $request->recorded_at,
            'note'           => $request->note,
        ]);

        $this->syncSymptomDiseaseLinks($userId, $symptom);

        // Redirect with fragment - to user show if admin is viewing a user, otherwise to health dashboard
        if ($request->input('user_id')) {
            // Admin is adding for a user
            return redirect(route('admin.users.show', $userId) . '#symptomsPane')->with('success', 'Symptom recorded successfully.');
        }
        return redirect(route('health') . '#symptomsPane')->with('success', 'Symptom recorded successfully.');
    }

    public function storeDisease(Request $request)
    {
        $user = Auth::user();
        if (!$user) abort(401);

        // Use user_id from request if provided (admin context), otherwise use Auth::id()
        $userId = $request->input('user_id') ? (int)$request->input('user_id') : Auth::id();

        // Authorization: user can only add for themselves, admin can add for anyone
        if ($userId !== $user->id && $user->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'disease_id'   => 'required|exists:diseases,id',
            'diagnosed_at' => 'nullable|date',
            'status'       => 'required|in:active,recovered,chronic,managed',
            'notes'        => 'nullable|string|max:1000',
        ]);

        if (UserDisease::where('user_id', $userId)->where('disease_id', $request->disease_id)->exists()) {
            return back()->with('error', 'This disease is already in the user\'s records.');
        }

        UserDisease::create([
            'user_id'      => $userId,
            'disease_id'   => $request->disease_id,
            'diagnosed_at' => $request->diagnosed_at,
            'status'       => $request->status,
            'notes'        => $request->notes,
        ]);

        // Redirect with fragment - to user show if admin is viewing a user, otherwise to health dashboard
        if ($request->input('user_id')) {
            // Admin is adding for a user
            return redirect(route('admin.users.show', $userId) . '#diseasesPane')->with('success', 'Disease record added successfully.');
        }
        return redirect(route('health') . '#diseasesPane')->with('success', 'Disease record added successfully.');
    }

    public function storeUpload(Request $request)
    {
        $user = Auth::user();
        if (!$user) abort(401);

        // Use user_id from request if provided (admin context), otherwise use Auth::id()
        $userId = $request->input('user_id') ? (int)$request->input('user_id') : Auth::id();

        // Authorization: user can only add for themselves, admin can add for anyone
        if ($userId !== $user->id && $user->role !== 'admin') {
            abort(403);
        }

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
            'user_id'       => $userId,
            'title'         => $request->title,
            'type'          => $request->type,
            'file_path'     => $path,
            'summary'       => $request->summary,
            'notes'         => $request->notes,
            'doctor_name'   => $request->doctor_name,
            'institution'   => $request->institution,
            'document_date' => $request->document_date,
        ]);

        // Redirect with fragment - to user show if admin is viewing a user, otherwise to health dashboard
        if ($request->input('user_id')) {
            // Admin is adding for a user
            $fragment = $request->type === 'prescription' ? '#prescriptions' : '#reportsPane';
            return redirect(route('admin.users.show', $userId) . $fragment)->with('success', ucfirst($request->type) . ' uploaded successfully.');
        }
        $fragment = $request->type === 'prescription' ? '#prescriptions' : '#reportsPane';
        return redirect(route('health') . $fragment)->with('success', ucfirst($request->type) . ' uploaded successfully.');
    }

    /* ====================================================================
     *  UPDATE methods
     * ==================================================================== */

    public function updateMetric(Request $request, UserHealth $healthMetric)
    {
        $user = Auth::user();
        if (!$user || ($healthMetric->user_id !== $user->id && $user->role !== 'admin')) abort(403);

        $definitions = $this->ensureMetricDefinitions();
        $validTypes = implode(',', $definitions->pluck('metric_name')->all());

        $request->validate([
            'metric_type' => "required|string|in:$validTypes",
            'recorded_at' => 'required|date',
        ]);

        $metricType = (string) $request->metric_type;
        $definition = $definitions->firstWhere('metric_name', $metricType);

        if (!$definition) {
            return back()->with('error', 'Selected metric definition no longer exists.');
        }

        $value = $this->extractMetricValuesFromRequest($request, $metricType, (array) $definition->fields);

        $healthMetric->update([
            'health_metric_id' => $definition->id,
            'recorded_at' => $request->recorded_at,
            'value'       => $value,
        ]);

        $referer = $request->header('referer');
        if ($referer && str_contains($referer, '/user/')) {
            $userId = $request->input('user_id') ? (int)$request->input('user_id') : $healthMetric->user_id;
            return redirect(route('admin.users.show', $userId) . '#metrics')->with('success', 'Health metric updated successfully.');
        }

        return redirect(route('health') . '#metrics')->with('success', 'Health metric updated successfully.');
    }

    public function updateSymptom(Request $request, UserSymptom $symptom)
    {
        $user = Auth::user();
        if (!$user || ($symptom->user_id !== $user->id && $user->role !== 'admin')) abort(403);

        $request->validate([
            'symptom_name'   => 'required|string|max:255',
            'severity_level' => 'required|integer|min:1|max:10',
            'recorded_at'    => 'required|date',
            'note'           => 'nullable|string|max:1000',
        ]);

        $catalogSymptom = Symptom::firstOrCreate([
            'name' => trim((string) $request->symptom_name),
        ]);

        $symptom->update([
            'symptom_id' => $catalogSymptom->id,
            'severity_level' => $request->severity_level,
            'recorded_at' => $request->recorded_at,
            'note' => $request->note,
        ]);

        $this->syncSymptomDiseaseLinks($symptom->user_id, $catalogSymptom);

        $referer = $request->header('referer');
        if ($referer && str_contains($referer, '/user/')) {
            $userId = $request->input('user_id') ? (int)$request->input('user_id') : $symptom->user_id;
            return redirect(route('admin.users.show', $userId) . '#symptomsPane')->with('success', 'Symptom updated successfully.');
        }

        return redirect(route('health') . '#symptomsPane')->with('success', 'Symptom updated successfully.');
    }

    public function updateDisease(Request $request, UserDisease $userDisease)
    {
        $user = Auth::user();
        if (!$user || ($userDisease->user_id !== $user->id && $user->role !== 'admin')) abort(403);

        $request->validate([
            'status'       => 'required|in:active,recovered,chronic,managed',
            'diagnosed_at' => 'nullable|date',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $userDisease->update($request->only('status', 'diagnosed_at', 'notes'));

        $referer = $request->header('referer');
        if ($referer && str_contains($referer, '/user/')) {
            $userId = $request->input('user_id') ? (int)$request->input('user_id') : $userDisease->user_id;
            return redirect(route('admin.users.show', $userId) . '#diseasesPane')->with('success', 'Disease record updated successfully.');
        }

        return redirect(route('health') . '#diseasesPane')->with('success', 'Disease record updated successfully.');
    }

    public function updateUpload(Request $request, Upload $upload)
    {
        $user = Auth::user();
        if (!$user || ($upload->user_id !== $user->id && $user->role !== 'admin')) abort(403);

        $request->validate([
            'title'         => 'required|string|max:255',
            'type'          => 'required|in:prescription,report',
            'file'          => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'summary'       => 'nullable|string|max:2000',
            'notes'         => 'nullable|string|max:1000',
            'doctor_name'   => 'nullable|string|max:255',
            'institution'   => 'nullable|string|max:255',
            'document_date' => 'nullable|date',
        ]);

        $data = $request->only('title', 'type', 'summary', 'notes', 'doctor_name', 'institution', 'document_date');

        if ($request->hasFile('file')) {
            if ($upload->file_path && Storage::disk('public')->exists($upload->file_path)) {
                Storage::disk('public')->delete($upload->file_path);
            }
            $data['file_path'] = $request->file('file')->store('uploads', 'public');
        }

        $upload->update($data);

        $referer = $request->header('referer');
        if ($referer && str_contains($referer, '/user/')) {
            $userId = $request->input('user_id') ? (int)$request->input('user_id') : $upload->user_id;
            $fragment = $upload->type === 'prescription' ? '#prescriptions' : '#reportsPane';
            return redirect(route('admin.users.show', $userId) . $fragment)->with('success', ucfirst($upload->type) . ' updated successfully.');
        }

        return redirect(route('health') . ($upload->type === 'prescription' ? '#prescriptions' : '#reportsPane'))->with('success', ucfirst($request->type) . ' updated successfully.');
    }

    /* ====================================================================
     *  DELETE methods
     * ==================================================================== */

    public function destroyMetric(UserHealth $healthMetric)
    {
        $user = Auth::user();
        if (!$user || ($healthMetric->user_id !== $user->id && $user->role !== 'admin')) abort(403);
        $userId = $healthMetric->user_id;
        $healthMetric->delete();
        
        if ($user->role === 'admin') {
            return redirect(route('admin.users.show', $userId) . '#metrics')->with('success', 'Health metric deleted.');
        }
        return redirect(route('health') . '#metrics')->with('success', 'Health metric deleted.');
    }

    public function destroySymptom(UserSymptom $symptom)
    {
        $user = Auth::user();
        if (!$user || ($symptom->user_id !== $user->id && $user->role !== 'admin')) abort(403);
        $userId = $symptom->user_id;
        $symptom->delete();
        
        if ($user->role === 'admin') {
            return redirect(route('admin.users.show', $userId) . '#symptomsPane')->with('success', 'Symptom record deleted.');
        }
        return redirect(route('health') . '#symptomsPane')->with('success', 'Symptom record deleted.');
    }

    public function destroyDisease(UserDisease $userDisease)
    {
        $user = Auth::user();
        if (!$user || ($userDisease->user_id !== $user->id && $user->role !== 'admin')) abort(403);
        $userId = $userDisease->user_id;
        $userDisease->delete();
        
        if ($user->role === 'admin') {
            return redirect(route('admin.users.show', $userId) . '#diseasesPane')->with('success', 'Disease record removed.');
        }
        return redirect(route('health') . '#diseasesPane')->with('success', 'Disease record removed.');
    }

    public function destroyUpload(Upload $upload)
    {
        $user = Auth::user();
        if (!$user || ($upload->user_id !== $user->id && $user->role !== 'admin')) abort(403);
        $userId = $upload->user_id;

        if ($upload->file_path && Storage::disk('public')->exists($upload->file_path)) {
            Storage::disk('public')->delete($upload->file_path);
        }

        $upload->delete();

        if ($user->role === 'admin') {
            $fragment = $upload->type === 'prescription' ? '#prescriptions' : '#reportsPane';
            return redirect(route('admin.users.show', $userId) . $fragment)->with('success', 'Upload deleted successfully.');
        }
        return redirect(route('health') . ($upload->type === 'prescription' ? '#prescriptions' : '#reportsPane'))->with('success', 'Upload deleted successfully.');
    }

    private function syncSymptomDiseaseLinks(int $userId, Symptom $symptom): void
    {
        $diseaseIds = UserDisease::where('user_id', $userId)
            ->pluck('disease_id')
            ->all();

        if (empty($diseaseIds)) {
            return;
        }

        $symptom->diseases()->syncWithoutDetaching($diseaseIds);
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
        
        // Get Bengali name from translation
        $bnName = __('ui.health.metric_config.' . $metricName, [], 'bn');
        if ($bnName === 'ui.health.metric_config.' . $metricName) {
            // Fallback: convert underscore to space and capitalize
            $bnName = ucwords(str_replace('_', ' ', $metricName));
        }
        
        $config[$metricName] = [
            'en' => ucwords(str_replace('_', ' ', $metricName)),
            'bn' => $bnName,
            'unit' => '',
            'fields' => $fields,
            'js_fields' => collect($fields)->values()->map(function (string $field, int $index): array {
                return [
                    'name' => 'value_' . $index,
                    'field_key' => $field,
                    'label' => $field,
                    'placeholder' => 'Enter ' . $field,
                    'min' => 0,
                    'max' => 100000,
                    'step' => '0.01',
                ];
            })->all(),
        ];
    }

    return $config;
}

    private function extractMetricValuesFromRequest(Request $request, string $metricType, array $fields): array
    {
        $values = [];
        $legacyFields = array_values((array) data_get(config('health.metric_types', []), $metricType . '.fields', []));

        foreach (array_values($fields) as $index => $fieldLabel) {
            $value = $request->input('value_' . $index);
            if ($value === null) {
                $legacyField = $legacyFields[$index] ?? null;
                if ($legacyField !== null) {
                    $value = $request->input('value_' . $legacyField);
                }
            }

            $values[$fieldLabel] = $value ?? 0;
        }

        return $values;
    }
}
