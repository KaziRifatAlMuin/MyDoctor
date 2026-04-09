@extends('layouts.app')

@section('content')
    <div
        style="background: linear-gradient(180deg, #eef4ff 0%, #f8fbff 60%, #ffffff 100%); min-height: 100vh; padding: 2rem 0 4rem;">
        <div class="container" style="max-width: 980px;">
            <div class="card border-0 shadow-sm mb-4"
                style="border-radius: 22px; overflow: hidden; box-shadow: 0 20px 55px rgba(10,50,120,0.12) !important;">
                <div class="card-body p-4 p-md-5"
                    style="background: linear-gradient(135deg, #0b57d0 0%, #1a73e8 48%, #2b7de9 100%); color: #fff;">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <p class="mb-2" style="font-size:.85rem; opacity:.92;">Community Post</p>
                            <h1 class="mb-0" style="font-size:1.7rem; font-weight:800;">Post #{{ $post->id }}</h1>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('community.posts.index') }}" class="btn btn-light"
                                style="border-radius: 12px; color:#0b57d0; font-weight:700;">All Posts</a>
                            @if ($post->disease_id)
                                <a href="{{ route('community.disease.posts', $post->disease_id) }}"
                                    class="btn btn-outline-light" style="border-radius: 12px; font-weight:700;">Disease
                                    Feed</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div
                            style="width:52px; height:52px; border-radius:50%; overflow:hidden; background:#e2e8f0; display:flex; align-items:center; justify-content:center; font-weight:700; color:#334155;">
                            @if ($post->user && $post->user->picture && !$post->is_anonymous)
                                <img src="{{ asset('storage/' . $post->user->picture) }}" alt="{{ $post->user->name }}"
                                    style="width:100%; height:100%; object-fit:cover;">
                            @else
                                {{ $post->is_anonymous ? 'A' : strtoupper(substr($post->user->name ?? 'U', 0, 1)) }}
                            @endif
                        </div>
                        <div>
                            <div style="font-weight:800; color:#0f172a;">
                                {{ $post->is_anonymous ? 'Anonymous Member' : $post->user->name ?? 'Unknown User' }}</div>
                            <div style="font-size:.85rem; color:#64748b;">{{ $post->created_at->format('M d, Y h:i A') }}
                            </div>
                        </div>
                    </div>

                    @if ($post->disease)
                        <div class="mb-3">
                            <a href="{{ route('public.disease.show', $post->disease) }}"
                                class="badge text-bg-light border text-decoration-none px-3 py-2">
                                {{ $post->disease->disease_name }}
                            </a>
                        </div>
                    @endif

                    @if (!empty($post->description))
                        <div style="font-size:1rem; line-height:1.65; color:#1e293b; white-space: pre-wrap;">
                            {{ $post->description }}</div>
                    @else
                        <p class="text-muted mb-0">No text content.</p>
                    @endif

                    <div class="d-flex gap-3 mt-4" style="font-size:.9rem; color:#475569;">
                        <span><i class="far fa-thumbs-up me-1"></i>{{ $post->likes_count }} likes</span>
                        <span><i class="far fa-comment me-1"></i>{{ $post->comments->count() }} comments</span>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <h2 class="h6 mb-3" style="font-weight:800; color:#0f2f6b;">All Comments
                        ({{ $post->comments->count() }})</h2>
                    @if ($post->comments->isEmpty())
                        <p class="text-muted mb-0">No comments yet.</p>
                    @else
                        @foreach ($post->comments as $comment)
                            <div class="border rounded-3 p-3 mb-2">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <strong style="font-size:.9rem;">{{ $comment->user?->name ?? 'Unknown user' }}</strong>
                                    <span class="text-muted"
                                        style="font-size:.8rem;">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <div style="font-size:.92rem; margin-top:.35rem;">{{ $comment->content }}</div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
