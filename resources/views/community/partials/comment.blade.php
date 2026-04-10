<div class="comment" id="comment-{{ $comment->id }}" data-comment-id="{{ $comment->id }}">
    @php
        $isAdminReadOnly = Auth::check() && Auth::user()->isAdmin();
    @endphp
    <div class="comment-avatar" onclick="showUserModal({{ $comment->user->id }})" style="cursor: pointer;">
        @if($comment->user->picture)
            <img src="{{ asset('storage/' . $comment->user->picture) }}" alt="{{ $comment->user->name }}">
        @else
            <div class="avatar-placeholder-small">
                {{ strtoupper(substr($comment->user->name, 0, 1)) }}
            </div>
        @endif
    </div>
    <div class="comment-content">
        <div class="comment-header">
            <span class="comment-author" onclick="showUserModal({{ $comment->user->id }})" style="cursor: pointer;">{{ $comment->user->name }}</span>
            <span class="comment-time">{{ $comment->created_at->diffForHumans() }}</span>
            
            @auth
                @if(Auth::id() === $comment->user_id && ! $isAdminReadOnly)
                    <div class="comment-actions" style="display: flex; gap: 4px;">
                        <button class="comment-action-btn" onclick="editComment({{ $comment->id }})" title="Edit" style="background: none; border: none; padding: 4px; color: #65676b; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; outline: none;">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="comment-action-btn text-danger" onclick="confirmDelete({{ $comment->id }}, 'comment')" title="Delete" style="background: none; border: none; padding: 4px; color: #dc3545; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; outline: none;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                @endif
            @endauth
        </div>
        
        <!-- Comment text with clickable links -->
        <p class="comment-text" id="comment-content-{{ $comment->id }}">
            @php
                $commentText = $comment->comment_details;
                // Convert URLs to clickable links
                $linkedText = preg_replace_callback(
                    '/(https?:\/\/[^\s]+)/',
                    function($matches) {
                        $url = $matches[1];
                        $displayUrl = strlen($url) > 50 ? substr($url, 0, 47) . '...' : $url;
                        return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer" class="comment-link">' . $displayUrl . '</a>';
                    },
                    e($commentText)
                );
                echo nl2br($linkedText);
            @endphp
        </p>
        
        @if($comment->file_path)
            <div class="comment-attachment" style="margin-top: 8px;">
                @if(str_starts_with($comment->file_type, 'image/'))
                    <img src="{{ Storage::url($comment->file_path) }}" 
                         alt="{{ $comment->file_name }}" 
                         class="comment-image"
                         onclick="openImageModal('{{ Storage::url($comment->file_path) }}')"
                         style="max-width: 200px; max-height: 150px; border-radius: 8px; cursor: pointer;">
                @else
                    @php
                        $icon = 'fa-file-alt';
                        if(str_contains($comment->file_type, 'pdf')) $icon = 'fa-file-pdf';
                        elseif(str_contains($comment->file_type, 'word')) $icon = 'fa-file-word';
                        elseif(str_contains($comment->file_type, 'excel')) $icon = 'fa-file-excel';
                        elseif(str_contains($comment->file_type, 'image')) $icon = 'fa-file-image';
                        elseif(str_contains($comment->file_type, 'video')) $icon = 'fa-file-video';
                    @endphp
                    <div style="display: flex; align-items: center; gap: 8px; padding: 6px 10px; background: #e4e6eb; border-radius: 6px; font-size: 12px;">
                        <i class="fas {{ $icon }}"></i>
                        <span>{{ $comment->file_name }}</span>
                        <span style="color: #65676b;">({{ number_format($comment->file_size / 1024, 1) }} KB)</span>
                        <a href="{{ Storage::url($comment->file_path) }}" download style="color: #1877f2;">
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                @endif
            </div>
        @endif
        
        @auth
            @if(! $isAdminReadOnly)
            <button class="comment-like-btn {{ $comment->likes()->where('user_id', Auth::id())->exists() ? 'liked' : '' }}" 
                    onclick="toggleCommentLike({{ $comment->id }}, this)"
                    style="background: none; border: none; padding: 2px 0; color: #65676b; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; outline: none;">
                <i class="{{ $comment->likes()->where('user_id', Auth::id())->exists() ? 'fas' : 'far' }} fa-heart"></i>
                <span class="like-count">{{ $comment->like_count }}</span>
            </button>
            @else
                <span style="display:inline-flex; align-items:center; gap:4px; color:#65676b; font-size:12px;">
                    <i class="far fa-heart"></i>
                    <span class="like-count">{{ $comment->like_count }}</span>
                </span>
            @endif
        @endauth
    </div>
</div>

<style>
/* Additional styles to ensure no borders appear */
.comment-action-btn {
    background: none !important;
    border: none !important;
    box-shadow: none !important;
    outline: none !important;
}

.comment-action-btn:focus,
.comment-action-btn:active,
.comment-action-btn:hover {
    background: none !important;
    border: none !important;
    box-shadow: none !important;
    outline: none !important;
}

.comment-action-btn.text-danger:hover {
    background-color: rgba(220, 53, 69, 0.1) !important;
    border-radius: 4px;
}

.comment-action-btn:hover {
    background-color: rgba(0, 0, 0, 0.05) !important;
    border-radius: 4px;
}

.comment-like-btn {
    background: none !important;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
}

.comment-like-btn:focus,
.comment-like-btn:active {
    outline: none !important;
    border: none !important;
    box-shadow: none !important;
}

/* Link styles for comments */
.comment-link {
    color: #1877f2;
    text-decoration: none;
    word-break: break-all;
    display: inline;
    margin: 0;
    padding: 0;
}

.comment-link:hover {
    text-decoration: underline;
    color: #0e5a9e;
}

/* Ensure the comment text properly wraps */
.comment-text {
    word-wrap: break-word;
    overflow-wrap: break-word;
}
</style>