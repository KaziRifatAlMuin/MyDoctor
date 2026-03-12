<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Medicine;
use App\Models\MedicineReminder;
use App\Models\MedicineSchedule;
use App\Models\HealthMetric;
use App\Models\Symptom;
use App\Models\Disease;
use App\Models\UserDisease;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request)
    {
        // Get paginated users with filtering
        $query = User::query();
        
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Apply role filter
        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }
        
        // Apply sorting
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            default:
                $query->latest();
                break;
        }
        
        $users = $query->paginate(50);
        
        // Get statistics for the dashboard
        $stats = $this->getDashboardStats();
        $recent_activities = $this->getRecentActivities();
        $recentUsersList = $this->getRecentUsers(6);
        $latestDiseaseRecords = UserDisease::with(['user', 'disease'])
            ->latest()
            ->take(8)
            ->get();
        $latestMedicines = Medicine::with(['user', 'activeSchedule'])
            ->latest()
            ->take(8)
            ->get();
        $latestMetrics = HealthMetric::with('user')
            ->latest()
            ->take(8)
            ->get();
        
        // Get user statistics
        $adminCount = User::where('role', 'admin')->count();
        $memberCount = User::where('role', 'member')->count();
        $recentUsers = User::whereDate('created_at', '>=', Carbon::now()->subWeek())->count();

        // Add to stats array
        $stats['admin_count'] = $adminCount;
        $stats['member_count'] = $memberCount;
        $stats['recent_users'] = $recentUsers;

        return view('admin.dashboard', compact(
            'stats', 
            'recent_activities',
            'users',
            'adminCount',
            'memberCount',
            'recentUsers',
            'recentUsersList',
            'latestDiseaseRecords',
            'latestMedicines',
            'latestMetrics'
        ));
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'occupation' => 'nullable|string|max:255',
            'blood_group' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'date_of_birth' => 'nullable|date|before:today',
            'role' => 'required|in:admin,member',
        ]);

        if ($user->id === $request->user()->id && $validated['role'] !== 'admin') {
            return back()->withErrors([
                'role' => 'You cannot remove your own admin access.',
            ])->withInput();
        }

        $user->update($validated);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', "{$user->name} was updated successfully.");
    }

    private function getDashboardStats()
    {
        $today = Carbon::today();
        
        return [
            // User statistics
            'total_users' => User::count(),
            'new_users_today' => User::whereDate('created_at', $today)->count(),
            
            // Community statistics
            'total_posts' => Post::count(),
            'total_comments' => Comment::count(),
            
            // Medical statistics
            'total_medicines' => Medicine::count(),
            // Count reminders whose parent schedule is active
            'active_reminders' => MedicineReminder::whereHas('schedule', function($q) {
                $q->where('is_active', true);
            })->count(),
            'total_health_metrics' => HealthMetric::count(),
            'recent_metrics' => HealthMetric::whereDate('created_at', '>=', $today->subDays(7))->count(),
            'total_symptoms' => Symptom::count(),
            'total_diseases' => Disease::count(),
            
            // System statistics
            'storage_usage' => $this->getStorageUsage(),
            'pending_jobs' => $this->getPendingJobsCount(),
        ];
    }

    private function getRecentUsers($limit = 10)
    {
        return User::latest()
            ->take($limit)
            ->get();
    }

    private function getRecentActivities($limit = 5)
    {
        $activities = collect();

        // Recent user registrations
        $recentUsers = User::whereDate('created_at', '>=', Carbon::today()->subDays(7))
            ->latest()
            ->take(3)
            ->get();

        foreach ($recentUsers as $user) {
            $activities->push([
                'message' => "New user registered: {$user->name}",
                'time' => $user->created_at->diffForHumans(),
                'type' => 'user_registered',
                'icon' => 'fa-user-plus'
            ]);
        }

        // Recent posts
        $recentPosts = Post::whereDate('created_at', '>=', Carbon::today()->subDays(7))
            ->with('user')
            ->latest()
            ->take(2)
            ->get();

        foreach ($recentPosts as $post) {
            $activities->push([
                'message' => "New post by {$post->user->name}",
                'time' => $post->created_at->diffForHumans(),
                'type' => 'post_created',
                'icon' => 'fa-newspaper'
            ]);
        }

        // Recent medicine reminders
        $recentReminders = MedicineReminder::whereDate('created_at', '>=', Carbon::today()->subDays(7))
            ->with('schedule.medicine.user')
            ->latest()
            ->take(2)
            ->get();

        foreach ($recentReminders as $reminder) {
            $actorName = 'Unknown';
            if ($reminder->schedule && $reminder->schedule->medicine && $reminder->schedule->medicine->user) {
                $actorName = $reminder->schedule->medicine->user->name;
            }

            $activities->push([
                'message' => "Medicine reminder set by {$actorName}",
                'time' => $reminder->created_at->diffForHumans(),
                'type' => 'reminder_created',
                'icon' => 'fa-bell'
            ]);
        }

        return $activities->sortByDesc('time')->take($limit)->values();
    }

    private function getStorageUsage()
    {
        try {
            // Simple estimation for storage usage
            // In a real application, you would implement proper storage monitoring
            $storageUsage = rand(30, 70); // Random between 30-70% for demo
            return $storageUsage;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getPendingJobsCount()
    {
        try {
            // In a real application, you would check your queue system
            // For now, return a mock value
            return rand(0, 10);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function users(Request $request)
    {
        // Redirect to main admin dashboard 
        return redirect()->route('admin.dashboard', $request->all());
    }
    
    public function show(User $user)
    {
        // Get user statistics
        $userStats = [
            'posts' => Post::where('user_id', $user->id)->count(),
            'comments' => Comment::where('user_id', $user->id)->count(),
            'medicines' => Medicine::where('user_id', $user->id)->count(),
            'health_metrics' => HealthMetric::where('user_id', $user->id)->count(),
            'active_reminders' => MedicineReminder::whereHas('schedule', function($q) use ($user) {
                $q->whereHas('medicine', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->where('is_active', true);
            })->count(),
            'likes' => 0 // Placeholder for post likes received
        ];
        
        // Get recent activities
        $recentActivities = collect();
        
        // Recent posts
        $recentPosts = Post::where('user_id', $user->id)
            ->latest()
            ->take(3)
            ->get();
            
        foreach ($recentPosts as $post) {
            $recentActivities->push([
                'title' => 'Created a new post',
                'description' => Str::limit($post->title ?? 'Untitled Post', 50),
                'time' => $post->created_at->diffForHumans(),
                'icon' => 'fa-newspaper',
                'color' => 'success'
            ]);
        }
        
        // Recent medicines
        $recentMedicines = Medicine::where('user_id', $user->id)
            ->latest()
            ->take(2)
            ->get();
            
        foreach ($recentMedicines as $medicine) {
            $recentActivities->push([
                'title' => 'Added new medicine',
                'description' => $medicine->medicine_name,
                'time' => $medicine->created_at->diffForHumans(),
                'icon' => 'fa-pills',
                'color' => 'primary'
            ]);
        }
        
        // Recent health metrics
        $recentMetrics = HealthMetric::where('user_id', $user->id)
            ->latest()
            ->take(2)
            ->get();
            
        foreach ($recentMetrics as $metric) {
            $recentActivities->push([
                'title' => 'Recorded health data',
                'description' => ucfirst($metric->metric_type ?? 'Health metric'),
                'time' => $metric->created_at->diffForHumans(),
                'icon' => 'fa-heartbeat',
                'color' => 'danger'
            ]);
        }
        
        $recentActivities = $recentActivities->sortByDesc('time')->take(10);
        
        return view('admin.users.show', compact('user', 'userStats', 'recentActivities'));
    }

    public function medical()
    {
        return redirect()->route('admin.medical.index');
    }

    public function analytics()
    {
        return redirect()->route('admin.analytics');
    }

    public function settings()
    {
        return redirect()->route('admin.settings');
    }
}
