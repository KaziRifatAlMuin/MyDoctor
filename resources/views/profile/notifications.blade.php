@extends('layouts.app')

@section('title', 'Notification Preferences - My Doctor')

@section('content')
<div class="container py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('profile') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                        <a href="{{ route('profile.notifications') }}" class="list-group-item list-group-item-action active">
                            <i class="fas fa-bell me-2"></i>Notifications
                        </a>
                        <a href="{{ route('profile.mailbox') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-inbox me-2"></i>Inbox
                        </a>
                        <a href="{{ route('profile.mailbox.sent') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-paper-plane me-2"></i>Sent
                        </a>
                        <a href="{{ route('profile.mailbox.compose') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-pen-to-square me-2"></i>Compose
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0"><i class="fas fa-bell me-2"></i>Notification Preferences</h4>
                </div>
                
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('profile.notifications.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Push Notifications -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-bell text-success me-2"></i>
                                    Push Notifications
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" 
                                           name="push_notifications" id="push_notifications" 
                                           value="1" {{ $user->push_notifications ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="push_notifications">
                                        Enable Push Notifications
                                    </label>
                                    <p class="text-muted small mt-1">
                                        Receive notifications even when the site is closed
                                    </p>
                                </div>

                                <div id="pushStatus" class="mt-3 small">
                                    @if($user->push_notifications)
                                        <span class="text-success">
                                            <i class="fas fa-check-circle"></i> Push notifications are enabled
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Email Notifications -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-envelope text-primary me-2"></i>
                                    Email Notifications
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" 
                                           name="email_notifications" id="email_notifications" 
                                           value="1" {{ $user->email_notifications ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="email_notifications">
                                        Enable Email Notifications
                                    </label>
                                    <p class="text-muted small mt-1">
                                        Receive reminders via email
                                    </p>
                                </div>

                                <div class="mt-3">
                                    <label class="form-label fw-bold">Reminder Timing</label>
                                    <select name="reminder_before_minutes" class="form-select">
                                        <option value="5" {{ ($user->getNotificationSetting('reminder_before_minutes', 5) == 5) ? 'selected' : '' }}>5 minutes before</option>
                                        <option value="10" {{ ($user->getNotificationSetting('reminder_before_minutes', 5) == 10) ? 'selected' : '' }}>10 minutes before</option>
                                        <option value="15" {{ ($user->getNotificationSetting('reminder_before_minutes', 5) == 15) ? 'selected' : '' }}>15 minutes before</option>
                                        <option value="30" {{ ($user->getNotificationSetting('reminder_before_minutes', 5) == 30) ? 'selected' : '' }}>30 minutes before</option>
                                    </select>
                                    <small class="text-muted">How early to send reminders</small>
                                </div>
                            </div>

                                @if (!$user->isAdmin())
                                    <!-- Chatbot Bubble -->
                                    <div class="card mb-4">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">
                                                <i class="fas fa-comments text-info me-2"></i>
                                                Chatbot Bubble
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox"
                                                       name="chatbot_bubble" id="chatbot_bubble"
                                                       value="1" {{ ($chatbotBubbleEnabled ?? true) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="chatbot_bubble">
                                                    Show Chatbot Bubble (floating assistant)
                                                </label>
                                                <p class="text-muted small mt-1">
                                                    Toggle whether the chatbot icon appears and shows reminders.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Preferences
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('push_notifications').addEventListener('change', function() {
        if (typeof window.toggleNotifications === 'function') {
            window.toggleNotifications(this.checked);
        }
    });
</script>
<script>
    // Helper to set cookie (days)
    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    }

    document.getElementById('chatbot_bubble')?.addEventListener('change', function() {
        const enabled = this.checked ? '1' : '0';
        setCookie('chatbot_bubble_enabled', enabled, 365);
        if (typeof window.updateChatbotVisibility === 'function') {
            window.updateChatbotVisibility(enabled === '1');
        }
    });
</script>
@endpush
@endsection