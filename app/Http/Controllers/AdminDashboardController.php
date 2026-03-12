<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Medicine;
use App\Models\MedicineReminder;
use App\Models\HealthMetric;
use App\Models\Symptom;
use App\Models\Disease;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        // Gather statistics for the dashboard
        $stats = $this->getDashboardStats();
        $recent_users = $this->getRecentUsers();
        $recent_activities = $this->getRecentActivities();

        return view('admin.dashboard', compact('stats', 'recent_users', 'recent_activities'));
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

    public function users()
    {
        return redirect()->route('admin.users.index');
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