<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
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
        $category = trim((string) $request->query('category', ''));
        $search = trim((string) $request->query('q', ''));
        $sort = (string) $request->query('sort', 'recent');

        $logsQuery = ActivityLog::query()
            ->with('user')
            ->when($category !== '' && $category !== 'all', function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('action', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('route_name', 'like', "%{$search}%")
                        ->orWhere('subject_type', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            });

        if ($sort === 'category') {
            $logsQuery->orderBy('category')->orderByDesc('updated_at');
        } elseif ($sort === 'oldest') {
            $logsQuery->orderBy('updated_at');
        } else {
            $logsQuery->orderByDesc('updated_at');
        }

        $logs = $logsQuery->paginate(50)->withQueryString();

        $categories = ActivityLog::query()
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->values();

        return view('admin.logs', [
            'logs' => $logs,
            'category' => $category,
            'search' => $search,
            'sort' => $sort,
            'categories' => $categories,
        ]);
    }
}
