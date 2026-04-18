@extends('layouts.app')

@section('title', __('ui.health.my_health_title'))

@push('styles')
    <style>
        /* All existing styles remain exactly the same */
        /* ── Page Section ── */
        .health-section {
            background: #f8f9fb;
            min-height: auto;
            padding: 2.5rem 0 3rem;
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

        .stat-summary-icon.pink {
            background: rgba(237, 100, 166, 0.12);
            color: #d53f8c;
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

        /* ── Section Cards ── */
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

        /* ── Metric Type Cards ── */
        .metric-type-card {
            background: #f8f9fb;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 0.75rem;
            border-left: 4px solid #667eea;
            transition: background 0.2s;
        }

        .metric-type-card:hover {
            background: #eef1f8;
        }

        .metric-type-name {
            font-weight: 700;
            color: #2d3748;
            font-size: 0.95rem;
            text-transform: capitalize;
        }

        .metric-type-value {
            font-size: 0.88rem;
            color: #4a5568;
        }

        .metric-type-date {
            font-size: 0.75rem;
            color: #a0aec0;
        }

        /* ── History Table ── */
        .health-table {
            width: 100%;
            font-size: 0.88rem;
        }

        .health-table thead th {
            background: #f8f9fb;
            color: #718096;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.75rem 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .health-table tbody td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #edf2f7;
            color: #4a5568;
        }

        .health-table tbody tr:hover {
            background: #f8f9fb;
        }

        .health-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* ── Severity Badges ── */
        .severity-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 0.2rem 0.65rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .severity-1,
        .severity-2 {
            background: rgba(72, 187, 120, 0.12);
            color: #38a169;
        }

        .severity-3,
        .severity-4 {
            background: rgba(237, 137, 54, 0.12);
            color: #dd6b20;
        }

        .severity-5,
        .severity-6 {
            background: rgba(237, 137, 54, 0.18);
            color: #c05621;
        }

        .severity-7,
        .severity-8 {
            background: rgba(245, 101, 101, 0.15);
            color: #e53e3e;
        }

        .severity-9,
        .severity-10 {
            background: rgba(197, 48, 48, 0.15);
            color: #c53030;
        }

        /* ── Medicine Status ── */
        .med-status-active {
            background: rgba(72, 187, 120, 0.12);
            color: #38a169;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .med-status-inactive {
            background: rgba(160, 174, 192, 0.15);
            color: #718096;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* ── Adherence bar ── */
        .adherence-bar {
            height: 10px;
            border-radius: 10px;
            background: #edf2f7;
            overflow: hidden;
        }

        .adherence-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.6s ease;
        }

        /* ── Medicine Log Row ── */
        .log-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.65rem 0;
            border-bottom: 1px solid #edf2f7;
        }

        .log-row:last-child {
            border-bottom: none;
        }

        .log-date {
            min-width: 75px;
            font-size: 0.78rem;
            color: #a0aec0;
            font-weight: 500;
        }

        .log-med-name {
            flex: 1;
            font-weight: 600;
            color: #2d3748;
            font-size: 0.88rem;
        }

        .log-stats {
            display: flex;
            gap: 12px;
            font-size: 0.78rem;
        }

        .log-taken {
            color: #38a169;
            font-weight: 600;
        }

        .log-missed {
            color: #e53e3e;
            font-weight: 600;
        }

        /* ── Tab nav ── */
        .health-nav-tabs {
            border: none;
            gap: 6px;
            margin-bottom: 1.5rem;
        }

        .health-nav-tabs .nav-link {
            border: none;
            border-radius: 25px;
            padding: 0.5rem 1.25rem;
            font-size: 0.88rem;
            font-weight: 600;
            color: #718096;
            background: #edf2f7;
            transition: all 0.2s;
        }

        .health-nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .health-nav-tabs .nav-link:hover:not(.active) {
            background: #e2e8f0;
            color: #4a5568;
        }

        /* ── Empty state ── */
        .empty-state {
            text-align: center;
            padding: 2.5rem 1rem;
            color: #a0aec0;
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
            color: #cbd5e0;
        }

        .empty-state p {
            font-size: 0.92rem;
            margin: 0;
        }

        /* ── Chart container ── */
        .chart-container {
            position: relative;
            width: 100%;
            height: 220px;
        }

        /* ── Metric pill values (JSON display) ── */
        .metric-value-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 4px;
        }

        .metric-value-pill {
            background: rgba(102, 126, 234, 0.08);
            color: #4a5568;
            border-radius: 8px;
            padding: 0.2rem 0.6rem;
            font-size: 0.78rem;
            font-weight: 500;
        }

        .metric-value-pill strong {
            color: #667eea;
        }

        /* ── Searchable Dropdown ── */
        .searchable-dropdown { position: relative; }
        .searchable-dropdown .dropdown-menu {
            width: 100%; max-height: 220px; overflow-y: auto;
            border-radius: 10px; box-shadow: 0 4px 14px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0; padding: 0.25rem 0;
        }
        .searchable-dropdown .dropdown-item:hover,
        .searchable-dropdown .dropdown-item:focus {
            background: rgba(102,126,234,0.08); color: #2d3748;
        }

        /* ── Action buttons in tables ── */
        .action-btn-group { display: flex; gap: 4px; flex-wrap: nowrap; }
        .action-btn-group .btn { padding: 0.2rem 0.45rem; border-radius: 6px; font-size: 0.72rem; }

        /* ── Bangla subtitle ── */
        .bn-label { font-size: 0.78rem; color: #a0aec0; font-weight: 400; }
    </style>
@endpush

@section('content')
    <div class="health-section">
        <div class="container" style="max-width: 1140px;">

            {{-- ── Flash Messages ── --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert"
                    style="border-radius: 12px; border: none; font-size: 0.9rem;">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('ui.actions.close') }}"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert"
                    style="border-radius: 12px; border: none; font-size: 0.9rem;">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('ui.actions.close') }}"></button>
                </div>
            @endif

            {{-- ── Page Header ── --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="fw-bold mb-1" style="color: #2d3748;">
                        <i class="fas fa-heartbeat me-2" style="color: #667eea;"></i>{{ __('ui.health.my_health') }}
                    </h3>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">
                        {{ __('ui.health.complete_health_overview') }} &middot; {{ __('ui.health.last_updated') }} {{ now()->format('M d, Y') }}
                    </p>
                </div>
                @if(!auth()->check() || !auth()->user()->isAdmin())
                    <div>
                        <button onclick="toggleChatbot()" class="btn btn-sm btn-outline-primary" style="border-radius:10px;">
                            <i class="fas fa-user-md me-1"></i> {{ __('ui.health.ask_mydoctor_ai') }}
                        </button>
                    </div>
                @endif
            </div>

            {{-- ── Summary Cards Row ── --}}
            @include('health.partials.summary-cards')

            {{-- ── Tab Navigation ── --}}
            <ul class="nav health-nav-tabs" id="healthTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview"
                        type="button" role="tab">
                        <i class="fas fa-th-large me-1"></i> {{ __('ui.health.overview') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="metrics-tab" data-bs-toggle="tab" data-bs-target="#metrics" type="button"
                        role="tab">
                        <i class="fas fa-chart-line me-1"></i> {{ __('ui.health.metrics') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="symptoms-tab" data-bs-toggle="tab" data-bs-target="#symptomsPane"
                        type="button" role="tab">
                        <i class="fas fa-notes-medical me-1"></i> {{ __('ui.health.symptoms') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="diseases-tab" data-bs-toggle="tab" data-bs-target="#diseasesPane"
                        type="button" role="tab">
                        <i class="fas fa-virus me-1"></i> {{ __('ui.health.diseases') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="prescriptions-tab" data-bs-toggle="tab" data-bs-target="#prescriptions"
                        type="button" role="tab">
                        <i class="fas fa-prescription-bottle-alt me-1"></i> {{ __('ui.health.prescriptions') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reportsPane"
                        type="button" role="tab">
                        <i class="fas fa-file-medical-alt me-1"></i> {{ __('ui.health.reports') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button"
                        role="tab">
                        <i class="fas fa-clipboard-list me-1"></i> {{ __('ui.health.medicine_logs') }}
                    </button>
                </li>
            </ul>

            {{-- ── Tab Content ── --}}
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
                    @include('health.partials.uploads', ['uploadType' => 'prescription', 'uploadItems' => $prescriptionUploads])
                </div>

                {{-- Reports Tab --}}
                <div class="tab-pane fade" id="reportsPane" role="tabpanel">
                    @include('health.partials.uploads', ['uploadType' => 'report', 'uploadItems' => $reportUploads])
                </div>

                {{-- Medicine Logs Tab --}}
                <div class="tab-pane fade" id="logs" role="tabpanel">
                    @include('health.partials.medicine-logs')
                </div>
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- ── MODALS ── --}}
    {{-- ══════════════════════════════════════════ --}}

    {{-- ── Add / Edit Health Metric Modal ── --}}
    <div class="modal fade" id="addMetricModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px; border: none;">
                <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 1.25rem 1.5rem;">
                    <h5 class="modal-title fw-bold" style="color: #667eea;">
                        <i class="fas fa-chart-line me-2"></i><span id="metricModalLabel">{{ __('ui.health.record_health_metric') }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('ui.actions.close') }}"></button>
                </div>
                <form id="metricForm" action="{{ route('health.metric.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="metricFormMethod" value="POST">
                    <div class="modal-body" style="padding: 1.5rem;">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem;">{{ __('ui.health.metric_type') }}</label>
                            <select name="metric_type" id="metricTypeSelect" class="form-select" style="border-radius: 10px;" required>
                                <option value="">{{ __('ui.health.select_metric_type') }}</option>
                                @foreach ($metricConfig as $key => $cfg)
                                    <option value="{{ $key }}">{{ app()->getLocale() === 'bn' ? $cfg['bn'] : $cfg['en'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="metricFieldsContainer"></div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem;">{{ __('ui.health.recorded_at') }}</label>
                            <input type="datetime-local" name="recorded_at" id="metricRecordedAt" class="form-control"
                                style="border-radius: 10px;" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #f0f0f0; padding: 1rem 1.5rem;">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 10px;">{{ __('ui.health.cancel') }}</button>
                        <button type="submit" class="btn text-white" style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 10px;">
                            <i class="fas fa-save me-1"></i> <span id="metricSubmitLabel">{{ __('ui.health.save_metric') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Add / Edit Symptom Modal ── --}}
    <div class="modal fade" id="addSymptomModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px; border: none;">
                <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 1.25rem 1.5rem;">
                    <h5 class="modal-title fw-bold" style="color: #667eea;">
                        <i class="fas fa-notes-medical me-2"></i><span id="symptomModalLabel">{{ __('ui.health.log_symptom') }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('ui.actions.close') }}"></button>
                </div>
                <form id="symptomForm" action="{{ route('health.symptom.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="symptomFormMethod" value="POST">
                    <div class="modal-body" style="padding: 1.5rem;">
                        {{-- Searchable Symptom Dropdown --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem;">{{ __('ui.health.symptom') }}</label>
                            <div class="position-relative" id="symptomDropdownWrap">
                                <input type="text" id="symptomSearchInput" class="form-control" style="border-radius: 10px;"
                                    placeholder="{{ __('ui.health.search_symptom') }}" autocomplete="off">
                                <input type="hidden" name="symptom_name" id="symptomNameHidden" required>
                                <div id="symptomDropdownList" class="dropdown-menu w-100 shadow-sm" style="max-height: 220px; overflow-y: auto; border-radius: 10px; display: none;"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem;">{{ __('ui.health.severity_level') }} (1-10)</label>
                            <input type="range" name="severity_level" id="severityRange" class="form-range" min="1" max="10" value="5"
                                oninput="document.getElementById('severityValue').textContent=this.value">
                            <div class="d-flex justify-content-between" style="font-size: 0.78rem; color: #a0aec0;">
                                <span>{{ __('ui.health.mild') }}</span>
                                <span class="fw-bold" id="severityValue" style="color: #667eea; font-size: 1.1rem;">5</span>
                                <span>{{ __('ui.health.severe') }}</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem;">{{ __('ui.health.recorded_at') }}</label>
                            <input type="datetime-local" name="recorded_at" id="symptomRecordedAt" class="form-control"
                                style="border-radius: 10px;" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem;">{{ __('ui.health.note_optional') }}</label>
                            <textarea name="note" id="symptomNote" class="form-control" style="border-radius: 10px;" rows="2"
                                placeholder="{{ __('ui.health.note_placeholder') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #f0f0f0; padding: 1rem 1.5rem;">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 10px;">{{ __('ui.health.cancel') }}</button>
                        <button type="submit" class="btn text-white" style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 10px;">
                            <i class="fas fa-save me-1"></i> <span id="symptomSubmitLabel">{{ __('ui.health.log_symptom') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Add / Edit Disease Modal ── --}}
    <div class="modal fade" id="addDiseaseModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px; border: none;">
                <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 1.25rem 1.5rem;">
                    <h5 class="modal-title fw-bold" style="color: #667eea;">
                        <i class="fas fa-virus me-2"></i><span id="diseaseModalLabel">{{ __('ui.health.add_disease_record') }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('ui.actions.close') }}"></button>
                </div>
                <form id="diseaseForm" action="{{ route('health.disease.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="diseaseFormMethod" value="POST">
                    <div class="modal-body" style="padding: 1.5rem;">
                        <div class="mb-3" id="diseaseSelectWrapper">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem;">{{ __('ui.health.disease') }}</label>
                            <div class="position-relative" id="diseaseDropdownWrap">
                                <input type="text" id="diseaseSearchInput" class="form-control" style="border-radius: 10px;"
                                    placeholder="{{ __('ui.health.search_disease') }}" autocomplete="off">
                                <input type="hidden" name="disease_id" id="diseaseIdHidden" required>
                                <div id="diseaseDropdownList" class="dropdown-menu w-100 shadow-sm" style="max-height: 220px; overflow-y: auto; border-radius: 10px; display: none;"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem;">{{ __('ui.health.status') }}</label>
                            <select name="status" id="diseaseStatus" class="form-select" style="border-radius: 10px;" required>
                                <option value="active">{{ __('ui.health.active') }}</option>
                                <option value="chronic">{{ __('ui.health.chronic') }}</option>
                                <option value="managed">{{ __('ui.health.managed') }}</option>
                                <option value="recovered">{{ __('ui.health.recovered') }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem;">{{ __('ui.health.diagnosed_date') }}</label>
                            <input type="date" name="diagnosed_at" id="diseaseDiagnosedAt" class="form-control" style="border-radius: 10px;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem;">{{ __('ui.health.notes_optional') }}</label>
                            <textarea name="notes" id="diseaseNotes" class="form-control" style="border-radius: 10px;" rows="2"
                                placeholder="{{ __('ui.health.notes_placeholder') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #f0f0f0; padding: 1rem 1.5rem;">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 10px;">{{ __('ui.health.cancel') }}</button>
                        <button type="submit" class="btn text-white" style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 10px;">
                            <i class="fas fa-save me-1"></i> <span id="diseaseSubmitLabel">{{ __('ui.health.add_disease') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Add / Edit Upload Modal ── --}}
    <div class="modal fade" id="addUploadModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 16px; border: none;">
                <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 1.25rem 1.5rem;">
                    <h5 class="modal-title fw-bold" style="color: #667eea;">
                        <i class="fas fa-cloud-upload-alt me-2"></i><span id="uploadModalLabel">{{ __('ui.health.upload_document') }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('ui.actions.close') }}"></button>
                </div>
                <form id="uploadForm" action="{{ route('health.upload.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="uploadFormMethod" value="POST">
                    <div class="modal-body" style="padding: 1.5rem;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">{{ __('ui.health.title') }}</label>
                                <input type="text" name="title" id="uploadTitle" class="form-control" style="border-radius: 10px;"
                                    placeholder="{{ __('ui.health.title_placeholder') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">{{ __('ui.health.document_type') }}</label>
                                <select name="type" id="uploadType" class="form-select" style="border-radius: 10px;" required>
                                    <option value="prescription">{{ __('ui.health.prescription') }}</option>
                                    <option value="report">{{ __('ui.health.medical_report') }}</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">{{ __('ui.health.upload_image') }}</label>
                                <input type="file" name="file" class="form-control" style="border-radius: 10px;"
                                    accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                    id="uploadFileInput">
                                <div class="form-text" id="uploadFileHint">{{ __('ui.health.image_formats_hint') }}</div>
                                <div id="imagePreviewContainer" class="mt-2 d-none">
                                    <img id="imagePreview" src="" alt="Preview"
                                        style="max-height: 150px; border-radius: 10px; border: 2px solid #edf2f7;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">{{ __('ui.health.doctor_name') }}</label>
                                <input type="text" name="doctor_name" id="uploadDoctorName" class="form-control" style="border-radius: 10px;"
                                    placeholder="{{ __('ui.health.doctor_placeholder') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">{{ __('ui.health.institution') }}</label>
                                <input type="text" name="institution" id="uploadInstitution" class="form-control" style="border-radius: 10px;"
                                    placeholder="{{ __('ui.health.institution_placeholder') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">{{ __('ui.health.document_date') }}</label>
                                <input type="date" name="document_date" id="uploadDocumentDate" class="form-control" style="border-radius: 10px;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">{{ __('ui.health.notes') }}</label>
                                <input type="text" name="notes" id="uploadNotes" class="form-control" style="border-radius: 10px;"
                                    placeholder="{{ __('ui.health.notes_optional_placeholder') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">{{ __('ui.health.summary') }}</label>
                                <textarea name="summary" id="uploadSummary" class="form-control" style="border-radius: 10px;" rows="2"
                                    placeholder="{{ __('ui.health.summary_placeholder') }}"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #f0f0f0; padding: 1rem 1.5rem;">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 10px;">{{ __('ui.health.cancel') }}</button>
                        <button type="submit" class="btn text-white" style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 10px;">
                            <i class="fas fa-save me-1"></i> <span id="uploadSubmitLabel">{{ __('ui.health.upload') }}</span>
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
                'name' => $d->disease_name,
                'bn' => $d->bangla_name,
                'display_name' => $d->display_name,
            ];
        })->values();
    @endphp
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* ═══════════════════════════════════════════════════════
             *  CONFIG DATA from PHP
             * ═══════════════════════════════════════════════════════ */
            window.metricFieldDefs = @json(collect($metricConfig)->map(fn($c) => $c['js_fields']));
            window.symptomsList    = @json($symptomsList, JSON_UNESCAPED_UNICODE);
            const diseasesData    = @json($diseaseDropdownData, JSON_UNESCAPED_UNICODE);


            /* ═══════════════════════════════════════════════════════
             *  REUSABLE: Searchable dropdown builder
             * ═══════════════════════════════════════════════════════ */
            function initSearchableDropdown(searchInput, dropdownList, hiddenInput, items, opts = {}) {
                function renderList(filter = '') {
                    const f = filter.toLowerCase();
                    const filtered = items.filter(i => {
                        const english = i.label.toLowerCase();
                        return english.includes(f);
                    });
                    dropdownList.innerHTML = '';
                    if (filtered.length === 0) {
                        dropdownList.innerHTML = '<div class="dropdown-item text-muted" style="font-size:0.85rem;">No results found</div>';
                    } else {
                        filtered.forEach(item => {
                            const a = document.createElement('a');
                            a.href = '#';
                            a.className = 'dropdown-item';
                            a.style.cssText = 'font-size:0.85rem; padding:0.45rem 1rem; white-space:normal;'
                            a.innerHTML = item.sub ? `${item.label} <span style="color:#a0aec0;">(${item.sub})</span>` : item.label;
                            a.addEventListener('click', function(e) {
                                e.preventDefault();
                                searchInput.value = item.label + (item.sub ? ' (' + item.sub + ')' : '');
                                hiddenInput.value = item.value;
                                dropdownList.style.display = 'none';
                            });
                            dropdownList.appendChild(a);
                        });
                    }
                    dropdownList.style.display = 'block';
                }

                // Show all items by default when input gets focus
                searchInput.addEventListener('focus', () => {
                    renderList(searchInput.value);
                    dropdownList.style.display = 'block';
                });
                // Filter as user types
                searchInput.addEventListener('input', () => renderList(searchInput.value));
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!searchInput.parentElement.contains(e.target)) {
                        dropdownList.style.display = 'none';
                    }
                });
                
                // Initialize: Show all items by default
                renderList('');
            }

            /* ═══════════════════════════════════════════════════════
             *  SYMPTOM searchable dropdown
             * ═══════════════════════════════════════════════════════ */
            const symptomItems = Object.entries(symptomsList).map(([en, bn]) => ({
                label: en, sub: bn, value: en
            }));
            const symptomSearch = document.getElementById('symptomSearchInput');
            const symptomDDList = document.getElementById('symptomDropdownList');
            const symptomHidden = document.getElementById('symptomNameHidden');
            if (symptomSearch) {
                initSearchableDropdown(symptomSearch, symptomDDList, symptomHidden, symptomItems);
            }

            /* ═══════════════════════════════════════════════════════
             *  DISEASE searchable dropdown
             * ═══════════════════════════════════════════════════════ */
            const diseaseItems = diseasesData.map(d => ({
                label: d.display_name || d.name, sub: d.bn || '', value: String(d.id)
            }));
            const diseaseSearch = document.getElementById('diseaseSearchInput');
            const diseaseDDList = document.getElementById('diseaseDropdownList');
            const diseaseHidden = document.getElementById('diseaseIdHidden');
            if (diseaseSearch) {
                initSearchableDropdown(diseaseSearch, diseaseDDList, diseaseHidden, diseaseItems);
            }

            /* ═══════════════════════════════════════════════════════
             *  DYNAMIC METRIC FIELDS
             * ═══════════════════════════════════════════════════════ */
            const metricTypeSelect = document.getElementById('metricTypeSelect');
            const fieldsContainer  = document.getElementById('metricFieldsContainer');

            function buildMetricFields(type, existingValues = {}) {
                fieldsContainer.innerHTML = '';
                const fields = metricFieldDefs[type];
                if (!fields) return;
                const row = document.createElement('div');
                row.className = 'row g-3 mb-3';
                fields.forEach(f => {
                    const col = document.createElement('div');
                    col.className = fields.length === 1 ? 'col-12' : 'col-6';
                    const val = existingValues[f.name] !== undefined ? existingValues[f.name] : '';
                    col.innerHTML = `
                        <label class="form-label fw-semibold" style="font-size: 0.85rem;">${f.label}</label>
                        <input type="number" name="${f.name}" class="form-control" style="border-radius: 10px;"
                            placeholder="${f.placeholder}" min="${f.min}" max="${f.max}"
                            step="${f.step || '1'}" value="${val}" required>
                    `;
                    row.appendChild(col);
                });
                fieldsContainer.appendChild(row);
            }

            if (metricTypeSelect) {
                metricTypeSelect.addEventListener('change', function() {
                    buildMetricFields(this.value);
                });
            }

            /* ═══════════════════════════════════════════════════════
             *  IMAGE PREVIEW
             * ═══════════════════════════════════════════════════════ */
            const fileInput = document.getElementById('uploadFileInput');
            const prevContainer = document.getElementById('imagePreviewContainer');
            const prevImg = document.getElementById('imagePreview');
            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = e => { prevImg.src = e.target.result; prevContainer.classList.remove('d-none'); };
                        reader.readAsDataURL(this.files[0]);
                    } else { prevContainer.classList.add('d-none'); }
                });
            }

            /* ═══════════════════════════════════════════════════════
             *  EDIT helpers — using global functions from layout
             * ═══════════════════════════════════════════════════════ */
            // window.openEditMetric, openEditSymptom, openEditDisease, openEditUpload
            // are now defined globally in layouts/app.blade.php and use window.metricFieldDefs, window.symptomsList

            /* Reset modals to "Add" mode when closed */
            ['addMetricModal','addSymptomModal','addDiseaseModal','addUploadModal'].forEach(modalId => {
                const el = document.getElementById(modalId);
                if (!el) return;
                el.addEventListener('hidden.bs.modal', function() {
                    if (modalId === 'addMetricModal') {
                        document.getElementById('metricModalLabel').textContent = '{{ __("ui.health.record_health_metric") }}';
                        document.getElementById('metricSubmitLabel').textContent = '{{ __("ui.health.save_metric") }}';
                        document.getElementById('metricForm').action = '{{ route("health.metric.store") }}';
                        document.getElementById('metricFormMethod').value = 'POST';
                        metricTypeSelect.value = '';
                        fieldsContainer.innerHTML = '';
                        document.getElementById('metricRecordedAt').value = '{{ now()->format("Y-m-d\\TH:i") }}';
                    }
                    if (modalId === 'addSymptomModal') {
                        document.getElementById('symptomModalLabel').textContent = '{{ __("ui.health.log_symptom") }}';
                        document.getElementById('symptomSubmitLabel').textContent = '{{ __("ui.health.log_symptom") }}';
                        document.getElementById('symptomForm').action = '{{ route("health.symptom.store") }}';
                        document.getElementById('symptomFormMethod').value = 'POST';
                        document.getElementById('symptomSearchInput').value = '';
                        document.getElementById('symptomNameHidden').value = '';
                        document.getElementById('severityRange').value = 5;
                        document.getElementById('severityValue').textContent = '5';
                        document.getElementById('symptomRecordedAt').value = '{{ now()->format("Y-m-d\\TH:i") }}';
                        document.getElementById('symptomNote').value = '';
                    }
                    if (modalId === 'addDiseaseModal') {
                        document.getElementById('diseaseModalLabel').textContent = '{{ __("ui.health.add_disease_record") }}';
                        document.getElementById('diseaseSubmitLabel').textContent = '{{ __("ui.health.add_disease") }}';
                        document.getElementById('diseaseForm').action = '{{ route("health.disease.store") }}';
                        document.getElementById('diseaseFormMethod').value = 'POST';
                        document.getElementById('diseaseSelectWrapper').style.display = '';
                        document.getElementById('diseaseIdHidden').setAttribute('required', 'required');
                        document.getElementById('diseaseSearchInput').value = '';
                        document.getElementById('diseaseIdHidden').value = '';
                        document.getElementById('diseaseStatus').value = 'active';
                        document.getElementById('diseaseDiagnosedAt').value = '';
                        document.getElementById('diseaseNotes').value = '';
                    }
                    if (modalId === 'addUploadModal') {
                        document.getElementById('uploadModalLabel').textContent = '{{ __("ui.health.upload_document") }}';
                        document.getElementById('uploadSubmitLabel').textContent = '{{ __("ui.health.upload") }}';
                        document.getElementById('uploadForm').action = '{{ route("health.upload.store") }}';
                        document.getElementById('uploadFormMethod').value = 'POST';
                        document.getElementById('uploadFileInput').setAttribute('required', 'required');
                        document.getElementById('uploadFileHint').textContent = '{{ __("ui.health.image_formats_hint") }}';
                        document.getElementById('uploadTitle').value = '';
                        document.getElementById('uploadType').value = 'prescription';
                        document.getElementById('uploadDoctorName').value = '';
                        document.getElementById('uploadInstitution').value = '';
                        document.getElementById('uploadDocumentDate').value = '';
                        document.getElementById('uploadNotes').value = '';
                        document.getElementById('uploadSummary').value = '';
                        prevContainer.classList.add('d-none');
                    }
                });
            });

            /* ═══════════════════════════════════════════════════════
             *  CHARTS
             * ═══════════════════════════════════════════════════════ */

            // ── Metric trend charts ──
            @foreach ($metricsByType as $type => $records)
                (function() {
                    const ctx = document.getElementById('chart-{{ Str::slug($type) }}');
                    if (!ctx) return;
                    const labels = @json($records->pluck('recorded_at')->map(fn($d) => $d->format('M d')));
                    const rawValues = @json($records->pluck('value'));
                    const data = rawValues.map(v => {
                        if (typeof v === 'number') return v;
                        if (typeof v === 'object' && v !== null) {
                            const first = Object.values(v)[0];
                            return parseFloat(first) || 0;
                        }
                        return parseFloat(v) || 0;
                    }).reverse();
                    new Chart(ctx, {
                        type: 'line',
                        data: { labels: [...labels].reverse(), datasets: [{ label: '{{ ucfirst(str_replace("_"," ",$type)) }}', data: data, borderColor: '#667eea', backgroundColor: 'rgba(102,126,234,0.08)', borderWidth: 2, tension: 0.4, fill: true, pointRadius: 3, pointBackgroundColor: '#667eea' }] },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#a0aec0' } }, y: { grid: { color: '#f0f0f0' }, ticks: { font: { size: 10 }, color: '#a0aec0' } } } }
                    });
                })();
            @endforeach

            // ── Adherence donut chart ──
            const adhCtx = document.getElementById('adherenceChart');
            if (adhCtx) {
                new Chart(adhCtx, {
                    type: 'doughnut',
                    data: { labels: ['{{ __("ui.health.taken") }}', '{{ __("ui.health.missed") }}'], datasets: [{ data: [{{ $totalTaken }}, {{ $totalMissed }}], backgroundColor: ['#38a169', '#e53e3e'], borderWidth: 0, hoverOffset: 6 }] },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '72%', plugins: { legend: { display: false } } }
                });
            }

            // ── Severity bar chart ──
            const sevCtx = document.getElementById('severityChart');
            if (sevCtx) {
                const sevLabels = @json($severityDistribution->keys()->map(fn($k) => 'Level ' . $k));
                const sevData = @json($severityDistribution->values());
                const sevColors = sevData.map((_, i) => {
                    const lvl = parseInt(sevLabels[i]?.replace('Level ', '')) || 1;
                    if (lvl <= 2) return '#38a169'; if (lvl <= 4) return '#dd6b20';
                    if (lvl <= 6) return '#c05621'; if (lvl <= 8) return '#e53e3e'; return '#c53030';
                });
                new Chart(sevCtx, {
                    type: 'bar',
                    data: { labels: sevLabels, datasets: [{ label: '{{ __("ui.health.count") }}', data: sevData, backgroundColor: sevColors, borderRadius: 6, barThickness: 28 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#a0aec0' } }, y: { beginAtZero: true, grid: { color: '#f0f0f0' }, ticks: { stepSize: 1, font: { size: 10 }, color: '#a0aec0' } } } }
                });
            }

            // Activate tab based on URL hash (e.g. #prescriptions)
            function activateTabFromHash() {
                const raw = window.location.hash || '';
                const hash = raw.split('?')[0];
                if (!hash) return;
                // try to find nav-link by data-bs-target or by id pattern
                let target = document.querySelector(`.nav-link[data-bs-target="${hash}"]`);
                if (!target) {
                    const idLookup = hash.replace('#', '') + '-tab';
                    target = document.getElementById(idLookup);
                }
                if (target) {
                    // use Bootstrap's Tab API to show
                    const tab = bootstrap.Tab.getOrCreateInstance(target);
                    tab.show();
                }
            }

            // activate on initial load and when the hash changes
            activateTabFromHash();
            window.addEventListener('hashchange', activateTabFromHash);
        });
    </script>
@endpush