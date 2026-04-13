@extends('layouts.app')

@section('title', 'Profile Settings')

@push('styles')
    <style>
        .settings-wrap {
            background: radial-gradient(circle at 10% 10%, #edf5ff 0%, #f6f9ff 40%, #f9fbff 100%);
            min-height: calc(100vh - 260px);
            padding: 2.2rem 0 3rem;
        }

        .settings-shell {
            max-width: 980px;
            margin: 0 auto;
            border-radius: 22px;
            overflow: hidden;
            border: 1px solid rgba(11, 87, 208, 0.15);
            box-shadow: 0 20px 45px rgba(11, 87, 208, 0.14);
            background: #ffffff;
        }

        .settings-top {
            background: linear-gradient(120deg, #0b57d0 0%, #1a73e8 48%, #2b7de9 100%);
            color: #fff;
            padding: 1.4rem 1.6rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .settings-title {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
        }

        .settings-subtitle {
            margin: 0.3rem 0 0;
            opacity: 0.92;
            font-size: 0.9rem;
        }

        .settings-body {
            padding: 1.5rem;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .settings-card {
            border: 1px solid #e5ecf8;
            border-radius: 14px;
            background: #fff;
            padding: 1rem;
            box-shadow: 0 8px 18px rgba(10, 55, 130, 0.05);
            height: 100%;
        }

        .settings-card h5 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: #0f2f6b;
        }

        .settings-help {
            font-size: 0.86rem;
            color: #5f6f8b;
            margin: 0.55rem 0 0;
            line-height: 1.45;
        }

        .consent-panel {
            margin-top: 1rem;
            border-radius: 12px;
            border: 1px solid #d8e5fb;
            background: #f5f9ff;
            padding: 0.9rem 1rem;
        }

        .consent-panel h6 {
            margin: 0 0 0.35rem;
            color: #0f2f6b;
            font-weight: 700;
            font-size: 0.92rem;
        }

        .consent-panel ul {
            margin: 0;
            padding-left: 1rem;
            color: #495a79;
            font-size: 0.84rem;
        }

        .form-check-input:checked {
            background-color: #0b57d0;
            border-color: #0b57d0;
        }

        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <div class="settings-wrap">
        <div class="container">
            <div class="settings-shell">
                <div class="settings-top">
                    <div>
                        <h4 class="settings-title"><i class="fas fa-sliders-h me-2"></i>Profile Settings</h4>
                        <p class="settings-subtitle">Control alerts and public visibility from one place.</p>
                    </div>
                    <a href="{{ route('profile') }}" class="btn btn-light btn-sm fw-semibold">
                        <i class="fas fa-arrow-left me-1"></i>Back to Profile
                    </a>
                </div>

                <div class="settings-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('profile.setting.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        @php
                            $settings = $userSetting;
                        @endphp

                        <div class="settings-grid">
                            <div class="settings-card">
                                <h5><i class="fas fa-envelope text-primary me-2"></i>Email Alerts</h5>
                                <p class="settings-help">Get medicine reminder emails before scheduled time.</p>
                                <div class="form-check form-switch mt-2 mb-2">
                                    <input class="form-check-input" type="checkbox" name="email_notifications"
                                        id="email_notifications" value="1"
                                        {{ $settings->email_notifications ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="email_notifications">Enable Email Alerts</label>
                                </div>
                                <label class="form-label small fw-semibold mb-1">Reminder Timing</label>
                                <select name="reminder_before_minutes" class="form-select form-select-sm">
                                    <option value="5" {{ $user->getNotificationSetting('reminder_before_minutes', 5) == 5 ? 'selected' : '' }}>5 minutes before</option>
                                    <option value="10" {{ $user->getNotificationSetting('reminder_before_minutes', 5) == 10 ? 'selected' : '' }}>10 minutes before</option>
                                    <option value="15" {{ $user->getNotificationSetting('reminder_before_minutes', 5) == 15 ? 'selected' : '' }}>15 minutes before</option>
                                    <option value="30" {{ $user->getNotificationSetting('reminder_before_minutes', 5) == 30 ? 'selected' : '' }}>30 minutes before</option>
                                </select>
                            </div>

        

                            @if (!$user->isAdmin())
                                <div class="settings-card">
                                    <h5><i class="fas fa-comments text-info me-2"></i>Chatbot Bubble</h5>
                                    <p class="settings-help">Show or hide the floating AI assistant bubble across your account.</p>
                                    <div class="form-check form-switch mt-2 mb-0">
                                        <input class="form-check-input" type="checkbox" name="show_chatbot" id="show_chatbot"
                                            value="1" {{ $settings->show_chatbot ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="show_chatbot">Show Chatbot Bubble</label>
                                    </div>
                                </div>
                            @endif

                            <div class="settings-card">
                                <h5><i class="fas fa-tags text-secondary me-2"></i>Badge Visibility</h5>
                                <p class="settings-help mb-2">Control whether unread badges appear in the top navigation.</p>

                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="show_notification_badge"
                                        id="show_notification_badge" value="1" {{ $settings->show_notification_badge ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="show_notification_badge">Show Notification Badge</label>
                                </div>
                                <p class="settings-help mt-0 mb-2">If disabled, the bell badge count will stay hidden even when you have unread notifications.</p>

                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="show_mail_badge"
                                        id="show_mail_badge" value="1" {{ $settings->show_mail_badge ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="show_mail_badge">Show Mail Badge</label>
                                </div>
                                <p class="settings-help mt-0 mb-0">If disabled, unread mailbox count badges remain hidden in navbar and dropdown.</p>
                            </div>

                            <div class="settings-card">
                                <h5><i class="fas fa-user-shield text-warning me-2"></i>Public Profile Permissions</h5>
                                <p class="settings-help mb-2">Choose what visitors can see on your public profile page.</p>

                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="show_personal_info"
                                        id="show_personal_info" value="1" {{ $settings->show_personal_info ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="show_personal_info">Show Personal Info</label>
                                </div>
                                <p class="settings-help mt-0 mb-2">If enabled, visitors can see: date of birth, phone number, occupation, blood group, gender, and address (district + upazila only). Street and house are never shown publicly.</p>

                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="show_diseases" id="show_diseases"
                                        value="1" {{ $settings->show_diseases ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="show_diseases">Show Disease History</label>
                                </div>
                                <p class="settings-help mt-0 mb-0">If enabled, public visitors can see your listed disease names and status.</p>
                            </div>
                        </div>

                        <div class="consent-panel">
                            <h6><i class="fas fa-file-contract me-1"></i>Consent & Terms Acknowledgement</h6>
                            <ul>
                                <li>By turning ON a public permission, you agree that the selected information is visible on your public profile.</li>
                                <li>"Show Personal Info" exposes date of birth, phone number, occupation, blood group, gender, and address (district + upazila only).</li>
                                <li>Street and house are always private and are never shown in public profile views.</li>
                                <li>"Show Disease History" exposes your disease names and disease status only.</li>
                                <li>You can turn these permissions OFF anytime from this settings page.</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                <i class="fas fa-save me-2"></i>Save Settings
                            </button>
                        </div>
                    </form>
                </div>
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
