<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Disease;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request): View
    {
        $type = trim((string) $request->query('type', 'all'));
        $search = trim((string) $request->query('q', ''));

        $allowedTypes = array_keys($this->activityTypes());
        if (!in_array($type, $allowedTypes, true)) {
            $type = 'all';
        }

        $logsQuery = ActivityLog::query()
            ->with('user')
            ->when($type !== 'all', function ($query) use ($type) {
                $this->applyTypeFilter($query, $type);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('action', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('subject_type', 'like', "%{$search}%")
                        ->orWhere('subject_id', 'like', "%{$search}%")
                        ->orWhere('context', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('created_at');

        $logs = $logsQuery->paginate(100)->withQueryString();
        $logs->getCollection()->transform(function (ActivityLog $log) {
            $log->subject_url = $this->subjectUrl($log);
            $log->subject_label = $this->subjectLabel($log);
            return $log;
        });

        return view('admin.logs', [
            'logs' => $logs,
            'type' => $type,
            'search' => $search,
            'activityTypes' => $this->activityTypes(),
        ]);
    }

    private function activityTypes(): array
    {
        return [
            'all' => __('ui.admin_activity_logs.all_types'),
            'login' => __('ui.admin_activity_logs.type_login'),
            'logout' => __('ui.admin_activity_logs.type_logout'),
            'post' => __('ui.admin_activity_logs.type_post'),
            'comment' => __('ui.admin_activity_logs.type_comment'),
            'interaction' => __('ui.admin_activity_logs.type_interaction'),
            'admin' => __('ui.admin_activity_logs.type_admin'),
            'health' => __('ui.admin_activity_logs.type_health'),
            'medicine' => __('ui.admin_activity_logs.type_medicine'),
            'mailbox' => __('ui.admin_activity_logs.type_mailbox'),
            'notification' => __('ui.admin_activity_logs.type_notification'),
            'account' => __('ui.admin_activity_logs.type_account'),
        ];
    }

    private function applyTypeFilter($query, string $type): void
    {
        if ($type === 'login') {
            $query->where('action', 'login');
            return;
        }

        if ($type === 'logout') {
            $query->where('action', 'logout');
            return;
        }

        if ($type === 'post') {
            $query->where('subject_type', Post::class);
            return;
        }

        if ($type === 'comment') {
            $query->where('subject_type', Comment::class);
            return;
        }

        if ($type === 'interaction') {
            $query->where(function ($inner) {
                $inner->whereIn('subject_type', [
                    'App\\Models\\PostLike',
                    'App\\Models\\CommentLike',
                    'App\\Models\\UserStarredDisease',
                ])->orWhere('action', 'like', '%like%')
                  ->orWhere('action', 'like', '%star%')
                  ->orWhere('context', 'like', '%like%')
                  ->orWhere('context', 'like', '%star%');
            });
            return;
        }

        $query->where('category', $type);
    }

    private function subjectUrl(ActivityLog $log): ?string
    {
        return match ($log->subject_type) {
            User::class => $log->subject_id ? route('admin.users.show', $log->subject_id) : null,
            Post::class => $log->subject_id ? route('community.posts.show', $log->subject_id) : null,
            Comment::class => $log->subject_id ? route('community.posts.index', ['focus_comment' => $log->subject_id]) : null,
            Disease::class => $log->subject_id ? route('public.disease.show', $log->subject_id) : null,
            default => null,
        };
    }

    private function subjectLabel(ActivityLog $log): string
    {
        if (!$log->subject_type) {
            return 'system';
        }

        $name = strtolower(class_basename($log->subject_type));

        if ($log->subject_id) {
            return sprintf('%s #%s', $name, (string) $log->subject_id);
        }

        return $name;
    }
}
