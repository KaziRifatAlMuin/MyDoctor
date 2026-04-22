@extends('layouts.app')

@section('title', __('ui.notifications.starred_notifications'))
@section('main_content_class', 'main-content main-content--wide')

@section('content')
<div class="notifications-section">
    <div class="container-fluid px-4 px-xl-5" style="max-width: 1200px;">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
               
                <h2 class="display-5 fw-bold text-dark mb-1">
                    <i class="fas fa-star me-2" style="color: #f7b500;"></i>{{ __('ui.notifications.starred_notifications') }}
                </h2>
                <p class="text-secondary lead fs-5">{{ __('ui.notifications.starred_notifications_description') }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('notifications.index') }}" class="btn btn-outline-primary btn-lg px-4 py-3 shadow-sm" style="border-radius: 12px;">
                    <i class="fas fa-bell me-2"></i>{{ __('ui.notifications.notifications') }}
                </a>
            </div>
        </div>

        @if($notifications->isNotEmpty())
            <div class="starred-list">
                @foreach($notifications as $notification)
                    <div class="starred-item" 
                         id="notification-{{ $notification->id }}"
                         data-post-preview="{{ e($notification->data['post_preview'] ?? '') }}"
                         onclick="openPostFromNotification({{ $notification->data['post_id'] ?? 'null' }}, {{ $notification->id }})">
                        
                        <!-- Star Icon -->
                        <div class="starred-icon" onclick="event.stopPropagation();">
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        
                        <!-- Avatar -->
                        <div class="starred-avatar" onclick="event.stopPropagation();">
                            @if($notification->fromUser && $notification->fromUser->picture)
                                <img src="{{ asset('storage/' . $notification->fromUser->picture) }}"
                                     alt="{{ $notification->fromUser->name }}">
                            @else
                                <div class="avatar-placeholder">
                                    {{ strtoupper(substr($notification->fromUser->name ?? 'U', 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="starred-content">
                            <div class="starred-header">
                                <div class="starred-title">
                                    {!! $notification->message !!}
                                </div>
                                <div class="starred-actions" onclick="event.stopPropagation();">
                                    @if(isset($notification->data['post_id']))
                                        <button class="icon-btn view-btn" onclick="openPostFromNotification({{ $notification->data['post_id'] }}, {{ $notification->id }})" title="{{ __('ui.notifications.view_post') }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endif
                                    <button class="icon-btn unstar-btn" onclick="unstarNotification({{ $notification->id }}, this)" 
                                            title="{{ __('ui.notifications.remove_star') }}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="starred-time">
                                <i class="far fa-clock me-1"></i>
                                {{ $notification->created_at->diffForHumans() }}
                            </div>
                            {{-- preview will be shown inside the post modal on click, not inline --}}
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($notifications->hasPages())
                <div class="pagination-wrapper mt-4">
                    {{ $notifications->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-star fa-4x"></i>
                </div>
                <h3 class="fw-bold mb-3">{{ __('ui.notifications.no_starred_notifications') }}</h3>
                <p class="text-muted mb-4">{{ __('ui.notifications.star_notifications_help') }}</p>
                <a href="{{ route('notifications.index') }}" class="btn btn-primary btn-lg px-5 py-3 rounded-pill">
                    <i class="fas fa-bell me-2"></i>{{ __('ui.notifications.view_all_notifications') }}
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Toast -->
<div id="toast" class="toast-notification" style="display: none;">
    <i class="fas fa-check-circle"></i>
    <span class="message" id="toastMessage"></span>
    <span class="close" onclick="hideToast()">✕</span>
</div>

@push('styles')
<style>
    .notifications-section {
        background: #f8f9fb;
        min-height: calc(100vh - 200px);
        padding: 2.5rem 0 3rem;
    }
    
    .starred-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .starred-item {
        background: white;
        border-radius: 16px;
        padding: 1.25rem;
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 0, 0, 0.04);
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
        position: relative;
        display: flex;
        gap: 1rem;
        cursor: pointer;
    }
    
    .starred-item:hover {
        transform: translateX(4px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        border-color: #f7b500;
    }
    
    .starred-icon {
        flex-shrink: 0;
        padding-top: 0.25rem;
    }
    
    .starred-icon i {
        font-size: 1.25rem;
        filter: drop-shadow(0 2px 4px rgba(247, 181, 0, 0.3));
    }
    
    .starred-avatar {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
        cursor: pointer;
    }
    
    .starred-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .avatar-placeholder {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f7b500 0%, #f59e0b 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        font-weight: 600;
    }
    
    .starred-content {
        flex: 1;
        min-width: 0;
    }
    
    .starred-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 0.5rem;
    }
    
    .starred-title {
        font-size: 0.95rem;
        font-weight: 500;
        color: #2d3748;
        line-height: 1.4;
        flex: 1;
    }
    
    .starred-title strong {
        font-weight: 700;
        color: #1a202c;
    }
    
    .starred-actions {
        display: flex;
        gap: 0.5rem;
        flex-shrink: 0;
    }
    
    .icon-btn {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: none;
        background: transparent;
        color: #a0aec0;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .icon-btn:hover {
        background: #f0f2f5;
        transform: scale(1.05);
    }
    
    .view-btn:hover {
        color: #667eea;
    }
    
    .unstar-btn:hover {
        color: #dc3545;
        background: #fff5f5;
    }
    
    .starred-time {
        font-size: 0.75rem;
        color: #a0aec0;
        margin-bottom: 0.5rem;
    }
    
    .starred-preview {
        font-size: 0.85rem;
        color: #718096;
        padding: 0.5rem 0.75rem;
        background: #f8f9fb;
        border-radius: 10px;
        margin-top: 0.5rem;
        line-height: 1.5;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
    }
    
    .empty-icon {
        margin-bottom: 1.5rem;
        opacity: 0.5;
    }
    
    .empty-icon i {
        color: #cbd5e0;
    }
    
    .empty-state h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }
    
    .empty-state p {
        color: #718096;
        margin-bottom: 1.5rem;
    }
    
    /* Pagination */
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        margin-top: 2rem;
    }
    
    .pagination {
        display: flex;
        gap: 0.5rem;
        list-style: none;
        padding: 0;
        margin: 0;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .page-item {
        display: flex;
    }
    
    .page-link {
        padding: 0.5rem 1rem;
        border-radius: 10px;
        color: #667eea;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.2s;
        background: white;
        border: 1px solid #e2e8f0;
    }
    
    .page-link:hover {
        background: #f0f2f5;
        border-color: #667eea;
        transform: translateY(-2px);
    }
    
    .page-item.active .page-link {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: transparent;
    }
    
    .page-item.disabled .page-link {
        color: #cbd5e0;
        border-color: #e2e8f0;
        cursor: not-allowed;
        transform: none;
    }
    
    /* Toast */
    .toast-notification {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        z-index: 9999;
        animation: slideIn 0.3s ease;
        border-left: 4px solid #28a745;
        border: 1px solid #e2e8f0;
    }
    
    .toast-notification.error {
        border-left-color: #dc3545;
    }
    
    .toast-notification i {
        font-size: 1.25rem;
    }
    
    .toast-notification.success i {
        color: #28a745;
    }
    
    .toast-notification.error i {
        color: #dc3545;
    }
    
    .toast-notification .message {
        font-size: 0.9rem;
        font-weight: 500;
        color: #2d3748;
    }
    
    .toast-notification .close {
        margin-left: 1rem;
        cursor: pointer;
        color: #a0aec0;
        font-size: 1rem;
        transition: color 0.2s;
    }
    
    .toast-notification .close:hover {
        color: #2d3748;
    }
    
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .starred-item {
            flex-wrap: wrap;
            padding: 1rem;
        }
        
        .starred-icon {
            order: 1;
        }
        
        .starred-avatar {
            order: 2;
            width: 44px;
            height: 44px;
        }
        
        .avatar-placeholder {
            width: 44px;
            height: 44px;
            font-size: 1rem;
        }
        
        .starred-content {
            order: 3;
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
// ========== POST MODAL FUNCTION ==========
function openPostFromNotification(postId, notificationId = null) {
    if (!postId) {
        console.warn('No post ID found for this notification');
        return;
    }
    
    if (notificationId) {
        fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .catch(err => console.error('Error:', err));
    }
    
    const modal = new bootstrap.Modal(document.getElementById('postModal'));
    const modalBody = document.getElementById('postModalBody');
    modalBody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">{{ __('ui.notifications.loading') }}</span>
            </div>
            <p class="mt-3 text-muted">{{ __('ui.notifications.loading_post') }}</p>
        </div>
    `;
    
    modal.show();
    
    fetch(`/community/modal-post/${postId}`, {
        headers: {
            'Accept': 'text/html',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to load post');
        return response.text();
    })
    .then(html => {
        modalBody.innerHTML = html;
    })
    .catch(error => {
        console.error('Error:', error);
        // Fallback: if notification contains a preview, show it inside the modal
        const notifEl = document.getElementById(`notification-${notificationId}`);
        const preview = notifEl?.dataset?.postPreview;
        if (preview) {
            modalBody.innerHTML = `<div class="reported-content"><strong class="reported-preview d-block mt-2">${escapeHtml(preview)}</strong></div>`;
        } else {
            modalBody.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-exclamation-circle text-danger" style="font-size: 48px;"></i>
                    <h5 class="mt-3">{{ __('ui.notifications.failed_to_load_post') }}</h5>
                    <p class="text-muted">{{ __('ui.notifications.try_again_later') }}</p>
                    <button class="btn btn-primary mt-2" onclick="location.reload()">{{ __('ui.notifications.refresh_page') }}</button>
                </div>
            `;
        }
    });
}

// ========== UNSTAR FUNCTION ==========
function unstarNotification(notificationId, button) {
    fetch(`/notifications/${notificationId}/star`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && !data.starred) {
            const card = document.getElementById(`notification-${notificationId}`);
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    card.remove();
                    showToast('{{ __("ui.notifications.unstarred") }}', 'success');
                    
                    if (document.querySelectorAll('.starred-item').length === 0) {
                        setTimeout(() => location.reload(), 500);
                    }
                }, 300);
            } else {
                showToast('{{ __("ui.notifications.unstarred") }}', 'success');
            }
        } else {
            showToast(data.message || '{{ __("ui.notifications.failed_to_toggle_star") }}', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('{{ __("ui.notifications.error_toggling_star") }}', 'error');
    });
}

// ========== TOAST FUNCTIONS ==========
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    const icon = toast.querySelector('i');
    
    toast.className = 'toast-notification ' + type;
    icon.className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
    toastMessage.textContent = message;
    
    toast.style.display = 'flex';
    
    setTimeout(hideToast, 3000);
}

function hideToast() {
    document.getElementById('toast').style.display = 'none';
}

// Make functions global
window.openPostFromNotification = openPostFromNotification;
window.unstarNotification = unstarNotification;
window.showToast = showToast;
window.hideToast = hideToast;
</script>
@endpush
@endsection