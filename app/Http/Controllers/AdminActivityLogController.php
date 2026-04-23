<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Disease;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Carbon\Carbon;

class AdminActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request): View|Response
    {
        $type = trim((string) $request->query('type', 'all'));
        $search = trim((string) $request->query('q', ''));

        // Date/time filter params
        $startDate = trim((string) $request->query('start_date', ''));
        $endDate = trim((string) $request->query('end_date', ''));
        $startTime = trim((string) $request->query('start_time', ''));
        $endTime = trim((string) $request->query('end_time', ''));

        $allowedTypes = array_keys($this->activityTypes());
        if (!in_array($type, $allowedTypes, true)) {
            $type = 'all';
        }

        $logsQuery = ActivityLog::query()
            ->with('user')
            // apply date/time filters
            ->when($startDate !== '', function ($query) use ($startDate, $startTime) {
                try {
                    $time = $startTime !== '' ? $startTime : '00:00';
                    $dt = Carbon::createFromFormat('Y-m-d H:i', sprintf('%s %s', $startDate, $time));
                    $query->where('created_at', '>=', $dt);
                } catch (\Throwable $e) {
                }
            })
            ->when($endDate !== '', function ($query) use ($endDate, $endTime) {
                try {
                    $time = $endTime !== '' ? $endTime : '23:59';
                    $dt = Carbon::createFromFormat('Y-m-d H:i', sprintf('%s %s', $endDate, $time));
                    $query->where('created_at', '<=', $dt);
                } catch (\Throwable $e) {
                }
            })
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

        // If download requested, export as TXT
        if ((string) $request->query('download', '') === 'txt') {
            $items = $logsQuery->orderByDesc('created_at')->get();
            // determine from/to timestamps for filename
            try {
                $fromDt = $startDate !== '' ? Carbon::createFromFormat('Y-m-d H:i', sprintf('%s %s', $startDate, $startTime !== '' ? $startTime : '00:00')) : ($items->last()->created_at ?? now());
            } catch (\Throwable $e) {
                $fromDt = $items->last()->created_at ?? now();
            }
            try {
                $toDt = $endDate !== '' ? Carbon::createFromFormat('Y-m-d H:i', sprintf('%s %s', $endDate, $endTime !== '' ? $endTime : '23:59')) : ($items->first()->created_at ?? now());
            } catch (\Throwable $e) {
                $toDt = $items->first()->created_at ?? now();
            }

            $from = $fromDt->format('Ymd_Hi');
            $to = $toDt->format('Ymd_Hi');
            $filename = sprintf('activitylog_mydoctor_from_%s_to_%s.txt', $from, $to);

            $content = $items->map(function ($log) {
                $time = optional($log->created_at)->format('Y-m-d H:i:s');
                $user = $log->user?->name ?? 'system';
                $cat = $log->category ?? 'n/a';
                $act = $log->action ?? '';
                $desc = trim((string) $log->description);
                return sprintf("[%s] %s | %s/%s | %s", $time, $user, $cat, $act, $desc);
            })->join("\n");

            return response($content, 200, [
                'Content-Type' => 'text/plain; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        $logs = $logsQuery->paginate(100)->withQueryString();
        $this->enrichLogs($logs, true);

        return view('admin.logs', [
            'logs' => $logs,
            'type' => $type,
            'search' => $search,
            'activityTypes' => $this->activityTypes(),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'startTime' => $startTime,
            'endTime' => $endTime,
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
                ])->orWhere('action', 'like', '%like%')
                  ->orWhere('action', 'like', '%star%')
                  ->orWhere('context', 'like', '%like%')
                  ->orWhere('context', 'like', '%star%');
            });
            return;
        }

        $query->where('category', $type);
    }

    private function enrichLogs(LengthAwarePaginator $logs, bool $adminContext): void
    {
        $collection = $logs->getCollection();

        // Group subject ids by subject_type so we can fetch their display names in bulk
        $subjectGroups = $collection
            ->filter(fn($l) => !empty($l->subject_type) && $l->subject_id)
            ->groupBy('subject_type');

        $nameMaps = [];

        foreach ($subjectGroups as $type => $items) {
            $ids = collect($items)->pluck('subject_id')->filter()->unique()->values()->all();
            if (empty($ids)) {
                continue;
            }

            if (!class_exists($type)) {
                continue;
            }

            try {
                $models = $type::withoutGlobalScopes()->whereIn('id', $ids)->get();
            } catch (\Throwable $e) {
                continue;
            }

            $map = $models->mapWithKeys(function ($m) {
                return [$m->id => $this->labelForModel($m)];
            });

            $nameMaps[$type] = $map;
        }

        $collection->transform(function (ActivityLog $log) use ($adminContext, $nameMaps) {
            $log->subject_url = $this->subjectUrl($log, $adminContext);
            $log->subject_label = $this->subjectLabel($log, $nameMaps);
            return $log;
        });
    }

    private function subjectUrl(ActivityLog $log, bool $adminContext): ?string
    {
        return match ($log->subject_type) {
            User::class => $log->subject_id
                ? ($adminContext ? route('admin.users.show', $log->subject_id) : route('users.show', $log->subject_id))
                : null,
            Post::class => $log->subject_id ? route('community.posts.show', $log->subject_id) : null,
            Comment::class => $log->subject_id ? route('community.posts.index', ['focus_comment' => $log->subject_id]) : null,
            Disease::class => $log->subject_id ? route('public.disease.show', $log->subject_id) : null,
            default => null,
        };
    }

    private function subjectLabel(ActivityLog $log, $nameMaps): string
    {
        if (!$log->subject_type) {
            return 'system';
        }
        $type = $log->subject_type;

        if (!empty($type) && isset($nameMaps[$type]) && $log->subject_id) {
            $map = $nameMaps[$type];
            $label = $map->get((int) $log->subject_id);
            if ($label) {
                return (string) $label;
            }
        }

        if ($type === User::class && $log->subject_id) {
            return sprintf('User #%s', (string) $log->subject_id);
        }

        $name = strtolower(class_basename($type));

        if ($log->subject_id) {
            return sprintf('%s #%s', $name, (string) $log->subject_id);
        }

        return $name;
    }

    private function labelForModel($model): string
    {
        $candidates = ['name', 'title', 'label', 'display_name', 'title_en', 'slug'];

        foreach ($candidates as $attr) {
            if (isset($model->{$attr}) && $model->{$attr} !== null && $model->{$attr} !== '') {
                return (string) $model->{$attr};
            }
        }

        // Fallback to class basename and id
        return sprintf('%s #%s', strtolower(class_basename($model)), (string) $model->id);
    }
}
