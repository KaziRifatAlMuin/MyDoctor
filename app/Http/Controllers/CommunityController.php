<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use App\Models\Disease;
use App\Models\User;
use App\Models\PostLike;
use App\Models\CommentLike;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class CommunityController extends Controller
{
    /**
     * Display the forum page with posts and disease filter
     */
    public function index(Request $request)
    {
        // Redirect non-authenticated users to landing page
        if (!Auth::check()) {
            return redirect()->route('community.landing');
        }
        
        try {
            $diseaseId = $request->get('disease');
            
            // Build the posts query with eager loading
            $query = Post::with(['user', 'disease', 'comments' => function($q) {
                $q->with('user')->latest()->limit(3);
            }])->withCount(['likes as likes_count']);

            // Apply disease filter if selected
            if ($diseaseId && $diseaseId !== 'all') {
                $query->where('disease_id', $diseaseId);
            }

            // Get paginated posts (10 per page)
            $posts = $query->latest()->paginate(10);
            
            // Get all diseases with post counts for the filter sidebar
            $diseases = Disease::withCount('posts')
                              ->orderBy('disease_name')
                              ->get();
            
            // If diseases table is empty, log a warning
            if ($diseases->isEmpty()) {
                Log::warning('No diseases found in database');
            }
            
            // Additional stats for right sidebar
            $totalPosts = Post::count();
            $totalUsers = User::count();
            $totalComments = Comment::count();
            
            // Get active users today 
            $activeToday = User::whereDate('updated_at', today())->count() ?: 0;
            
            // Get trending diseases (with at least one post)
            $trendingDiseases = Disease::withCount('posts')
                                       ->having('posts_count', '>', 0)
                                       ->orderBy('posts_count', 'desc')
                                       ->take(5)
                                       ->get();
            
            // Log for debugging
            Log::info('Community index loaded', [
                'posts_count' => $posts->total(),
                'diseases_count' => $diseases->count(),
                'diseaseId' => $diseaseId
            ]);
            
            return view('community.index', compact(
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
            Log::error('Community Index Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return with empty paginator
            return view('community.index', [
                'posts' => new LengthAwarePaginator([], 0, 10),
                'diseases' => collect([]),
                'diseaseId' => null,
                'totalPosts' => 0,
                'totalUsers' => 0,
                'totalComments' => 0,
                'activeToday' => 0,
                'trendingDiseases' => collect([]),
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

            $query = Post::with(['user', 'disease', 'comments' => function($q) {
                $q->with('user')->latest()->limit(3);
            }])->withCount(['likes as likes_count'])
              ->whereHas('likes', function ($q) use ($userId) {
                  $q->where('user_id', $userId)
                    ->where('is_starred', true);
              });

            if ($diseaseId && $diseaseId !== 'all') {
                $query->where('disease_id', $diseaseId);
            }

            $posts = $query->latest()->paginate(10)->withQueryString();

            $diseases = Disease::withCount([
                'posts as posts_count' => function ($q) use ($userId) {
                    $q->whereHas('likes', function ($likeQuery) use ($userId) {
                        $likeQuery->where('user_id', $userId)
                                  ->where('is_starred', true);
                    });
                }
            ])->orderBy('disease_name')->get();

            $totalPosts = (clone $query)->count();
            $totalUsers = User::count();
            $totalComments = Comment::whereHas('post.likes', function ($q) use ($userId) {
                $q->where('user_id', $userId)->where('is_starred', true);
            })->count();
            $activeToday = User::whereDate('updated_at', today())->count() ?: 0;

            $trendingDiseases = Disease::withCount([
                'posts as posts_count' => function ($q) use ($userId) {
                    $q->whereHas('likes', function ($likeQuery) use ($userId) {
                        $likeQuery->where('user_id', $userId)
                                  ->where('is_starred', true);
                    });
                }
            ])->get()
              ->filter(function ($disease) {
                  return (int) $disease->posts_count > 0;
              })
              ->sortByDesc('posts_count')
              ->take(5)
              ->values();

            return view('community.index', [
                'posts' => $posts,
                'diseases' => $diseases,
                'diseaseId' => $diseaseId,
                'totalPosts' => $totalPosts,
                'totalUsers' => $totalUsers,
                'totalComments' => $totalComments,
                'activeToday' => $activeToday,
                'trendingDiseases' => $trendingDiseases,
                'isStarredPage' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Community Starred Posts Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return view('community.index', [
                'posts' => new LengthAwarePaginator([], 0, 10),
                'diseases' => collect([]),
                'diseaseId' => null,
                'totalPosts' => 0,
                'totalUsers' => 0,
                'totalComments' => 0,
                'activeToday' => 0,
                'trendingDiseases' => collect([]),
                'isStarredPage' => true,
                'error' => 'Error loading starred posts: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the landing page (redirects to forum if logged in)
     */
    public function landing()
    {
        if (Auth::check()) {
            return redirect()->route('community.index');
        }
        return view('community.landing');
    }

    /**
     * Display a single post with all comments
     */
    public function showPost(Post $post)
    {
        try {
            $post->load(['user', 'disease', 'comments' => function($q) {
                $q->with('user')->latest();
            }])->loadCount(['likes as likes_count']);
            
            return view('community.show', compact('post'));
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
            return view('community.modal-post', compact('post'));
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
            
            $query = Post::with(['user', 'disease'])
                ->withCount(['likes as likes_count']);

            if ($diseaseId && $diseaseId !== 'all') {
                $query->where('disease_id', $diseaseId);
            }

            $posts = $query->latest()->get();
            
            $formattedPosts = $posts->map(function($post) {
                return [
                    'id' => $post->id,
                    'description' => $post->description,
                    'files' => $post->all_files,
                    'file_count' => $post->file_count,
                    'total_size' => $post->formatted_total_size,
                    'like_count' => $post->likes_count,
                    'comment_count' => $post->comment_count,
                    'created_at' => $post->created_at,
                    'user' => [
                        'id' => $post->user->id,
                        'name' => $post->user->name,
                        'email' => $post->user->email,
                        'avatar' => $post->user->picture ? Storage::url($post->user->picture) : null,
                        'created_at' => $post->user->created_at,
                    ],
                    'disease' => $post->disease ? [
                        'id' => $post->disease->id,
                        'name' => $post->disease->disease_name,
                    ] : null,
                    'user_liked' => Auth::check() ? $post->likes()->where('user_id', Auth::id())->exists() : false,
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

            $request->validate([
                'disease_id' => 'required|exists:diseases,id',
                'description' => 'nullable|string|max:5000',
                'files.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,mp4,mp3,pdf,doc,docx,xls,xlsx,ppt,pptx,txt',
            ]);

            // Check if at least one file or description exists
            if (!$request->description && !$request->hasFile('files')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please write something or attach at least one file'
                ], 422);
            }

            $data = [
                'user_id' => Auth::id(),
                'disease_id' => $request->disease_id,
                'description' => $request->description ?? '',
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
            $post->load(['user', 'disease']);
            
            $html = view('community.partials.post', ['post' => $post])->render();

            return response()->json([
                'success' => true,
                'post' => $post,
                'html' => $html,
                'message' => 'Post created successfully!'
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

            if (Auth::id() !== $post->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only edit your own posts'
                ], 403);
            }

            $request->validate([
                'description' => 'required|string|max:5000',
            ]);

            $post->update([
                'description' => $request->input('description'),
            ]);

            return response()->json([
                'success' => true,
                'post' => [
                    'id' => $post->id,
                    'description' => $post->description,
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
     * Delete a post and its files
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

            if (Auth::id() !== $post->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own posts'
                ], 403);
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
     * Toggle like on a post - FIXED to prevent negative counts and send notifications
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

            $user = Auth::user();
            $existingLike = PostLike::where('post_id', $post->id)
                                    ->where('user_id', $user->id)
                                    ->first();

            if ($existingLike) {
                $existingLike->delete();
                // Make sure like_count doesn't go below 0
                if ($post->like_count > 0) {
                    $post->decrement('like_count');
                }
                $post->refresh();
                $liked = false;
                $starred = false;
                
                // Remove notification when unliked
                $this->removeNotification($post, $user, 'like');
                
            } else {
                PostLike::create([
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                    'is_starred' => false,
                ]);
                $post->increment('like_count');
                $post->refresh();
                $liked = true;
                $starred = false;
            }

            return response()->json([
                'success' => true,
                'liked' => $liked,
                'starred' => $starred,
                'count' => max(0, $post->like_count), // Ensure non-negative
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

                $post->increment('like_count');
                $post->refresh();

                return response()->json([
                    'success' => true,
                    'starred' => true,
                    'liked' => true,
                    'count' => max(0, $post->like_count),
                    'message' => 'Post starred successfully.',
                ]);
            }

            $postLike->is_starred = !$postLike->is_starred;
            $postLike->save();
            $post->refresh();

            return response()->json([
                'success' => true,
                'starred' => (bool) $postLike->is_starred,
                'liked' => true,
                'count' => max(0, $post->like_count),
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
     * Store a new comment with optional file and send notification
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

            if (Auth::id() !== $comment->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only edit your own comments'
                ], 403);
            }

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
        } catch (\Exception $e) {
            Log::error('Update Comment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating comment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a comment
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

            if (Auth::id() !== $comment->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own comments'
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
                'count' => max(0, $comment->like_count), // Ensure non-negative
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
}