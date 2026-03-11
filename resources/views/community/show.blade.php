@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <!-- Post -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <h5>{{ $post->user->name }}</h5>
                            <small class="text-muted">
                                {{ $post->created_at->format('F j, Y, g:i a') }}
                                <span class="badge bg-info ms-2">{{ $post->disease->disease_name }}</span>
                            </small>
                        </div>
                    </div>
                    
                    <p class="lead">{{ $post->description }}</p>
                    
                    <div class="d-flex gap-3 mt-4">
                        @auth
                        <button class="btn btn-sm {{ $post->isLikedBy(Auth::user()) ? 'btn-danger' : 'btn-outline-danger' }}"
                                onclick="likePost({{ $post->id }}, this)">
                            ❤ <span class="like-count">{{ $post->like_count }}</span>
                        </button>
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Comments -->
            <h4>Comments ({{ $post->comments->count() }})</h4>

            @auth
            <div class="card mb-4">
                <div class="card-body">
                    <form action="{{ route('community.comments.store', $post) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <textarea name="comment_details" class="form-control" rows="2" placeholder="Write a comment..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Post Comment</button>
                    </form>
                </div>
            </div>
            @endauth

            @forelse($post->comments as $comment)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h6 class="mb-1">{{ $comment->user->name }}</h6>
                        <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                    </div>
                    <p class="mb-2">{{ $comment->comment_details }}</p>
                    
                    @auth
                    <button class="btn btn-sm {{ $comment->isLikedBy(Auth::user()) ? 'btn-primary' : 'btn-outline-primary' }}"
                            onclick="likeComment({{ $comment->id }}, this)">
                        👍 <span class="like-count">{{ $comment->like_count }}</span>
                    </button>
                    @endauth
                </div>
            </div>
            @empty
            <div class="alert alert-info">No comments yet. Be the first to comment!</div>
            @endforelse

            <div class="mt-3">
                <a href="{{ route('community.index', ['disease' => request('disease')]) }}" class="btn btn-secondary">← Back to Community</a>
            </div>
        </div>
    </div>
</div>

@auth
<script>
function likePost(postId, button) {
    fetch(`/community/posts/${postId}/like`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        button.classList.toggle('btn-danger', data.liked);
        button.classList.toggle('btn-outline-danger', !data.liked);
        button.querySelector('.like-count').textContent = data.count;
    });
}

function likeComment(commentId, button) {
    fetch(`/community/comments/${commentId}/like`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        button.classList.toggle('btn-primary', data.liked);
        button.classList.toggle('btn-outline-primary', !data.liked);
        button.querySelector('.like-count').textContent = data.count;
    });
}
</script>
@endauth
@endsection