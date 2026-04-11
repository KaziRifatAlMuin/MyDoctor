<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\HealthMetric;
use App\Models\UserHealth;
use App\Models\User;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Display a listing of users (public view).
     */
    public function index(Request $request)
    {
        $query = User::query()->with(['address', 'setting']);
        
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Default listing order: alphabetical by member name.
        $query->orderBy('name', 'asc');
        
        $users = $query->paginate(20);
        
        // Get statistics
        $adminCount = User::where('role', 'admin')->count();
        $memberCount = User::where('role', 'member')->count();
        $totalUsers = User::count();
        $recentUsers = User::whereDate('created_at', '>=', Carbon::now()->subWeek())->count();

        return view('users.index', compact(
            'users', 
            'adminCount',
            'memberCount', 
            'totalUsers',
            'recentUsers'
        ));
    }

    /**
     * Display the specified user profile (public view).
     */
    public function publicShow(User $user)
    {
        $user->load(['setting', 'address']);

        if ($user->setting->show_diseases) {
            $user->load(['userDiseases' => function ($query) {
                $query->with('disease')->latest();
            }]);
        }

        return view('users.public-show', compact('user'));
    }

    /**
     * Display the specified user profile (admin only).
     */
    public function show(User $user)
    {
        // Check if user is admin
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }
        
        $user->load('address');
        $metricDefinitions = $this->ensureMetricDefinitions();

        // Load all health data for the user (same as HealthController)
        $healthMetrics = UserHealth::with('healthMetric')
            ->where('user_id', $user->id)
            ->orderByDesc('recorded_at')
            ->limit(50)
            ->get()
            ->filter(fn(UserHealth $record) => $record->healthMetric !== null)
            ->values();

        $metricsByType = $healthMetrics->groupBy(fn(UserHealth $record) => $record->metric_type ?? 'unknown');
        $latestMetrics = $metricsByType->map(fn($group) => $group->first());
        $metricConfig = $this->buildMetricConfig($metricDefinitions);

        // Symptoms
        $symptoms = \App\Models\UserSymptom::where('user_id', $user->id)
            ->with('symptom')
            ->orderByDesc('recorded_at')
            ->limit(30)
            ->get();

        // Symptom severity distribution (counts by severity_level)
        $severityDistribution = $symptoms->groupBy('severity_level')->map->count();

        // Medicines
        $medicines = \App\Models\Medicine::where('user_id', $user->id)
            ->with('schedules')
            ->orderByDesc('created_at')
            ->get();

        // Medicine logs
        $medicineLogs = \App\Models\MedicineLog::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $totalScheduled = $medicineLogs->where('taken_at', null)->count();
        $totalTaken = $medicineLogs->where('taken_at', '!=', null)->count();
        $totalMissed = $medicineLogs->where('status', 'missed')->count();
        $denominator = $totalTaken + $totalMissed + $totalScheduled;
        $adherenceRate = ($denominator > 0) ? round(($totalTaken / $denominator) * 100, 1) : 0;

        // User diseases
        $userDiseases = \App\Models\UserDisease::where('user_id', $user->id)
            ->with('disease')
            ->orderByDesc('created_at')
            ->get();

        $allDiseases = \App\Models\Disease::orderBy('disease_name')->get();

        $diseasesBn = [];

        // Uploads
        $uploads = \App\Models\Upload::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $prescriptionUploads = $uploads->where('type', 'prescription');
        $reportUploads = $uploads->where('type', 'report');

        // Symptom list
        $symptomsList = config('health.symptoms', []);
        
        return view('users.show', compact(
            'user',
            'healthMetrics',
            'metricsByType',
            'latestMetrics',
            'metricConfig',
            'symptoms',
            'severityDistribution',
            'medicines',
            'medicineLogs',
            'totalScheduled',
            'totalTaken',
            'totalMissed',
            'adherenceRate',
            'userDiseases',
            'allDiseases',
            'diseasesBn',
            'uploads',
            'prescriptionUploads',
            'reportUploads',
            'symptomsList'
        ));
    }

    /**
     * Update the specified user (admin only).
     */
    public function update(Request $request, User $user)
    {
        // Check if user is admin
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'occupation' => 'nullable|string|max:255',
            'blood_group' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'role' => 'required|in:admin,member',
            'is_active' => 'nullable|boolean',
            'division_id' => 'nullable|integer',
            'division' => 'nullable|string|max:255',
            'division_bn' => 'nullable|string|max:255',
            'district_id' => 'nullable|integer',
            'district' => 'nullable|string|max:255',
            'district_bn' => 'nullable|string|max:255',
            'upazila_id' => 'nullable|integer',
            'upazila' => 'nullable|string|max:255',
            'upazila_bn' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'house' => 'nullable|string|max:255',
        ]);

        // Prevent admin from removing their own admin access
        if ($user->id === auth()->id() && $validated['role'] !== 'admin') {
            return back()->withErrors([
                'role' => 'You cannot remove your own admin access.',
            ])->withInput();
        }

        if ($user->id === auth()->id() && isset($validated['is_active']) && ! (bool) $validated['is_active']) {
            return back()->withErrors([
                'is_active' => 'You cannot deactivate your own account.',
            ])->withInput();
        }

        $user->update($validated);

        $user->address()->updateOrCreate([], [
            'division_id' => $validated['division_id'] ?? ($user->address?->division_id ?? null),
            'division' => $validated['division'] ?? ($user->address?->division ?? 'Not set'),
            'division_bn' => $validated['division_bn'] ?? ($user->address?->division_bn ?? null),
            'district_id' => $validated['district_id'] ?? ($user->address?->district_id ?? null),
            'district' => $validated['district'],
            'district_bn' => $validated['district_bn'] ?? ($user->address?->district_bn ?? null),
            'upazila_id' => $validated['upazila_id'] ?? ($user->address?->upazila_id ?? null),
            'upazila' => $validated['upazila'],
            'upazila_bn' => $validated['upazila_bn'] ?? ($user->address?->upazila_bn ?? null),
            'street' => $validated['street'] ?? null,
            'house' => $validated['house'] ?? null,
        ]);

        $redirectTab = request()->input('redirect_tab', 'overview');
        return back()->with('success', "{$user->name} was updated successfully.")->withFragment($redirectTab);
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
}