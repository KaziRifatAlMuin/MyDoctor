<?php

namespace App\Http\Controllers;

use App\Models\Disease;
use App\Models\HealthMetric;
use App\Models\Symptom;
use App\Models\User;
use App\Models\UserHealth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function usersIndex(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $users = User::query()
            ->with('address')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->paginate(60)
            ->withQueryString();

        return view('admin.users', [
            'users' => $users,
            'search' => $search,
            'adminCount' => User::where('role', 'admin')->count(),
            'memberCount' => User::where('role', 'member')->count(),
            'activeCount' => User::where('is_active', true)->count(),
            'inactiveCount' => User::where('is_active', false)->count(),
            'verifiedCount' => User::whereNotNull('email_verified_at')->count(),
            'newThisWeekCount' => User::whereDate('created_at', '>=', now()->subDays(7))->count(),
        ]);
    }

    public function usersStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,member'],
            'phone' => ['nullable', 'string', 'max:20'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'in:male,female,other'],
            'is_active' => ['nullable', 'boolean'],
            'division_id' => ['nullable', 'integer'],
            'division' => ['nullable', 'string', 'max:255'],
            'division_bn' => ['nullable', 'string', 'max:255'],
            'district_id' => ['nullable', 'integer'],
            'district' => ['nullable', 'string', 'max:255'],
            'district_bn' => ['nullable', 'string', 'max:255'],
            'upazila_id' => ['nullable', 'integer'],
            'upazila' => ['nullable', 'string', 'max:255'],
            'upazila_bn' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'house' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'gender' => $validated['gender'] ?? 'other',
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'email_verified_at' => now(),
        ]);

        $user->address()->updateOrCreate([], [
            'division_id' => $validated['division_id'] ?? 0,
            'division' => $validated['division'] ?? 'Not set',
            'division_bn' => $validated['division_bn'] ?? null,
            'district_id' => $validated['district_id'] ?? 0,
            'district' => $validated['district'] ?? 'Not set',
            'district_bn' => $validated['district_bn'] ?? null,
            'upazila_id' => $validated['upazila_id'] ?? 0,
            'upazila' => $validated['upazila'] ?? 'Not set',
            'upazila_bn' => $validated['upazila_bn'] ?? null,
            'street' => $validated['street'] ?? null,
            'house' => $validated['house'] ?? null,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function usersUpdate(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:admin,member'],
            'phone' => ['nullable', 'string', 'max:20'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
            'gender' => ['nullable', 'in:male,female,other'],
            'is_active' => ['nullable', 'boolean'],
            'division_id' => ['nullable', 'integer'],
            'division' => ['nullable', 'string', 'max:255'],
            'division_bn' => ['nullable', 'string', 'max:255'],
            'district_id' => ['nullable', 'integer'],
            'district' => ['nullable', 'string', 'max:255'],
            'district_bn' => ['nullable', 'string', 'max:255'],
            'upazila_id' => ['nullable', 'integer'],
            'upazila' => ['nullable', 'string', 'max:255'],
            'upazila_bn' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'house' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->user()->id === $user->id && $validated['role'] !== 'admin') {
            return back()->withErrors([
                'role' => 'You cannot remove your own admin role.',
            ]);
        }

        if ($request->user()->id === $user->id && isset($validated['is_active']) && ! (bool) $validated['is_active']) {
            return back()->withErrors([
                'is_active' => 'You cannot deactivate your own account.',
            ]);
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'gender' => $validated['gender'] ?? ($user->gender ?? 'other'),
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        $user->address()->updateOrCreate([], [
            'division_id' => $validated['division_id'] ?? ($user->address?->division_id ?? 0),
            'division' => $validated['division'] ?? ($user->address?->division ?? 'Not set'),
            'division_bn' => $validated['division_bn'] ?? ($user->address?->division_bn ?? null),
            'district_id' => $validated['district_id'] ?? ($user->address?->district_id ?? 0),
            'district' => $validated['district'] ?? ($user->address?->district ?? 'Not set'),
            'district_bn' => $validated['district_bn'] ?? ($user->address?->district_bn ?? null),
            'upazila_id' => $validated['upazila_id'] ?? ($user->address?->upazila_id ?? 0),
            'upazila' => $validated['upazila'] ?? ($user->address?->upazila ?? 'Not set'),
            'upazila_bn' => $validated['upazila_bn'] ?? ($user->address?->upazila_bn ?? null),
            'street' => $validated['street'] ?? null,
            'house' => $validated['house'] ?? null,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function usersToggleActive(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return back()->withErrors([
                'is_active' => 'You cannot deactivate your own account.',
            ]);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', $user->is_active
                ? 'User activated successfully.'
                : 'User deactivated successfully.');
    }

    public function usersDestroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return back()->withErrors([
                'delete' => 'You cannot delete your own account from admin panel.',
            ]);
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function diseasesIndex(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $diseases = Disease::query()
            ->withCount(['userDiseases', 'symptoms'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where('disease_name', 'like', "%{$search}%");
            })
            ->orderBy('disease_name')
            ->paginate(60)
            ->withQueryString();

        return view('admin.diseases', [
            'diseases' => $diseases,
            'search' => $search,
        ]);
    }

    public function diseasesStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'disease_name' => ['required', 'string', 'max:255', 'unique:diseases,disease_name'],
            'description' => ['nullable', 'string', 'max:3000'],
        ]);

        Disease::create($validated);

        return redirect()
            ->route('admin.diseases.index')
            ->with('success', 'Disease created successfully.');
    }

    public function diseasesUpdate(Request $request, Disease $disease): RedirectResponse
    {
        $validated = $request->validate([
            'disease_name' => ['required', 'string', 'max:255', 'unique:diseases,disease_name,' . $disease->id],
            'description' => ['nullable', 'string', 'max:3000'],
        ]);

        $disease->update($validated);

        return redirect()
            ->route('admin.diseases.index')
            ->with('success', 'Disease updated successfully.');
    }

    public function diseasesDestroy(Disease $disease): RedirectResponse
    {
        if ($disease->userDiseases()->exists() || $disease->symptoms()->exists()) {
            return back()->withErrors([
                'delete' => 'This disease is linked to existing records and cannot be deleted.',
            ]);
        }

        $disease->delete();

        return redirect()
            ->route('admin.diseases.index')
            ->with('success', 'Disease deleted successfully.');
    }

    public function symptomsIndex(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $symptoms = Symptom::query()
            ->withCount(['userSymptoms', 'diseases'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(60)
            ->withQueryString();

        return view('admin.symptoms', [
            'symptoms' => $symptoms,
            'search' => $search,
        ]);
    }

    public function symptomsStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:symptoms,name'],
        ]);

        Symptom::create($validated);

        return redirect()
            ->route('admin.symptoms.index')
            ->with('success', 'Symptom created successfully.');
    }

    public function symptomsUpdate(Request $request, Symptom $symptom): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:symptoms,name,' . $symptom->id],
        ]);

        $symptom->update($validated);

        return redirect()
            ->route('admin.symptoms.index')
            ->with('success', 'Symptom updated successfully.');
    }

    public function symptomsDestroy(Symptom $symptom): RedirectResponse
    {
        if ($symptom->userSymptoms()->exists() || $symptom->diseases()->exists()) {
            return back()->withErrors([
                'delete' => 'This symptom is linked to existing records and cannot be deleted.',
            ]);
        }

        $symptom->delete();

        return redirect()
            ->route('admin.symptoms.index')
            ->with('success', 'Symptom deleted successfully.');
    }

    public function metricsIndex(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $metrics = HealthMetric::query()
            ->withCount('userHealthRecords')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('metric_name', 'like', "%{$search}%");
            })
            ->orderBy('metric_name')
            ->paginate(60)
            ->withQueryString();

        return view('admin.health', [
            'metrics' => $metrics,
            'search' => $search,
        ]);
    }

    public function metricsShow(HealthMetric $healthMetric): View
    {
        $healthMetric->loadCount('userHealthRecords');

        $recentEntries = UserHealth::query()
            ->with('user')
            ->where('health_metric_id', $healthMetric->id)
            ->orderByDesc('recorded_at')
            ->paginate(25);

        return view('admin.metric-show', [
            'healthMetric' => $healthMetric,
            'recentEntries' => $recentEntries,
        ]);
    }

    public function metricsStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'metric_name' => ['required', 'string', 'max:255', 'unique:health_metrics,metric_name'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*' => ['required', 'string', 'max:255'],
        ]);

        HealthMetric::create([
            'metric_name' => $validated['metric_name'],
            'fields' => $this->parseFields($validated['fields']),
        ]);

        return redirect()
            ->route('admin.health.index')
            ->with('success', 'Health metric definition created successfully.');
    }

    public function metricsUpdate(Request $request, HealthMetric $healthMetric): RedirectResponse
    {
        $validated = $request->validate([
            'metric_name' => ['required', 'string', 'max:255', 'unique:health_metrics,metric_name,' . $healthMetric->id],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*' => ['required', 'string', 'max:255'],
        ]);

        $healthMetric->update([
            'metric_name' => $validated['metric_name'],
            'fields' => $this->parseFields($validated['fields']),
        ]);

        return redirect()
            ->route('admin.metrics.show', $healthMetric)
            ->with('success', 'Health metric definition updated successfully.');
    }

    public function metricsDestroy(HealthMetric $healthMetric): RedirectResponse
    {
        if ($healthMetric->userHealthRecords()->exists()) {
            return back()->withErrors([
                'delete' => 'This metric is linked to user health records and cannot be deleted.',
            ]);
        }

        $healthMetric->delete();

        return redirect()
            ->route('admin.health.index')
            ->with('success', 'Health metric definition deleted successfully.');
    }

    private function parseFields(array $fieldsRaw): array
    {
        $parts = collect($fieldsRaw)
            ->map(fn(string $field) => trim($field))
            ->filter(fn(string $field) => $field !== '')
            ->unique()
            ->values()
            ->all();

        if (empty($parts)) {
            return ['value'];
        }

        return $parts;
    }
}
