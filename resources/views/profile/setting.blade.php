@extends('layouts.app')

@section('title', 'Profile Settings - My Doctor')

@section('content')
    <div class="container py-4" style="max-width: 860px;">
        <div class="card border-0 shadow-lg">
            <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-cog me-2"></i>Profile Settings</h4>
                <a href="{{ route('profile') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Back to Profile
                </a>
            </div>

            <div class="card-body p-4">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('profile.setting.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                Email Alerts
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="email_notifications"
                                    id="email_notifications" value="1"
                                    {{ $user->email_notifications ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="email_notifications">
                                    Enable Email Alerts
                                </label>
                                <p class="text-muted small mt-1 mb-0">
                                    Receive medicine reminders via email.
                                </p>
                            </div>

                            <div class="mt-3">
                                <label class="form-label fw-bold">Reminder Timing</label>
                                <select name="reminder_before_minutes" class="form-select">
                                    <option value="5"
                                        {{ $user->getNotificationSetting('reminder_before_minutes', 5) == 5 ? 'selected' : '' }}>
                                        5 minutes before</option>
                                    <option value="10"
                                        {{ $user->getNotificationSetting('reminder_before_minutes', 5) == 10 ? 'selected' : '' }}>
                                        10 minutes before</option>
                                    <option value="15"
                                        {{ $user->getNotificationSetting('reminder_before_minutes', 5) == 15 ? 'selected' : '' }}>
                                        15 minutes before</option>
                                    <option value="30"
                                        {{ $user->getNotificationSetting('reminder_before_minutes', 5) == 30 ? 'selected' : '' }}>
                                        30 minutes before</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-bell text-success me-2"></i>
                                Push Alerts
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" name="push_notifications"
                                    id="push_notifications" value="1" {{ $user->push_notifications ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="push_notifications">
                                    Enable Push Alerts
                                </label>
                                <p class="text-muted small mt-1 mb-0">
                                    Receive alerts even when the website is not open.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-comments text-info me-2"></i>
                                Chatbot Bubble
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" name="chatbot_bubble" id="chatbot_bubble"
                                    value="1" {{ $chatbotBubbleEnabled ?? true ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="chatbot_bubble">
                                    Show Chatbot Bubble
                                </label>
                                <p class="text-muted small mt-1 mb-0">
                                    Hide or show the floating chatbot bubble using cookies.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function setCookie(name, value, days) {
                let expires = "";
                if (days) {
                    const date = new Date();
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                    expires = "; expires=" + date.toUTCString();
                }
                document.cookie = name + "=" + (value || "") + expires + "; path=/";
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
