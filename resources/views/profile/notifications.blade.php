@extends('layouts.app')

@section('title', __('ui.notification_preferences.title'))

@section('content')
<div class="container py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('profile') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-user me-2"></i>{{ __('ui.notification_preferences.profile') }}
                        </a>
                        <a href="{{ route('profile.notifications') }}" class="list-group-item list-group-item-action active">
                            <i class="fas fa-bell me-2"></i>{{ __('ui.notification_preferences.notifications') }}
                        </a>
                        <a href="{{ route('profile.mailbox') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-inbox me-2"></i>{{ __('ui.notification_preferences.inbox') }}
                        </a>
                        <a href="{{ route('profile.mailbox.sent') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-paper-plane me-2"></i>{{ __('ui.notification_preferences.sent') }}
                        </a>
                        <a href="{{ route('profile.mailbox.compose') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-pen-to-square me-2"></i>{{ __('ui.notification_preferences.compose') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0"><i class="fas fa-bell me-2"></i>{{ __('ui.notification_preferences.title') }}</h4>
                </div>
                
                <div class="card-body p-4">
                    @php
                        $settings = $user->setting;
                    @endphp

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
                                    {{ __('ui.notification_preferences.push_notifications') }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" 
                                           name="push_notifications" id="push_notifications" 
                                           value="1" {{ $settings->push_notifications ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="push_notifications">
                                        {{ __('ui.notification_preferences.enable_push') }}
                                    </label>
                                    <p class="text-muted small mt-1">
                                        {{ __('ui.notification_preferences.receive_notifications_closed') }}
                                    </p>
                                </div>

                                <div id="pushStatus" class="mt-3 small">
                                    @if($settings->push_notifications)
                                        <span class="text-success">
                                            <i class="fas fa-check-circle"></i> {{ __('ui.notification_preferences.push_enabled') }}
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
                                    {{ __('ui.notification_preferences.email_notifications') }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" 
                                           name="email_notifications" id="email_notifications" 
                                           value="1" {{ $settings->email_notifications ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="email_notifications">
                                        {{ __('ui.notification_preferences.enable_email') }}
                                    </label>
                                    <p class="text-muted small mt-1">
                                        {{ __('ui.notification_preferences.receive_reminders_via_email') }}
                                    </p>
                                </div>

                                <div class="mt-3">
                                    <label class="form-label fw-bold">{{ __('ui.notification_preferences.reminder_timing') }}</label>
                                    <select name="reminder_before_minutes" class="form-select">
                                        <option value="5" {{ ($user->getNotificationSetting('reminder_before_minutes', 5) == 5) ? 'selected' : '' }}>{{ __('ui.notification_preferences.minutes_before_5') }}</option>
                                        <option value="10" {{ ($user->getNotificationSetting('reminder_before_minutes', 5) == 10) ? 'selected' : '' }}>{{ __('ui.notification_preferences.minutes_before_10') }}</option>
                                        <option value="15" {{ ($user->getNotificationSetting('reminder_before_minutes', 5) == 15) ? 'selected' : '' }}>{{ __('ui.notification_preferences.minutes_before_15') }}</option>
                                        <option value="30" {{ ($user->getNotificationSetting('reminder_before_minutes', 5) == 30) ? 'selected' : '' }}>{{ __('ui.notification_preferences.minutes_before_30') }}</option>
                                    </select>
                                    <small class="text-muted">{{ __('ui.notification_preferences.reminder_timing_help') }}</small>
                                </div>
                            </div>

                            @if (!$user->isAdmin())
                                <!-- Chatbot Bubble -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">
                                            <i class="fas fa-comments text-info me-2"></i>
                                            {{ __('ui.notification_preferences.chatbot_bubble') }}
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox"
                                                   name="show_chatbot" id="show_chatbot"
                                                   value="1" {{ $settings->show_chatbot ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="show_chatbot">
                                                {{ __('ui.notification_preferences.show_chatbot_bubble') }}
                                            </label>
                                            <p class="text-muted small mt-1">
                                                {{ __('ui.notification_preferences.chatbot_bubble_help') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>{{ __('ui.notification_preferences.save_preferences') }}
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

    document.getElementById('show_chatbot')?.addEventListener('change', function() {
        const enabled = this.checked ? '1' : '0';
        setCookie('chatbot_bubble_enabled', enabled, 365);
        if (typeof window.updateChatbotVisibility === 'function') {
            window.updateChatbotVisibility(enabled === '1');
        }
    });
</script>
@endpush
@endsection