@extends('layouts.app')

@section('title', __('ui.admin_reported_posts.title'))

@push('styles')
<style>
    .reported-section {
        background: #f8f9fb;
        min-height: 100vh;
        padding: 2rem 0 3rem;
    }
    
    .reported-card {
        background: white;
        border-radius: 16px;
        border: none;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
        overflow: hidden;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }
    
    .reported-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }
    
    .reported-header {
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .reported-badge {
        background: #dc3545;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .reported-body {
        padding: 1.5rem;
    }
    
    .reported-meta {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
        font-size: 0.85rem;
        color: #718096;
    }
    
    .reported-content {
        background: #f8f9fb;
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1rem;
        font-size: 0.95rem;
        line-height: 1.6;
        color: #2d3748;
    }
    
    .action-buttons {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }
    
    .btn-approve {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.5rem 1.25rem;
        font-weight: 600;
        transition: all 0.2s;
    }
    
    .btn-approve:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }
    
    /* .btn-dismiss removed */

    .btn-delete {
        background: transparent;
        color: #dc3545;
        border: 1px solid #dc3545;
        border-radius: 8px;
        padding: 0.4rem 0.9rem;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.15s ease;
    }

    .btn-delete:hover {
        background: #dc3545;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(220,53,69,0.12);
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 16px;
    }
    
    .empty-state i {
        font-size: 4rem;
        color: #cbd5e0;
        margin-bottom: 1rem;
    }
</style>
@endpush

@section('content')
<div class="reported-section">
    <div class="container" style="max-width: 1000px;">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold mb-1">
                    <i class="fas fa-flag me-2" style="color: #dc3545;"></i>
                    {{ __('ui.admin_reported_posts.title') }}
                </h1>
                <p class="text-muted">{{ __('ui.admin_reported_posts.subtitle') }}</p>
            </div>
            <a href="{{ route('admin.community.posts.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>{{ __('ui.admin_reported_posts.back_to_posts') }}
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($posts->count() > 0)
            @foreach($posts as $post)
                <div class="reported-card" id="post-{{ $post->id }}">
                    <div class="reported-header">
                        <div>
                            <span class="reported-badge">
                                <i class="fas fa-flag me-1"></i>{{ __('ui.admin_reported_posts.reported') }}
                            </span>
                            <span class="ms-2 text-muted small">
                                {{ __('ui.admin_reported_posts.post_id') }} #{{ $post->id }}
                            </span>
                        </div>
                        <div class="text-muted small">
                            <i class="fas fa-clock me-1"></i>
                            {{ $post->updated_at->diffForHumans() }}
                        </div>
                    </div>
                    
                    <div class="reported-body">
                        <div class="reported-meta">
                            <span>
                                <i class="fas fa-user me-1"></i>
                                <strong>{{ $post->user?->name ?? __('ui.admin_reported_posts.deleted_user') }}</strong>
                            </span>
                            <span>
                                <i class="fas fa-tag me-1"></i>
                                {{ $post->disease?->display_name ?? __('ui.admin_reported_posts.general') }}
                            </span>
                            <span>
                                <i class="fas fa-calendar me-1"></i>
                                {{ $post->created_at->format('M d, Y') }}
                            </span>
                        </div>
                        
                        <div class="reported-content">
                            <strong class="d-block">{{ Str::limit($post->description, 300) }}</strong>
                        </div>
                        
                        <div class="action-buttons">
                            <button class="btn-approve" onclick="approveAndClearReport({{ $post->id }})">
                                <i class="fas fa-check me-2"></i>{{ __('ui.admin_reported_posts.approve_and_clear') }}
                            </button>
                            <button class="btn-delete" onclick="confirmDelete({{ $post->id }}, 'post')">
                                <i class="fas fa-trash me-2"></i>{{ __('ui.admin_reported_posts.delete_post') }}
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
            
            <div class="mt-4">
                {{ $posts->links() }}
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-flag-checkered"></i>
                <h4 class="fw-bold mt-3">{{ __('ui.admin_reported_posts.no_reported_posts') }}</h4>
                <p class="text-muted">{{ __('ui.admin_reported_posts.no_reported_posts_message') }}</p>
                <a href="{{ route('admin.community.posts.index') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('ui.admin_reported_posts.back_to_posts') }}
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('ui.admin_reported_posts.confirm_delete') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('ui.admin_reported_posts.delete_confirmation_message') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('ui.admin_reported_posts.cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">{{ __('ui.admin_reported_posts.delete') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let pendingDeleteId = null;
    
    function approveAndClearReport(postId) {
        if (!confirm('{{ __("ui.admin_reported_posts.approve_clear_confirm") }}')) return;
        
        fetch(`/admin/community/posts/${postId}/approve`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Also clear the reported flag
                fetch(`/admin/community/posts/${postId}/clear-report`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                }).catch(console.error);
                
                document.getElementById(`post-${postId}`).remove();
                showToast('{{ __("ui.admin_reported_posts.post_approved_and_cleared") }}', 'success');
                
                if (document.querySelectorAll('.reported-card').length === 0) {
                    setTimeout(() => location.reload(), 1000);
                }
            } else {
                showToast(data.message || '{{ __("ui.admin_reported_posts.error_approving") }}', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('{{ __("ui.admin_reported_posts.error_approving") }}', 'error');
        });
    }
    
    /* dismissReport removed */
    
    function confirmDelete(postId, type) {
        pendingDeleteId = postId;
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }
    
    document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
        if (!pendingDeleteId) return;
        
        fetch(`/admin/community/posts/${pendingDeleteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`post-${pendingDeleteId}`).remove();
                showToast('{{ __("ui.admin_reported_posts.post_deleted") }}', 'success');
                
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                modal.hide();
                
                if (document.querySelectorAll('.reported-card').length === 0) {
                    setTimeout(() => location.reload(), 1000);
                }
            } else {
                showToast(data.message || '{{ __("ui.admin_reported_posts.error_deleting") }}', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('{{ __("ui.admin_reported_posts.error_deleting") }}', 'error');
        })
        .finally(() => {
            pendingDeleteId = null;
        });
    });
    
    function showToast(message, type) {
        const toastContainer = document.getElementById('toastContainer') || createToastContainer();
        const toastId = 'toast-' + Date.now();
        const bgColor = type === 'success' ? '#28a745' : '#dc3545';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const html = `
            <div id="${toastId}" class="toast align-items-center text-white border-0" role="alert" style="background: ${bgColor};">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas ${icon} me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', html);
        const toast = new bootstrap.Toast(document.getElementById(toastId), { delay: 3000 });
        toast.show();
        
        document.getElementById(toastId).addEventListener('hidden.bs.toast', function() {
            this.remove();
        });
    }
    
    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    }
</script>
@endpush