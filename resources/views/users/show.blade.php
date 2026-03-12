@extends('layouts.app')

@section('title', $user->name . ' - Profile')

@push('styles')
    <style>
        /* ── Page Section ── */
        .health-section {
            background: #f8f9fb;
            min-height: auto;
            padding: 2.5rem 0 3rem;
        }

        /* ── User Hero ── */
        .user-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 40%, #f093fb 80%, #667eea 100%);
            background-size: 300% 300%;
            animation: heroGradient 8s ease infinite;
            border-radius: 20px;
            padding: 2.5rem;
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

        /* ── Summary Stat Cards ── */
        .stat-summary-card {
            background: white;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }

        .stat-summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-summary-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
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
            margin-bottom: 2px;
        }

        .stat-summary-value {
            font-size: 1.35rem;
            font-weight: 700;
            color: #2d3748;
            line-height: 1.1;
        }

        .stat-summary-sub {
            font-size: 0.78rem;
            color: #a0aec0;
        }

        /* ── Health Nav Tabs ── */
        .health-nav-tabs {
            border-bottom: 2px solid #e0e0e0;
            list-style: none;
            padding: 0;
            margin: 1.5rem 0;
            display: flex;
            gap: 0;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .health-nav-tabs .nav-item {
            margin: 0;
        }

        .health-nav-tabs .nav-link {
            border: none;
            color: #6f7c96;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.75rem 1.25rem;
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
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
            padding: 1.25rem 1.5rem 0.75rem;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .health-card-header h5 {
            font-weight: 700;
            color: #667eea;
            margin: 0;
            font-size: 1.05rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .health-card-body {
            padding: 1rem 1.5rem 1.5rem;
        }

        .health-card-badge {
            font-size: 0.72rem;
            padding: 0.25rem 0.7rem;
            border-radius: 20px;
            font-weight: 600;
        }

        /* ── Bangla label ── */
        .bn-label {
            font-size: 0.78rem;
            color: #a0aec0;
            font-weight: 400;
            display: block;
        }
    </style>
@endpush

@section('content')
    <div class="health-section">
        <div class="container" style="max-width: 1140px;">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- User Hero Section --}}
            <div class="user-hero">
                <div
                    style="position: relative; z-index: 2; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 2rem;">
                    <div style="display: flex; align-items: center; gap: 1.5rem; flex: 1; min-width: 250px;">
                        <div
                            style="width: 100px; height: 100px; border-radius: 20px; background: linear-gradient(135deg, rgba(255,255,255,0.2), rgba(255,255,255,0.1)); border: 4px solid rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 700;">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div>
                            <h2
                                style="font-size: 1.8rem; font-weight: 800; margin: 0 0 0.5rem; text-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                                {{ $user->name }}</h2>
                            <p style="margin: 0 0 0.75rem; opacity: 0.95; font-size: 0.95rem;">{{ $user->email }}</p>
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <span
                                    style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 0.9rem; border-radius: 999px; font-size: 0.78rem; font-weight: 700; background: rgba(255,255,255,0.2); color: white;">
                                    <i class="fas {{ $user->role === 'admin' ? 'fa-crown' : 'fa-user' }}"></i>
                                    {{ ucfirst($user->role) }}
                                </span>
                                @if ($user->email_verified_at)
                                    <span
                                        style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 0.9rem; border-radius: 999px; font-size: 0.78rem; font-weight: 700; background: rgba(47,158,114,0.3); color: #2f9e72;">
                                        <i class="fas fa-circle-check"></i>Verified
                                    </span>
                                @endif
                                <span
                                    style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 0.9rem; border-radius: 999px; font-size: 0.78rem; font-weight: 700; background: rgba(255,255,255,0.2); color: white;">
                                    <i class="fas fa-calendar-alt"></i>
                                    Joined {{ $user->created_at->format('M Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-light"
                            style="border-radius: 12px; font-weight: 600; font-size: 0.88rem; padding: 0.65rem 1.25rem;">
                            <i class="fas fa-arrow-left me-1"></i>Back
                        </a>
                        <button class="btn btn-light"
                            style="border-radius: 12px; font-weight: 600; font-size: 0.88rem; padding: 0.65rem 1.25rem;"
                            data-bs-toggle="modal" data-bs-target="#editUserModal">
                            <i class="fas fa-edit me-1"></i>Edit User
                        </button>
                    </div>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stat-summary-card">
                        <div class="stat-summary-icon purple">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div>
                            <div class="stat-summary-label">Metrics</div>
                            <div class="stat-summary-value">{{ $healthMetrics->count() }}</div>
                            <div class="stat-summary-sub">recorded</div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stat-summary-card">
                        <div class="stat-summary-icon blue">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div>
                            <div class="stat-summary-label">Types</div>
                            <div class="stat-summary-value">{{ $metricsByType->count() }}</div>
                            <div class="stat-summary-sub">tracked</div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stat-summary-card">
                        <div class="stat-summary-icon orange">
                            <i class="fas fa-notes-medical"></i>
                        </div>
                        <div>
                            <div class="stat-summary-label">Symptoms</div>
                            <div class="stat-summary-value">{{ $symptoms->count() }}</div>
                            <div class="stat-summary-sub">logged</div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stat-summary-card">
                        <div class="stat-summary-icon green">
                            <i class="fas fa-pills"></i>
                        </div>
                        <div>
                            <div class="stat-summary-label">Medicines</div>
                            <div class="stat-summary-value">{{ $medicines->count() }}</div>
                            <div class="stat-summary-sub">tracked</div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stat-summary-card">
                        <div class="stat-summary-icon red">
                            <i class="fas fa-virus"></i>
                        </div>
                        <div>
                            <div class="stat-summary-label">Diseases</div>
                            <div class="stat-summary-value">{{ $userDiseases->count() }}</div>
                            <div class="stat-summary-sub">managed</div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stat-summary-card">
                        <div class="stat-summary-icon purple">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <div class="stat-summary-label">Adherence</div>
                            <div class="stat-summary-value">{{ $adherenceRate }}%</div>
                            <div class="stat-summary-sub">completion</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab Navigation --}}
            <ul class="nav health-nav-tabs" id="healthTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview"
                        type="button" role="tab">
                        <i class="fas fa-th-large me-1"></i> Overview
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="metrics-tab" data-bs-toggle="tab" data-bs-target="#metrics" type="button"
                        role="tab">
                        <i class="fas fa-chart-line me-1"></i> Metrics
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="symptoms-tab" data-bs-toggle="tab" data-bs-target="#symptomsPane"
                        type="button" role="tab">
                        <i class="fas fa-notes-medical me-1"></i> Symptoms
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="diseases-tab" data-bs-toggle="tab" data-bs-target="#diseasesPane"
                        type="button" role="tab">
                        <i class="fas fa-virus me-1"></i> Diseases
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="prescriptions-tab" data-bs-toggle="tab" data-bs-target="#prescriptions"
                        type="button" role="tab">
                        <i class="fas fa-prescription-bottle-alt me-1"></i> Prescriptions
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reportsPane"
                        type="button" role="tab">
                        <i class="fas fa-file-medical-alt me-1"></i> Reports
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button"
                        role="tab">
                        <i class="fas fa-clipboard-list me-1"></i> Medicine Logs
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

    {{-- Edit User Modal --}}
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 16px; border: none;">
                <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 1.25rem 1.5rem;">
                    <h5 class="modal-title fw-bold" style="color: #667eea;">
                        <i class="fas fa-user-edit me-2"></i>Edit User Profile
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body" style="padding: 1.5rem;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">Full Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $user->name }}"
                                    required style="border-radius: 10px; min-height: 42px;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ $user->email }}"
                                    required style="border-radius: 10px; min-height: 42px;">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">Phone</label>
                                <input type="tel" name="phone" class="form-control"
                                    value="{{ $user->phone ?? '' }}" style="border-radius: 10px; min-height: 42px;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">Occupation</label>
                                <input type="text" name="occupation" class="form-control"
                                    value="{{ $user->occupation ?? '' }}" style="border-radius: 10px; min-height: 42px;">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control"
                                    value="{{ $user->date_of_birth ?? '' }}"
                                    style="border-radius: 10px; min-height: 42px;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">Gender</label>
                                <select name="gender" class="form-select"
                                    style="border-radius: 10px; min-height: 42px;">
                                    <option value="">Select...</option>
                                    <option value="male" {{ $user->gender === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ $user->gender === 'female' ? 'selected' : '' }}>Female
                                    </option>
                                    <option value="other" {{ $user->gender === 'other' ? 'selected' : '' }}>Other
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">Blood Group</label>
                                <select name="blood_group" class="form-select"
                                    style="border-radius: 10px; min-height: 42px;">
                                    <option value="">Select...</option>
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
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">Account Role</label>
                                <select name="role" class="form-select"
                                    style="border-radius: 10px; min-height: 42px;">
                                    <option value="member" {{ $user->role === 'member' ? 'selected' : '' }}>Member
                                    </option>
                                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrator
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3" role="alert" style="border-radius: 10px;">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> Manage the user's account just like they would manage their own.
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn text-white"
                            style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 10px;">
                            <i class="fas fa-save me-1"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush
