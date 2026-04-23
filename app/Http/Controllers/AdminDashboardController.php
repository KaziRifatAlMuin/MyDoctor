<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Disease;
use App\Models\HealthMetric;
use App\Models\UserHealth;
use App\Models\Mailing;
use App\Models\Medicine;
use App\Models\MedicineLog;
use App\Models\MedicineReminder;
use App\Models\Notification;
use App\Models\PostLike;
use App\Models\Symptom;
use App\Models\Upload;
use App\Models\UserDisease;
use App\Models\UserSymptom;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request)
    {
        $stats = $this->getDashboardStats();
        $recentActivities = $this->getRecentActivities(8);
        $latestDiseaseRecords = UserDisease::with(['user', 'disease'])
            ->latest()
            ->take(6)
            ->get();
        $latestMedicines = Medicine::with(['user', 'activeSchedule'])
            ->latest()
            ->take(6)
            ->get();
        $latestMetrics = UserHealth::with(['user', 'healthMetric'])
            ->latest('recorded_at')
            ->take(6)
            ->get();
        $pendingPosts = Post::with(['user', 'diseases'])
            ->where('is_approved', false)
            ->latest()
            ->take(5)
            ->get();
        $navigationCards = $this->getNavigationCards();

        return view('admin.dashboard', compact(
            'stats',
            'recentActivities',
            'latestDiseaseRecords',
            'latestMedicines',
            'latestMetrics',
            'pendingPosts',
            'navigationCards'
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
                'role' => __('ui.admin_messages.cannot_remove_own_admin'),
            ])->withInput();
        }

        $user->update($validated);

        $message = __('ui.admin_messages.user_updated_successfully', ['name' => $user->name]);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', $message);
    }

    private function getDashboardStats()
    {
        $today = Carbon::today();
        $weekAgo = Carbon::now()->subDays(7);
        $monthAgo = Carbon::now()->subDays(30);
        $adminCount = User::where('role', 'admin')->count();
        $memberCount = User::where('role', 'member')->count();
        $databaseSummary = $this->getDatabaseSummary();
        
        return [
            'users' => [
                'total' => User::count(),
                'admins' => $adminCount,
                'members' => $memberCount,
                'new_today' => User::whereDate('created_at', $today)->count(),
                'new_this_week' => User::whereDate('created_at', '>=', $weekAgo)->count(),
                'new_this_month' => User::whereDate('created_at', '>=', $monthAgo)->count(),
            ],
            'community' => [
                'posts' => Post::count(),
                'pending_posts' => Post::where('is_approved', false)->count(),
                'approved_posts' => Post::where('is_approved', true)->count(),
                'approved_today' => Post::where('is_approved', true)->whereDate('updated_at', $today)->count(),
                'comments' => Comment::count(),
                'post_likes' => PostLike::count(),
                'comment_likes' => CommentLike::count(),
            ],
            'medical' => [
                'medicines' => Medicine::count(),
                'medicine_logs' => MedicineLog::count(),
                'health_metrics' => UserHealth::count(),
                'user_symptoms' => UserSymptom::count(),
                'user_diseases' => UserDisease::count(),
                'reference_diseases' => Disease::count(),
                'reference_symptoms' => Symptom::count(),
            ],
            'engagement' => [
                'notifications' => Notification::count(),
                'mailings' => Mailing::count(),
                'uploads' => Upload::count(),
            ],
            'operations' => [
                'active_reminders' => MedicineReminder::whereHas('schedule', function($q) {
                    $q->where('is_active', true);
                })->count(),
                'new_metrics_this_week' => UserHealth::whereDate('created_at', '>=', $weekAgo)->count(),
                'new_logs_this_week' => MedicineLog::whereDate('created_at', '>=', $weekAgo)->count(),
            ],
            'database' => $databaseSummary,
        ];
    }

    private function getNavigationCards(): array
    {
        return [
            [
                'title' => __('ui.admin_nav.user_management'),
                'description' => __('ui.admin_nav.user_management_desc'),
                'icon' => 'fa-users-cog',
                'route' => route('admin.users.index'),
                'accent' => 'accent-users',
            ],
            [
                'title' => __('ui.admin_nav.disease_catalog'),
                'description' => __('ui.admin_nav.disease_catalog_desc'),
                'icon' => 'fa-virus',
                'route' => route('admin.diseases.index'),
                'accent' => 'accent-diseases',
            ],
            [
                'title' => __('ui.admin_nav.symptoms_catalog'),
                'description' => __('ui.admin_nav.symptoms_catalog_desc'),
                'icon' => 'fa-stethoscope',
                'route' => route('admin.symptoms.index'),
                'accent' => 'accent-symptoms',
            ],
            [
                'title' => __('ui.admin_nav.health_metrics_catalog'),
                'description' => __('ui.admin_nav.health_metrics_catalog_desc'),
                'icon' => 'fa-heartbeat',
                'route' => route('admin.health.index'),
                'accent' => 'accent-public',
            ],
            [
                'title' => __('ui.admin_nav.public_diseases'),
                'description' => __('ui.admin_nav.public_diseases_desc'),
                'icon' => 'fa-earth-asia',
                'route' => route('public.diseases.index'),
                'accent' => 'accent-public',
            ],
            [
                'title' => __('ui.admin_nav.public_symptoms'),
                'description' => __('ui.admin_nav.public_symptoms_desc'),
                'icon' => 'fa-globe',
                'route' => route('public.symptoms.index'),
                'accent' => 'accent-public',
            ],
        ];
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
                'message' => __('ui.admin_activities.new_user_registered', ['name' => $user->name]),
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
                'message' => __('ui.admin_activities.new_post_by', ['name' => $post->user->name]),
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
            $actorName = __('ui.admin_activities.unknown');
            if ($reminder->schedule && $reminder->schedule->medicine && $reminder->schedule->medicine->user) {
                $actorName = $reminder->schedule->medicine->user->name;
            }

            $activities->push([
                'message' => __('ui.admin_activities.medicine_reminder_set', ['name' => $actorName]),
                'time' => $reminder->created_at->diffForHumans(),
                'type' => 'reminder_created',
                'icon' => 'fa-bell'
            ]);
        }

        return $activities->sortByDesc('time')->take($limit)->values();
    }

    private function getDatabaseSummary(): array
    {
        try {
            $tables = Schema::getTableListing();
            $tableRows = [];
            $totalRecords = 0;

            foreach ($tables as $table) {
                try {
                    $rows = (int) DB::table($table)->count();
                } catch (\Throwable $exception) {
                    $rows = 0;
                }

                $tableRows[] = [
                    'name' => $table,
                    'rows' => $rows,
                ];

                $totalRecords += $rows;
            }

            usort($tableRows, fn ($a, $b) => $b['rows'] <=> $a['rows']);

            return [
                'tables_count' => count($tables),
                'total_records' => $totalRecords,
                'largest_tables' => array_slice($tableRows, 0, 8),
            ];
        } catch (\Exception $e) {
            return [
                'tables_count' => 0,
                'total_records' => 0,
                'largest_tables' => [],
            ];
        }
    }

/**
 * Display reported posts for moderation
 */
public function reportedPosts(Request $request)
{
    $posts = Post::with(['user', 'diseases'])
        ->where('is_reported', true)
        ->orderByDesc('updated_at')
        ->paginate(20);

    return view('admin.reported-posts', [
        'posts' => $posts,
    ]);
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
            'health_metrics' => UserHealth::where('user_id', $user->id)->count(),
            'active_reminders' => MedicineReminder::whereHas('schedule', function($q) use ($user) {
                $q->whereHas('medicine', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->where('is_active', true);
            })->count(),
            'likes' => 0
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
                'title' => __('ui.admin_user_activities.created_new_post'),
                'description' => Str::limit($post->title ?? __('ui.admin_user_activities.untitled_post'), 50),
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
                'title' => __('ui.admin_user_activities.added_new_medicine'),
                'description' => $medicine->medicine_name,
                'time' => $medicine->created_at->diffForHumans(),
                'icon' => 'fa-pills',
                'color' => 'primary'
            ]);
        }
        
        // Recent health metrics
        $recentMetrics = UserHealth::with('healthMetric')
            ->where('user_id', $user->id)
            ->latest('recorded_at')
            ->take(2)
            ->get();
            
        foreach ($recentMetrics as $metric) {
            $recentActivities->push([
                'title' => __('ui.admin_user_activities.recorded_health_data'),
                'description' => ucfirst($metric->metric_type ?? __('ui.admin_user_activities.health_metric')),
                'time' => $metric->recorded_at?->diffForHumans() ?? $metric->created_at->diffForHumans(),
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