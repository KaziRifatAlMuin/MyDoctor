@php
    $description = $post->description ?? '';
    $isAuthenticated = Auth::check();
    $userLiked = $isAuthenticated ? $post->likes()->where('user_id', Auth::id())->exists() : false;
    $userStarred = $isAuthenticated
        ? $post->likes()->where('user_id', Auth::id())->where('is_starred', true)->exists()
        : false;
@endphp

<div class="post-card" id="post-{{ $post->id }}" data-post-id="{{ $post->id }}">
    <!-- Post Header -->
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
                        </span>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Three Dots Dropdown Menu -->
        <div class="post-actions-menu" style="position:relative; display:flex; align-items:center;">
            <button class="post-menu-btn" onclick="togglePostMenu({{ $post->id }})" title="More options" style="width:34px;height:34px;border:none;border-radius:50%;background:#f0f2f5;color:#65676b;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            
            <!-- Dropdown Menu -->
            <div class="post-dropdown-menu" id="post-menu-{{ $post->id }}" style="display:none; position:absolute; top:40px; right:0; background:white; border-radius:8px; box-shadow:0 2px 12px rgba(0,0,0,0.15); min-width:180px; z-index:1000; overflow:hidden;">
                <!-- View Full Post in Modal -->
                <button class="dropdown-item" onclick="openPostModal({{ $post->id }})" style="display:flex; align-items:center; gap:10px; width:100%; padding:10px 16px; border:none; background:none; color:#1a1a1a; cursor:pointer; transition:background 0.2s; text-align:left; border-bottom:1px solid #e4e6eb;">
                    <i class="fas fa-expand" style="width:18px; color:#1877f2;"></i>
                    <span>View Full Post</span>
                </button>

                @auth
                    <button class="dropdown-item" onclick="toggleStar({{ $post->id }}, document.getElementById('star-btn-{{ $post->id }}'))" style="display:flex; align-items:center; gap:10px; width:100%; padding:10px 16px; border:none; background:none; color:#1a1a1a; cursor:pointer; transition:background 0.2s; text-align:left; border-bottom:1px solid #e4e6eb;">
                        <i class="{{ $userStarred ? 'fas text-warning' : 'far' }} fa-star" style="width:18px;"></i>
                        <span>{{ $userStarred ? 'Remove Star' : 'Star Post' }}</span>
                    </button>
                @endauth
                
                @auth
                    @if(Auth::id() === $post->user_id)
                        <!-- Edit Post (only for owner) -->
                        <button class="dropdown-item" onclick="editPost({{ $post->id }})" style="display:flex; align-items:center; gap:10px; width:100%; padding:10px 16px; border:none; background:none; color:#1a1a1a; cursor:pointer; transition:background 0.2s; text-align:left; border-bottom:1px solid #e4e6eb;">
                            <i class="fas fa-edit" style="width:18px; color:#1877f2;"></i>
                            <span>Edit Post</span>
                        </button>
                        
                        <!-- Delete Post (only for owner) -->
                        <button class="dropdown-item text-danger" onclick="confirmDelete({{ $post->id }},'post')" style="display:flex; align-items:center; gap:10px; width:100%; padding:10px 16px; border:none; background:none; color:#dc3545; cursor:pointer; transition:background 0.2s; text-align:left;">
                            <i class="fas fa-trash" style="width:18px; color:#dc3545;"></i>
                            <span>Delete Post</span>
                        </button>
                    @endif
                @endauth
                
                <!-- Close option (useful for mobile) -->
                <button class="dropdown-item" onclick="togglePostMenu({{ $post->id }})" style="display:flex; align-items:center; gap:10px; width:100%; padding:10px 16px; border:none; background:none; color:#65676b; cursor:pointer; transition:background 0.2s; text-align:left;">
                    <i class="fas fa-times" style="width:18px; color:#65676b;"></i>
                    <span>Close Menu</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Post Content -->
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
                        <p class="post-text" id="post-content-{{ $post->id }}" style="margin:0;padding:0;line-height:1.5;font-size:15px;color:#1a1a1a;text-align:left;">
                            @php
                                $escapedText = e($textDescription);
                                $linkedText = preg_replace_callback('/(https?:\/\/[^\s]+)/', function($matches) {
                                    $url = $matches[1];
                                    $displayUrl = strlen($url) > 50 ? substr($url,0,47).'...' : $url;
                                    return '<a href="'.$url.'" target="_blank" rel="noopener noreferrer" class="post-link">'.$displayUrl.'</a>';
                                }, $escapedText);
                                echo nl2br($linkedText);
                            @endphp
                        </p>
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
                                    <div style="position:relative;padding-top:75%;background:#000;cursor:pointer;" onclick="openVideoModal('file','{{ $file['url'] }}',false)">
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

    <!-- Post Action Buttons -->
    <div class="post-action-buttons" style="display:flex;gap:8px;margin-top:12px;padding:0;">
        @auth
            <button class="post-action-btn like-btn {{ $userLiked ? 'liked' : '' }}" onclick="toggleLike({{ $post->id }},this)" id="like-btn-{{ $post->id }}" style="flex:1;padding:10px;border:none;border-radius:6px;background:#f0f2f5;color:#1a1a1a;font-size:14px;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;">
                <i class="{{ $userLiked ? 'fas' : 'far' }} fa-heart"></i>
                <span class="like-count">{{ $post->like_count }}</span>
            </button>
            <button class="post-action-btn star-btn {{ $userStarred ? 'starred' : '' }}" onclick="toggleStar({{ $post->id }},this)" id="star-btn-{{ $post->id }}" style="flex:0 0 auto;min-width:48px;padding:10px;border:none;border-radius:6px;background:#f0f2f5;color:#1a1a1a;font-size:14px;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:8px;" title="{{ $userStarred ? 'Remove star' : 'Star this post' }}">
                <i class="{{ $userStarred ? 'fas text-warning' : 'far' }} fa-star"></i>
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

    <!-- Comments Section -->
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
            <form class="comment-form" onsubmit="submitComment(event, {{ $post->id }}); return false;" style="margin:0;padding:0;">
                @csrf
                <div class="comment-input-group" style="display:flex;gap:8px;align-items:flex-start;margin:0;padding:0;">
                    <div class="user-avatar-small" onclick="showUserModal({{ Auth::id() }})" style="width:32px;height:32px;border-radius:50%;overflow:hidden;flex-shrink:0;cursor:pointer;">
                        @if(Auth::user()->picture)
                            <img src="{{ asset('storage/' . Auth::user()->picture) }}" alt="{{ Auth::user()->name }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <div class="avatar-placeholder-small" style="width:100%;height:100%;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:12px;">{{ strtoupper(substr(Auth::user()->name,0,1)) }}</div>
                        @endif
                    </div>
                    
                    <div style="flex:1;">
                        <!-- File Preview Container - UNIFIED -->
                        <div id="comment-file-preview-{{ $post->id }}" class="comment-file-preview" style="display: none; margin-bottom: 8px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; background: #f0f2f5; padding: 8px 12px; border-radius: 8px;">
                                <div id="comment-file-preview-content-{{ $post->id }}" class="file-preview-content" style="display: flex; align-items: center; gap: 8px; flex: 1;"></div>
                                <button type="button" onclick="clearCommentFile({{ $post->id }})" style="border: none; background: none; cursor: pointer; color: #65676b;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Input Area -->
                        <div class="comment-input-wrapper" style="display: flex; gap: 6px; background: #f0f2f5; border-radius: 20px; padding: 4px 4px 4px 12px; align-items: center;">
                            <textarea class="comment-textarea" 
                                      placeholder="Write a comment..." 
                                      rows="1" 
                                      id="comment-input-{{ $post->id }}" 
                                      name="comment_details"
                                      style="flex:1;border:none;background:transparent;padding:8px 0;font-size:13px;resize:none;outline:none;min-height:32px;font-family:inherit;"
                                      oninput="this.style.height = 'auto'; this.style.height = (this.scrollHeight) + 'px';"></textarea>
                            
                            <div style="display:flex;gap:5px;align-items:center;">
                                <label for="comment-file-{{ $post->id }}" style="cursor:pointer;color:#1877f2;margin:0;">
                                    <i class="fas fa-paperclip"></i>
                                </label>
                                <input type="file" 
                                       id="comment-file-{{ $post->id }}" 
                                       class="d-none" 
                                       accept="image/*,video/*,.pdf,.doc,.docx,.txt" 
                                       onchange="handleCommentFileSelect({{ $post->id }}, this)">
                                <button type="submit" 
                                        class="comment-submit-btn" 
                                        id="comment-submit-{{ $post->id }}" 
                                        style="width:32px;height:32px;border:none;border-radius:50%;background:#1877f2;color:white;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- REMOVED DUPLICATE PREVIEW CONTAINER -->
            </form>
        @endauth
    </div>
</div>

<script>
function togglePostMenu(postId) {
    const menu = document.getElementById(`post-menu-${postId}`);
    const isVisible = menu.style.display === 'block';
    
    // Close all other menus first
    document.querySelectorAll('.post-dropdown-menu').forEach(m => {
        if (m.id !== `post-menu-${postId}`) {
            m.style.display = 'none';
        }
    });
    
    // Toggle current menu
    menu.style.display = isVisible ? 'none' : 'block';
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.post-actions-menu')) {
        document.querySelectorAll('.post-dropdown-menu').forEach(menu => {
            menu.style.display = 'none';
        });
    }
});

function toggleSeeMore(postId, event) {
    event.preventDefault();
    event.stopPropagation();
    
    const textContent = document.getElementById(`post-text-content-${postId}`);
    const seeMoreBtn = document.getElementById(`see-more-${postId}`);
    if (!textContent || !seeMoreBtn) return;
    
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

/* Dropdown menu styles */
.post-dropdown-menu {
    border-radius: 8px;
    overflow: hidden;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.dropdown-item {
    font-size: 14px;
    transition: background 0.2s;
    cursor: pointer;
    font-family: inherit;
}

.dropdown-item:hover {
    background: #f0f2f5 !important;
}

.dropdown-item.text-danger:hover {
    background: #fee !important;
}

/* Ensure dropdown stays above other content */
.post-actions-menu {
    z-index: 100;
}

.post-dropdown-menu {
    z-index: 1000;
}

/* Comment File Preview Styles */
.comment-file-preview {
    display: none;
    margin-bottom: 8px;
    width: 100%;
}

.comment-file-preview > div {
    padding: 8px 12px;
    background: #f0f2f5;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.file-preview-content {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
}

.file-preview-content img {
    max-height: 40px;
    border-radius: 4px;
}

.file-preview-content i {
    font-size: 20px;
    color: #1877f2;
}

.comment-input-wrapper {
    display: flex;
    gap: 6px;
    background: #f0f2f5;
    border-radius: 20px;
    padding: 4px 4px 4px 12px;
    align-items: center;
}

.comment-textarea {
    flex: 1;
    border: none;
    background: transparent;
    padding: 8px 0;
    font-size: 13px;
    resize: none;
    outline: none;
    min-height: 32px;
    font-family: inherit;
}

.comment-submit-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 50%;
    background: #1877f2;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.comment-submit-btn:hover {
    background: #166fe5;
    transform: scale(1.05);
}

label[for^="comment-file-"] {
    cursor: pointer;
    color: #1877f2;
    margin: 0;
    padding: 0 5px;
}

label[for^="comment-file-"]:hover {
    color: #166fe5;
}
</style>