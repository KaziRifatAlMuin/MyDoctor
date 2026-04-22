@php
    $description = $post->description ?? '';
    $isAuthenticated = Auth::check();
    
    // SAFE: Check if user exists
    $postUser = $post->user;
    $isOwner = $isAuthenticated && $postUser && Auth::id() === $postUser->id;
    $isAdmin = $isAuthenticated && Auth::user()->isAdmin();
    $adminReadOnlyCommunity = $adminReadOnlyCommunity ?? false;
    $isAdminReadOnly = $isAdmin && $adminReadOnlyCommunity;
    $isAnonymous = (bool) $post->is_anonymous;
    
    // Check if post is rejected/deleted by admin
    $isRejected = $post->rejected_at !== null;
    $rejectedBy = $post->rejectedBy;
    $rejectionReason = $post->rejection_reason;
    
    // SAFE: Default values for deleted users
    $userName = $postUser ? $postUser->name : 'Deleted User';
    $userPicture = $postUser ? $postUser->picture : null;
    $userId = $postUser ? $postUser->id : 0;
    $displayName = $isAnonymous ? __('ui.community.anonymous_member') : $userName;
    
    $userLiked = $isAuthenticated && $postUser ? $post->likes()->where('user_id', Auth::id())->exists() : false;
    $userStarred = $isAuthenticated && $postUser
        ? $post->likes()->where('user_id', Auth::id())->where('is_starred', true)->exists()
        : false;
        
    // SAFE: Disease display names
    $postDiseases = $post->disease_models;
    $diseaseDisplayName = $postDiseases->pluck('display_name')->implode(', ');
@endphp

@if($isRejected)
    <!-- Rejected Post Banner -->
    <div style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 16px; text-align: center; border-radius: 8px; margin: 16px;">
        <i class="fas fa-times-circle fa-2x mb-2"></i>
        <h5 style="margin: 0 0 8px 0; font-weight: 600;">Post Deleted by Admin</h5>
        <p style="margin: 0; font-size: 14px; opacity: 0.9;">
            This post was removed from the community.
            @if($rejectionReason)
                <br><strong>Reason:</strong> {{ $rejectionReason }}
            @endif
            @if($rejectedBy)
                <br><small>Rejected by {{ $rejectedBy->name }} on {{ $post->rejected_at->format('M j, Y \a\t g:i A') }}</small>
            @endif
        </p>
    </div>
@endif

<div class="modal-post-container" data-post-id="{{ $post->id }}" style="padding: 0; display: flex; position: relative; flex-direction: column; height: 100%; max-height: calc(90vh - 60px);">
    
    <!-- Scrollable Content Area -->
    <div style="flex: 1; overflow-y: auto; padding: 0;">
        
        <!-- Post Header -->
        <div style="padding: 16px 16px 12px 16px; margin: 0; display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #e4e6eb;">
            <div style="display: flex; gap: 12px; cursor: {{ $isAnonymous ? 'default' : ($userId ? 'pointer' : 'default') }};" @if(!$isAnonymous && $userId) onclick="showUserModal({{ $userId }})" @endif>
                <div style="width: 48px; height: 48px; border-radius: 50%; overflow: hidden; flex-shrink: 0;">
                    @if(!$isAnonymous && $userPicture)
                        <img src="{{ asset('storage/' . $userPicture) }}" alt="{{ $userName }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 18px;">
                            {{ $isAnonymous ? 'A' : ($userName ? strtoupper(substr($userName,0,1)) : '?') }}
                        </div>
                    @endif
                </div>
                <div>
                    <h6 style="font-size: 15px; font-weight: 600; margin: 0; padding: 0; color: #1a1a1a;">
                        {{ $displayName }}
                        @if(!$userId && !$isAnonymous)
                            <span style="font-size: 11px; color: #dc3545; margin-left: 4px;">(Deleted User)</span>
                        @endif
                    </h6>
                    <div style="display: flex; align-items: center; gap: 12px; font-size: 12px; color: #65676b; margin: 0; padding: 0;">
                        @if($post->approved_at)
                            <span><i class="far fa-clock me-1"></i>{{ $post->approved_at->diffForHumans() }}</span>
                        @else
                            <span><i class="far fa-clock me-1"></i>{{ $post->created_at->diffForHumans() }}</span>
                        @endif
                        @if($post->is_edited)
                            <span style="font-size:11px; font-weight:600; color:#65676b; background:#f0f2f5; border-radius:12px; padding:4px 8px;">{{ __('ui.community.edited') }}</span>
                        @endif
                        @if($postDiseases->isNotEmpty())
                            @foreach($postDiseases as $disease)
                                <a href="{{ route('community.disease.posts', $disease) }}" style="background: #e7f3ff; color: #1877f2; padding: 4px 12px; border-radius: 4px; font-weight: 500; font-size: 12px; display: inline-flex; align-items: center; gap: 4px; text-decoration: none;">
                                    <i class="fas fa-tag me-1"></i>{{ $disease->display_name }}
                                </a>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons - Star, Report, Edit, Delete, Approve -->
            @auth
                @if(!$isRejected)
                <div style="display: flex; gap: 8px;">
                    <!-- Star Button - Hide for admin -->
                    @if(! $isAdminReadOnly && !$isAdmin)
                        <button onclick="toggleStar({{ $post->id }}, this)" 
                                id="star-btn-{{ $post->id }}" 
                                style="width: 34px; height: 34px; border: none; border-radius: 50%; background: #f0f2f5; color: {{ $userStarred ? '#f7b500' : '#65676b' }}; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" 
                                title="{{ $userStarred ? __('ui.community.unstar') : __('ui.community.star') }}">
                            <i class="{{ $userStarred ? 'fas' : 'far' }} fa-star" style="color: {{ $userStarred ? '#f7b500' : 'inherit' }};"></i>
                        </button>
                    @endif
                    
                    <!-- Report Button - Hide for admin -->
                    @if(! $isAdminReadOnly && !$isAdmin)
                        <button onclick="reportPost({{ $post->id }})" 
                                style="width: 34px; height: 34px; border: none; border-radius: 50%; background: #f0f2f5; color: #dc3545; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" 
                                title="{{ __('ui.community.report_post') }}">
                            <i class="fas fa-flag"></i>
                        </button>
                    @endif
                    
                    <!-- Approve Button (Admin only for unapproved posts) -->
                    @if($isAdmin && !$post->is_approved)
                        <button onclick="approvePost({{ $post->id }})" 
                                style="width: 34px; height: 34px; border: none; border-radius: 50%; background: #f0f2f5; color: #198754; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" 
                                title="{{ __('ui.community.approve_post') }}">
                            <i class="fas fa-check-circle"></i>
                        </button>
                    @endif
                    
                    <!-- Edit Button (Owner only - not for admin) -->
                    @if($isOwner && ! $isAdminReadOnly && !$isAdmin)
                        <button onclick="editPost({{ $post->id }})" 
                                style="width: 34px; height: 34px; border: none; border-radius: 50%; background: #f0f2f5; color: #1877f2; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" 
                                title="{{ __('ui.community.edit_post') }}">
                            <i class="fas fa-edit"></i>
                        </button>
                    @endif
                    
                    <!-- Delete Button (Owner or Admin) -->
                    @if($isOwner || $isAdmin)
                        <button onclick="confirmDelete({{ $post->id }}, 'post')" 
                                style="width: 34px; height: 34px; border: none; border-radius: 50%; background: #f0f2f5; color: #dc3545; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" 
                                title="{{ __('ui.community.delete_post') }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    @endif
                </div>
                @endif
            @endauth
        </div>

        <!-- Post Content -->
        <div style="padding: 0 16px; margin: 0;">
            @php
                $youtubePattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:watch\?v=|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/';
                preg_match($youtubePattern, $description, $youtubeMatches);
                
                $textDescription = $description;
                $videoUrls = [];
                if (!empty($youtubeMatches)) $videoUrls[] = $youtubeMatches[0];
                
                foreach ($videoUrls as $videoUrl) $textDescription = str_replace($videoUrl, '', $textDescription);
                $textDescription = trim($textDescription);
            @endphp
            
            @if(!empty($textDescription))
                <p style="margin: 12px 0 0 0; padding: 0; line-height: 1.5; font-size: 15px; color: #1a1a1a;" id="post-content-{{ $post->id }}">
                    @php
                        $escapedText = e($textDescription);
                        $linkedText = preg_replace_callback('/(https?:\/\/[^\s]+)/', function($matches) {
                            $url = $matches[1];
                            $displayUrl = strlen($url) > 50 ? substr($url,0,47).'...' : $url;
                            return '<a href="'.$url.'" target="_blank" rel="noopener noreferrer" style="color:#1877f2; text-decoration:none;">'.$displayUrl.'</a>';
                        }, $escapedText);
                        echo nl2br($linkedText);
                    @endphp
                </p>
            @endif
            
            <!-- YouTube Embed -->
            @if(!empty($youtubeMatches))
                @php $videoId = $youtubeMatches[1]; $isShort = str_contains($youtubeMatches[0],'shorts/'); @endphp
                <div style="margin: 12px 0 0 0; padding: 0;">
                    <div onclick="openVideoModal('youtube','{{ $videoId }}',{{ $isShort ? 'true' : 'false' }})" style="position: relative; width: 100%; aspect-ratio: {{ $isShort ? '9/16' : '16/9' }}; border-radius: 12px; overflow: hidden; cursor: pointer; background: #000;">
                        <img src="https://img.youtube.com/vi/{{ $videoId }}/hqdefault.jpg" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); width: 60px; height: 60px; background: rgba(0,0,0,0.7); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-play" style="color: white; font-size: 24px;"></i>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Post Attachments -->
            @if($post->file_count > 0)
                @php $files = $post->all_files; $fileCount = count($files); @endphp
                <div style="margin: 12px 0 0 0; padding: 0;">
                    @if($fileCount === 1)
                        @php $file = $files[0]; @endphp
                        @if(str_starts_with($file['type'],'image/'))
                            <div style="margin: 0; padding: 0; border-radius: 12px; overflow: hidden; cursor: pointer;" onclick="openImageModal('{{ $file['url'] }}')">
                                <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}" style="width: 100%; max-height: 400px; object-fit: contain; display: block;">
                            </div>
                        @elseif(str_starts_with($file['type'],'video/'))
                            <div onclick="openVideoModal('file','{{ $file['url'] }}',false)" style="position: relative; width: 100%; aspect-ratio: 16/9; background: #000; border-radius: 12px; overflow: hidden; cursor: pointer;">
                                <video style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: contain;">
                                    <source src="{{ $file['url'] }}" type="{{ $file['type'] }}">
                                </video>
                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); width: 60px; height: 60px; background: rgba(0,0,0,0.7); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-play" style="color: white; font-size: 24px;"></i>
                                </div>
                            </div>
                        @else
                            <div style="display: flex; align-items: center; gap: 12px; padding: 16px; background: #f0f2f5; border-radius: 12px;">
                                <i class="fas {{ $file['icon'] }}" style="font-size: 32px; color: #1877f2;"></i>
                                <div style="flex: 1;">
                                    <div style="font-weight: 500; margin-bottom: 4px;">{{ $file['name'] }}</div>
                                    <div style="font-size: 12px; color: #65676b;">{{ $file['formatted_size'] }}</div>
                                </div>
                                <a href="{{ $file['url'] }}" download style="color: #1877f2; padding: 8px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; text-decoration: none;">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        @endif
                    @else
                        <div style="display: grid; grid-template-columns: repeat(2,1fr); gap: 12px; margin: 0; padding: 0;">
                            @foreach($files as $file)
                                <div style="border: 1px solid #e4e6eb; border-radius: 10px; overflow: hidden; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                    @if(str_starts_with($file['type'],'image/'))
                                        <div style="position: relative; padding-top: 75%; background: #f0f2f5; cursor: pointer;" onclick="openImageModal('{{ $file['url'] }}')">
                                            <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
                                        </div>
                                    @elseif(str_starts_with($file['type'],'video/'))
                                        <div style="position: relative; padding-top: 75%; background: #000; cursor: pointer;" onclick="openVideoModal('file','{{ $file['url'] }}',false)">
                                            <video style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
                                                <source src="{{ $file['url'] }}" type="{{ $file['type'] }}">
                                            </video>
                                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); width: 40px; height: 40px; background: rgba(0,0,0,0.7); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-play" style="color: white; font-size: 16px;"></i>
                                            </div>
                                        </div>
                                    @else
                                        <div style="padding: 20px; text-align: center; background: #f0f2f5;">
                                            <i class="fas {{ $file['icon'] }}" style="font-size: 40px; color: #1877f2;"></i>
                                        </div>
                                        <div style="padding: 10px; background: white;">
                                            <div style="font-size: 12px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 4px;">{{ Str::limit($file['name'],20) }}</div>
                                            <div style="display: flex; justify-content: flex-end;">
                                                <a href="{{ $file['url'] }}" download style="color: #1877f2; padding: 4px 8px; border-radius: 4px; background: #e7f3ff; text-decoration: none; display: flex; align-items: center; gap: 4px;">
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
        <div style="display: flex; gap: 8px; margin: 12px 16px 0 16px; padding: 0;">
            @auth
                @if(! $isAdminReadOnly && !$isAdmin)
                    <button onclick="toggleLike({{ $post->id }}, this)" id="like-btn-{{ $post->id }}" style="flex: 1; padding: 10px; border: none; border-radius: 6px; background: #f0f2f5; color: #1a1a1a; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;" class="{{ $userLiked ? 'liked' : '' }}">
                        <i class="{{ $userLiked ? 'fas' : 'far' }} fa-heart" style="{{ $userLiked ? 'color: #dc3545;' : '' }}"></i>
                        <span class="like-count">{{ $post->like_count }}</span>
                    </button>
                @else
                    <div style="flex: 1; padding: 10px; border: none; border-radius: 6px; background: #f8f6ff; color: #4b5563; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="far fa-heart"></i>
                        <span class="like-count">{{ $post->like_count }}</span>
                    </div>
                @endif
            @else
                <a href="{{ route('login') }}" style="flex: 1; padding: 10px; border: none; border-radius: 6px; background: #f0f2f5; color: #1a1a1a; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none;">
                    <i class="far fa-heart"></i>
                    <span class="like-count">{{ $post->like_count }}</span>
                </a>
            @endauth
            <button onclick="toggleCommentsModal({{ $post->id }})" style="flex: 1; padding: 10px; border: none; border-radius: 6px; background: #f0f2f5; color: #1a1a1a; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i class="far fa-comment"></i>
                <span class="comment-count" data-post="{{ $post->id }}">{{ $post->comment_count }}</span>
            </button>
        </div>

        <!-- Comments Display Area -->
        <div id="comments-section-{{ $post->id }}" style="margin: 12px 0 0 0; padding: 16px; border-top: 1px solid #e4e6eb;">
            <div id="comments-container-{{ $post->id }}" style="margin: 0; padding: 0; max-height: 400px; overflow-y: auto;">
                @forelse($post->comments as $comment)
                    @include('community.partials.comment', ['comment' => $comment, 'adminReadOnlyCommunity' => $adminReadOnlyCommunity])
                @empty
                    <div style="text-align: center; padding: 20px; color: #65676b;">
                        <i class="fas fa-comment-slash fa-2x mb-2"></i>
                        <p>{{ __('ui.community.no_comments_yet') }}</p>
                    </div>
                @endforelse
            </div>
            
            @if($post->comment_count > $post->comments->count())
            <button class="load-more-comments-modal" 
                    id="modal-load-more-{{ $post->id }}" 
                    data-offset="{{ $post->comments->count() }}" 
                    onclick="loadMoreModalComments({{ $post->id }})" 
                    style="width:100%; padding:8px; background:none; border:1px solid #e4e6eb; border-radius:6px; color:#1877f2; font-size:13px; cursor:pointer; margin-top: 12px;">
                <i class="fas fa-chevron-down me-1"></i> {{ __('ui.community.load_more_comments') }} ({{ $post->comment_count - $post->comments->count() }})
            </button>
            @endif
        </div>

    </div>

    <!-- Sticky Comment Form at Bottom -->
    @auth
        @if(! $isAdminReadOnly && !$isAdmin)
        <div style="border-top: 1px solid #e4e6eb; padding: 12px 16px; background: white; flex-shrink: 0;">
            <form id="modal-comment-form-{{ $post->id }}" onsubmit="submitModalComment(event, {{ $post->id }}); return false;">
                @csrf
                <div style="display: flex; gap: 8px; align-items: flex-start;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; overflow: hidden; flex-shrink: 0; cursor: pointer;" onclick="showUserModal({{ Auth::id() }})">
                        @if(Auth::user()->picture)
                            <img src="{{ asset('storage/' . Auth::user()->picture) }}" alt="{{ Auth::user()->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <div style="width: 100%; height: 100%; background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 12px;">{{ strtoupper(substr(Auth::user()->name,0,1)) }}</div>
                        @endif
                    </div>
                    
                    <div style="flex: 1;">
                        <!-- File Preview Container -->
                        <div id="modal-comment-file-preview-{{ $post->id }}" class="comment-file-preview" style="display: none; margin-bottom: 8px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; background: #f0f2f5; padding: 8px 12px; border-radius: 8px;">
                                <div id="modal-comment-file-preview-content-{{ $post->id }}" class="file-preview-content" style="display: flex; align-items: center; gap: 8px; flex: 1;"></div>
                                <button type="button" onclick="clearModalCommentFile({{ $post->id }})" style="border: none; background: none; cursor: pointer; color: #65676b; padding: 4px;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Input Area -->
                        <div style="display: flex; gap: 6px; background: #f0f2f5; border-radius: 20px; padding: 4px 4px 4px 12px; align-items: center;">
                            <textarea 
                                id="modal-comment-input-{{ $post->id }}"
                                placeholder="{{ __('ui.community.write_comment') }}" 
                                rows="1" 
                                name="comment_details"
                                style="flex: 1; border: none; background: transparent; padding: 8px 0; font-size: 13px; resize: none; outline: none; min-height: 32px; max-height: 80px; font-family: inherit; line-height: 1.4;"
                                oninput="this.style.height = 'auto'; this.style.height = (this.scrollHeight) + 'px';"></textarea>
                            
                            <div style="display: flex; gap: 5px; align-items: center;">
                                <label for="modal-comment-file-{{ $post->id }}" style="cursor: pointer; color: #1877f2; margin: 0; padding: 0 5px;">
                                    <i class="fas fa-paperclip"></i>
                                </label>
                                
                                <input type="file" 
                                    id="modal-comment-file-{{ $post->id }}" 
                                    name="file"
                                    accept="image/*,video/*,.pdf,.doc,.docx,.txt" 
                                    onchange="handleModalCommentFileSelect({{ $post->id }}, this)"
                                    style="display: none;">
                                    
                                <button type="submit" 
                                        id="modal-comment-submit-{{ $post->id }}" 
                                        style="width: 32px; height: 32px; border: none; border-radius: 50%; background: #1877f2; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        @endif
    @endauth

</div>

<style>
/* Base Styles */
.modal-post-container {
    background: white;
    color: #1a1a1a;
    font-family: inherit;
}

.modal-post-container * {
    box-sizing: border-box;
}

.modal-post-container p,
.modal-post-container h6,
.modal-post-container div,
.modal-post-container span {
    margin: 0;
    padding: 0;
    font-family: inherit;
}

.modal-post-container button {
    font-family: inherit;
    cursor: pointer;
}

.modal-post-container a {
    color: #1877f2;
    text-decoration: none;
}

.modal-post-container a:hover {
    text-decoration: underline;
}

/* Post Content */
.modal-post-container p {
    line-height: 1.5;
    font-size: 15px;
    color: #1a1a1a;
}

/* Like Button */
button.liked {
    background: #fee !important;
    color: #dc3545 !important;
}

button.liked i {
    color: #dc3545 !important;
}

/* Star Button */
button.starred {
    background: #fff8e7 !important;
    color: #f7b500 !important;
}

button.starred i {
    color: #f7b500 !important;
}

/* File Preview */
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

/* Edit/Delete Buttons */
button[onclick*="editPost"]:hover,
button[onclick*="confirmDelete"]:hover,
button[onclick*="reportPost"]:hover,
button[onclick*="toggleStar"]:hover,
button[onclick*="approvePost"]:hover {
    transform: scale(1.1);
}

button[onclick*="editPost"]:hover {
    background: #e7f3ff !important;
}

button[onclick*="confirmDelete"]:hover,
button[onclick*="reportPost"]:hover {
    background: #fee !important;
}

button[onclick*="toggleStar"]:hover {
    background: #fff8e7 !important;
}

button[onclick*="approvePost"]:hover {
    background: #e8f5e9 !important;
}

/* Comments */
.modal-post-container .comment {
    display: flex;
    gap: 10px;
    margin-bottom: 12px;
}

.modal-post-container .comment:last-child {
    margin-bottom: 0;
}

.modal-post-container .comment-content {
    flex: 1;
    background: #f0f2f5;
    padding: 10px 12px;
    border-radius: 18px;
}

.modal-post-container .comment-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
    flex-wrap: wrap;
}

.modal-post-container .comment-author {
    font-weight: 600;
    font-size: 13px;
    color: #1a1a1a;
    cursor: pointer;
}

.modal-post-container .comment-time {
    font-size: 11px;
    color: #65676b;
}

.modal-post-container .comment-text {
    font-size: 13px;
    color: #1a1a1a;
    margin-bottom: 6px;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

/* Load More Button */
.load-more-comments-modal {
    transition: all 0.2s ease;
}

.load-more-comments-modal:hover {
    background: #e7f3ff;
    border-color: #1877f2;
}

/* Spinner */
.spinner-small {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>