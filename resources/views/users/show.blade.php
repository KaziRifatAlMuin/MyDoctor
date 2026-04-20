@extends('layouts.app')

@section('title', $user->name . ' - ' . __('ui.admin_profile.page_title'))

@push('styles')
    <style>
        /* ── Page Section ── */
        .health-section {
            background: #f8f9fb;
            min-height: auto;
            padding: 2.5rem 0 3rem;
        }

        /* ── User Hero - EXPANDED ── */
        .user-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 40%, #f093fb 80%, #667eea 100%);
            background-size: 300% 300%;
            animation: heroGradient 8s ease infinite;
            border-radius: 20px;
            padding: 3rem;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 2rem;
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.3);
        }

        @keyframes heroGradient {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        .user-hero::before {
            content: '';
            position: absolute;
            top: -60%;
            right: -15%;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            pointer-events: none;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) scale(1);
            }

            50% {
                transform: translateY(-20px) scale(1.05);
            }
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-avatar {
            width: 120px;
            height: 120px;
            border-radius: 24px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
            border: 4px solid rgba(255, 255, 255, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 700;
            flex-shrink: 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin-bottom: 1rem;
        }

        .hero-info h2 {
            font-size: 2rem;
            font-weight: 800;
            margin: 0 0 0.5rem;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            line-height: 1.2;
        }

        .hero-info p {
            margin: 0 0 0.5rem;
            opacity: 0.95;
            font-size: 0.95rem;
            line-height: 1.4;
        }

        .hero-badges {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.6rem 1rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .hero-badge.verified {
            background: rgba(47, 158, 114, 0.3);
            color: #2f9e72;
            border-color: rgba(47, 158, 114, 0.4);
        }

        .hero-disease-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            margin-top: 1rem;
        }

        .hero-disease-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.38rem 0.75rem;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 700;
            background: rgba(11, 87, 208, 0.2);
            color: #dbeafe;
            border: 1px solid rgba(147, 197, 253, 0.45);
        }

        .hero-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .hero-detail {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .hero-detail-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .hero-detail-text small {
            display: block;
            font-size: 0.75rem;
            opacity: 0.85;
            margin-bottom: 2px;
        }

        .hero-detail-text strong {
            display: block;
            font-size: 0.95rem;
            font-weight: 600;
        }

        /* ── Action Buttons ── */
        .hero-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .btn-action {
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.88rem;
            padding: 0.65rem 1.25rem;
            transition: all 0.2s;
            border: none;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        /* ── Summary Stat Cards ── */
        .stat-summary-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }

        .stat-summary-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .stat-summary-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .stat-summary-icon.purple {
            background: rgba(102, 126, 234, 0.12);
            color: #667eea;
        }

        .stat-summary-icon.red {
            background: rgba(245, 101, 101, 0.12);
            color: #e53e3e;
        }

        .stat-summary-icon.green {
            background: rgba(72, 187, 120, 0.12);
            color: #38a169;
        }

        .stat-summary-icon.orange {
            background: rgba(237, 137, 54, 0.12);
            color: #dd6b20;
        }

        .stat-summary-icon.blue {
            background: rgba(66, 153, 225, 0.12);
            color: #3182ce;
        }

        .stat-summary-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #a0aec0;
            margin: 0;
        }

        .stat-summary-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #2d3748;
            line-height: 1;
        }

        .stat-summary-sub {
            font-size: 0.78rem;
            color: #a0aec0;
        }

        /* ── Health Tabs ── */
        .health-nav-tabs {
            border-bottom: 2px solid #e0e0e0;
            list-style: none;
            padding: 0;
            margin: 2rem 0 1.5rem;
            display: flex;
            gap: 0;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }

        .health-nav-tabs .nav-item {
            margin: 0;
        }

        .health-nav-tabs .nav-link {
            border: none;
            color: #6f7c96;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 1rem 1.25rem;
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
            cursor: pointer;
            transition: color 0.2s;
        }

        .health-nav-tabs .nav-link:hover {
            color: #667eea;
        }

        .health-nav-tabs .nav-link.active {
            color: #667eea;
            border-bottom: 3px solid #667eea;
        }

        /* ── Health Cards ── */
        .health-card {
            background: white;
            border-radius: 16px;
            border: none;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            height: 100%;
        }

        .health-card-header {
            padding: 1.5rem;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .health-card-header h5 {
            font-weight: 700;
            color: #667eea;
            margin: 0;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .health-card-body {
            padding: 1.5rem;
        }

        .health-card-badge {
            font-size: 0.72rem;
            padding: 0.35rem 0.8rem;
            border-radius: 20px;
            font-weight: 600;
        }

        .bn-label {
            font-size: 0.78rem;
            color: #a0aec0;
            font-weight: 400;
            display: block;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #a0aec0;
        }

        .empty-state i {
            font-size: 3rem;
            opacity: 0.3;
            margin-bottom: 1rem;
            display: block;
        }

        .chart-container {
            position: relative;
            width: 100%;
        }

        /* ── Modal Styles ── */
        .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            border-bottom: 1px solid #f0f0f0;
            padding: 1.5rem 1.5rem;
        }

        .modal-title {
            color: #667eea;
            font-weight: 700;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid #f0f0f0;
            padding: 1rem 1.5rem;
        }

        /* Improve admin edit-user modal ergonomics */
        #editUserModal .modal-dialog {
            margin-top: 4vh;
            margin-bottom: 1rem;
        }

        #editUserModal .modal-content {
            max-height: 92vh;
            overflow: hidden;
        }

        #editUserModal .modal-body {
            max-height: calc(92vh - 150px);
            overflow-y: auto;
        }

        #editUserModal .modal-footer {
            position: sticky;
            bottom: 0;
            background: #fff;
            z-index: 2;
        }

        /* Red cross close button for Edit User modal */
        #editUserModal .modal-header .btn-close {
            background-color: #ffffff;
            border: 1px solid rgba(229, 62, 62, 0.16);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            opacity: 1;
            color: #e53e3e;
            --bs-btn-close-color: #e53e3e;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            z-index: 20;
        }

        #editUserModal .modal-header .btn-close:hover {
            background-color: #fff5f5;
        }

        /* Ensure the explicit × inside the button is visible and red */
        #editUserModal .modal-header .btn-close .close-x {
            font-size: 18px;
            color: #e53e3e;
            line-height: 1;
            display: inline-block;
            transform: translateY(-1px);
            pointer-events: none;
        }

        #editUserModal .modal-footer .btn {
            min-width: 130px;
            font-weight: 600;
        }

        .form-control,
        .form-select,
        textarea {
            border-radius: 10px !important;
            border: 1.5px solid #e0e0e0 !important;
            transition: border-color 0.2s;
            min-height: 42px;
        }

        .form-control:focus,
        .form-select:focus,
        textarea:focus {
            border-color: #667eea !important;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .dropdown-menu {
            border-radius: 10px !important;
            border: 1px solid #e0e0e0 !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
        }

        .dropdown-item {
            padding: 0.7rem 1rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        .dropdown-item:hover {
            background-color: #f7fafc;
            color: #667eea;
        }

        .action-btn-group {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn-group .btn {
            padding: 0.35rem 0.65rem;
            font-size: 0.75rem;
            border-radius: 8px;
        }

        /* ── Severity Badge ── */
        .severity-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.35rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .severity-badge.severity-1,
        .severity-badge.severity-2 {
            background: rgba(72, 187, 120, 0.12);
            color: #38a169;
        }

        .severity-badge.severity-3,
        .severity-badge.severity-4,
        .severity-badge.severity-6 {
            background: rgba(237, 137, 54, 0.12);
            color: #dd6b20;
        }

        .severity-badge.severity-7,
        .severity-badge.severity-8 {
            background: rgba(245, 101, 101, 0.12);
            color: #e53e3e;
        }

        .severity-badge.severity-9,
        .severity-badge.severity-10 {
            background: rgba(192, 86, 33, 0.12);
            color: #c05621;
        }

        /* ── Table Styles ── */
        .health-table {
            width: 100%;
            border-collapse: collapse;
        }

        .health-table thead {
            background: #f7fafc;
            border-bottom: 2px solid #e0e0e0;
        }

        .health-table th {
            padding: 0.75rem 1rem;
            text-align: left;
            font-weight: 600;
            color: #667eea;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .health-table td {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.9rem;
            color: #2d3748;
        }

        .health-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
    </style>
@endpush

@section('content')
    <div class="health-section">
        <div class="container" style="max-width: 1140px;">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert"
                    style="border-radius: 12px; border: none; background: rgba(72,187,120,0.1); color: #38a169; border-left: 4px solid #38a169;">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert"
                    style="border-radius: 12px; border: none; background: rgba(229,62,62,0.1); color: #e53e3e; border-left: 4px solid #e53e3e;">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Expanded User Hero Section --}}
            <div class="user-hero">
                <div class="hero-content">
                    <div class="hero-avatar">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="hero-info">
                        <h2>{{ $user->name }}</h2>
                        <p>{{ $user->email }}</p>
                        @if ($user->phone)
                            <p style="margin: 0.3rem 0 0; font-size: 0.9rem;"><i
                                    class="fas fa-phone me-1"></i>{{ $user->phone }}</p>
                        @endif

                        <div class="hero-badges">
                            <span class="hero-badge">
                                <i class="fas {{ $user->role === 'admin' ? 'fa-crown' : 'fa-user' }}"></i>
                                {{ ucfirst($user->role) }}
                            </span>
                            @if ($user->email_verified_at)
                                <span class="hero-badge verified">
                                    <i class="fas fa-circle-check"></i>{{ __('ui.admin_profile.verified') }}
                                </span>
                            @endif
                            <span class="hero-badge">
                                <i class="fas fa-calendar-alt"></i>
                                {{ $user->created_at->format('M d, Y') }}
                            </span>
                        </div>

                        @php
                            $heroDiseases = $userDiseases
                                ->pluck('disease')
                                ->filter()
                                ->unique('id')
                                ->values();
                        @endphp
                        <div class="hero-disease-tags">
                            @forelse($heroDiseases as $disease)
                                <a href="{{ route('public.disease.show', $disease) }}" class="hero-disease-tag text-decoration-none">
                                    {{ $disease->display_name }}
                                </a>
                            @empty
                                <span class="hero-disease-tag" style="background: rgba(255,255,255,0.14); color: #f1f5f9; border-color: rgba(255,255,255,0.25);">
                                    {{ __('ui.admin_profile.no_disease_tags') }}
                                </span>
                            @endforelse
                        </div>
                    </div>

                    {{-- Hero Details Grid --}}
                    @if ($user->date_of_birth || $user->blood_group || $user->occupation || $user->gender || $user->address)
                        <div class="hero-details">
                            @if ($user->date_of_birth)
                                <div class="hero-detail">
                                    <div class="hero-detail-icon"><i class="fas fa-birthday-cake"></i></div>
                                    <div class="hero-detail-text">
                                        <small>{{ __('ui.admin_profile.date_of_birth') }}</small>
                                        <strong>{{ $user->date_of_birth->format('M d, Y') }}</strong>
                                    </div>
                                </div>
                            @endif
                            @if ($user->blood_group)
                                <div class="hero-detail">
                                    <div class="hero-detail-icon"><i class="fas fa-droplet"></i></div>
                                    <div class="hero-detail-text">
                                        <small>{{ __('ui.admin_profile.blood_group') }}</small>
                                        <strong>{{ $user->blood_group }}</strong>
                                    </div>
                                </div>
                            @endif
                            @if ($user->occupation)
                                <div class="hero-detail">
                                    <div class="hero-detail-icon"><i class="fas fa-briefcase"></i></div>
                                    <div class="hero-detail-text">
                                        <small>{{ __('ui.admin_profile.occupation') }}</small>
                                        <strong>{{ $user->occupation }}</strong>
                                    </div>
                                </div>
                            @endif
                            @if ($user->gender)
                                <div class="hero-detail">
                                    <div class="hero-detail-icon"><i class="fas fa-person"></i></div>
                                    <div class="hero-detail-text">
                                        <small>{{ __('ui.admin_profile.gender') }}</small>
                                        <strong>{{ ucfirst($user->gender) }}</strong>
                                    </div>
                                </div>
                            @endif
                            @if ($user->address)
                                <div class="hero-detail">
                                    <div class="hero-detail-icon"><i class="fas fa-location-dot"></i></div>
                                    <div class="hero-detail-text">
                                        <small>{{ __('ui.admin_profile.address') }}</small>
                                        <strong>{{ $user->address->display_upazila }}, {{ $user->address->display_district }}</strong>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="hero-actions">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-light btn-action">
                            <i class="fas fa-arrow-left me-1"></i>{{ __('ui.admin_profile.back_to_dashboard') }}
                        </a>
                        @if (auth()->id() !== $user->id)
                            <a href="{{ route('profile.mailbox.compose', ['to' => $user->id]) }}" class="btn btn-light btn-action">
                                <i class="fas fa-paper-plane me-1"></i>{{ __('ui.admin_profile.send_mail') }}
                            </a>
                        @endif
                        <button class="btn btn-light btn-action" data-bs-toggle="modal" data-bs-target="#editUserModal">
                            <i class="fas fa-user-edit me-1"></i>{{ __('ui.admin_profile.edit_profile') }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- Summary Stats Grid --}}
            <div class="row g-4 mb-4">
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stat-summary-card">
                        <div class="stat-summary-icon purple"><i class="fas fa-chart-line"></i></div>
                        <div class="stat-summary-label">{{ __('ui.admin_profile.metrics') }}</div>
                        <div class="stat-summary-value">{{ $healthMetrics->count() }}</div>
                        <div class="stat-summary-sub">{{ __('ui.admin_profile.recorded') }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stat-summary-card">
                        <div class="stat-summary-icon blue"><i class="fas fa-layer-group"></i></div>
                        <div class="stat-summary-label">{{ __('ui.admin_profile.types') }}</div>
                        <div class="stat-summary-value">{{ $metricsByType->count() }}</div>
                        <div class="stat-summary-sub">{{ __('ui.admin_profile.tracked') }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stat-summary-card">
                        <div class="stat-summary-icon orange"><i class="fas fa-notes-medical"></i></div>
                        <div class="stat-summary-label">{{ __('ui.admin_profile.symptoms') }}</div>
                        <div class="stat-summary-value">{{ $symptoms->count() }}</div>
                        <div class="stat-summary-sub">{{ __('ui.admin_profile.logged') }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stat-summary-card">
                        <div class="stat-summary-icon green"><i class="fas fa-pills"></i></div>
                        <div class="stat-summary-label">{{ __('ui.admin_profile.medicines') }}</div>
                        <div class="stat-summary-value">{{ $medicines->count() }}</div>
                        <div class="stat-summary-sub">{{ __('ui.admin_profile.tracked') }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stat-summary-card">
                        <div class="stat-summary-icon red"><i class="fas fa-virus"></i></div>
                        <div class="stat-summary-label">{{ __('ui.admin_profile.diseases') }}</div>
                        <div class="stat-summary-value">{{ $userDiseases->count() }}</div>
                        <div class="stat-summary-sub">{{ __('ui.admin_profile.managed') }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stat-summary-card">
                        <div class="stat-summary-icon purple"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-summary-label">{{ __('ui.admin_profile.adherence') }}</div>
                        <div class="stat-summary-value">{{ $adherenceRate }}%</div>
                        <div class="stat-summary-sub">{{ __('ui.admin_profile.completion') }}</div>
                    </div>
                </div>
            </div>

            {{-- Health Tab Navigation --}}
            <ul class="nav health-nav-tabs" id="healthTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview"
                        type="button" role="tab">
                        <i class="fas fa-th-large me-1"></i> {{ __('ui.admin_profile.overview') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="metrics-tab" data-bs-toggle="tab" data-bs-target="#metrics"
                        type="button" role="tab">
                        <i class="fas fa-chart-line me-1"></i> {{ __('ui.admin_profile.health_metrics') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="symptoms-tab" data-bs-toggle="tab" data-bs-target="#symptomsPane"
                        type="button" role="tab">
                        <i class="fas fa-notes-medical me-1"></i> {{ __('ui.admin_profile.symptoms_tab') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="diseases-tab" data-bs-toggle="tab" data-bs-target="#diseasesPane"
                        type="button" role="tab">
                        <i class="fas fa-virus me-1"></i> {{ __('ui.admin_profile.diseases_tab') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="prescriptions-tab" data-bs-toggle="tab" data-bs-target="#prescriptions"
                        type="button" role="tab">
                        <i class="fas fa-prescription-bottle-alt me-1"></i> {{ __('ui.admin_profile.prescriptions') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reportsPane"
                        type="button" role="tab">
                        <i class="fas fa-file-medical-alt me-1"></i> {{ __('ui.admin_profile.reports') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button"
                        role="tab">
                        <i class="fas fa-clipboard-list me-1"></i> {{ __('ui.admin_profile.medicine_logs') }}
                    </button>
                </li>
            </ul>

            {{-- Tab Content --}}
            <div class="tab-content" id="healthTabsContent">
                {{-- Overview Tab --}}
                <div class="tab-pane fade show active" id="overview" role="tabpanel">
                    @include('health.partials.overview')
                </div>

                {{-- Metrics Tab --}}
                <div class="tab-pane fade" id="metrics" role="tabpanel">
                    @include('health.partials.metrics')
                </div>

                {{-- Symptoms Tab --}}
                <div class="tab-pane fade" id="symptomsPane" role="tabpanel">
                    @include('health.partials.symptoms')
                </div>

                {{-- Diseases Tab --}}
                <div class="tab-pane fade" id="diseasesPane" role="tabpanel">
                    @include('health.partials.diseases')
                </div>

                {{-- Prescriptions Tab --}}
                <div class="tab-pane fade" id="prescriptions" role="tabpanel">
                    @include('health.partials.uploads', [
                        'uploadType' => 'prescription',
                        'uploadItems' => $prescriptionUploads,
                    ])
                </div>

                {{-- Reports Tab --}}
                <div class="tab-pane fade" id="reportsPane" role="tabpanel">
                    @include('health.partials.uploads', [
                        'uploadType' => 'report',
                        'uploadItems' => $reportUploads,
                    ])
                </div>

                {{-- Medicine Logs Tab --}}
                <div class="tab-pane fade" id="logs" role="tabpanel">
                    @include('health.partials.medicine-logs')
                </div>
            </div>

        </div>
    </div>

    {{-- ────────────────────────────────────────────────────────────── --}}
    {{-- MODALS --}}
    {{-- ────────────────────────────────────────────────────────────── --}}

    {{-- Edit User Modal --}}
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-edit me-2"></i>{{ __('ui.admin_profile.edit_user_profile') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span class="close-x" aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="user-update-form">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="redirect_tab" id="redirectTab" value="overview">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">{{ __('ui.admin_profile.full_name') }}</label>
                                <input type="text" name="name" class="form-control" value="{{ $user->name }}"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">{{ __('ui.admin_profile.email') }}</label>
                                <input type="email" name="email" class="form-control" value="{{ $user->email }}"
                                    required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">{{ __('ui.admin_profile.phone') }}</label>
                                <input type="tel" name="phone" class="form-control"
                                    value="{{ $user->phone ?? '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">{{ __('ui.admin_profile.occupation_label') }}</label>
                                <input type="text" name="occupation" class="form-control"
                                    value="{{ $user->occupation ?? '' }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">{{ __('ui.admin_profile.date_of_birth_label') }}</label>
                                <input type="date" name="date_of_birth" class="form-control"
                                    value="{{ $user->date_of_birth ?? '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">{{ __('ui.admin_profile.gender_label') }}</label>
                                <select name="gender" class="form-select">
                                    <option value="">{{ __('ui.admin_profile.select_gender') }}</option>
                                    <option value="male" {{ $user->gender === 'male' ? 'selected' : '' }}>{{ __('ui.admin_profile.male') }}</option>
                                    <option value="female" {{ $user->gender === 'female' ? 'selected' : '' }}>{{ __('ui.admin_profile.female') }}
                                    </option>
                                    <option value="other" {{ $user->gender === 'other' ? 'selected' : '' }}>{{ __('ui.admin_profile.other') }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">{{ __('ui.admin_profile.blood_group_label') }}</label>
                                <select name="blood_group" class="form-select">
                                    <option value="">{{ __('ui.admin_profile.select_blood_group') }}</option>
                                    <option value="O+" {{ $user->blood_group === 'O+' ? 'selected' : '' }}>O+</option>
                                    <option value="O-" {{ $user->blood_group === 'O-' ? 'selected' : '' }}>O-</option>
                                    <option value="A+" {{ $user->blood_group === 'A+' ? 'selected' : '' }}>A+</option>
                                    <option value="A-" {{ $user->blood_group === 'A-' ? 'selected' : '' }}>A-</option>
                                    <option value="B+" {{ $user->blood_group === 'B+' ? 'selected' : '' }}>B+</option>
                                    <option value="B-" {{ $user->blood_group === 'B-' ? 'selected' : '' }}>B-</option>
                                    <option value="AB+" {{ $user->blood_group === 'AB+' ? 'selected' : '' }}>AB+
                                    </option>
                                    <option value="AB-" {{ $user->blood_group === 'AB-' ? 'selected' : '' }}>AB-
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">{{ __('ui.admin_profile.account_role') }}</label>
                                <select name="role" class="form-select" required>
                                    <option value="member" {{ $user->role === 'member' ? 'selected' : '' }}>{{ __('ui.admin_profile.member') }}
                                    </option>
                                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>{{ __('ui.admin_profile.administrator') }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">{{ __('ui.admin_profile.account_status') }}</label>
                                <select name="is_active" class="form-select" required>
                                    <option value="1" {{ $user->is_active ? 'selected' : '' }}>{{ __('ui.admin_profile.active') }}</option>
                                    <option value="0" {{ !$user->is_active ? 'selected' : '' }}>{{ __('ui.admin_profile.inactive') }}</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                @php
                                    $isBnLocale = str_starts_with((string) app()->getLocale(), 'bn');
                                @endphp
                                <label class="form-label fw-semibold">{{ __('ui.admin_profile.division') }}</label>
                                <select id="adminDivisionSelect" class="form-select" data-current-id="{{ $user->address?->division_id }}">
                                    @if($user->address?->division)
                                        <option value="{{ $user->address->division }}" data-id="{{ $user->address->division_id }}" data-bn="{{ $user->address->division_bn }}" selected>{{ $isBnLocale ? ($user->address->division_bn ?: $user->address->division) : $user->address->division }}</option>
                                    @else
                                        <option value="">{{ __('ui.admin_profile.select_division') }}</option>
                                    @endif
                                </select>
                                <input type="hidden" name="division_id" id="adminDivisionId" value="{{ $user->address?->division_id }}">
                                <input type="hidden" name="division" id="adminDivisionEn" value="{{ $user->address?->division }}">
                                <input type="hidden" name="division_bn" id="adminDivisionBn" value="{{ $user->address?->division_bn }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">{{ __('ui.admin_profile.district') }}</label>
                                <select name="district" id="adminDistrictSelect" class="form-select" required data-current="{{ $user->address?->district }}" data-current-id="{{ $user->address?->district_id }}">
                                    <option value="{{ $user->address?->district ?? '' }}" selected>{{ $user->address?->district ? ($isBnLocale ? ($user->address?->district_bn ?: $user->address?->district) : $user->address?->district) : __('ui.admin_profile.select_district') }}</option>
                                </select>
                                <input type="hidden" name="district_id" id="adminDistrictId" value="{{ $user->address?->district_id }}">
                                <input type="hidden" name="district_bn" id="adminDistrictBn" value="{{ $user->address?->district_bn }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">{{ __('ui.admin_profile.upazila') }}</label>
                                <select name="upazila" id="adminUpazilaSelect" class="form-select" required data-current="{{ $user->address?->upazila }}" data-current-id="{{ $user->address?->upazila_id }}">
                                    <option value="{{ $user->address?->upazila ?? '' }}" selected>{{ $user->address?->upazila ? ($isBnLocale ? ($user->address?->upazila_bn ?: $user->address?->upazila) : $user->address?->upazila) : __('ui.admin_profile.select_upazila') }}</option>
                                </select>
                                <input type="hidden" name="upazila_id" id="adminUpazilaId" value="{{ $user->address?->upazila_id }}">
                                <input type="hidden" name="upazila_bn" id="adminUpazilaBn" value="{{ $user->address?->upazila_bn }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">{{ __('ui.admin_profile.street') }}</label>
                                <input type="text" name="street" class="form-control" value="{{ $user->address?->street ?? '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">{{ __('ui.admin_profile.house') }}</label>
                                <input type="text" name="house" class="form-control" value="{{ $user->address?->house ?? '' }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('ui.admin_profile.cancel') }}</button>
                        <button type="submit" class="btn text-white"
                            style="background: linear-gradient(135deg, #667eea, #764ba2);">
                            <i class="fas fa-save me-1"></i>{{ __('ui.admin_profile.save_changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Add/Edit Health Metric Modal --}}
    <div class="modal fade" id="addMetricModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-chart-line me-2"></i><span id="metricModalLabel">{{ __('ui.admin_profile.record_health_metric') }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="metricForm" action="{{ route('health.metric.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="metricFormMethod" value="POST">
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('ui.admin_profile.metric_type') }}</label>
                            <select name="metric_type" id="metricTypeSelect" class="form-select" required>
                                <option value="">{{ __('ui.admin_profile.select_metric_type') }}</option>
                                @foreach ($metricConfig as $key => $cfg)
                                    <option value="{{ $key }}">{{ $cfg['en'] }} ({{ $cfg['bn'] ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div id="metricFieldsContainer"></div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('ui.admin_profile.recorded_at') }}</label>
                            <input type="datetime-local" name="recorded_at" id="metricRecordedAt" class="form-control"
                                value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('ui.admin_profile.cancel_button') }}</button>
                        <button type="submit" class="btn text-white"
                            style="background: linear-gradient(135deg, #667eea, #764ba2);">
                            <i class="fas fa-save me-1"></i> <span id="metricSubmitLabel">{{ __('ui.admin_profile.save_metric') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Add/Edit Symptom Modal --}}
    <div class="modal fade" id="addSymptomModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-notes-medical me-2"></i><span id="symptomModalLabel">{{ __('ui.admin_profile.log_symptom') }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="symptomForm" action="{{ route('health.symptom.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="symptomFormMethod" value="POST">
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('ui.admin_profile.symptom_label') }}</label>
                            <div class="position-relative" id="symptomDropdownWrap">
                                <input type="text" id="symptomSearchInput" class="form-control"
                                    placeholder="{{ __('ui.admin_profile.search_symptom') }}" autocomplete="off">
                                <input type="hidden" name="symptom_name" id="symptomNameHidden" required>
                                <div id="symptomDropdownList" class="dropdown-menu w-100 shadow-sm"
                                    style="max-height: 220px; overflow-y: auto; display: none;"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('ui.admin_profile.severity_level') }}</label>
                            <input type="range" name="severity_level" id="severityRange" class="form-range"
                                min="1" max="10" value="5"
                                oninput="document.getElementById('severityValue').textContent=this.value">
                            <div class="d-flex justify-content-between" style="font-size: 0.78rem; color: #a0aec0;">
                                <span>{{ __('ui.admin_profile.mild') }}</span>
                                <span class="fw-bold" id="severityValue"
                                    style="color: #667eea; font-size: 1.1rem;">5</span>
                                <span>{{ __('ui.admin_profile.severe') }}</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('ui.admin_profile.recorded_at') }}</label>
                            <input type="datetime-local" name="recorded_at" id="symptomRecordedAt" class="form-control"
                                value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('ui.admin_profile.note_optional') }}</label>
                            <textarea name="note" id="symptomNote" class="form-control" rows="2" placeholder="{{ __('ui.admin_profile.additional_details') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('ui.admin_profile.cancel_button') }}</button>
                        <button type="submit" class="btn text-white"
                            style="background: linear-gradient(135deg, #667eea, #764ba2);">
                            <i class="fas fa-save me-1"></i> <span id="symptomSubmitLabel">{{ __('ui.admin_profile.log_symptom_button') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Add/Edit Disease Modal --}}
    <div class="modal fade" id="addDiseaseModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-virus me-2"></i><span id="diseaseModalLabel">{{ __('ui.admin_profile.add_disease_record') }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="diseaseForm" action="{{ route('health.disease.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="diseaseFormMethod" value="POST">
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('ui.admin_profile.disease_label') }}</label>
                            <div class="position-relative" id="diseaseDropdownWrap">
                                <input type="text" id="diseaseSearchInput" class="form-control"
                                    placeholder="{{ __('ui.admin_profile.search_disease') }}" autocomplete="off">
                                <input type="hidden" name="disease_id" id="diseaseIdHidden" required>
                                <div id="diseaseDropdownList" class="dropdown-menu w-100 shadow-sm"
                                    style="max-height: 220px; overflow-y: auto; display: none;"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('ui.admin_profile.status') }}</label>
                            <select name="status" id="diseaseStatus" class="form-select" required>
                                <option value="active">{{ __('ui.admin_profile.active_status') }}</option>
                                <option value="chronic">{{ __('ui.admin_profile.chronic') }}</option>
                                <option value="managed">{{ __('ui.admin_profile.managed_status') }}</option>
                                <option value="recovered">{{ __('ui.admin_profile.recovered') }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('ui.admin_profile.diagnosed_date') }}</label>
                            <input type="date" name="diagnosed_at" id="diseaseDiagnosedAt" class="form-control" value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('ui.admin_profile.notes_optional') }}</label>
                            <textarea name="notes" id="diseaseNotes" class="form-control" rows="2" placeholder="{{ __('ui.admin_profile.any_relevant_notes') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('ui.admin_profile.cancel_button') }}</button>
                        <button type="submit" class="btn text-white"
                            style="background: linear-gradient(135deg, #667eea, #764ba2);">
                            <i class="fas fa-save me-1"></i> <span id="diseaseSubmitLabel">{{ __('ui.admin_profile.add_disease_button') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Add/Edit Upload Modal --}}
    <div class="modal fade" id="addUploadModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-cloud-upload-alt me-2"></i><span id="uploadModalLabel">{{ __('ui.admin_profile.upload_document') }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="uploadForm" action="{{ route('health.upload.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="uploadFormMethod" value="POST">
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ __('ui.admin_profile.title') }}</label>
                                <input type="text" name="title" id="uploadTitle" class="form-control"
                                    placeholder="{{ __('ui.admin_profile.title_placeholder') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ __('ui.admin_profile.document_type') }}</label>
                                <select name="type" id="uploadType" class="form-select" required>
                                    <option value="prescription">{{ __('ui.admin_profile.prescription') }}</option>
                                    <option value="report">{{ __('ui.admin_profile.medical_report') }}</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">{{ __('ui.admin_profile.upload_image') }}</label>
                                <input type="file" name="file" class="form-control"
                                    accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" id="uploadFileInput"
                                    required>
                                <div class="form-text" id="uploadFileHint">{{ __('ui.admin_profile.file_hint') }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ __('ui.admin_profile.doctor_name') }}</label>
                                <input type="text" name="doctor_name" id="uploadDoctorName" class="form-control"
                                    placeholder="{{ __('ui.admin_profile.doctor_placeholder') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ __('ui.admin_profile.institution') }}</label>
                                <input type="text" name="institution" id="uploadInstitution" class="form-control"
                                    placeholder="{{ __('ui.admin_profile.institution_placeholder') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ __('ui.admin_profile.document_date') }}</label>
                                <input type="date" name="document_date" id="uploadDocumentDate" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ __('ui.admin_profile.notes') }}</label>
                                <input type="text" name="notes" id="uploadNotes" class="form-control"
                                    placeholder="{{ __('ui.admin_profile.quick_note') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">{{ __('ui.admin_profile.summary') }}</label>
                                <textarea name="summary" id="uploadSummary" class="form-control" rows="2" placeholder="{{ __('ui.admin_profile.summary_placeholder') }}"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('ui.admin_profile.cancel_button') }}</button>
                        <button type="submit" class="btn text-white"
                            style="background: linear-gradient(135deg, #667eea, #764ba2);">
                            <i class="fas fa-cloud-upload-alt me-1"></i> <span id="uploadSubmitLabel">{{ __('ui.admin_profile.upload_button') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @php
        $diseaseDropdownData = $allDiseases->map(function ($d) {
            return [
                'id' => $d->id,
                'display_name' => $d->display_name,
            ];
        })->values();

        $diseaseEditData = $allDiseases->map(function ($d) {
            return [
                'id' => $d->id,
                'display_name' => $d->display_name,
            ];
        })->values();
    @endphp
    <script>
        // Symptom search dropdown
        document.addEventListener('DOMContentLoaded', function() {
            const symptoms = @json($symptomsList);
            const diseases = @json($diseaseDropdownData, JSON_UNESCAPED_UNICODE);

            // ── Symptom Search ──
            const symptomSearch = document.getElementById('symptomSearchInput');
            const symptomDropdown = document.getElementById('symptomDropdownList');
            const symptomHidden = document.getElementById('symptomNameHidden');

            function renderSymptomDropdown(query = '') {
                symptomDropdown.innerHTML = '';
                const matches = query 
                    ? Object.keys(symptoms).filter(s => s.toLowerCase().includes(query) || (symptoms[s] && symptoms[s].toLowerCase().includes(query)))
                    : Object.keys(symptoms);
                
                if (matches.length) {
                    matches.forEach(symptom => {
                        const bn = symptoms[symptom] || '';
                        const opt = document.createElement('a');
                        opt.className = 'dropdown-item';
                        opt.href = '#';
                        opt.style.cssText = 'font-size:0.85rem; padding:0.45rem 1rem; white-space:normal;';
                        opt.innerHTML = symptom + (bn ? ' <span style="color:#a0aec0;">(' + bn + ')</span>' : '');
                        opt.addEventListener('click', (e) => {
                            e.preventDefault();
                            symptomSearch.value = symptom + (bn ? ' (' + bn + ')' : '');
                            symptomHidden.value = symptom;
                            symptomDropdown.style.display = 'none';
                        });
                        symptomDropdown.appendChild(opt);
                    });
                    symptomDropdown.style.display = 'block';
                } else if (query) {
                    symptomDropdown.innerHTML = '<div class="dropdown-item text-muted" style="font-size:0.85rem;">{{ __("ui.admin_profile.no_results_found") }}</div>';
                    symptomDropdown.style.display = 'block';
                }
            }

            if (symptomSearch) {
                // Show all items on focus
                symptomSearch.addEventListener('focus', function() {
                    renderSymptomDropdown(this.value);
                });
                // Filter on input
                symptomSearch.addEventListener('input', function() {
                    renderSymptomDropdown(this.value.toLowerCase());
                });
                // Close on click outside
                document.addEventListener('click', function(e) {
                    if (!symptomSearch.parentElement.contains(e.target)) {
                        symptomDropdown.style.display = 'none';
                    }
                });
            }

            // ── Disease Search ──
            const diseaseSearch = document.getElementById('diseaseSearchInput');
            const diseaseDropdown = document.getElementById('diseaseDropdownList');
            const diseaseHidden = document.getElementById('diseaseIdHidden');

            function renderDiseaseDropdown(query = '') {
                diseaseDropdown.innerHTML = '';
                const matches = query
                    ? diseases.filter(d => (d.display_name || '').toLowerCase().includes(query))
                    : diseases;
                
                if (matches.length) {
                    matches.forEach(disease => {
                        const opt = document.createElement('a');
                        opt.className = 'dropdown-item';
                        opt.href = '#';
                        opt.style.cssText = 'font-size:0.85rem; padding:0.45rem 1rem; white-space:normal;';
                        opt.innerHTML = disease.display_name;
                        opt.addEventListener('click', (e) => {
                            e.preventDefault();
                            diseaseSearch.value = disease.display_name;
                            diseaseHidden.value = disease.id;
                            diseaseDropdown.style.display = 'none';
                        });
                        diseaseDropdown.appendChild(opt);
                    });
                    diseaseDropdown.style.display = 'block';
                } else if (query) {
                    diseaseDropdown.innerHTML = '<div class="dropdown-item text-muted" style="font-size:0.85rem;">{{ __("ui.admin_profile.no_results_found") }}</div>';
                    diseaseDropdown.style.display = 'block';
                }
            }

            if (diseaseSearch) {
                // Show all items on focus
                diseaseSearch.addEventListener('focus', function() {
                    renderDiseaseDropdown(this.value);
                });
                // Filter on input
                diseaseSearch.addEventListener('input', function() {
                    renderDiseaseDropdown(this.value.toLowerCase());
                });
                // Close on click outside
                document.addEventListener('click', function(e) {
                    if (!diseaseSearch.parentElement.contains(e.target)) {
                        diseaseDropdown.style.display = 'none';
                    }
                });
            }

            // ── Metric Type Handler ──
            const metricTypeSelect = document.getElementById('metricTypeSelect');
            const metricConfig = @json($metricConfig);

            if (metricTypeSelect) {
                metricTypeSelect.addEventListener('change', function() {
                    const container = document.getElementById('metricFieldsContainer');
                    container.innerHTML = '';

                    if (!this.value) return;

                    const config = metricConfig[this.value];
                    if (!config || !config.fields) return;

                    config.fields.forEach(field => {
                        const div = document.createElement('div');
                        div.className = 'mb-3';
                        const input = document.createElement('input');
                        input.type = 'number';
                        input.step = '0.01';
                        input.name = `value_${field}`;
                        input.className = 'form-control';
                        input.placeholder = `${field} (${config.unit})`;
                        input.required = true;

                        const label = document.createElement('label');
                        label.className = 'form-label fw-semibold';
                        label.innerText = `${field} (${config.unit})`;

                        div.appendChild(label);
                        div.appendChild(input);
                        container.appendChild(div);
                    });
                });
            }

            // ── Initialize Charts ──
            setTimeout(() => {
                // Adherence doughnut chart
                const adhCtx = document.getElementById('adherenceChart');
                if (adhCtx) {
                    new Chart(adhCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Taken', 'Missed'],
                            datasets: [{
                                data: [{{ $totalTaken }}, {{ $totalMissed }}],
                                backgroundColor: ['#38a169', '#e53e3e'],
                                borderWidth: 0,
                                hoverOffset: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '72%',
                            plugins: {
                                legend: {
                                    display: false
                                }
                            }
                        }
                    });
                }

                // Severity bar chart
                const sevCtx = document.getElementById('severityChart');
                if (sevCtx) {
                    const sevLabels = @json($severityDistribution->keys()->map(fn($k) => 'Level ' . $k));
                    const sevData = @json($severityDistribution->values());
                    const sevColors = sevData.map((_, i) => {
                        const lvl = parseInt(sevLabels[i]?.replace('Level ', '')) || 1;
                        if (lvl <= 2) return '#38a169';
                        if (lvl <= 4) return '#dd6b20';
                        if (lvl <= 6) return '#c05621';
                        if (lvl <= 8) return '#e53e3e';
                        return '#c53030';
                    });
                    new Chart(sevCtx, {
                        type: 'bar',
                        data: {
                            labels: sevLabels,
                            datasets: [{
                                label: 'Count',
                                data: sevData,
                                backgroundColor: sevColors,
                                borderRadius: 6,
                                barThickness: 28
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 10
                                        },
                                        color: '#a0aec0'
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: '#f0f0f0'
                                    },
                                    ticks: {
                                        stepSize: 1,
                                        font: {
                                            size: 10
                                        },
                                        color: '#a0aec0'
                                    }
                                }
                            }
                        }
                    });
                }

                // Metric type bar chart
                @if ($metricsByType->count() > 0)
                    const metricsByTypeData = @json($metricsByType);
                    const metricConfigData = @json($metricConfig);
                    
                    Object.entries(metricsByTypeData).forEach(([type, metrics]) => {
                        const canvasId = 'chart-' + type.replace(/_/g, '-');
                        const ctx = document.getElementById(canvasId);
                        if (!ctx || !metrics || metrics.length === 0) return;
                        
                        const labels = metrics.map(m => new Date(m.recorded_at).toLocaleDateString());
                        const datasets = [];
                        const config = metricConfigData[type];
                        if (!config || !config.fields) return;
                        
                        const colors = ['#667eea', '#f093fb', '#764ba2', '#38a169', '#dd6b20', '#e53e3e'];
                        
                        config.fields.forEach((field, idx) => {
                            const data = metrics.map(m => {
                                if (typeof m.value === 'string') {
                                    try {
                                        const parsed = JSON.parse(m.value);
                                        return parseFloat(parsed[field]) || 0;
                                    } catch(e) { return 0; }
                                }
                                return parseFloat(m.value[field]) || 0;
                            });
                            
                            datasets.push({
                                label: field.replace(/_/g, ' '),
                                data: data,
                                borderColor: colors[idx % colors.length],
                                backgroundColor: colors[idx % colors.length].replace(')', ', 0.1)').replace('rgb', 'rgba'),
                                borderWidth: 2,
                                tension: 0.3,
                                fill: true,
                                pointRadius: 4,
                                pointBackgroundColor: colors[idx % colors.length],
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2
                            });
                        });
                        
                        new Chart(ctx, {
                            type: 'line',
                            data: { labels: labels, datasets: datasets },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: true, position: 'bottom', labels: { usePointStyle: true, padding: 15 } }
                                },
                                scales: {
                                    y: { beginAtZero: false, grid: { color: '#f0f0f0' } }
                                }
                            }
                        });
                    });
                @endif
            }, 100);
        });

        // Initialize data structures for edit functions (page-specific overrides)
        window.metricFieldDefs = @json(collect($metricConfig)->map(fn($c) => $c['js_fields']));
        window.symptomsList = @json($symptomsList, JSON_UNESCAPED_UNICODE);
        const diseasesData = @json($diseaseEditData, JSON_UNESCAPED_UNICODE);

        // ── Tab Persistence ──
        const editUserForm = document.querySelector('.user-update-form');
        const redirectTabInput = document.getElementById('redirectTab');
        
        // Map between pane IDs and button IDs for all tabs
        const tabMapping = {
            'overview': 'overview-tab',
            'metrics': 'metrics-tab',
            'symptomsPane': 'symptoms-tab',
            'diseasesPane': 'diseases-tab',
            'prescriptions': 'prescriptions-tab',
            'reportsPane': 'reports-tab',
            'logs': 'logs-tab'
        };
        
        // Get active tab before form submission
        if (editUserForm) {
            editUserForm.addEventListener('submit', function() {
                const activeTab = document.querySelector('.health-nav-tabs .nav-link.active');
                if (activeTab && activeTab.id) {
                    // Extract tab name from button id (e.g., 'overview-tab' -> 'overview')
                    const tabName = activeTab.id.replace('-tab', '');
                    redirectTabInput.value = tabName;
                }
            });
        }
        
        // Restore active tab from URL fragment
        window.addEventListener('load', function() {
            const hash = window.location.hash.replace('#', '');
            if (hash) {
                // First try direct mapping, then try with '-tab' suffix
                let tabButtonId = tabMapping[hash] || hash + '-tab';
                const tabButton = document.getElementById(tabButtonId);
                if (tabButton) {
                    const tabInstance = new bootstrap.Tab(tabButton);
                    tabInstance.show();
                }
            }

            initializeBdAddressSelector({
                divisionId: 'adminDivisionSelect',
                districtId: 'adminDistrictSelect',
                upazilaId: 'adminUpazilaSelect'
            });
        });

        async function initializeBdAddressSelector({ divisionId, districtId, upazilaId }) {
            const divisionSelect = document.getElementById(divisionId);
            const districtSelect = document.getElementById(districtId);
            const upazilaSelect = document.getElementById(upazilaId);
            const divisionIdInput = document.getElementById('adminDivisionId');
            const divisionEnInput = document.getElementById('adminDivisionEn');
            const divisionBnInput = document.getElementById('adminDivisionBn');
            const districtIdInput = document.getElementById('adminDistrictId');
            const districtBnInput = document.getElementById('adminDistrictBn');
            const upazilaIdInput = document.getElementById('adminUpazilaId');
            const upazilaBnInput = document.getElementById('adminUpazilaBn');

            if (!divisionSelect || !districtSelect || !upazilaSelect) {
                return;
            }

            const locale = document.documentElement.lang?.startsWith('bn') ? 'bn' : 'en';
            const apiBase = '{{ url('/geo/v2.0') }}';
            const divisionPlaceholder = locale === 'bn' ? '{{ __("ui.admin_profile.select_division") }}' : '{{ __("ui.admin_profile.select_division") }}';
            const districtPlaceholder = locale === 'bn' ? '{{ __("ui.admin_profile.select_district") }}' : '{{ __("ui.admin_profile.select_district") }}';
            const upazilaPlaceholder = locale === 'bn' ? '{{ __("ui.admin_profile.select_upazila") }}' : '{{ __("ui.admin_profile.select_upazila") }}';
            const currentDistrict = districtSelect.dataset.current || '';
            const currentUpazila = upazilaSelect.dataset.current || '';
            const currentDivisionId = divisionSelect.dataset.currentId || '';
            const currentDistrictId = districtSelect.dataset.currentId || '';
            const currentUpazilaId = upazilaSelect.dataset.currentId || '';

            const toList = (payload) => Array.isArray(payload) ? payload : (payload?.data || payload?.result || []);
            const getId = (item) => item?.id ?? item?.division_id ?? item?.district_id ?? item?.upazila_id ?? null;
            const getEn = (item) => item?.name ?? item?.division ?? item?.district ?? item?.upazila ?? item?.upazilla ?? item?.name_en ?? '';
            const getBn = (item) => item?.bn_name ?? item?.bn ?? item?.name_bn ?? item?.bangla ?? '';
            const labelOf = (item) => locale === 'bn' ? (getBn(item) || getEn(item)) : getEn(item);

            const setDivisionMeta = (item) => {
                divisionIdInput.value = getId(item) ?? '';
                divisionEnInput.value = getEn(item) || '';
                divisionBnInput.value = getBn(item) || '';
            };

            const setDistrictMeta = (item) => {
                districtIdInput.value = getId(item) ?? '';
                districtBnInput.value = getBn(item) || '';
            };

            const setUpazilaMeta = (item) => {
                upazilaIdInput.value = getId(item) ?? '';
                upazilaBnInput.value = getBn(item) || '';
            };

            const renderUpazilas = (upazilas, selectedName, selectedId) => {
                upazilaSelect.innerHTML = `<option value="">${upazilaPlaceholder}</option>`;
                (upazilas || []).forEach((item) => {
                    const option = document.createElement('option');
                    option.value = getEn(item);
                    option.textContent = labelOf(item);
                    option.dataset.id = String(getId(item) ?? '');
                    option.dataset.bn = getBn(item) || '';
                    if ((selectedId && option.dataset.id === String(selectedId)) || (selectedName && selectedName === option.value)) {
                        option.selected = true;
                        setUpazilaMeta(item);
                    }
                    upazilaSelect.appendChild(option);
                });
            };

            const renderDistricts = (districts, selectedDistrictName, selectedDistrictId, selectedUpazilaName, selectedUpazilaId) => {
                districtSelect.innerHTML = `<option value="">${districtPlaceholder}</option>`;
                districts.forEach((districtRow) => {
                    const option = document.createElement('option');
                    option.value = getEn(districtRow);
                    option.textContent = labelOf(districtRow);
                    option.dataset.id = String(getId(districtRow) ?? '');
                    option.dataset.bn = getBn(districtRow) || '';
                    if ((selectedDistrictId && option.dataset.id === String(selectedDistrictId)) || (selectedDistrictName && selectedDistrictName === option.value)) {
                        option.selected = true;
                        setDistrictMeta(districtRow);
                    }
                    districtSelect.appendChild(option);
                });

                const selectedRow = districts.find((item) => String(getId(item) ?? '') === districtSelect.selectedOptions[0]?.dataset.id) || districts.find((item) => getEn(item) === districtSelect.value);
                renderUpazilas([], '', '');
                if (selectedRow && getId(selectedRow)) {
                    loadUpazilas(getId(selectedRow), selectedUpazilaName, selectedUpazilaId);
                }
            };

            const loadUpazilas = async (districtPk, selectedUpazilaName, selectedUpazilaId) => {
                const response = await fetch(`${apiBase}/upazilas/${districtPk}`);
                const payload = await response.json();
                renderUpazilas(toList(payload), selectedUpazilaName, selectedUpazilaId);
            };

            try {
                const divisionsResponse = await fetch(`${apiBase}/divisions`);
                const divisionsJson = await divisionsResponse.json();
                const divisions = toList(divisionsJson);

                divisionSelect.innerHTML = `<option value="">${divisionPlaceholder}</option>`;
                divisions.forEach((division) => {
                    const option = document.createElement('option');
                    option.value = getEn(division);
                    option.textContent = labelOf(division);
                    option.dataset.id = String(getId(division) ?? '');
                    option.dataset.bn = getBn(division) || '';
                    if ((currentDivisionId && option.dataset.id === String(currentDivisionId)) || (!currentDivisionId && divisionEnInput?.value && option.value === divisionEnInput.value)) {
                        option.selected = true;
                        setDivisionMeta(division);
                    }
                    divisionSelect.appendChild(option);
                });

                const selectedDivisionPk = divisionSelect.selectedOptions[0]?.dataset.id;
                if (selectedDivisionPk) {
                    const districtsResponse = await fetch(`${apiBase}/districts/${selectedDivisionPk}`);
                    const districtsPayload = await districtsResponse.json();
                    renderDistricts(toList(districtsPayload), currentDistrict, currentDistrictId, currentUpazila, currentUpazilaId);
                }

                divisionSelect.addEventListener('change', async function() {
                    const selected = this.selectedOptions[0];
                    const divisionPk = selected?.dataset.id || '';
                    setDivisionMeta({ id: divisionPk, name: selected?.value || '', bn_name: selected?.dataset.bn || '' });
                    districtSelect.innerHTML = `<option value="">${districtPlaceholder}</option>`;
                    upazilaSelect.innerHTML = `<option value="">${upazilaPlaceholder}</option>`;
                    setDistrictMeta({});
                    setUpazilaMeta({});

                    if (!divisionPk) {
                        return;
                    }

                    const districtsResponse = await fetch(`${apiBase}/districts/${divisionPk}`);
                    const districtsPayload = await districtsResponse.json();
                    renderDistricts(toList(districtsPayload), '', '', '', '');
                });

                districtSelect.addEventListener('change', async function() {
                    const selected = this.selectedOptions[0];
                    const districtPk = selected?.dataset.id || '';
                    setDistrictMeta({ id: districtPk, bn_name: selected?.dataset.bn || '' });
                    upazilaSelect.innerHTML = `<option value="">${upazilaPlaceholder}</option>`;
                    setUpazilaMeta({});

                    if (!districtPk) {
                        return;
                    }

                    await loadUpazilas(districtPk, '', '');
                });

                upazilaSelect.addEventListener('change', function() {
                    const selected = this.selectedOptions[0];
                    setUpazilaMeta({ id: selected?.dataset.id || '', bn_name: selected?.dataset.bn || '' });
                });
            } catch (error) {
                console.error('Failed to load BD address API', error);
            }
        }
    </script>
@endpush