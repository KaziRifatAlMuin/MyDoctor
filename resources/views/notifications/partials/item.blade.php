<div class="list-group-item notification-item {{ is_null($notification->read_at) ? 'bg-light' : '' }}"
     id="notification-{{ $notification->id }}">
    <div class="d-flex align-items-start gap-3">
        <div class="flex-shrink-0">
            @if($notification->fromUser && $notification->fromUser->picture)
                <img src="{{ asset('storage/' . $notification->fromUser->picture) }}"
                     alt="{{ $notification->fromUser->name }}"
                     class="rounded-circle"
                     width="40" height="40">
            @else
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                     style="width: 40px; height: 40px;">
                    {{ strtoupper(substr($notification->fromUser->name ?? 'ই', 0, 1)) }}
                </div>
            @endif
        </div>

        <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="mb-1">{{ $notification->message }}</p>
                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                </div>
                @if(is_null($notification->read_at))
                    <span class="badge bg-primary rounded-pill unread-badge">{{ __('ui.notifications.new') }}</span>
                @endif
            </div>

            @if($notification->type === 'like')
                <div class="mt-2 p-2 bg-white rounded border">
                    <i class="fas fa-heart text-danger me-2"></i>
                    <span>{{ $notification->data['post_preview'] ?? '' }}</span>
                </div>
            @elseif(in_array($notification->type, ['comment', 'reply']))
                <div class="mt-2 p-2 bg-white rounded border">
                    <i class="fas fa-comment text-primary me-2"></i>
                    <span>{{ $notification->data['comment_preview'] ?? $notification->data['reply_preview'] ?? '' }}</span>
                </div>
            @endif

            <div class="mt-2 d-flex gap-2">
                <a href="/community/posts/{{ $notification->data['post_id'] }}"
                   class="btn btn-sm btn-outline-primary"
                   onclick="markAsRead({{ $notification->id }})">
                    {{ __('ui.notifications.view_post') }}
                </a>
                @if(is_null($notification->read_at))
                    <button class="btn btn-sm btn-outline-secondary"
                            onclick="markAsRead({{ $notification->id }})">
                        {{ __('ui.notifications.mark_as_read') }}
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>