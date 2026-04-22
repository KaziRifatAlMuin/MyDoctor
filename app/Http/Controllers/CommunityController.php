<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use App\Models\Disease;
use App\Models\User;
use App\Models\PostLike;
use App\Models\CommentLike;
use App\Models\Notification;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CommunityController extends Controller
{
    /**
     * Community landing page with disease cards.
     */
    public function home()
    {
        $userStarredDiseaseIds = Auth::check()
            ? Auth::user()->getStarredDiseaseIds()
            : [];

        $diseases = $this->buildDiseaseCollectionWithCounts(
            fn ($q) => $q->where('is_approved', true),
            $userStarredDiseaseIds
        );

        $totalPosts = Post::where('is_approved', true)->count();
        $totalDiseases = $diseases->count();

        return view('community.pages.home', compact('diseases', 'totalPosts', 'totalDiseases', 'userStarredDiseaseIds'));
    }

    /**
     * Canonical posts feed route.
     */
    public function postsIndex(Request $request)
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.community.posts.index');
        }

        return $this->index($request);
    }

    /**
     * Admin community posts feed.
     */
    public function adminPostsIndex(Request $request)
    {
        $request->merge(['admin_community' => true]);

        return $this->index($request);
    }

    /**
     * Admin reported posts feed - shows ALL reported posts.
     */
    public function adminReportedPosts(Request $request)
    {
        // Return an admin-specific reported posts view so admins see
        // the moderation UI (approve/clear/delete) similar to pending posts.
        try {
            $diseaseId = $request->get('disease');

            $query = Post::with(['user', 'diseases', 'comments' => function ($q) {
                $q->with('user')->latest()->limit(3);
            }])->withCount(['likes as likes_count']);

            $query->where('is_reported', true);

            $this->applyDiseaseFilter($query, $diseaseId);

            $posts = $query->latest()->paginate(10)->withQueryString();

            $diseases = $this->buildDiseaseCollectionWithCounts(
                fn ($q) => $q->where('is_reported', true)
            );

            $totalPosts = (clone $query)->count();
            $totalUsers = User::count();
            $totalComments = Comment::whereHas('post', function ($postQuery) {
                $postQuery->where('is_reported', true);
            })->count();
            $activeToday = User::whereDate('updated_at', today())->count() ?: 0;

            $trendingDiseases = $diseases
              ->filter(function ($disease) {
                  return (int) $disease->posts_count > 0;
              })
              ->sortByDesc('posts_count')
              ->take(5)
              ->values();

            return view('admin.reported-posts', compact(
                'posts',
                'diseases',
                'diseaseId',
                'totalPosts',
                'totalUsers',
                'totalComments',
                'activeToday',
                'trendingDiseases'
            ));
        } catch (\Exception $e) {
            Log::error('Admin Reported Posts Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return view('admin.reported-posts', [
                'posts' => new LengthAwarePaginator([], 0, 10),
                'diseases' => collect([]),
                'diseaseId' => null,
                'totalPosts' => 0,
                'totalUsers' => 0,
                'totalComments' => 0,
                'activeToday' => 0,
                'trendingDiseases' => collect([]),
                'error' => 'Error loading reported posts: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * User's own reported posts feed - shows posts reported by others that belong to the user.
     */
    public function userReportedPosts(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        try {
            $diseaseId = $request->get('disease');
            $userId = Auth::id();
            $isAdmin = Auth::user()->isAdmin();

            $query = Post::with(['user', 'diseases', 'comments' => function ($q) {
                $q->with('user')->latest()->limit(3);
            }])->withCount(['likes as likes_count'])
                ->where('is_reported', true);

            if (!$isAdmin) {
                $query->where('user_id', $userId);
            }

            $this->applyDiseaseFilter($query, $diseaseId);

            $posts = $query->latest()->paginate(10)->withQueryString();

            $diseases = $this->buildDiseaseCollectionWithCounts(
                function ($q) use ($userId, $isAdmin) {
                    if (!$isAdmin) {
                        $q->where('user_id', $userId);
                    }
                    $q->where('is_reported', true);
                }
            );

            $totalPosts = (clone $query)->count();
            $totalUsers = User::count();
            $totalComments = Comment::whereHas('post', function ($postQuery) use ($userId, $isAdmin) {
                if (!$isAdmin) {
                    $postQuery->where('user_id', $userId);
                }
                $postQuery->where('is_reported', true);
            })->count();
            $activeToday = User::whereDate('updated_at', today())->count() ?: 0;

            $trendingDiseases = $diseases
              ->filter(function ($disease) {
                  return (int) $disease->posts_count > 0;
              })
              ->sortByDesc('posts_count')
              ->take(5)
              ->values();

            return view('community.pages.index', [
                'posts' => $posts,
                'diseases' => $diseases,
                'diseaseId' => $diseaseId,
                'totalPosts' => $totalPosts,
                'totalUsers' => $totalUsers,
                'totalComments' => $totalComments,
                'activeToday' => $activeToday,
                'trendingDiseases' => $trendingDiseases,
                'isReportedPage' => true,
                'isAdminCommunity' => false,
                'showReported' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Community Reported Posts Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return view('community.pages.index', [
                'posts' => new LengthAwarePaginator([], 0, 10),
                'diseases' => collect([]),
                'diseaseId' => null,
                'totalPosts' => 0,
                'totalUsers' => 0,
                'totalComments' => 0,
                'activeToday' => 0,
                'trendingDiseases' => collect([]),
                'isReportedPage' => true,
                'isAdminCommunity' => false,
                'showReported' => true,
                'error' => 'Error loading reported posts: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Disease-specific posts feed route.
     */
    public function diseasePosts(Request $request, Disease $disease)
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.community.posts.index', ['disease' => $disease->id]);
        }

        $request->merge(['disease' => $disease->id]);

        return $this->index($request);
    }

    /**
     * Display the forum page with posts and disease filter
     */
    public function index(Request $request)
    {
        try {
            $diseaseId = $request->get('disease');
            $showReported = (bool) $request->boolean('reported', false);
            $isAdminCommunity = (bool) $request->boolean('admin_community', false)
                && Auth::check()
                && Auth::user()->isAdmin();
            $userStarredDiseaseIds = Auth::check()
                ? Auth::user()->getStarredDiseaseIds()
                : [];
            
            // Build the posts query with eager loading
            $query = Post::with(['user', 'diseases', 'comments' => function($q) {
                $q->with('user')->latest()->limit(3);
            }])->withCount(['likes as likes_count' => function ($q) {
                $q->where('is_starred', false);
            }]);
            
            // Apply filters based on page type
            if ($showReported) {
                // Show reported posts
                $query->where('is_reported', true);
                if (!$isAdminCommunity) {
                    // Regular users see only their own reported posts
                    $query->where('user_id', Auth::id());
                }
            } else {
                // Normal approved posts
                $query->where('is_approved', true);
            }

            // Apply disease filter if selected
            $this->applyDiseaseFilter($query, $diseaseId);

            // Prioritize posts matching the current user's diseases, then newest by approval time.
            if (!empty($userStarredDiseaseIds) && !$showReported) {
                $ids = implode(',', array_map('intval', $userStarredDiseaseIds));
                $this->applyStarredDiseaseOrder($query, $userStarredDiseaseIds);
            }

            // Order by approval time (most recently approved first), then fallback to creation time.
            $query->orderByDesc('approved_at')->orderByDesc('created_at');
            
            $posts = $query->paginate(10);
            
            // Get all diseases with post counts for the filter sidebar
            $diseases = $this->buildDiseaseCollectionWithCounts(
                function ($q) use ($showReported, $isAdminCommunity) {
                    if ($showReported) {
                        $q->where('is_reported', true);
                        if (!$isAdminCommunity && Auth::check()) {
                            $q->where('user_id', Auth::id());
                        }
                    } else {
                        $q->where('is_approved', true);
                    }
                },
                $showReported ? [] : $userStarredDiseaseIds
            );
            
            // If diseases table is empty, log a warning
            if ($diseases->isEmpty()) {
                Log::warning('No diseases found in database');
            }
            
            // Additional stats for right sidebar
            if ($showReported) {
                $totalPosts = Post::where('is_reported', true)
                    ->when(!$isAdminCommunity && Auth::check(), function($q) {
                        $q->where('user_id', Auth::id());
                    })
                    ->count();
                $totalComments = Comment::whereHas('post', function($q) use ($showReported, $isAdminCommunity) {
                    $q->where('is_reported', true);
                    if (!$isAdminCommunity && Auth::check()) {
                        $q->where('user_id', Auth::id());
                    }
                })->count();
            } else {
                $totalPosts = Post::where('is_approved', true)->count();
                $totalComments = Comment::count();
            }
            
            $totalUsers = User::count();
            
            // Get active users today 
            $activeToday = User::whereDate('updated_at', today())->count() ?: 0;
            
            // Get trending diseases (with at least one post)
            $trendingDiseases = $diseases
               ->filter(function ($disease) {
                   return (int) $disease->posts_count > 0;
               })
               ->sortByDesc('posts_count')
               ->take(5)
               ->values();

            $pendingPreviewPosts = collect();
            if ($isAdminCommunity && !$showReported) {
                $pendingPreviewPosts = Post::with(['user', 'diseases'])
                    ->where('is_approved', false)
                    ->latest()
                    ->take(3)
                    ->get();
            }
            
            // Log for debugging
            Log::info('Community index loaded', [
                'posts_count' => $posts->total(),
                'diseases_count' => $diseases->count(),
                'diseaseId' => $diseaseId,
                'showReported' => $showReported
            ]);
            
            if ($diseaseId) {
                try {
                    Log::info('Community index post ids for filter', ['disease_id' => $diseaseId, 'post_ids' => $posts->getCollection()->pluck('id')->values()->all()]);
                } catch (\Throwable $e) {
                    Log::warning('Failed to log post ids: ' . $e->getMessage());
                }
            }
            return view('community.pages.index', compact(
                'posts', 
                'diseases', 
                'diseaseId', 
                'totalPosts', 
                'totalUsers', 
                'totalComments', 
                'activeToday', 
                'trendingDiseases',
                'isAdminCommunity',
                'pendingPreviewPosts',
                'showReported'
            ));
        } catch (\Exception $e) {
            Log::error('Community Index Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return with empty paginator
            return view('community.pages.index', [
                'posts' => new LengthAwarePaginator([], 0, 10),
                'diseases' => collect([]),
                'diseaseId' => null,
                'totalPosts' => 0,
                'totalUsers' => 0,
                'totalComments' => 0,
                'activeToday' => 0,
                'trendingDiseases' => collect([]),
                'isAdminCommunity' => (bool) $request->boolean('admin_community', false),
                'pendingPreviewPosts' => collect([]),
                'showReported' => false,
                'error' => 'Error loading community: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display posts starred by the current user.
     */
    public function starredPosts(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        try {
            $diseaseId = $request->get('disease');
            $userId = Auth::id();

            $query = Post::with(['user', 'diseases', 'comments' => function($q) {
                $q->with('user')->latest()->limit(3);
            }])->withCount(['likes as likes_count'])
                ->where('is_approved', true)
                ->whereHas('likes', function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                        ->where('is_starred', true);
                });

            $this->applyDiseaseFilter($query, $diseaseId);

            $posts = $query->orderByDesc('approved_at')->orderByDesc('created_at')->paginate(10)->withQueryString();

            $diseases = $this->buildDiseaseCollectionWithCounts(
                function ($q) use ($userId) {
                    $q->where('is_approved', true)
                        ->whereHas('likes', function ($likeQuery) use ($userId) {
                            $likeQuery->where('user_id', $userId)
                                      ->where('is_starred', true);
                        });
                }
            );

            $totalPosts = (clone $query)->count();
            $totalUsers = User::count();
            $totalComments = Comment::whereHas('post', function ($postQuery) {
                $postQuery->where('is_approved', true);
            })->whereHas('post.likes', function ($q) use ($userId) {
                $q->where('user_id', $userId)->where('is_starred', true);
            })->count();
            $activeToday = User::whereDate('updated_at', today())->count() ?: 0;

            $trendingDiseases = $diseases
              ->filter(function ($disease) {
                  return (int) $disease->posts_count > 0;
              })
              ->sortByDesc('posts_count')
              ->take(5)
              ->values();

            return view('community.pages.index', [
                'posts' => $posts,
                'diseases' => $diseases,
                'diseaseId' => $diseaseId,
                'totalPosts' => $totalPosts,
                'totalUsers' => $totalUsers,
                'totalComments' => $totalComments,
                'activeToday' => $activeToday,
                'trendingDiseases' => $trendingDiseases,
                'isStarredPage' => true,
                'showReported' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Community Starred Posts Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return view('community.pages.index', [
                'posts' => new LengthAwarePaginator([], 0, 10),
                'diseases' => collect([]),
                'diseaseId' => null,
                'totalPosts' => 0,
                'totalUsers' => 0,
                'totalComments' => 0,
                'activeToday' => 0,
                'trendingDiseases' => collect([]),
                'isStarredPage' => true,
                'showReported' => false,
                'error' => 'Error loading starred posts: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Display posts waiting for approval created by the current user.
     */
    public function pendingPosts(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->isAdmin() && !(bool) $request->boolean('admin_community', false)) {
            return redirect()->route('admin.community.posts.pending');
        }

        try {
            $diseaseId = $request->get('disease');
            $userId = Auth::id();
            $isAdmin = Auth::user()->isAdmin();
            $isAdminCommunity = (bool) $request->boolean('admin_community', false) && $isAdmin;

            $query = Post::with(['user', 'diseases', 'comments' => function ($q) {
                $q->with('user')->latest()->limit(3);
            }])->withCount(['likes as likes_count'])
                ->where('is_approved', false);

            if (!$isAdmin) {
                $query->where('user_id', $userId);
            }

            $this->applyDiseaseFilter($query, $diseaseId);

            $posts = $query->latest()->paginate(10)->withQueryString();

            $diseases = $this->buildDiseaseCollectionWithCounts(
                function ($q) use ($userId, $isAdmin) {
                    if (!$isAdmin) {
                        $q->where('user_id', $userId);
                    }
                    $q->where('is_approved', false);
                }
            );

            $totalPosts = (clone $query)->count();
            $totalUsers = User::count();
            $totalComments = Comment::whereHas('post', function ($postQuery) use ($userId, $isAdmin) {
                if (!$isAdmin) {
                    $postQuery->where('user_id', $userId);
                }
                $postQuery->where('is_approved', false);
            })->count();
            $activeToday = User::whereDate('updated_at', today())->count() ?: 0;

            $trendingDiseases = $diseases
              ->filter(function ($disease) {
                  return (int) $disease->posts_count > 0;
              })
              ->sortByDesc('posts_count')
              ->take(5)
              ->values();

            return view('community.pages.index', [
                'posts' => $posts,
                'diseases' => $diseases,
                'diseaseId' => $diseaseId,
                'totalPosts' => $totalPosts,
                'totalUsers' => $totalUsers,
                'totalComments' => $totalComments,
                'activeToday' => $activeToday,
                'trendingDiseases' => $trendingDiseases,
                'isPendingPage' => true,
                'isAdminCommunity' => $isAdminCommunity,
                'showReported' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Community Pending Posts Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return view('community.pages.index', [
                'posts' => new LengthAwarePaginator([], 0, 10),
                'diseases' => collect([]),
                'diseaseId' => null,
                'totalPosts' => 0,
                'totalUsers' => 0,
                'totalComments' => 0,
                'activeToday' => 0,
                'trendingDiseases' => collect([]),
                'isPendingPage' => true,
                'isAdminCommunity' => (bool) $request->boolean('admin_community', false),
                'showReported' => false,
                'error' => 'Error loading pending posts: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Admin-only pending posts feed.
     */
    public function adminPendingPosts(Request $request)
    {
        $request->merge(['admin_community' => true]);

        return $this->pendingPosts($request);
    }

    /**
     * Display the landing page (redirects to forum if logged in)
     */
    public function landing()
    {
        if (Auth::check()) {
            return redirect()->route('community.posts.index');
        }
        return view('community.pages.landing');
    }

    /**
     * Display a single post with all comments
     */
    public function showPost(Post $post)
    {
        try {
            $isAdminCommunity = false;

            // Check if user can access this post (unapproved or rejected)
            $canAccess = $post->is_approved || $this->canAccessUnapprovedPost($post) || $post->rejected_at !== null;

            if (!$canAccess) {
                abort(403, 'This post is not accessible.');
            }

            $post->load(['user', 'disease', 'comments' => function($q) {
                $q->with('user')->withCount(['likes as likes_count'])->latest();
            }])->loadCount(['likes as likes_count']);
            
            return view('community.pages.show', [
                'post' => $post,
                'isAdminCommunity' => $isAdminCommunity,
            ]);
        } catch (\Exception $e) {
            Log::error('Show Post Error: ' . $e->getMessage());
            return back()->with('error', 'Error loading post');
        }
    }

    /**
     * Return post HTML for modal (with all comments)
     */
    public function modalPost(Post $post)
    {
        try {
            $isAdminCommunity = request()->routeIs('admin.community.*')
                || ((bool) request()->boolean('admin_community', false)
                    && Auth::check()
                    && Auth::user()->isAdmin());

            // Check if user can access this post (unapproved or rejected)
            $canAccess = $post->is_approved || $this->canAccessUnapprovedPost($post) || $post->rejected_at !== null;

            if (!$canAccess) {
                return response()->json(['error' => 'This post is not accessible.'], 403);
            }

            // Load the post with all comments and necessary relationships
            $post->load([
                'user', 
                'disease', 
                'comments' => function($q) {
                    $q->with(['user', 'likes'])
                      ->withCount('likes')
                      ->latest();
                }
            ])->loadCount(['likes as likes_count']);
            
            // Return the modal post view
            return view('community.pages.modal-post', [
                'post' => $post,
                'adminReadOnlyCommunity' => $isAdminCommunity,
            ]);
        } catch (\Exception $e) {
            Log::error('Modal Post Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load post'], 500);
        }
    }

    /**
     * API endpoint to load posts (returns JSON)
     */
    public function loadPosts(Request $request)
    {
        try {
            $diseaseId = $request->get('disease');
            
            $query = Post::with(['user', 'diseases'])
                ->withCount(['likes as likes_count' => function ($q) {
                    $q->where('is_starred', false);
                }])
                ->where('is_approved', true);

            if (Auth::check() && !Auth::user()->isAdmin()) {
                $userStarredDiseaseIds = Auth::user()->getStarredDiseaseIds();
                if (!empty($userStarredDiseaseIds)) {
                    $this->applyStarredDiseaseOrder($query, $userStarredDiseaseIds);
                }
            }

            $this->applyDiseaseFilter($query, $diseaseId);

            $posts = $query->latest()->get();
            
            $formattedPosts = $posts->map(function($post) {
                return [
                    'id' => $post->id,
                    'description' => $post->description,
                    'is_anonymous' => (bool) $post->is_anonymous,
                    'is_approved' => (bool) $post->is_approved,
                    'is_edited' => (bool) $post->is_edited,
                    'is_reported' => (bool) $post->is_reported,
                    'files' => $post->all_files,
                    'file_count' => $post->file_count,
                    'total_size' => $post->formatted_total_size,
                    'like_count' => $post->likes_count,
                    'comment_count' => $post->comment_count,
                    'created_at' => $post->created_at,
                    'user' => [
                        'id' => $post->user->id,
                        'name' => $post->is_anonymous ? 'Anonymous Member' : $post->user->name,
                        'email' => $post->user->email,
                        'avatar' => $post->user->picture ? Storage::url($post->user->picture) : null,
                        'created_at' => $post->user->created_at,
                    ],
                    'disease' => $post->disease ? [
                        'id' => $post->disease->id,
                        'name' => $post->disease->display_name,
                        'raw_name' => $post->disease->disease_name,
                        'bangla_name' => $post->disease->bangla_name,
                    ] : null,
                    'diseases' => $post->disease_models
                        ->map(fn (Disease $disease) => [
                            'id' => $disease->id,
                            'name' => $disease->display_name,
                            'raw_name' => $disease->disease_name,
                            'bangla_name' => $disease->bangla_name,
                        ])->values(),
                    'user_liked' => Auth::check() ? $post->likes()->where('user_id', Auth::id())->where('is_starred', false)->exists() : false,
                    'is_owner' => Auth::check() && $post->user_id === Auth::id(),
                ];
            });

            return response()->json([
                'success' => true,
                'posts' => $formattedPosts
            ]);
        } catch (\Exception $e) {
            Log::error('Load Posts API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading posts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint to load comments for a post (returns JSON)
     */
    public function loadComments(Request $request, Post $post)
    {
        try {
            $comments = $post->comments()
                ->with('user')
                ->withCount(['likes as likes_count'])
                ->latest()
                ->get();

            $formattedComments = $comments->map(function($comment) {
                // Determine file icon based on mime type
                $fileIcon = 'fa-file-alt';
                if ($comment->file_type) {
                    if (str_contains($comment->file_type, 'pdf')) $fileIcon = 'fa-file-pdf';
                    elseif (str_contains($comment->file_type, 'word')) $fileIcon = 'fa-file-word';
                    elseif (str_contains($comment->file_type, 'excel')) $fileIcon = 'fa-file-excel';
                    elseif (str_contains($comment->file_type, 'image')) $fileIcon = 'fa-file-image';
                    elseif (str_contains($comment->file_type, 'video')) $fileIcon = 'fa-file-video';
                    elseif (str_contains($comment->file_type, 'audio')) $fileIcon = 'fa-file-audio';
                }
                
                return [
                    'id' => $comment->id,
                    'comment_details' => $comment->comment_details,
                    'file_url' => $comment->file_path ? Storage::url($comment->file_path) : null,
                    'file_type' => $comment->file_type,
                    'file_name' => $comment->file_name,
                    'file_size' => $comment->file_size,
                    'file_icon' => $fileIcon,
                    'formatted_file_size' => $comment->file_size ? number_format($comment->file_size / 1024, 1) . ' KB' : null,
                    'like_count' => $comment->likes_count,
                    'created_at' => $comment->created_at,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'avatar' => $comment->user->picture ? Storage::url($comment->user->picture) : null,
                    ],
                    'user_liked' => Auth::check() ? $comment->likes()->where('user_id', Auth::id())->exists() : false,
                    'is_owner' => Auth::check() && $comment->user_id === Auth::id(),
                ];
            });

            return response()->json([
                'success' => true,
                'comments' => $formattedComments
            ]);
        } catch (\Exception $e) {
            Log::error('Load Comments API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading comments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Load more comments for a post (pagination)
     */
    public function loadMoreComments(Request $request, Post $post)
    {
        try {
            $offset = $request->get('offset', 0);
            $limit = 10;

            $comments = $post->comments()
                ->with('user')
                ->withCount(['likes as likes_count'])
                ->latest()
                ->skip($offset)
                ->take($limit)
                ->get();

            $remaining = max(0, $post->comment_count - ($offset + $comments->count()));

            // Generate HTML for each comment
            $html = '';
            foreach ($comments as $comment) {
                $html .= view('community.partials.comment', ['comment' => $comment])->render();
            }

            return response()->json([
                'success' => true,
                'comments' => $comments,
                'html' => $html,
                'has_more' => $comments->count() === $limit,
                'remaining' => $remaining
            ]);
        } catch (\Exception $e) {
            Log::error('Load More Comments Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading comments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user details for modal
     */
    public function getUserDetails($userId)
    {
        try {
            $user = User::withCount(['posts', 'comments'])->findOrFail($userId);
            
            // Get user's recent activity
            $recentPosts = Post::where('user_id', $userId)
                               ->latest()
                               ->take(3)
                               ->get(['id', 'description', 'created_at']);
            
            $recentComments = Comment::where('user_id', $userId)
                                     ->with('post:id')
                                     ->latest()
                                     ->take(3)
                                     ->get(['id', 'comment_details', 'post_id', 'created_at']);
            
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->picture ? Storage::url($user->picture) : null,
                    'bio' => $user->bio ?? 'No bio provided',
                    'location' => $user->location ?? 'Not specified',
                    'joined' => $user->created_at->format('M Y'),
                    'posts_count' => $user->posts_count,
                    'comments_count' => $user->comments_count,
                    'recent_posts' => $recentPosts->map(function($post) {
                        return [
                            'id' => $post->id,
                            'description' => strlen($post->description) > 50 ? substr($post->description, 0, 50) . '...' : $post->description,
                            'created_at' => $post->created_at->diffForHumans(),
                        ];
                    }),
                    'recent_comments' => $recentComments->map(function($comment) {
                        return [
                            'id' => $comment->id,
                            'comment_details' => strlen($comment->comment_details) > 50 ? substr($comment->comment_details, 0, 50) . '...' : $comment->comment_details,
                            'post_id' => $comment->post_id,
                            'created_at' => $comment->created_at->diffForHumans(),
                        ];
                    }),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Get User Details Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading user details'
            ], 500);
        }
    }

    /**
     * Store a new post with multiple files
     */
    public function storePost(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to create a post'
                ], 401);
            }

            if (Auth::user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin accounts are view-only in community.'
                ], 403);
            }

            $request->validate([
                'disease_ids' => 'required|array|min:1',
                'disease_ids.*' => 'integer|exists:diseases,id',
                'description' => 'nullable|string|max:5000',
                'is_anonymous' => 'nullable|boolean',
                'files.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,mp4,mp3,pdf,doc,docx,xls,xlsx,ppt,pptx,txt',
            ]);

            $diseaseIds = collect($request->input('disease_ids'))
                ->map(fn ($id) => (int) $id)
                ->filter(fn (int $id) => $id > 0)
                ->unique()
                ->values();

            if ($diseaseIds->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select at least one disease tag'
                ], 422);
            }

            // Check if at least one file or description exists
            if (!$request->description && !$request->hasFile('files')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please write something or attach at least one file'
                ], 422);
            }

            $data = [
                'user_id' => Auth::id(),
                'description' => $request->description ?? '',
                'is_anonymous' => $request->boolean('is_anonymous'),
                'is_approved' => false,
                'is_edited' => false,
                'is_reported' => false,
                'like_count' => 0,
                'comment_count' => 0,
            ];

            // Handle multiple file uploads
            $uploadedFiles = [];
            $totalSize = 0;
            
            if ($request->hasFile('files')) {
                $files = $request->file('files');
                
                // Check total size (max 50MB total)
                foreach ($files as $file) {
                    $totalSize += $file->getSize();
                }
                
                if ($totalSize > 50 * 1024 * 1024) { // 50MB total limit
                    return response()->json([
                        'success' => false,
                        'message' => 'Total file size cannot exceed 50MB'
                    ], 422);
                }
                
                foreach ($files as $index => $file) {
                    try {
                        $path = $file->store('community/posts/' . Auth::id(), 'public');
                        
                        $uploadedFiles[] = [
                            'path' => $path,
                            'type' => $file->getMimeType(),
                            'name' => $file->getClientOriginalName(),
                            'size' => $file->getSize(),
                        ];
                    } catch (\Exception $e) {
                        Log::error('File upload error: ' . $e->getMessage());
                        return response()->json([
                            'success' => false,
                            'message' => 'Error uploading file: ' . $file->getClientOriginalName()
                        ], 422);
                    }
                }
            }

            // For backward compatibility, store first file in old columns
            if (!empty($uploadedFiles)) {
                $firstFile = $uploadedFiles[0];
                $data['file_path'] = $firstFile['path'];
                $data['file_type'] = $firstFile['type'];
                $data['file_name'] = $firstFile['name'];
                $data['file_size'] = $firstFile['size'];
                $data['files'] = $uploadedFiles; // Store all files in JSON
            }

            $post = Post::create($data);
            $post->diseases()->attach($diseaseIds);
            $post->load(['user', 'diseases']);

            return response()->json([
                'success' => true,
                'post' => $post,
                'html' => null,
                'requires_approval' => true,
                'message' => 'Post submitted for admin approval.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Store Post Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating post: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing post
     */
    public function updatePost(Request $request, Post $post)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login'
                ], 401);
            }

            $isOwner = Auth::id() === $post->user_id;
            $isAdmin = Auth::user()->isAdmin();

            if (!$isOwner && !$isAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only edit your own posts unless you are an admin'
                ], 403);
            }

            $this->authorize('update', $post);

            $request->validate([
                'description' => 'required|string|max:5000',
            ]);

            $post->update([
                'description' => $request->input('description'),
                'is_edited' => true,
            ]);

            return response()->json([
                'success' => true,
                'post' => [
                    'id' => $post->id,
                    'description' => $post->description,
                    'is_edited' => (bool) $post->is_edited,
                ],
                'message' => 'Post updated successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Update Post Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating post: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a post and its files - Clean up notifications
     */
    public function destroyPost(Request $request, Post $post)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login'
                ], 401);
            }

            $isOwner = Auth::id() === $post->user_id;
            $isAdmin = Auth::user()->isAdmin();

            if (!$isOwner && !$isAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own posts unless you are an admin'
                ], 403);
            }

            $this->authorize('delete', $post);

            // Send deletion notification to post author if deleted by admin
            if (Auth::user()->isAdmin() && $post->user_id !== Auth::id()) {
                $postPreview = strlen($post->description) > 100 
                    ? substr($post->description, 0, 100) . '...' 
                    : $post->description;

                $messageText = "Your post (#{$post->id}) was deleted by \n " . Auth::user()->name . ". " ;
                $messageText .= "\n\n";
                   if ($postPreview) {
        $messageText .= "Preview:\n\n [ " . $postPreview . " ]";  
    }

                Notification::create([
                    'user_id' => $post->user_id,
                    'from_user_id' => Auth::id(),
                    'type' => 'post_deleted',
                    'notifiable_type' => Post::class,
                    'notifiable_id' => $post->id,
                    'message' => $messageText,
                    'data' => [
                        'type' => 'post_deleted',
                        'post_id' => $post->id,
                        'post_preview' => $postPreview,
                        'message' => $messageText,
                        'action_url' => route('users.show', $post->user_id),
                        'deleted_by' => Auth::user()->name,
                        'deleted_at' => now()->toISOString(),
                        'from_user_id' => Auth::id(),
                        'from_user_name' => Auth::user()->name,
                    ],
                ]);
            }

            // Delete all files
            if ($post->files && is_array($post->files)) {
                foreach ($post->files as $file) {
                    try {
                        Storage::disk('public')->delete($file['path']);
                    } catch (\Exception $e) {
                        Log::warning('File deletion error: ' . $e->getMessage());
                    }
                }
            } elseif ($post->file_path) {
                try {
                    Storage::disk('public')->delete($post->file_path);
                } catch (\Exception $e) {
                    Log::warning('File deletion error: ' . $e->getMessage());
                }
            }

            // Delete all related notifications before deleting the post (but preserve post_deleted notification)
            Notification::where('notifiable_type', Post::class)
                ->where('notifiable_id', $post->id)
                ->where('type', '!=', 'post_deleted')
                ->delete();

            $post->delete();

            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Destroy Post Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting post: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle like on a post
     */
    public function togglePostLike(Request $request, Post $post)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to like posts'
                ], 401);
            }

            if (Auth::user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin accounts cannot react to posts.'
                ], 403);
            }

            $user = Auth::user();
            $existingLike = PostLike::where('post_id', $post->id)
                                    ->where('user_id', $user->id)
                                    ->first();

            if (!$existingLike) {
                PostLike::create([
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                    'is_starred' => false,
                ]);
                $liked = true;
                $starred = false;
            } else {
                if ($existingLike->is_starred) {
                    $existingLike->is_starred = false;
                    $existingLike->save();
                    $liked = true;
                    $starred = false;
                } else {
                    $existingLike->delete();
                    $liked = false;
                    $starred = false;
                    $this->removeNotification($post, $user, 'like');
                }
            }

            $likeCount = PostLike::query()
                ->where('post_id', $post->id)
                ->where('is_starred', false)
                ->count();

            $post->update(['like_count' => $likeCount]);
            $post->refresh();

            return response()->json([
                'success' => true,
                'liked' => $liked,
                'starred' => $starred,
                'count' => max(0, $post->like_count),
            ]);
        } catch (\Exception $e) {
            Log::error('Toggle Post Like Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error liking post: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle star on a post for the current user.
     */
    public function togglePostStar(Request $request, Post $post)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to star posts'
                ], 401);
            }

            if (Auth::user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin accounts cannot react to posts.'
                ], 403);
            }

            $user = Auth::user();
            $postLike = PostLike::where('post_id', $post->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$postLike) {
                $postLike = PostLike::create([
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                    'is_starred' => true,
                ]);

                return response()->json([
                    'success' => true,
                    'starred' => true,
                    'liked' => false,
                    'count' => max(0, PostLike::query()->where('post_id', $post->id)->where('is_starred', false)->count()),
                    'message' => 'Post starred successfully.',
                ]);
            }

            $postLike->is_starred = !$postLike->is_starred;
            $postLike->save();

            if (!$postLike->is_starred) {
                $postLike->delete();
            }

            return response()->json([
                'success' => true,
                'starred' => (bool) $postLike->is_starred,
                'liked' => false,
                'count' => max(0, PostLike::query()->where('post_id', $post->id)->where('is_starred', false)->count()),
                'message' => $postLike->is_starred ? 'Post starred successfully.' : 'Post removed from starred.',
            ]);
        } catch (\Exception $e) {
            Log::error('Toggle Post Star Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error starring post: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Report a post for moderation - FIXED with manual notification creation
     */
    public function reportPost(Request $request, Post $post)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to report posts'
                ], 401);
            }

            if (Auth::user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin accounts cannot report posts.'
                ], 403);
            }

            $reason = $request->input('reason', 'Inappropriate content');

            if ($post->is_reported) {
                return response()->json([
                    'success' => true,
                    'reported' => true,
                    'message' => 'This post is already reported.'
                ]);
            }

            $post->update(['is_reported' => true]);

            // Notify the post author that their post was reported - MANUAL CREATION
            if ($post->user_id !== Auth::id()) {
                $postPreview = strlen($post->description) > 100 
                    ? substr($post->description, 0, 100) . '...' 
                    : $post->description;

                Notification::create([
                    'user_id' => $post->user_id,
                    'from_user_id' => Auth::id(),
                    'type' => 'post_reported',
                    'notifiable_type' => Post::class,
                    'notifiable_id' => $post->id,
                    'message' => "Your post has been reported and is under review by moderators.",
                    'data' => [
                        'type' => 'post_reported',
                        'post_id' => $post->id,
                        'post_preview' => $postPreview,
                        'reported_by' => Auth::user()->name,
                        'report_reason' => $reason,
                        'message' => "Your post has been reported and is under review by moderators.",
                        'action_url' => route('community.posts.show', $post),
                        'reported_at' => now()->toISOString(),
                        'from_user_id' => Auth::id(),
                        'from_user_name' => Auth::user()->name,
                    ],
                ]);
            }

            // Also notify admins about the report
            $this->notifyAdminsAboutReport($post, Auth::user(), $reason);

            return response()->json([
                'success' => true,
                'reported' => true,
                'message' => 'Post reported successfully. Our team will review it.'
            ]);
        } catch (\Exception $e) {
            Log::error('Report Post Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error reporting post: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Notify admins when a post is reported - FIXED with manual notification creation
     */
    private function notifyAdminsAboutReport(Post $post, $reporter, $reason)
    {
        try {
            $admins = User::where('role', 'admin')->get();
            $postPreview = strlen($post->description) > 100 
                ? substr($post->description, 0, 100) . '...' 
                : $post->description;

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'from_user_id' => $reporter->id,
                    'type' => 'post_reported_admin',
                    'notifiable_type' => Post::class,
                    'notifiable_id' => $post->id,
                    'message' => "Post reported by {$reporter->name}",
                    'data' => [
                        'post_id' => $post->id,
                        'post_preview' => $postPreview,
                        'reporter_name' => $reporter->name,
                        'reporter_id' => $reporter->id,
                        'report_reason' => $reason,
                        'action_url' => route('admin.community.posts.reported'),
                        'from_user_id' => $reporter->id,
                        'from_user_name' => $reporter->name,
                    ],
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to notify admins about report: ' . $e->getMessage());
        }
    }

    /**
     * Approve a pending post (admin only) - FIXED with manual notification creation
     */
    public function approvePost(Request $request, Post $post)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login'
                ], 401);
            }

            if (!Auth::user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admins can approve posts'
                ], 403);
            }

            $post->update([
                'is_approved' => true,
                'approved_at' => now(),
                'is_reported' => false, // Clear reported status when approved
            ]);

            // Send notification to post author - MANUAL CREATION
            if ($post->user_id !== Auth::id()) {
                $postPreview = strlen($post->description) > 100 
                    ? substr($post->description, 0, 100) . '...' 
                    : $post->description;

                Notification::create([
                    'user_id' => $post->user_id,
                    'from_user_id' => Auth::id(),
                    'type' => 'post_approved',
                    'notifiable_type' => Post::class,
                    'notifiable_id' => $post->id,
                    'message' => "Your post has been approved and is now visible to the community!",
                    'data' => [
                        'type' => 'post_approved',
                        'post_id' => $post->id,
                        'post_preview' => $postPreview,
                        'disease_name' => $post->disease?->display_name,
                        'message' => "Your post has been approved and is now visible to the community!",
                        'action_url' => route('community.posts.show', $post),
                        'approved_by' => Auth::user()->name,
                        'approved_at' => now()->toISOString(),
                        'from_user_id' => Auth::id(),
                        'from_user_name' => Auth::user()->name,
                    ],
                ]);
            }

            // Reload to ensure relations are fresh before notifying followers
            $post->refresh();
            $this->notifyStarredDiseaseFollowers($post);

            return response()->json([
                'success' => true,
                'approved' => true,
                'message' => 'Post approved successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Approve Post Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error approving post: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a pending post (admin only) - FIXED with manual notification creation
     */
    public function rejectPost(Request $request, Post $post)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login'
                ], 401);
            }

            if (!Auth::user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admins can reject posts'
                ], 403);
            }

            $reason = $request->input('reason', 'Does not meet community guidelines.');

            // Mark post as rejected instead of deleting
            $post->update([
                'rejected_at' => now(),
                'rejected_by' => Auth::id(),
                'rejection_reason' => $reason,
                'is_reported' => false, // Clear reported status
            ]);

            // Send rejection notification to post author - MANUAL CREATION
            if ($post->user_id !== Auth::id()) {
                $postPreview = strlen($post->description) > 100 
                    ? substr($post->description, 0, 100) . '...' 
                    : $post->description;

                Notification::create([
                    'user_id' => $post->user_id,
                    'from_user_id' => Auth::id(),
                    'type' => 'post_rejected',
                    'notifiable_type' => Post::class,
                    'notifiable_id' => $post->id,
                    'message' => "Your post was rejected by admin." . ($reason ? " Reason: {$reason}" : ""),
                    'data' => [
                        'type' => 'post_rejected',
                        'post_id' => $post->id,
                        'post_preview' => $postPreview,
                        'reason' => $reason,
                        'message' => "Your post was rejected by admin." . ($reason ? " Reason: {$reason}" : ""),
                        'action_url' => route('community.posts.show', $post),
                        'rejected_by' => Auth::user()->name,
                        'rejected_at' => now()->toISOString(),
                        'from_user_id' => Auth::id(),
                        'from_user_name' => Auth::user()->name,
                    ],
                ]);
            }

            return response()->json([
                'success' => true,
                'rejected' => true,
                'message' => 'Post rejected successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Reject Post Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting post: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear report from a post (admin only)
     */
    public function clearReport(Request $request, Post $post)
    {
        try {
            if (!Auth::check() || !Auth::user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admins can clear reports'
                ], 403);
            }

            $post->update(['is_reported' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Report cleared successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Clear Report Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error clearing report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Allow owners and admins to access unapproved or rejected posts.
     */
    protected function canAccessUnapprovedPost(Post $post): bool
    {
        if (!Auth::check()) {
            return false;
        }

        // Allow access if user is the owner or admin
        $isOwnerOrAdmin = Auth::id() === $post->user_id || Auth::user()->isAdmin();

        // Allow access to rejected posts for owners and admins
        $isRejected = $post->rejected_at !== null;

        return $isOwnerOrAdmin || (!$post->is_approved && $isOwnerOrAdmin) || ($isRejected && $isOwnerOrAdmin);
    }

    /**
     * Remove notification when like is removed
     */
    protected function removeNotification($post, $user, $type)
    {
        try {
            // Delete the notification if it exists
            Notification::where('user_id', $post->user_id)
                ->where('from_user_id', $user->id)
                ->where('notifiable_type', Post::class)
                ->where('notifiable_id', $post->id)
                ->where('type', $type)
                ->delete();
                
            Log::info('Notification removed for unlike');
        } catch (\Exception $e) {
            Log::error('Failed to remove notification: ' . $e->getMessage());
        }
    }

    /**
     * Notify users who starred this disease about new post
     */
    protected function notifyStarredDiseaseFollowers(Post $post): void
    {
        try {
            $postDiseaseIds = $post->diseases->pluck('id')->toArray();
            if ($postDiseaseIds === []) {
                return;
            }

            $post->loadMissing(['diseases', 'user']);

            $postDiseases = $post->diseases->sortBy('disease_name');

            $targetUserIds = User::query()
                ->where('id', '!=', $post->user_id)
                ->get(['id', 'starred_disease_ids'])
                ->filter(function (User $user) use ($postDiseaseIds): bool {
                    return collect($user->getStarredDiseaseIds())
                        ->intersect($postDiseaseIds)
                        ->isNotEmpty();
                })
                ->pluck('id')
                ->unique()
                ->values();

            if ($targetUserIds->isEmpty()) {
                return;
            }

            $preview = strlen((string) $post->description) > 60
                ? substr((string) $post->description, 0, 60) . '...'
                : (string) $post->description;

            $primaryDisease = $postDiseases->first();
            $diseaseLabel = $postDiseases->pluck('display_name')->take(2)->implode(', ');
            if ($postDiseases->count() > 2) {
                $diseaseLabel .= ' +' . ($postDiseases->count() - 2);
            }

            foreach ($targetUserIds as $targetUserId) {
                Notification::create([
                    'user_id' => $targetUserId,
                    'from_user_id' => $post->user_id,
                    'type' => 'starred_disease_post',
                    'notifiable_type' => Post::class,
                    'notifiable_id' => $post->id,
                    'message' => "New post in your starred disease: " . ($diseaseLabel !== '' ? $diseaseLabel : 'Unknown Disease'),
                    'data' => [
                        'post_id' => $post->id,
                        'disease_id' => $primaryDisease?->id,
                        'disease_ids' => $postDiseaseIds,
                        'disease_name' => $primaryDisease?->display_name,
                        'disease_names' => $postDiseases->pluck('display_name')->values()->all(),
                        'post_preview' => $preview,
                        'from_user_id' => $post->user_id,
                        'from_user_name' => $post->user?->name,
                    ],
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to notify starred disease followers: ' . $e->getMessage());
        }
    }

    /**
     * Toggle star on a disease for the current user
     */
    public function toggleDiseaseStar(Request $request, Disease $disease)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to star diseases',
            ], 401);
        }

        if (Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Admin accounts are view-only in community.',
            ], 403);
        }

        $user = Auth::user();
        $starred = $user->toggleDiseaseStarred((int) $disease->id);

        ActivityLogger::log([
            'user_id' => $user->id,
            'category' => 'community',
            'action' => $starred ? 'disease_starred' : 'disease_unstarred',
            'description' => $starred
                ? 'Starred a disease from community cards.'
                : 'Removed a disease from starred list.',
            'subject_type' => Disease::class,
            'subject_id' => (int) $disease->id,
            'context' => [
                'disease_id' => (int) $disease->id,
                'disease_name' => $disease->display_name,
                'starred' => $starred,
            ],
        ]);

        return response()->json([
            'success' => true,
            'starred' => $starred,
            'message' => $starred
                ? __('ui.community.disease_starred_successfully')
                : __('ui.community.disease_removed_from_starred'),
        ]);
    }

    /**
     * Display star/unstar history of diseases for current user.
     */
    public function starredDiseaseHistory(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->isAdmin()) {
            return redirect()->route('community.home');
        }

        $status = (string) $request->query('status', 'all');
        if (!in_array($status, ['all', 'current', 'previous'], true)) {
            $status = 'all';
        }

        $diseaseFilter = (string) $request->query('disease', 'all');
        $selectedDiseaseId = $diseaseFilter !== 'all' ? (int) $diseaseFilter : null;

        $user = Auth::user();
        $currentIds = $user->getStarredDiseaseIds();
        $history = collect($user->getStarredDiseaseHistory());

        $historyDiseaseIds = $history
            ->pluck('disease_id')
            ->map(static fn ($id) => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();

        $diseases = Disease::query()
            ->whereIn('id', $historyDiseaseIds)
            ->orderBy('disease_name')
            ->get();
        $diseaseMap = $diseases->keyBy('id');

        $rows = $history
            ->map(function (array $row) use ($currentIds, $diseaseMap) {
                $diseaseId = (int) ($row['disease_id'] ?? 0);
                if ($diseaseId <= 0 || !$diseaseMap->has($diseaseId)) {
                    return null;
                }

                $unstarredAt = isset($row['unstarred_at']) && $row['unstarred_at'] !== null
                    ? (string) $row['unstarred_at']
                    : null;

                return [
                    'disease' => $diseaseMap->get($diseaseId),
                    'disease_id' => $diseaseId,
                    'starred_at' => (string) ($row['starred_at'] ?? now()->toIso8601String()),
                    'unstarred_at' => $unstarredAt,
                    'is_current' => $unstarredAt === null && in_array($diseaseId, $currentIds, true),
                ];
            })
            ->filter();

        if ($selectedDiseaseId !== null) {
            $rows = $rows->where('disease_id', $selectedDiseaseId);
        }

        if ($status === 'current') {
            $rows = $rows->where('is_current', true);
        } elseif ($status === 'previous') {
            $rows = $rows->where('is_current', false);
        }

        $rows = $rows
            ->sort(function (array $a, array $b): int {
                if ($a['is_current'] !== $b['is_current']) {
                    return $a['is_current'] ? -1 : 1;
                }

                return strcmp((string) $b['starred_at'], (string) $a['starred_at']);
            })
            ->values();

        return view('community.pages.starred-diseases', [
            'rows' => $rows,
            'diseases' => $diseases,
            'status' => $status,
            'selectedDiseaseId' => $selectedDiseaseId,
            'currentStarredDiseaseIds' => $currentIds,
        ]);
    }

    /**
     * Store a new comment with optional file and send notification - FIXED with manual creation
     */
    public function storeComment(Request $request, Post $post)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to comment'
                ], 401);
            }

            if (Auth::user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin accounts cannot comment on posts.'
                ], 403);
            }

            $request->validate([
                'comment_details' => 'nullable|string|max:2000',
                'file' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,mp4,mp3,pdf,doc,docx,xls,xlsx,ppt,pptx,txt',
            ]);

            if (!$request->comment_details && !$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please write something or attach a file'
                ], 422);
            }

            $data = [
                'post_id' => $post->id,
                'user_id' => Auth::id(),
                'comment_details' => $request->comment_details ?? '',
                'like_count' => 0,
            ];

            if ($request->hasFile('file')) {
                try {
                    $file = $request->file('file');
                    $path = $file->store('community/comments/' . Auth::id(), 'public');
                    
                    $data['file_path'] = $path;
                    $data['file_type'] = $file->getMimeType();
                    $data['file_name'] = $file->getClientOriginalName();
                    $data['file_size'] = $file->getSize();
                } catch (\Exception $e) {
                    Log::error('Comment file upload error: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Error uploading file: ' . $e->getMessage()
                    ], 422);
                }
            }

            $comment = Comment::create($data);
            $post->increment('comment_count');
            $comment->load('user');

            // Send notification to post author - MANUAL CREATION
            if ($post->user_id !== Auth::id()) {
                $commentPreview = strlen($comment->comment_details) > 100 
                    ? substr($comment->comment_details, 0, 100) . '...' 
                    : $comment->comment_details;

                Notification::create([
                    'user_id' => $post->user_id,
                    'from_user_id' => Auth::id(),
                    'type' => 'comment',
                    'notifiable_type' => Comment::class,
                    'notifiable_id' => $comment->id,
                    'message' => Auth::user()->name . " commented on your post",
                    'data' => [
                        'post_id' => $post->id,
                        'comment_id' => $comment->id,
                        'comment_preview' => $commentPreview,
                        'actor_name' => Auth::user()->name,
                        'actor_avatar' => Auth::user()->picture ? asset('storage/' . Auth::user()->picture) : null,
                        'from_user_id' => Auth::id(),
                        'from_user_name' => Auth::user()->name,
                    ],
                ]);
            }

            $html = view('community.partials.comment', ['comment' => $comment])->render();

            return response()->json([
                'success' => true,
                'comment' => $comment,
                'html' => $html,
                'comment_count' => $post->fresh()->comment_count,
                'message' => 'Comment added successfully!'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Store Comment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error adding comment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a comment
     */
    public function updateComment(Request $request, Comment $comment)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login'
                ], 401);
            }

            $this->authorize('update', $comment);

            $request->validate([
                'description' => 'nullable|string|max:2000',
                'comment_details' => 'nullable|string|max:2000',
            ]);

            $content = $request->input('description') ?? $request->input('comment_details');
            
            if (!$content) {
                return response()->json([
                    'success' => false,
                    'message' => 'Content cannot be empty'
                ], 422);
            }

            $comment->update([
                'comment_details' => $content,
            ]);

            return response()->json([
                'success' => true,
                'comment' => [
                    'id' => $comment->id,
                    'comment_details' => $comment->comment_details,
                ],
                'message' => 'Comment updated successfully!'
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'You can only edit your own comments'
            ], 403);
        } catch (\Exception $e) {
            Log::error('Update Comment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating comment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a comment - Clean up notifications
     */
    public function destroyComment(Request $request, Comment $comment)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login'
                ], 401);
            }

            $user = Auth::user();
            $isOwner = $user->id === $comment->user_id;
            $isAdmin = $user->isAdmin();

            // Allow admin to delete any comment, owner can delete their own
            if (!$isOwner && !$isAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own comments unless you are an admin'
                ], 403);
            }

            $post = $comment->post;
            
            if ($comment->file_path) {
                try {
                    Storage::disk('public')->delete($comment->file_path);
                } catch (\Exception $e) {
                    Log::warning('Comment file deletion error: ' . $e->getMessage());
                }
            }
            
            // Delete related notifications before deleting the comment
            Notification::where('notifiable_type', Comment::class)
                ->where('notifiable_id', $comment->id)
                ->delete();
            
            $comment->delete();
            
            // Make sure comment_count doesn't go below 0
            if ($post->comment_count > 0) {
                $post->decrement('comment_count');
            }

            return response()->json([
                'success' => true,
                'comment_count' => $post->fresh()->comment_count,
                'message' => 'Comment deleted successfully!'
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own comments'
            ], 403);
        } catch (\Exception $e) {
            Log::error('Destroy Comment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting comment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle like on a comment
     */
    public function toggleCommentLike(Request $request, Comment $comment)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to like comments'
                ], 401);
            }

            if (Auth::user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin accounts cannot react to comments.'
                ], 403);
            }

            $user = Auth::user();
            $existingLike = CommentLike::where('comment_id', $comment->id)
                                       ->where('user_id', $user->id)
                                       ->first();

            if ($existingLike) {
                $existingLike->delete();
                // Make sure like_count doesn't go below 0
                if ($comment->like_count > 0) {
                    $comment->decrement('like_count');
                }
                $comment->refresh();
                $liked = false;
            } else {
                CommentLike::create([
                    'comment_id' => $comment->id,
                    'user_id' => $user->id,
                ]);
                $comment->increment('like_count');
                $comment->refresh();
                $liked = true;
            }

            return response()->json([
                'success' => true,
                'liked' => $liked,
                'count' => max(0, $comment->like_count),
            ]);
        } catch (\Exception $e) {
            Log::error('Toggle Comment Like Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error liking comment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get file icon based on mime type
     */
    private function getFileIcon($mimeType)
    {
        if (str_contains($mimeType, 'pdf')) return 'fa-file-pdf';
        if (str_contains($mimeType, 'word')) return 'fa-file-word';
        if (str_contains($mimeType, 'excel') || str_contains($mimeType, 'sheet')) return 'fa-file-excel';
        if (str_contains($mimeType, 'image')) return 'fa-file-image';
        if (str_contains($mimeType, 'video')) return 'fa-file-video';
        if (str_contains($mimeType, 'audio')) return 'fa-file-audio';
        if (str_contains($mimeType, 'text')) return 'fa-file-alt';
        return 'fa-file';
    }

    /**
     * Format file size
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    private function applyDiseaseFilter($query, $diseaseId): void
    {
        if (!$diseaseId || $diseaseId === 'all') {
            return;
        }

        $id = (int) $diseaseId;
        if ($id <= 0) {
            return;
        }

        // Log matching count for debugging test failures
        try {
            $matching = (clone $query)->whereHas('diseases', function ($q) use ($id) {
                $q->where('id', $id);
            })->count();
            Log::info('applyDiseaseFilter', ['disease_id' => $id, 'matching_posts' => $matching]);
        } catch (\Throwable $e) {
            Log::warning('applyDiseaseFilter count failed: ' . $e->getMessage());
        }

        $query->whereHas('diseases', function ($q) use ($id) {
            $q->where('id', $id);
        });
    }

    private function applyStarredDiseaseOrder($query, array $diseaseIds): void
    {
        $ids = collect($diseaseIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return;
        }

        $query->orderByRaw(
            'EXISTS (SELECT 1 FROM post_diseases WHERE post_diseases.post_id = posts.id AND post_diseases.disease_id IN (' . $ids->implode(',') . ')) DESC'
        );
    }

    private function buildDiseaseCollectionWithCounts(callable $postConstraint, array $priorityDiseaseIds = []): Collection
    {
        $diseases = Disease::withCount(['posts' => $postConstraint])->get();

        // Get latest post timestamp per disease
        $latestPostsQuery = Post::query()
            ->select('post_diseases.disease_id', DB::raw('MAX(COALESCE(posts.approved_at, posts.created_at)) as latest_at'))
            ->join('post_diseases', 'posts.id', '=', 'post_diseases.post_id')
            ->groupBy('post_diseases.disease_id');

        $postConstraint($latestPostsQuery);

        $latestPosts = $latestPostsQuery->pluck('latest_at', 'disease_id');

        $priorityOrder = collect($priorityDiseaseIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values()
            ->flip();

        return $diseases
            ->map(function (Disease $disease) use ($latestPosts) {
                $disease->setAttribute('latest_post_at', $latestPosts->get($disease->id));
                return $disease;
            })
            ->sort(function (Disease $a, Disease $b) use ($priorityOrder): int {
                $aPriority = $priorityOrder->has($a->id) ? 0 : 1;
                $bPriority = $priorityOrder->has($b->id) ? 0 : 1;

                if ($aPriority !== $bPriority) {
                    return $aPriority <=> $bPriority;
                }

                $aCount = (int) $a->posts_count;
                $bCount = (int) $b->posts_count;
                if ($aCount !== $bCount) {
                    return $bCount <=> $aCount;
                }

                $aLatest = (string) ($a->latest_post_at ?? '');
                $bLatest = (string) ($b->latest_post_at ?? '');
                if ($aLatest !== $bLatest) {
                    return strcmp($bLatest, $aLatest);
                }

                return strcasecmp((string) $a->disease_name, (string) $b->disease_name);
            })
            ->values();
    }
}