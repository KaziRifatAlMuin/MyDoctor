<?php

namespace App\Http\Controllers;

use App\Models\Disease;
use App\Models\Symptom;
use App\Models\User;
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
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users', [
            'users' => $users,
            'search' => $search,
            'adminCount' => User::where('role', 'admin')->count(),
            'memberCount' => User::where('role', 'member')->count(),
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
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'email_verified_at' => now(),
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
        ]);

        if ($request->user()->id === $user->id && $validated['role'] !== 'admin') {
            return back()->withErrors([
                'role' => 'You cannot remove your own admin role.',
            ]);
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
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
            ->paginate(20)
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
            ->paginate(20)
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
}
