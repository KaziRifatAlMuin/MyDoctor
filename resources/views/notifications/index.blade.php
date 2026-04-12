@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<style>
    /* Override app layout constraints */
    .main-content {
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        background: #f8fafc;
        min-height: calc(100vh - 60px);
    }

    .container, .container-fluid {
        padding-left: 0 !important;
        padding-right: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
    }

    /* Main container */
    .notifications-wrapper {
        width: 100%;
        max-width: 800px;
        margin: 30px auto;
        padding: 0 20px;
    }

    /* Header */
    .notifications-header {
        margin-bottom: 25px;
    }

    .notifications-header h1 {
        font-size: 28px;
        font-weight: 600;
        color: #1e293b;
        margin: 0 0 5px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .notifications-header h1 i {
        color: #3b82f6;
    }

    .notifications-header p {
        color: #64748b;
        font-size: 14px;
        margin: 0;
    }

    /* Action Bar */
    .action-bar {
        background: white;
        border-radius: 10px;
        padding: 16px 20px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }

    .action-left {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .select-all {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .select-all input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #3b82f6;
    }

    .select-all label {
        font-size: 14px;
        font-weight: 500;
        color: #334155;
        cursor: pointer;
    }

    .selected-count {
        font-size: 14px;
        color: #64748b;
        background: #f1f5f9;
        padding: 4px 12px;
        border-radius: 20px;
    }

    .action-right {
        display: flex;
        gap: 12px;
    }

    .btn-action {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-action.mark-read {
        background: #3b82f6;
        color: white;
    }

    .btn-action.mark-read:hover:not(:disabled) {
        background: #2563eb;
    }

    .btn-action.clear {
        background: white;
        color: #ef4444;
        border: 1px solid #e2e8f0;
    }

    .btn-action.clear:hover:not(:disabled) {
        background: #fef2f2;
        border-color: #ef4444;
    }

    .btn-action:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Notifications List */
    .notifications-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    /* Card */
    .notification-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.2s;
        position: relative;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }

    .notification-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 4px 12px rgba(59,130,246,0.1);
    }

    .notification-card.unread {
        border-left: 4px solid #3b82f6;
        background: #ffffff;
    }

    .notification-card.selected {
        border: 2px solid #3b82f6;
        background: #f8fafc;
    }

    /* Card Content */
    .card-content {
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }

    /* Checkbox */
    .card-checkbox {
        padding-top: 4px;
    }

    .card-checkbox input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #3b82f6;
    }

    /* Avatar */
    .card-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
        border: 2px solid #e2e8f0;
        background: #f1f5f9;
    }

    .card-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .card-avatar-placeholder {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #3b82f6;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 600;
    }

    /* Info */
    .card-info {
        flex: 1;
        min-width: 0;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 8px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .card-title {
        font-size: 16px;
        font-weight: 500;
        color: #1e293b;
        line-height: 1.5;
        margin: 0;
    }

    .card-title strong {
        font-weight: 600;
        color: #0f172a;
    }

    .card-time {
        font-size: 13px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
    }

    .card-time i {
        font-size: 12px;
        color: #94a3b8;
    }

    /* Actions */
    .card-actions {
        margin-top: 12px;
        display: flex;
        gap: 10px;
    }

    .btn-view {
        padding: 6px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #3b82f6;
        color: white;
    }

    .btn-view:hover {
        background: #2563eb;
    }

    .btn-view i {
        font-size: 12px;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }

    .empty-state i {
        font-size: 48px;
        color: #cbd5e1;
        margin-bottom: 16px;
    }

    .empty-state h3 {
        font-size: 18px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .empty-state p {
        color: #64748b;
        font-size: 14px;
        margin: 0;
    }

    /* Pagination */
    .pagination-wrapper {
        margin-top: 30px;
        display: flex;
        justify-content: center;
    }

    .pagination {
        display: flex;
        gap: 6px;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .page-link {
        padding: 8px 14px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        color: #3b82f6;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s;
        background: white;
    }

    .page-link:hover {
        background: #f8fafc;
        border-color: #3b82f6;
    }

    .page-item.active .page-link {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }

    .page-item.disabled .page-link {
        color: #94a3b8;
        border-color: #e2e8f0;
        cursor: not-allowed;
    }

    /* Toast */
    .toast {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        padding: 14px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 9999;
        animation: slideIn 0.3s ease;
        border-left: 4px solid #10b981;
        border: 1px solid #e2e8f0;
    }

    .toast.error {
        border-left-color: #ef4444;
    }

    .toast i {
        font-size: 18px;
    }

    .toast.success i {
        color: #10b981;
    }

    .toast.error i {
        color: #ef4444;
    }

    .toast .message {
        font-size: 14px;
        font-weight: 500;
        color: #1e293b;
    }

    .toast .close {
        margin-left: 20px;
        cursor: pointer;
        color: #94a3b8;
        font-size: 16px;
    }

    .toast .close:hover {
        color: #475569;
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
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        animation: fadeIn 0.2s ease;
    }

    .confirm-modal {
        background: white;
        border-radius: 12px;
        padding: 30px;
        max-width: 400px;
        width: 90%;
        text-align: center;
        animation: scaleIn 0.2s ease;
        border: 1px solid #e2e8f0;
    }

    .confirm-modal i {
        font-size: 48px;
        color: #f59e0b;
        margin-bottom: 16px;
    }

    .confirm-modal h3 {
        font-size: 20px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .confirm-modal p {
        color: #64748b;
        font-size: 14px;
        margin-bottom: 24px;
    }

    .modal-actions {
        display: flex;
        gap: 12px;
        justify-content: center;
    }

    .btn-modal {
        padding: 10px 24px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-modal.cancel {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    .btn-modal.cancel:hover {
        background: #e2e8f0;
    }

    .btn-modal.confirm {
        background: #ef4444;
        color: white;
    }

    .btn-modal.confirm:hover {
        background: #dc2626;
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
    @media (max-width: 640px) {
        .notifications-wrapper {
            padding: 0 16px;
        }

        .action-bar {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .action-right {
            width: 100%;
        }

        .btn-action {
            flex: 1;
            justify-content: center;
        }

        .card-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .card-header {
            flex-direction: column;
            gap: 4px;
        }

        .card-time {
            white-space: normal;
        }

        .toast {
            left: 20px;
            right: 20px;
            bottom: 20px;
        }
    }
</style>

<!-- Main Content -->
<div class="notifications-wrapper">
    <!-- Header -->
    <div class="notifications-header">
        <h1>
            <i class="fas fa-bell"></i>
            Notifications
        </h1>
        <p>Stay updated with your community activity</p>
    </div>

    @if($notifications->isNotEmpty())
        <!-- Action Bar -->
        <div class="action-bar">
            <div class="action-left">
                <div class="select-all">
                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                    <label for="selectAll">Select all</label>
                </div>
                <span class="selected-count" id="selectedCount">0 selected</span>
            </div>
            <div class="action-right">
                <button class="btn-action mark-read" id="markReadBtn" onclick="markSelectedRead()" disabled>
                    <i class="fas fa-check"></i>
                    Mark as read
                </button>
                <button class="btn-action clear" id="clearBtn" onclick="showClearModal()" disabled>
                    <i class="fas fa-trash"></i>
                    Clear
                </button>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="notifications-list">
            @foreach($notifications as $notification)
                <div class="notification-card {{ is_null($notification->read_at) ? 'unread' : '' }}" 
                     id="notification-{{ $notification->id }}"
                     @if(isset($notification->data['post_id']))
                         onclick="openPostModal({{ $notification->data['post_id'] }}, {{ $notification->id }})"
                         style="cursor: pointer;"
                     @endif>
                    
                    <div class="card-content">
                        <!-- Checkbox -->
                        <div class="card-checkbox" onclick="event.stopPropagation();">
                            <input type="checkbox" 
                                   class="notification-check" 
                                   data-id="{{ $notification->id }}"
                                   onchange="updateSelection()">
                        </div>
                        
                        <!-- Avatar -->
                        <div class="card-avatar">
                            @if($notification->fromUser && $notification->fromUser->picture)
                                <img src="{{ asset('storage/' . $notification->fromUser->picture) }}"
                                     alt="{{ $notification->fromUser->name }}">
                            @else
                                <div class="card-avatar-placeholder">
                                    {{ strtoupper(substr($notification->fromUser->name ?? 'U', 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        <!-- Info -->
                        <div class="card-info">
                            <div class="card-header">
                                <div class="card-title">
                                    {!! $notification->message !!}
                                </div>
                                <span class="card-time">
                                    <i class="far fa-clock"></i>
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                            </div>

                            <!-- Actions -->
                            <div class="card-actions">
                                @if(isset($notification->data['post_id']))
                                    <button class="btn-view" onclick="event.stopPropagation(); openPostModal({{ $notification->data['post_id'] }}, {{ $notification->id }})">
                                        <i class="fas fa-eye"></i>
                                        View Post
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($notifications->hasPages())
            <div class="pagination-wrapper">
                {{ $notifications->links() }}
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="empty-state">
            <i class="fas fa-bell-slash"></i>
            <h3>No notifications yet</h3>
            <p>When someone interacts with your posts, you'll see it here</p>
        </div>
    @endif
</div>

<!-- Toast -->
<div id="toast" class="toast" style="display: none;">
    <i class="fas fa-check-circle"></i>
    <span class="message" id="toastMessage"></span>
    <span class="close" onclick="hideToast()">✕</span>
</div>

<!-- Confirm Modal -->
<div id="confirmModal" class="modal-overlay" style="display: none;">
    <div class="confirm-modal">
        <i class="fas fa-exclamation-triangle"></i>
        <h3>Clear selected notifications?</h3>
        <p>This action cannot be undone. Selected notifications will be permanently deleted.</p>
        <div class="modal-actions">
            <button class="btn-modal cancel" onclick="hideModal()">Cancel</button>
            <button class="btn-modal confirm" onclick="clearSelected()">Clear</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Add click anywhere to close dropdowns
document.addEventListener('click', function(event) {
    // Close notification dropdown if open
    const notifDropdown = document.getElementById('notificationDropdown');
    const notifBell = document.getElementById('notificationBell');
    
    if (notifDropdown && notifDropdown.classList.contains('show')) {
        if (!notifBell?.contains(event.target) && !notifDropdown.contains(event.target)) {
            notifDropdown.classList.remove('show');
        }
    }
    
    // Close user dropdown if open
    const userDropdown = document.getElementById('userDropdown');
    const userCircle = document.querySelector('.user-circle');
    
    if (userDropdown && userDropdown.classList.contains('show')) {
        if (!userCircle?.contains(event.target) && !userDropdown.contains(event.target)) {
            userDropdown.classList.remove('show');
        }
    }
});

let selectedIds = new Set();

// ========== POST MODAL FUNCTION ==========
function openPostModal(postId, notificationId = null) {
    // If there's a notification, mark it as read
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
                // Update the UI
                const card = document.getElementById(`notification-${notificationId}`);
                if (card) {
                    card.classList.remove('unread');
                }
                updateNotificationCount();
            }
        })
        .catch(err => console.error('Error marking notification as read:', err));
    }
    
    // Show modal with loading spinner
    const modal = new bootstrap.Modal(document.getElementById('postModal'));
    const modalBody = document.getElementById('postModalBody');
    modalBody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading post...</p>
        </div>
    `;
    
    modal.show();
    
    // Fetch the post content from modal-post.blade.php
    fetch(`/community/modal-post/${postId}`, {
        headers: {
            'Accept': 'text/html',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to load post');
        }
        return response.text();
    })
    .then(html => {
        modalBody.innerHTML = html;
    })
    .catch(error => {
        console.error('Error loading post:', error);
        modalBody.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-exclamation-circle text-danger" style="font-size: 48px;"></i>
                <h5 class="mt-3">Failed to load post</h5>
                <p class="text-muted">Please try again later</p>
                <button class="btn btn-primary mt-2" onclick="location.reload()">Refresh Page</button>
            </div>
        `;
    });
}

// ========== NOTIFICATION FUNCTIONS ==========
function markNotificationRead(event, id, postId) {
    event.preventDefault();
    openPostModal(postId, id);
}

function markAllAsRead() {
    fetch('/notifications/mark-all-read', {
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
            document.querySelectorAll('.notification-card').forEach(card => {
                card.classList.remove('unread');
            });
            updateNotificationCount();
            showToast('All notifications marked as read');
        }
    })
    .catch(error => console.error('Error:', error));
}

function updateNotificationCount() {
    const badge = document.getElementById('notificationCount');
    const badge2 = document.getElementById('notificationCountBadge');
    
    fetch('/notifications/unread-count', {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
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
    document.getElementById('selectedCount').textContent = `${count} selected`;
    
    // Update select all checkbox
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
    
    // Update selected class on cards
    document.querySelectorAll('.notification-card').forEach(card => {
        const id = parseInt(card.id.replace('notification-', ''));
        if (selectedIds.has(id)) {
            card.classList.add('selected');
        } else {
            card.classList.remove('selected');
        }
    });
    
    // Enable/disable action buttons
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
            // Update UI
            selectedIds.forEach(id => {
                const card = document.getElementById(`notification-${id}`);
                if (card) {
                    card.classList.remove('unread');
                }
            });
            
            showToast(`${selectedIds.size} notifications marked as read`);
            
            // Uncheck all
            document.querySelectorAll('.notification-check').forEach(cb => {
                cb.checked = false;
            });
            
            updateSelection();
            updateNotificationCount();
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to mark notifications as read', 'error');
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
            
            // Remove items from DOM
            selectedIds.forEach(id => {
                const card = document.getElementById(`notification-${id}`);
                if (card) {
                    card.remove();
                }
            });
            
            showToast(`${count} notifications cleared`);
            hideModal();
            
            // Uncheck all
            document.querySelectorAll('.notification-check').forEach(cb => {
                cb.checked = false;
            });
            
            updateSelection();
            updateNotificationCount();
            
            // Check if no notifications left
            if (document.querySelectorAll('.notification-card').length === 0) {
                setTimeout(() => location.reload(), 1000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to clear notifications', 'error');
            hideModal();
        });
}

// ========== TOAST FUNCTIONS ==========
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    const icon = toast.querySelector('i');
    
    toast.className = 'toast ' + type;
    icon.className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
    toastMessage.textContent = message;
    
    toast.style.display = 'flex';
    
    setTimeout(hideToast, 3000);
}

function hideToast() {
    document.getElementById('toast').style.display = 'none';
}

// ========== INITIALIZATION ==========
document.addEventListener('DOMContentLoaded', function() {
    updateNotificationCount();
    
    // Handle click outside modal
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
window.markNotificationRead = markNotificationRead;
window.markAllAsRead = markAllAsRead;
window.hideToast = hideToast;
window.openPostModal = openPostModal;
</script>
@endpush