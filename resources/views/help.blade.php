@extends('layouts.app')

@section('title', __('ui.help.help'))

@push('styles')
    <style>
        .help-section {
            background: #f8f9fb;
            min-height: 100vh;
            padding: 2.5rem 0 3rem;
        }

        .help-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 2.5rem 2rem;
            color: white;
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .help-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
        }

        .help-hero h2 {
            font-weight: 800;
            margin-bottom: 0.5rem;
            position: relative;
        }

        .help-hero p {
            opacity: 0.9;
            font-size: 1rem;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .help-search {
            max-width: 500px;
            margin: 0 auto;
            position: relative;
        }

        .help-search input {
            width: 100%;
            border: none;
            border-radius: 30px;
            padding: 0.8rem 1.5rem 0.8rem 3rem;
            font-size: 0.95rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .help-search input:focus {
            outline: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .help-search .search-icon {
            position: absolute;
            left: 1.1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 1rem;
        }

        /* Quick Links Grid */
        .quick-link-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            text-align: center;
            text-decoration: none;
            color: #2d3748;
            transition: transform 0.2s, box-shadow 0.2s;
            display: block;
            height: 100%;
        }

        .quick-link-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.1);
            color: #2d3748;
        }

        .quick-link-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 0.75rem;
        }

        .quick-link-icon.ql-purple {
            background: rgba(102, 126, 234, 0.12);
            color: #667eea;
        }

        .quick-link-icon.ql-green {
            background: rgba(56, 161, 105, 0.12);
            color: #38a169;
        }

        .quick-link-icon.ql-orange {
            background: rgba(221, 107, 32, 0.12);
            color: #dd6b20;
        }

        .quick-link-icon.ql-red {
            background: rgba(229, 62, 62, 0.12);
            color: #e53e3e;
        }

        .quick-link-icon.ql-blue {
            background: rgba(49, 130, 206, 0.12);
            color: #3182ce;
        }

        .quick-link-icon.ql-teal {
            background: rgba(56, 178, 172, 0.12);
            color: #319795;
        }

        .quick-link-title {
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 0.3rem;
        }

        .quick-link-desc {
            font-size: 0.8rem;
            color: #718096;
            margin: 0;
        }

        /* FAQ Section */
        .faq-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .faq-card-header {
            padding: 1.25rem 1.5rem 0.75rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .faq-card-header h5 {
            font-weight: 700;
            color: #667eea;
            font-size: 1.05rem;
            margin: 0;
        }

        .faq-card-body {
            padding: 0;
        }

        .accordion-item {
            border: none !important;
            border-bottom: 1px solid #f0f0f0 !important;
        }

        .accordion-item:last-child {
            border-bottom: none !important;
        }

        .accordion-button {
            font-weight: 600;
            font-size: 0.92rem;
            color: #2d3748;
            padding: 1rem 1.5rem;
            background: white;
            box-shadow: none !important;
        }

        .accordion-button:not(.collapsed) {
            color: #667eea;
            background: rgba(102, 126, 234, 0.04);
        }

        .accordion-button::after {
            background-size: 0.9rem;
        }

        .accordion-body {
            padding: 0 1.5rem 1rem;
            font-size: 0.88rem;
            color: #4a5568;
            line-height: 1.7;
        }

        /* Guide Cards */
        .guide-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: transform 0.2s;
        }

        .guide-card:hover {
            transform: translateY(-2px);
        }

        .guide-step {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.82rem;
            flex-shrink: 0;
        }

        .guide-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: #2d3748;
        }

        .guide-desc {
            font-size: 0.85rem;
            color: #4a5568;
            margin: 0;
        }

        /* Contact Card */
        .contact-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            border-left: 5px solid #667eea;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.6rem 0;
            font-size: 0.88rem;
            color: #4a5568;
        }

        .contact-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .searchable-item {
            transition: opacity 0.2s;
        }

        .searchable-item.hidden {
            display: none;
        }
    </style>
@endpush

@section('content')
    @php
        $isAuthenticated = auth()->check();
        $loginUrl = route('login');
        $protectUrl = static function (string $url) use ($isAuthenticated, $loginUrl): string {
            return $isAuthenticated ? $url : $loginUrl . '?redirect=' . urlencode($url);
        };
    @endphp
    <div class="help-section">
        <div class="container" style="max-width: 1140px;">

            {{-- Hero --}}
            <div class="help-hero">
                <h2><i class="fas fa-question-circle me-2"></i>{{ __('ui.help.how_can_we_help_you') }}</h2>
                <p>{{ __('ui.help.find_answers_guides_support') }}</p>
                {{-- search and Ask AI moved below, just above General Questions --}}
            </div>

            {{-- Quick Links --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#faq-general" class="quick-link-card" onclick="scrollToFaq('general')">
                        <div class="quick-link-icon ql-purple"><i class="fas fa-info-circle"></i></div>
                        <div class="quick-link-title">{{ __('ui.help.general') }}</div>
                        <p class="quick-link-desc">{{ __('ui.help.about_mydoctor') }}</p>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#faq-health" class="quick-link-card" onclick="scrollToFaq('health')">
                        <div class="quick-link-icon ql-green"><i class="fas fa-heartbeat"></i></div>
                        <div class="quick-link-title">{{ __('ui.help.health') }}</div>
                        <p class="quick-link-desc">{{ __('ui.help.metrics_tracking') }}</p>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#faq-medicine" class="quick-link-card" onclick="scrollToFaq('medicine')">
                        <div class="quick-link-icon ql-orange"><i class="fas fa-pills"></i></div>
                        <div class="quick-link-title">{{ __('ui.help.medicine') }}</div>
                        <p class="quick-link-desc">{{ __('ui.help.reminders_logs') }}</p>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#faq-uploads" class="quick-link-card" onclick="scrollToFaq('uploads')">
                        <div class="quick-link-icon ql-red"><i class="fas fa-file-medical"></i></div>
                        <div class="quick-link-title">{{ __('ui.help.uploads') }}</div>
                        <p class="quick-link-desc">{{ __('ui.help.prescriptions_reports') }}</p>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#faq-account" class="quick-link-card" onclick="scrollToFaq('account')">
                        <div class="quick-link-icon ql-blue"><i class="fas fa-user-cog"></i></div>
                        <div class="quick-link-title">{{ __('ui.help.account') }}</div>
                        <p class="quick-link-desc">{{ __('ui.help.profile_settings') }}</p>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#getting-started" class="quick-link-card">
                        <div class="quick-link-icon ql-teal"><i class="fas fa-rocket"></i></div>
                        <div class="quick-link-title">{{ __('ui.help.get_started') }}</div>
                        <p class="quick-link-desc">{{ __('ui.help.step_by_step_guide') }}</p>
                    </a>
                </div>
            </div>

            <div class="row">
                {{-- FAQ Column --}}
                <div class="col-lg-8">

                    {{-- Search + Ask AI (moved here: just above General FAQ) --}}
                    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between mb-3">
                        <div class="help-search me-md-3" style="flex:1; max-width:560px;">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="helpSearchInput" placeholder="{{ __('ui.help.search_help_topics') }}" autocomplete="off" class="form-control">
                        </div>
                        <div class="mt-3 mt-md-0 ms-md-3" style="white-space:nowrap;">
                            @if(!auth()->check() || !auth()->user()->isAdmin())
                                @auth
                                    <button onclick="toggleChatbot()" class="btn btn-sm btn-light" style="border-radius:12px;border:1px solid rgba(0,0,0,0.06);">
                                        <i class="fas fa-user-md me-1"></i> {{ __('ui.help.ask_mydoctor_ai') }}
                                    </button>
                                @else
                                    <a href="{{ route('login') }}?redirect={{ urlencode(request()->fullUrl()) }}" class="btn btn-sm btn-light" style="border-radius:12px;border:1px solid rgba(0,0,0,0.06);">
                                        <i class="fas fa-user-md me-1"></i> {{ __('ui.help.ask_mydoctor_ai') }}
                                    </a>
                                @endauth
                            @endif
                        </div>
                    </div>

                    {{-- General FAQ --}}
                    <div class="faq-card searchable-item" id="faq-general">
                        <div class="faq-card-header">
                            <h5><i class="fas fa-info-circle me-2"></i>{{ __('ui.help.general_questions') }}</h5>
                        </div>
                        <div class="faq-card-body">
                            <div class="accordion" id="accGeneral">
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#g1">{{ __('ui.help.what_is_mydoctor') }}</button></h2>
                                    <div id="g1" class="accordion-collapse collapse" data-bs-parent="#accGeneral">
                                        <div class="accordion-body">{{ __('ui.help.what_is_mydoctor_answer') }}</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#g2">{{ __('ui.help.is_mydoctor_free') }}</button>
                                    </h2>
                                    <div id="g2" class="accordion-collapse collapse" data-bs-parent="#accGeneral">
                                        <div class="accordion-body">{{ __('ui.help.is_mydoctor_free_answer') }}</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#g3">{{ __('ui.help.is_health_data_safe') }}</button>
                                    </h2>
                                    <div id="g3" class="accordion-collapse collapse" data-bs-parent="#accGeneral">
                                        <div class="accordion-body">{{ __('ui.help.is_health_data_safe_answer') }}</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#g4">{{ __('ui.help.need_medical_knowledge') }}</button>
                                    </h2>
                                    <div id="g4" class="accordion-collapse collapse" data-bs-parent="#accGeneral">
                                        <div class="accordion-body">{{ __('ui.help.need_medical_knowledge_answer') }}</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#g5">{{ __('ui.help.does_mydoctor_replace_doctor') }}</button>
                                    </h2>
                                    <div id="g5" class="accordion-collapse collapse" data-bs-parent="#accGeneral">
                                        <div class="accordion-body">{{ __('ui.help.does_mydoctor_replace_doctor_answer') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Health FAQ --}}
                    <div class="faq-card searchable-item" id="faq-health">
                        <div class="faq-card-header">
                            <h5><i class="fas fa-heartbeat me-2"></i>{{ __('ui.help.health_tracking') }}</h5>
                        </div>
                        <div class="faq-card-body">
                            <div class="accordion" id="accHealth">
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#h1">{{ __('ui.help.what_health_metrics_can_i_track') }}</button></h2>
                                    <div id="h1" class="accordion-collapse collapse" data-bs-parent="#accHealth">
                                        <div class="accordion-body">{{ __('ui.help.what_health_metrics_can_i_track_answer') }}</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#h2">{{ __('ui.help.how_to_record_health_metric') }}</button></h2>
                                    <div id="h2" class="accordion-collapse collapse" data-bs-parent="#accHealth">
                                        <div class="accordion-body">{{ __('ui.help.how_to_record_health_metric_answer') }}</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#h3">{{ __('ui.help.how_to_log_symptoms') }}</button></h2>
                                    <div id="h3" class="accordion-collapse collapse" data-bs-parent="#accHealth">
                                        <div class="accordion-body">{{ __('ui.help.how_to_log_symptoms_answer') }}</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#h4">{{ __('ui.help.what_are_smart_suggestions') }}</button></h2>
                                    <div id="h4" class="accordion-collapse collapse" data-bs-parent="#accHealth">
                                        <div class="accordion-body">{{ __('ui.help.what_are_smart_suggestions_answer') }}</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#h5">{{ __('ui.help.how_often_log_health_metrics') }}</button></h2>
                                    <div id="h5" class="accordion-collapse collapse" data-bs-parent="#accHealth">
                                        <div class="accordion-body">{{ __('ui.help.how_often_log_health_metrics_answer') }}</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#h6">{{ __('ui.help.can_view_progress_over_time') }}</button></h2>
                                    <div id="h6" class="accordion-collapse collapse" data-bs-parent="#accHealth">
                                        <div class="accordion-body">{{ __('ui.help.can_view_progress_over_time_answer') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Medicine FAQ --}}
                    <div class="faq-card searchable-item" id="faq-medicine">
                        <div class="faq-card-header">
                            <h5><i class="fas fa-pills me-2"></i>{{ __('ui.help.medicine_management') }}</h5>
                        </div>
                        <div class="faq-card-body">
                            <div class="accordion" id="accMedicine">
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#m1">{{ __('ui.help.how_to_add_medicine') }}</button></h2>
                                    <div id="m1" class="accordion-collapse collapse"
                                        data-bs-parent="#accMedicine">
                                        <div class="accordion-body">{{ __('ui.help.how_to_add_medicine_answer') }}</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#m2">{{ __('ui.help.how_medicine_reminders_work') }}</button></h2>
                                    <div id="m2" class="accordion-collapse collapse"
                                        data-bs-parent="#accMedicine">
                                        <div class="accordion-body">{{ __('ui.help.how_medicine_reminders_work_answer') }}</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#m3">{{ __('ui.help.what_is_adherence_rate') }}</button></h2>
                                    <div id="m3" class="accordion-collapse collapse"
                                        data-bs-parent="#accMedicine">
                                        <div class="accordion-body">{{ __('ui.help.what_is_adherence_rate_answer') }}</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#m4">{{ __('ui.help.can_export_medicine_logs') }}</button></h2>
                                    <div id="m4" class="accordion-collapse collapse"
                                        data-bs-parent="#accMedicine">
                                        <div class="accordion-body">{{ __('ui.help.can_export_medicine_logs_answer') }}</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#m5">{{ __('ui.help.what_if_miss_dose') }}</button></h2>
                                    <div id="m5" class="accordion-collapse collapse"
                                        data-bs-parent="#accMedicine">
                                        <div class="accordion-body">{{ __('ui.help.what_if_miss_dose_answer') }}</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#m6">{{ __('ui.help.can_set_multiple_reminder_times') }}</button></h2>
                                    <div id="m6" class="accordion-collapse collapse"
                                        data-bs-parent="#accMedicine">
                                        <div class="accordion-body">{{ __('ui.help.can_set_multiple_reminder_times_answer') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Uploads FAQ --}}
                    <div class="faq-card searchable-item" id="faq-uploads">
                        <div class="faq-card-header">
                            <h5><i class="fas fa-file-medical me-2"></i>{{ __('ui.help.prescriptions_reports_title') }}</h5>
                        </div>
                        <div class="faq-card-body">
                            <div class="accordion" id="accUploads">
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#u1">{{ __('ui.help.how_to_upload_prescription') }}</button></h2>
                                    <div id="u1" class="accordion-collapse collapse" data-bs-parent="#accUploads">
                                        <div class="accordion-body">{{ __('ui.help.how_to_upload_prescription_answer') }}</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#u2">{{ __('ui.help.can_upload_medical_reports') }}</button></h2>
                                    <div id="u2" class="accordion-collapse collapse" data-bs-parent="#accUploads">
                                        <div class="accordion-body">{{ __('ui.help.can_upload_medical_reports_answer') }}</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#u3">{{ __('ui.help.which_file_formats_supported') }}</button></h2>
                                    <div id="u3" class="accordion-collapse collapse" data-bs-parent="#accUploads">
                                        <div class="accordion-body">{{ __('ui.help.which_file_formats_supported_answer') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Account FAQ --}}
                    <div class="faq-card searchable-item" id="faq-account">
                        <div class="faq-card-header">
                            <h5><i class="fas fa-user-cog me-2"></i>{{ __('ui.help.account_settings') }}</h5>
                        </div>
                        <div class="faq-card-body">
                            <div class="accordion" id="accAccount">
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#a1">{{ __('ui.help.how_to_update_profile') }}</button></h2>
                                    <div id="a1" class="accordion-collapse collapse" data-bs-parent="#accAccount">
                                        <div class="accordion-body">{{ __('ui.help.how_to_update_profile_answer') }}</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#a2">{{ __('ui.help.how_to_enable_push_notifications') }}</button></h2>
                                    <div id="a2" class="accordion-collapse collapse" data-bs-parent="#accAccount">
                                        <div class="accordion-body">{{ __('ui.help.how_to_enable_push_notifications_answer') }}</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#a3">{{ __('ui.help.can_change_password') }}</button></h2>
                                    <div id="a3" class="accordion-collapse collapse" data-bs-parent="#accAccount">
                                        <div class="accordion-body">{{ __('ui.help.can_change_password_answer') }}</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#a4">{{ __('ui.help.how_to_manage_privacy_settings') }}</button></h2>
                                    <div id="a4" class="accordion-collapse collapse" data-bs-parent="#accAccount">
                                        <div class="accordion-body">{{ __('ui.help.how_to_manage_privacy_settings_answer') }}</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#a5">{{ __('ui.help.can_switch_language') }}</button></h2>
                                    <div id="a5" class="accordion-collapse collapse" data-bs-parent="#accAccount">
                                        <div class="accordion-body">{{ __('ui.help.can_switch_language_answer') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="col-lg-4">

                    {{-- Getting Started Guide --}}
                    <div class="faq-card" id="getting-started">
                        <div class="faq-card-header">
                            <h5><i class="fas fa-rocket me-2"></i>{{ __('ui.help.getting_started') }}</h5>
                        </div>
                        <div class="faq-card-body" style="padding: 1rem 1.5rem;">
                            <div class="guide-card">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="guide-step">1</div>
                                    <div>
                                        <div class="guide-title">{{ __('ui.help.create_your_profile') }}</div>
                                        <p class="guide-desc">{{ __('ui.help.create_your_profile_desc') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="guide-card">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="guide-step">2</div>
                                    <div>
                                        <div class="guide-title">{{ __('ui.help.add_your_medicines') }}</div>
                                        <p class="guide-desc">{{ __('ui.help.add_your_medicines_desc') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="guide-card">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="guide-step">3</div>
                                    <div>
                                        <div class="guide-title">{{ __('ui.help.record_health_metrics') }}</div>
                                        <p class="guide-desc">{{ __('ui.help.record_health_metrics_desc') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="guide-card">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="guide-step">4</div>
                                    <div>
                                        <div class="guide-title">{{ __('ui.help.upload_prescriptions') }}</div>
                                        <p class="guide-desc">{{ __('ui.help.upload_prescriptions_desc') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="guide-card">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="guide-step">5</div>
                                    <div>
                                        <div class="guide-title">{{ __('ui.help.check_suggestions') }}</div>
                                        <p class="guide-desc">{{ __('ui.help.check_suggestions_desc') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="guide-card">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="guide-step">6</div>
                                    <div>
                                        <div class="guide-title">{{ __('ui.help.review_weekly_trends') }}</div>
                                        <p class="guide-desc">{{ __('ui.help.review_weekly_trends_desc') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="guide-card">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="guide-step">7</div>
                                    <div>
                                        <div class="guide-title">{{ __('ui.help.share_during_checkups') }}</div>
                                        <p class="guide-desc">{{ __('ui.help.share_during_checkups_desc') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Contact & Support --}}
                    <div class="contact-card mt-3">
                        <h6 class="fw-bold mb-3" style="color: #667eea;">
                            <i class="fas fa-headset me-2"></i>{{ __('ui.help.need_more_help') }}
                        </h6>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                            <div>
                                <div class="fw-semibold" style="font-size: 0.85rem;">{{ __('ui.help.email_support') }}</div>
                                <div class="text-muted" style="font-size: 0.8rem;">support@mydoctor.com</div>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-phone"></i></div>
                            <div>
                                <div class="fw-semibold" style="font-size: 0.85rem;">{{ __('ui.help.phone_support') }}</div>
                                <div class="text-muted" style="font-size: 0.8rem;">+880 1XXX-XXXXXX</div>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-clock"></i></div>
                            <div>
                                <div class="fw-semibold" style="font-size: 0.85rem;">{{ __('ui.help.available') }}</div>
                                <div class="text-muted" style="font-size: 0.8rem;">{{ __('ui.help.available_hours') }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Useful Links --}}
                    <div class="summary-card mt-3">
                        <div class="summary-card-header">
                            <h6><i class="fas fa-link me-2"></i>{{ __('ui.help.useful_links') }}</h6>
                        </div>
                        <div class="summary-card-body">
                            <a href="{{ $protectUrl(route('health')) }}" class="d-block text-decoration-none mb-2"
                                style="font-size: 0.85rem; color: #667eea;">
                                <i class="fas fa-heartbeat me-2"></i>{{ __('ui.help.health_dashboard') }}
                            </a>
                            <a href="{{ $protectUrl(route('medicine.reminders')) }}" class="d-block text-decoration-none mb-2"
                                style="font-size: 0.85rem; color: #667eea;">
                                <i class="fas fa-bell me-2"></i>{{ __('ui.help.medicine_reminders') }}
                            </a>
                            <a href="{{ $protectUrl(route('suggestions')) }}" class="d-block text-decoration-none mb-2"
                                style="font-size: 0.85rem; color: #667eea;">
                                <i class="fas fa-lightbulb me-2"></i>{{ __('ui.help.smart_suggestions') }}
                            </a>
                            <a href="{{ $protectUrl(route('dashboard')) }}" class="d-block text-decoration-none mb-2"
                                style="font-size: 0.85rem; color: #667eea;">
                                <i class="fas fa-tachometer-alt me-2"></i>{{ __('ui.help.dashboard') }}
                            </a>
                            <a href="{{ $protectUrl(route('community.landing')) }}" class="d-block text-decoration-none"
                                style="font-size: 0.85rem; color: #667eea;">
                                <i class="fas fa-users me-2"></i>{{ __('ui.help.community') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('helpSearchInput');
            if (!searchInput) return;

            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                const items = document.querySelectorAll('.accordion-item.searchable-item');

                if (!query) {
                    items.forEach(item => item.classList.remove('hidden'));
                    document.querySelectorAll('.faq-card.searchable-item').forEach(c => c.classList.remove(
                        'hidden'));
                    return;
                }

                // First hide all FAQ sections, then show those with matches
                document.querySelectorAll('.faq-card.searchable-item').forEach(c => c.classList.add(
                    'hidden'));

                items.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    if (text.includes(query)) {
                        item.classList.remove('hidden');
                        item.closest('.faq-card')?.classList.remove('hidden');
                    } else {
                        item.classList.add('hidden');
                    }
                });
            });
        });

        function scrollToFaq(id) {
            const el = document.getElementById('faq-' + id);
            if (el) {
                el.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    </script>
@endpush