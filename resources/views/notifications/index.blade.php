@extends('layouts.app')

@section('title', __('ui.notifications.notifications'))
@section('main_content_class', 'main-content main-content--wide')

@section('content')
<div class="notifications-section">
    <div class="container-fluid px-4 px-xl-5" style="max-width: 1200px;">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
             
                <h2 class="display-5 fw-bold text-dark mb-1">
                    <i class="fas fa-bell me-2" style="color: #667eea;"></i>{{ __('ui.notifications.notifications') }}
                </h2>
                <p class="text-secondary lead fs-5">{{ __('ui.notifications.stay_updated') }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('notifications.starred') }}" class="btn btn-outline-warning btn-lg px-4 py-3 shadow-sm" style="border-radius: 12px;">
                    <i class="fas fa-star me-2"></i>{{ __('ui.notifications.view_starred') }}
                </a>
            </div>
        </div>

        @if($notifications->isNotEmpty())
            <!-- Action Bar -->
            <div class="action-bar mb-4">
                <div class="action-left">
                    <div class="select-all">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                        <label for="selectAll">{{ __('ui.notifications.select_all') }}</label>
                    </div>
                    <span class="selected-count" id="selectedCount">0 {{ __('ui.notifications.selected') }}</span>
                </div>
                <div class="action-right">
                    <button class="btn-action mark-read" id="markReadBtn" onclick="markSelectedRead()" disabled>
                        <i class="fas fa-check me-2"></i>{{ __('ui.notifications.mark_as_read') }}
                    </button>
                    <button class="btn-action clear" id="clearBtn" onclick="showClearModal()" disabled>
                        <i class="fas fa-trash-alt me-2"></i>{{ __('ui.notifications.clear') }}
                    </button>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="notifications-list">
                @foreach($notifications as $notification)
                    <div class="notification-item {{ is_null($notification->read_at) ? 'unread' : '' }}" 
                         id="notification-{{ $notification->id }}"
                         onclick="openPostFromNotification({{ $notification->data['post_id'] ?? 'null' }}, {{ $notification->id }})">
                        
                        <!-- Checkbox -->
                        <div class="notification-checkbox" onclick="event.stopPropagation();">
                            <input type="checkbox" 
                                   class="notification-check" 
                                   data-id="{{ $notification->id }}"
                                   onchange="updateSelection()">
                        </div>
                        
                        <!-- Avatar -->
                        <div class="notification-avatar" onclick="event.stopPropagation();">
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
                        <div class="notification-content">
                            <div class="notification-header-info">
                                <div class="notification-title">
                                    {!! $notification->message !!}
                                    @if(is_null($notification->read_at))
                                        <span class="new-badge ms-2">{{ __('ui.notifications.new') }}</span>
                                    @endif
                                </div>
                                <div class="notification-actions" onclick="event.stopPropagation();">
                                    @if(isset($notification->data['post_id']))
                                        <button class="icon-btn view-btn" onclick="openPostFromNotification({{ $notification->data['post_id'] }}, {{ $notification->id }})" title="{{ __('ui.notifications.view_post') }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endif
                                    <button class="icon-btn star-btn" onclick="toggleStar({{ $notification->id }}, this)" 
                                            data-starred="{{ $notification->isStarred() ? 'true' : 'false' }}">
                                        <i class="{{ $notification->isStarred() ? 'fas fa-star text-warning' : 'far fa-star' }}"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="notification-time">
                                <i class="far fa-clock me-1"></i>
                                {{ $notification->created_at->diffForHumans() }}
                            </div>
                            @if(isset($notification->data['post_preview']))
                                <div class="notification-preview">
                                    {{ Str::limit($notification->data['post_preview'], 120) }}
                                </div>
                            @endif
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
                    <i class="fas fa-bell-slash fa-4x"></i>
                </div>
                <h3 class="fw-bold mb-3">{{ __('ui.notifications.no_notifications_yet') }}</h3>
                <p class="text-muted mb-4">{{ __('ui.notifications.when_someone_interacts') }}</p>
                <a href="{{ route('notifications.starred') }}" class="btn btn-outline-warning btn-lg px-5 py-3 rounded-pill">
                    <i class="fas fa-star me-2"></i>{{ __('ui.notifications.view_starred') }}
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

<!-- Confirm Modal -->
<div id="confirmModal" class="modal-overlay" style="display: none;">
    <div class="confirm-modal">
        <i class="fas fa-exclamation-triangle"></i>
        <h3>{{ __('ui.notifications.clear_selected_notifications') }}</h3>
        <p>{{ __('ui.notifications.clear_warning') }}</p>
        <div class="modal-actions">
            <button class="btn-modal cancel" onclick="hideModal()">{{ __('ui.notifications.cancel') }}</button>
            <button class="btn-modal confirm" onclick="clearSelected()">{{ __('ui.notifications.clear') }}</button>
        </div>
    </div>
</div>

@push('styles')
<style>
    .notifications-section {
        background: #f8f9fb;
        min-height: calc(100vh - 200px);
        padding: 2.5rem 0 3rem;
    }
    
    /* Action Bar */
    .action-bar {
        background: white;
        border-radius: 16px;
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
        border: 1px solid rgba(0, 0, 0, 0.04);
    }
    
    .action-left {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        flex-wrap: wrap;
    }
    
    .select-all {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .select-all input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #667eea;
    }
    
    .select-all label {
        font-size: 0.9rem;
        font-weight: 500;
        color: #2d3748;
        cursor: pointer;
        margin: 0;
    }
    
    .selected-count {
        font-size: 0.85rem;
        color: #718096;
        background: #f0f2f5;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
    }
    
    .action-right {
        display: flex;
        gap: 0.75rem;
    }
    
    .btn-action {
        padding: 0.6rem 1.25rem;
        border-radius: 40px;
        font-size: 0.85rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-action.mark-read {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .btn-action.mark-read:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }
    
    .btn-action.clear {
        background: white;
        color: #dc3545;
        border: 1px solid #e2e8f0;
    }
    
    .btn-action.clear:hover:not(:disabled) {
        background: #fff5f5;
        border-color: #dc3545;
        transform: translateY(-2px);
    }
    
    .btn-action:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    /* Notifications List */
    .notifications-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .notification-item {
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
    
    .notification-item:hover {
        transform: translateX(4px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        border-color: #667eea;
    }
    
    .notification-item.unread {
        background: linear-gradient(135deg, #ffffff 0%, #f8f5ff 100%);
        border-left: 4px solid #667eea;
    }
    
    .notification-item.selected {
        border: 2px solid #667eea;
        background: #f8f9fb;
    }
    
    .notification-checkbox {
        padding-top: 0.25rem;
        flex-shrink: 0;
    }
    
    .notification-checkbox input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #667eea;
    }
    
    .notification-avatar {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
        cursor: pointer;
    }
    
    .notification-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .avatar-placeholder {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        font-weight: 600;
    }
    
    .notification-content {
        flex: 1;
        min-width: 0;
    }
    
    .notification-header-info {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 0.5rem;
    }
    
    .notification-title {
        font-size: 0.95rem;
        font-weight: 500;
        color: #2d3748;
        line-height: 1.4;
        flex: 1;
    }
    
    .notification-title strong {
        font-weight: 700;
        color: #1a202c;
    }
    
    .new-badge {
        display: inline-block;
        background: #dc3545;
        color: white;
        font-size: 0.65rem;
        font-weight: 600;
        padding: 0.2rem 0.5rem;
        border-radius: 20px;
        vertical-align: middle;
    }
    
    .notification-actions {
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
    
    .star-btn:hover {
        color: #f7b500;
    }
    
    .notification-time {
        font-size: 0.75rem;
        color: #a0aec0;
        margin-bottom: 0.5rem;
    }
    
    .notification-preview {
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
    
    /* Confirm Modal */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        animation: fadeIn 0.2s ease;
    }
    
    .confirm-modal {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        max-width: 400px;
        width: 90%;
        text-align: center;
        animation: scaleIn 0.2s ease;
        border: 1px solid #e2e8f0;
    }
    
    .confirm-modal i {
        font-size: 3rem;
        color: #f59e0b;
        margin-bottom: 1rem;
    }
    
    .confirm-modal h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }
    
    .confirm-modal p {
        color: #718096;
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
    }
    
    .modal-actions {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
    }
    
    .btn-modal {
        padding: 0.6rem 1.5rem;
        border-radius: 40px;
        font-size: 0.85rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-modal.cancel {
        background: #f0f2f5;
        color: #4a5568;
    }
    
    .btn-modal.cancel:hover {
        background: #e2e8f0;
        transform: translateY(-2px);
    }
    
    .btn-modal.confirm {
        background: #dc3545;
        color: white;
    }
    
    .btn-modal.confirm:hover {
        background: #c82333;
        transform: translateY(-2px);
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes scaleIn {
        from {
            transform: scale(0.95);
            opacity: 0;
        }
        to {
            transform: scale(1);
            opacity: 1;
        }
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .action-bar {
            flex-direction: column;
            align-items: stretch;
        }
        
        .action-left {
            justify-content: space-between;
        }
        
        .action-right {
            width: 100%;
        }
        
        .btn-action {
            flex: 1;
            justify-content: center;
        }
        
        .notification-item {
            flex-wrap: wrap;
            padding: 1rem;
        }
        
        .notification-checkbox {
            order: 1;
        }
        
        .notification-avatar {
            order: 2;
            width: 44px;
            height: 44px;
        }
        
        .avatar-placeholder {
            width: 44px;
            height: 44px;
            font-size: 1rem;
        }
        
        .notification-content {
            order: 3;
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
let selectedIds = new Set();

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
        .then(data => {
            if (data.success) {
                const card = document.getElementById(`notification-${notificationId}`);
                if (card) {
                    card.classList.remove('unread');
                }
                updateNotificationCount();
            }
        })
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
        modalBody.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-exclamation-circle text-danger" style="font-size: 48px;"></i>
                <h5 class="mt-3">{{ __('ui.notifications.failed_to_load_post') }}</h5>
                <p class="text-muted">{{ __('ui.notifications.try_again_later') }}</p>
                <button class="btn btn-primary mt-2" onclick="location.reload()">{{ __('ui.notifications.refresh_page') }}</button>
            </div>
        `;
    });
}

// ========== SELECTION FUNCTIONS ==========
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.notification-check');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelection();
}

function updateSelection() {
    selectedIds.clear();
    
    document.querySelectorAll('.notification-check:checked').forEach(cb => {
        selectedIds.add(parseInt(cb.dataset.id));
    });
    
    const count = selectedIds.size;
    document.getElementById('selectedCount').textContent = `${count} {{ __('ui.notifications.selected') }}`;
    
    const totalCheckboxes = document.querySelectorAll('.notification-check').length;
    const selectAll = document.getElementById('selectAll');
    
    if (count === 0) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
    } else if (count === totalCheckboxes) {
        selectAll.checked = true;
        selectAll.indeterminate = false;
    } else {
        selectAll.indeterminate = true;
    }
    
    document.querySelectorAll('.notification-item').forEach(card => {
        const id = parseInt(card.id.replace('notification-', ''));
        if (selectedIds.has(id)) {
            card.classList.add('selected');
        } else {
            card.classList.remove('selected');
        }
    });
    
    document.getElementById('markReadBtn').disabled = count === 0;
    document.getElementById('clearBtn').disabled = count === 0;
}

function markSelectedRead() {
    if (selectedIds.size === 0) return;
    
    const promises = Array.from(selectedIds).map(id => 
        fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
    );
    
    Promise.all(promises)
        .then(responses => Promise.all(responses.map(r => r.json())))
        .then(() => {
            selectedIds.forEach(id => {
                const card = document.getElementById(`notification-${id}`);
                if (card) card.classList.remove('unread');
            });
            
            showToast(`${selectedIds.size} {{ __('ui.notifications.notifications_marked_as_read') }}`, 'success');
            
            document.querySelectorAll('.notification-check').forEach(cb => cb.checked = false);
            updateSelection();
            updateNotificationCount();
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('{{ __("ui.notifications.failed_to_mark_read") }}', 'error');
        });
}

function showClearModal() {
    if (selectedIds.size === 0) return;
    document.getElementById('confirmModal').style.display = 'flex';
}

function hideModal() {
    document.getElementById('confirmModal').style.display = 'none';
}

function clearSelected() {
    if (selectedIds.size === 0) {
        hideModal();
        return;
    }
    
    const promises = Array.from(selectedIds).map(id => 
        fetch(`/notifications/${id}/delete`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
    );
    
    Promise.all(promises)
        .then(responses => Promise.all(responses.map(r => r.json())))
        .then(() => {
            const count = selectedIds.size;
            
            selectedIds.forEach(id => {
                const card = document.getElementById(`notification-${id}`);
                if (card) card.remove();
            });
            
            showToast(`${count} {{ __('ui.notifications.notifications_cleared') }}`, 'success');
            hideModal();
            
            document.querySelectorAll('.notification-check').forEach(cb => cb.checked = false);
            updateSelection();
            updateNotificationCount();
            
            if (document.querySelectorAll('.notification-item').length === 0) {
                setTimeout(() => location.reload(), 1000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('{{ __("ui.notifications.failed_to_clear") }}', 'error');
            hideModal();
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

// ========== STAR FUNCTION ==========
function toggleStar(notificationId, button) {
    const icon = button.querySelector('i');
    
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
        if (data.success) {
            if (data.starred) {
                icon.classList.remove('far');
                icon.classList.add('fas', 'text-warning');
                button.dataset.starred = 'true';
                showToast('{{ __("ui.notifications.starred") }}', 'success');
            } else {
                icon.classList.remove('fas', 'text-warning');
                icon.classList.add('far');
                button.dataset.starred = 'false';
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

function updateNotificationCount() {
    fetch('/notifications/unread-count', {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        const badge = document.getElementById('notificationCount');
        const badge2 = document.getElementById('notificationCountBadge');
        if (badge) {
            badge.textContent = data.count;
            badge.style.display = data.count > 0 ? 'inline-block' : 'none';
        }
        if (badge2) {
            badge2.textContent = data.count;
            badge2.style.display = data.count > 0 ? 'inline-block' : 'none';
        }
    });
}

// ========== INITIALIZATION ==========
document.addEventListener('DOMContentLoaded', function() {
    updateNotificationCount();
    
    window.onclick = function(event) {
        const modal = document.getElementById('confirmModal');
        if (event.target === modal) {
            hideModal();
        }
    };
});

// Make functions global
window.toggleSelectAll = toggleSelectAll;
window.updateSelection = updateSelection;
window.markSelectedRead = markSelectedRead;
window.showClearModal = showClearModal;
window.hideModal = hideModal;
window.clearSelected = clearSelected;
window.hideToast = hideToast;
window.openPostFromNotification = openPostFromNotification;
window.toggleStar = toggleStar;
window.showToast = showToast;
</script>
@endpush
@endsection