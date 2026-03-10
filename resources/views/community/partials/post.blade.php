<div class="post-card" id="post-{{ $post->id }}" data-post-id="{{ $post->id }}">@php $description = $post->description ?? ''; @endphp
    <div class="post-header" style="padding:0 0 12px 0;margin:0;">
        <div class="post-user" onclick="showUserModal({{ $post->user->id }})" style="display:flex;gap:12px;cursor:pointer;">
            <div class="user-avatar" style="width:48px;height:48px;border-radius:50%;overflow:hidden;flex-shrink:0;">
                @if($post->user->picture)
                    <img src="{{ asset('storage/' . $post->user->picture) }}" alt="{{ $post->user->name }}" style="width:100%;height:100%;object-fit:cover;">
                @else
                    <div class="avatar-placeholder" style="width:100%;height:100%;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:18px;">{{ strtoupper(substr($post->user->name,0,1)) }}</div>
                @endif
            </div>
            <div class="user-info">
                <h6 class="user-name" style="font-size:15px;font-weight:600;margin:0;padding:0;color:#1a1a1a;">{{ $post->user->name }}</h6>
                <div class="post-meta" style="display:flex;align-items:center;gap:12px;font-size:12px;color:#65676b;margin:0;padding:0;">
                    <span class="post-time"><i class="far fa-clock me-1"></i>{{ $post->created_at->diffForHumans() }}</span>
                    @if($post->disease)
                        <span class="post-disease-badge" title="{{ $post->disease->disease_name }}" style="background:#e7f3ff;color:#1877f2;padding:4px 12px;border-radius:4px;font-weight:500;font-size:12px;display:inline-flex;align-items:center;gap:4px;">
                            <i class="fas fa-tag me-1"></i>{{ $post->disease->disease_name }}
                            @if(isset($post->disease->bn_name))
                                <span style="font-size:10px;color:#65676b;margin-left:4px;">({{ $post->disease->bn_name }})</span>
                            @endif
                        </span>
                    @endif
                </div>
            </div>
        </div>
        @auth
            @if(Auth::id() === $post->user_id)
                <div class="post-actions-menu" style="display:flex;gap:6px;">
                    <button class="post-menu-btn" onclick="editPost({{ $post->id }})" title="Edit" style="width:34px;height:34px;border:none;border-radius:50%;background:#f0f2f5;color:#65676b;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="post-menu-btn text-danger" onclick="confirmDelete({{ $post->id }},'post')" title="Delete" style="width:34px;height:34px;border:none;border-radius:50%;background:#f0f2f5;color:#dc3545;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            @endif
        @endauth
    </div>

    <div class="post-content" style="margin:0;padding:0;">
        @php
            $youtubePattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:watch\?v=|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/';
            
            preg_match($youtubePattern, $description, $youtubeMatches);
            
            $textDescription = $description;
            $videoUrls = [];
            if (!empty($youtubeMatches)) $videoUrls[] = $youtubeMatches[0];
            
            foreach ($videoUrls as $videoUrl) $textDescription = str_replace($videoUrl, '', $textDescription);
            $textDescription = trim($textDescription);
            $textLength = strlen($textDescription);
            $lineCount = substr_count($textDescription, "\n") + 1;
            $isLongText = $textLength > 600 || $lineCount > 10;
        @endphp
        
        @if(!empty($textDescription))
            <div class="post-text-wrapper" style="margin:0 0 12px 0;padding:0;text-align:left;">
                <div class="post-text-container" id="post-text-container-{{ $post->id }}" style="margin:0;padding:0;position:relative;text-align:left;">
                    <div class="post-text-content {{ $isLongText ? 'truncated' : '' }}" id="post-text-content-{{ $post->id }}" style="{{ $isLongText ? 'max-height:200px;overflow:hidden;' : '' }}margin:0;padding:0;text-align:left;">
                        <p class="post-text" id="post-content-{{ $post->id }}" style="margin:0;padding:0;line-height:1.5;font-size:15px;color:#1a1a1a;text-align:left;">@php
                            $escapedText = e($textDescription);
                            $linkedText = preg_replace_callback('/(https?:\/\/[^\s]+)/', function($matches) {
                                $url = $matches[1];
                                $displayUrl = strlen($url) > 50 ? substr($url,0,47).'...' : $url;
                                return '<a href="'.$url.'" target="_blank" rel="noopener noreferrer" class="post-link">'.$displayUrl.'</a>';
                            }, $escapedText);
                            echo nl2br($linkedText);
                        @endphp</p>
                    </div>
                    @if($isLongText)
                        <div class="see-more-overlay" id="see-more-{{ $post->id }}" style="position:relative;margin-top:8px;text-align:left;">
                            <button class="see-more-btn" onclick="toggleSeeMore({{ $post->id }}, event)" style="background:none;border:none;color:#1877f2;font-size:14px;font-weight:500;cursor:pointer;padding:4px 0;display:inline-flex;align-items:center;gap:4px;">
                                <span class="see-more-text">See More</span>
                                <i class="fas fa-chevron-down" style="font-size:12px;"></i>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @endif
        
        <!-- YouTube Embed -->
        @if(!empty($youtubeMatches))
            @php $videoId = $youtubeMatches[1]; $isShort = str_contains($youtubeMatches[0],'shorts/') || str_contains($youtubeMatches[0],'/shorts/'); @endphp
            <div class="video-embed-wrapper" style="width:100%;margin:0 0 16px 0;padding:0;display:flex;justify-content:{{ $isShort ? 'center' : 'flex-start' }};">
                <div class="video-embed-container {{ $isShort ? 'reel-container' : '' }}" onclick="openVideoModal('youtube','{{ $videoId }}',{{ $isShort ? 'true' : 'false' }})" style="position:relative;width:{{ $isShort ? 'auto' : '100%' }};{{ $isShort ? 'max-width:360px;' : '' }}aspect-ratio:{{ $isShort ? '9/16' : '16/9' }};border-radius:12px;overflow:hidden;cursor:pointer;background:#000;">
                    <img src="https://img.youtube.com/vi/{{ $videoId }}/hqdefault.jpg" alt="YouTube Video" style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;">
                    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:60px;height:60px;background:rgba(0,0,0,0.7);border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid rgba(255,255,255,0.5);">
                        <i class="fas fa-play" style="color:white;font-size:24px;"></i>
                    </div>
                    <div style="position:absolute;top:12px;right:12px;background:rgba(0,0,0,0.7);color:white;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;">
                        <i class="fab fa-youtube me-1" style="color:#ff0000;"></i> {{ $isShort ? 'Short' : 'YouTube' }}
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Post Attachments -->
        @if($post->file_count > 0)
            <div class="post-attachments" style="margin:0;padding:0;">
                @php $files = $post->all_files; $fileCount = count($files); @endphp
                @if($fileCount === 1)
                    @php $file = $files[0]; @endphp
                    <div class="single-file-container" style="margin:0 0 16px 0;padding:0;">
                        @if(str_starts_with($file['type'],'image/'))
                            <div style="position:relative;width:100%;background:#f0f2f5;border-radius:12px;overflow:hidden;cursor:pointer;" onclick="openImageModal('{{ $file['url'] }}')">
                                <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}" style="width:100%;max-height:500px;object-fit:contain;display:block;">
                            </div>
                        @elseif(str_starts_with($file['type'],'video/'))
                            @php $isVerticalVideo = false; @endphp
                            <div class="video-embed-wrapper" style="width:100%;margin:0;padding:0;display:flex;justify-content:{{ $isVerticalVideo ? 'center' : 'flex-start' }};">
                                <div class="video-embed-container {{ $isVerticalVideo ? 'reel-container' : '' }}" onclick="openVideoModal('file','{{ $file['url'] }}',{{ $isVerticalVideo ? 'true' : 'false' }})" style="position:relative;width:{{ $isVerticalVideo ? 'auto' : '100%' }};{{ $isVerticalVideo ? 'max-width:360px;' : '' }}aspect-ratio:{{ $isVerticalVideo ? '9/16' : '16/9' }};background:#000;border-radius:12px;overflow:hidden;cursor:pointer;">
                                    <video style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:contain;">
                                        <source src="{{ $file['url'] }}" type="{{ $file['type'] }}">
                                    </video>
                                    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:60px;height:60px;background:rgba(0,0,0,0.7);border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid rgba(255,255,255,0.5);">
                                        <i class="fas fa-play" style="color:white;font-size:24px;"></i>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div style="display:flex;align-items:center;gap:12px;padding:16px;background:#f0f2f5;border-radius:12px;">
                                <i class="fas {{ $file['icon'] }}" style="font-size:32px;color:#1877f2;"></i>
                                <div style="flex:1;">
                                    <div style="font-weight:500;margin-bottom:4px;">{{ $file['name'] }}</div>
                                    <div style="font-size:12px;color:#65676b;">{{ $file['formatted_size'] }}</div>
                                </div>
                                <a href="{{ $file['url'] }}" download style="color:#1877f2;padding:8px;border-radius:50%;background:white;display:flex;align-items:center;justify-content:center;text-decoration:none;">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="multiple-files-grid" style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin:0 0 16px 0;padding:0;">
                        @foreach($files as $file)
                            <div class="file-item" style="border:1px solid #e4e6eb;border-radius:10px;overflow:hidden;background:white;transition:transform 0.2s;box-shadow:0 2px 4px rgba(0,0,0,0.05);">
                                @if(str_starts_with($file['type'],'image/'))
                                    <div style="position:relative;padding-top:75%;background:#f0f2f5;cursor:pointer;" onclick="openImageModal('{{ $file['url'] }}')">
                                        <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}" style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;">
                                    </div>
                                @elseif(str_starts_with($file['type'],'video/'))
                                    <div style="position:relative;padding-top:75%;background:#000;cursor:pointer;" onclick="openVideoModal('file','{{ $file['url'] }}')">
                                        <video style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;">
                                            <source src="{{ $file['url'] }}" type="{{ $file['type'] }}">
                                        </video>
                                        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:40px;height:40px;background:rgba(0,0,0,0.7);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                            <i class="fas fa-play" style="color:white;font-size:16px;"></i>
                                        </div>
                                    </div>
                                @else
                                    <div style="padding:20px;text-align:center;background:#f0f2f5;">
                                        <i class="fas {{ $file['icon'] }}" style="font-size:40px;color:#1877f2;"></i>
                                    </div>
                                    <div style="padding:10px;background:white;">
                                        <div style="font-size:12px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:4px;" title="{{ $file['name'] }}">{{ Str::limit($file['name'],20) }}</div>
                                        <div style="display:flex;justify-content:flex-end;">
                                            <a href="{{ $file['url'] }}" download class="btn btn-sm" style="color:#1877f2;padding:4px 8px;border-radius:4px;background:#e7f3ff;text-decoration:none;display:flex;align-items:center;gap:4px;">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div class="post-action-buttons" style="display:flex;gap:8px;margin-top:12px;padding:0;">
        @auth
            <button class="post-action-btn like-btn {{ $post->likes()->where('user_id',Auth::id())->exists() ? 'liked' : '' }}" onclick="toggleLike({{ $post->id }},this)" id="like-btn-{{ $post->id }}" style="flex:1;padding:10px;border:none;border-radius:6px;background:#f0f2f5;color:#1a1a1a;font-size:14px;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;">
                <i class="{{ $post->likes()->where('user_id',Auth::id())->exists() ? 'fas' : 'far' }} fa-heart"></i>
                <span class="like-count">{{ $post->like_count }}</span>
            </button>
        @else
            <a href="{{ route('login') }}" class="post-action-btn" style="flex:1;padding:10px;border:none;border-radius:6px;background:#f0f2f5;color:#1a1a1a;font-size:14px;display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;">
                <i class="far fa-heart"></i>
                <span class="like-count">{{ $post->like_count }}</span>
            </a>
        @endauth
        <button class="post-action-btn" onclick="toggleComments({{ $post->id }})" id="toggle-comments-{{ $post->id }}" style="flex:1;padding:10px;border:none;border-radius:6px;background:#f0f2f5;color:#1a1a1a;font-size:14px;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:8px;">
            <i class="far fa-comment"></i>
            <span class="comment-count">{{ $post->comment_count }}</span>
        </button>
    </div>

    <div class="comments-section" id="comments-section-{{ $post->id }}" style="display:none;margin-top:16px;padding:0;">
        <div class="comments-container" id="comments-container-{{ $post->id }}" style="margin:0;padding:0;">
            @foreach($post->comments as $comment)
                @include('community.partials.comment', ['comment' => $comment])
            @endforeach
        </div>
        @if($post->comment_count > 3)
            <button class="load-more-comments" id="load-more-{{ $post->id }}" data-offset="3" onclick="loadMoreComments({{ $post->id }})" style="width:100%;padding:8px;background:none;border:1px solid #e4e6eb;border-radius:6px;color:#1877f2;font-size:13px;cursor:pointer;margin:0 0 12px 0;">
                <i class="fas fa-chevron-down me-1"></i> Load more comments ({{ $post->comment_count - 3 }})
            </button>
        @endif
        @auth
            <form class="comment-form" onsubmit="submitComment(event,{{ $post->id }})" enctype="multipart/form-data" style="margin:0;padding:0;">
                @csrf
                <div class="comment-input-group" style="display:flex;gap:8px;align-items:flex-start;margin:0;padding:0;">
                    <div class="user-avatar-small" onclick="showUserModal({{ Auth::id() }})" style="width:32px;height:32px;border-radius:50%;overflow:hidden;flex-shrink:0;cursor:pointer;">
                        @if(Auth::user()->picture)
                            <img src="{{ asset('storage/' . Auth::user()->picture) }}" alt="{{ Auth::user()->name }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <div class="avatar-placeholder-small" style="width:100%;height:100%;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:12px;">{{ strtoupper(substr(Auth::user()->name,0,1)) }}</div>
                        @endif
                    </div>
                    <div class="comment-input-wrapper" style="flex:1;display:flex;gap:6px;background:#f0f2f5;border-radius:20px;padding:4px 4px 4px 12px;align-items:center;">
                        <textarea class="comment-textarea" placeholder="Write a comment..." rows="1" id="comment-input-{{ $post->id }}" style="flex:1;border:none;background:transparent;padding:8px 0;font-size:13px;resize:none;outline:none;min-height:32px;margin:0;font-family:inherit;"></textarea>
                        <div style="display:flex;gap:5px;align-items:center;">
                            <label for="comment-file-{{ $post->id }}" style="cursor:pointer;color:#1877f2;margin:0;"><i class="fas fa-paperclip"></i></label>
                            <input type="file" id="comment-file-{{ $post->id }}" class="d-none" accept="image/*,video/*,.pdf,.doc,.docx,.txt" onchange="handleCommentFileSelect({{ $post->id }},this)">
                            <button type="submit" class="comment-submit-btn" id="comment-submit-{{ $post->id }}" style="width:32px;height:32px;border:none;border-radius:50%;background:#1877f2;color:white;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin:0;padding:0;">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div id="comment-file-preview-{{ $post->id }}" class="file-preview-area" style="display:none;margin-top:8px;padding:0;">
                    <div class="file-preview-card" style="padding:8px;background:#f0f2f5;border-radius:8px;display:flex;align-items:center;gap:12px;">
                        <div id="comment-file-preview-content-{{ $post->id }}" class="file-preview-content" style="flex:1;display:flex;align-items:center;gap:12px;">
                            <!-- Preview will be shown here -->
                        </div>
                        <button type="button" class="remove-file" onclick="clearCommentFile({{ $post->id }})" style="background:none;border:none;color:#65676b;cursor:pointer;padding:4px 8px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </form>
        @endauth
    </div>
</div>
<script>
function toggleSeeMore(postId, event) {
    event.preventDefault();
    event.stopPropagation();
    
    const textContent = document.getElementById(`post-text-content-${postId}`);
    const seeMoreBtn = document.getElementById(`see-more-${postId}`);
    const seeMoreText = seeMoreBtn.querySelector('.see-more-text');
    const chevronIcon = seeMoreBtn.querySelector('i');
    const postCard = document.getElementById(`post-${postId}`);
    
    if (textContent.classList.contains('truncated')) {
        // Expand
        textContent.style.maxHeight = textContent.scrollHeight + 'px';
        textContent.classList.remove('truncated');
        seeMoreText.textContent = 'See Less';
        chevronIcon.style.transform = 'rotate(180deg)';
        
        // Smooth scroll to keep the post in view
        setTimeout(() => {
            const rect = postCard.getBoundingClientRect();
            const isInView = rect.top >= 0 && rect.bottom <= window.innerHeight;
            if (!isInView) {
                postCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 100);
    } else {
        // Collapse
        textContent.style.maxHeight = '200px';
        textContent.classList.add('truncated');
        seeMoreText.textContent = 'See More';
        chevronIcon.style.transform = 'rotate(0deg)';
        
        // Smooth scroll back to the post
        setTimeout(() => {
            postCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 50);
    }
}
</script>

<style>
/* Post link styles */
.post-link {
    color: #1877f2;
    text-decoration: none;
    word-break: break-all;
    display: inline;
    margin: 0;
    padding: 0;
}

.post-link:hover {
    text-decoration: underline;
    color: #0e5a9e;
}

/* Video container styles */
.video-embed-wrapper {
    width: 100%;
    margin: 0 0 16px 0;
    padding: 0;
    display: flex;
}

.video-embed-container {
    position: relative;
    width: 100%;
    aspect-ratio: 16/9;
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    background: #000;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.reel-container {
    width: auto !important;
    max-width: 360px;
    aspect-ratio: 9/16;
}

.video-embed-container:hover .fa-play {
    transform: scale(1.2);
}

/* See More button styles */
.see-more-btn {
    background: none;
    border: none;
    color: #1877f2;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    padding: 4px 0;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.see-more-btn:hover {
    opacity: 0.8;
}

.see-more-btn i {
    font-size: 12px;
    transition: transform 0.3s;
}

/* Truncated text */
.post-text-content.truncated {
    max-height: 200px;
    overflow: hidden;
    position: relative;
}

.post-text-content.truncated::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 40px;
    background: linear-gradient(transparent, white);
    pointer-events: none;
}
</style>