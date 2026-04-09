{{-- ══════════════════════════════════════════════════════════
     Overview Tab — Full Redesign
     Rows:
       1. Hero Quick Stats   (4 gradient stat cards)
       2. Metrics Grid + Adherence Donut   (8/4)
       3. Symptoms · Conditions · Medicines   (4/4/4)
       4. Prescriptions · Reports   (6/6)
══════════════════════════════════════════════════════════ --}}
@php
    $metricConfig  = $metricConfig  ?? config('health.metric_types');
    $symptomsBn    = $symptomsList  ?? config('health.symptoms');

    $activeMeds       = $medicines->filter(fn($m) => $m->schedules->where('is_active', true)->isNotEmpty());
    $activeConditions = $userDiseases->whereIn('status', ['active', 'chronic', 'managed']);
    $lastSymptom      = $symptoms->first();

    // Per-metric icon + colour palette
    $metricIcons = [
        'blood_pressure' => ['icon' => 'fa-heartbeat',        'color' => 'red'],
        'blood_sugar'    => ['icon' => 'fa-tint',             'color' => 'orange'],
        'weight'         => ['icon' => 'fa-weight',           'color' => 'blue'],
        'height'         => ['icon' => 'fa-ruler-vertical',   'color' => 'purple'],
        'bmi'            => ['icon' => 'fa-balance-scale',    'color' => 'pink'],
        'heart_rate'     => ['icon' => 'fa-heart',            'color' => 'red'],
        'temperature'    => ['icon' => 'fa-thermometer-half', 'color' => 'orange'],
        'oxygen'         => ['icon' => 'fa-lungs',            'color' => 'blue'],
        'cholesterol'    => ['icon' => 'fa-flask',            'color' => 'green'],
        'sleep'          => ['icon' => 'fa-bed',              'color' => 'purple'],
    ];
@endphp


{{-- ══ Row 1 — Metrics Grid + Adherence Donut ═════════════════════ --}}
<div class="row g-4 mb-4">

    {{-- ── Metric tiles (col-lg-8) ── --}}
    <div class="col-lg-8">
        <div class="health-card h-100">
            <div class="health-card-header">
                <h5>
                    <i class="fas fa-tachometer-alt"></i> Health Metrics
                    <span class="bn-label">{{ __('ui.nav.health') }}</span>
                </h5>
                <button class="btn btn-sm text-white px-3"
                        data-bs-toggle="modal" data-bs-target="#addMetricModal"
                        style="background:linear-gradient(135deg,#667eea,#764ba2);border-radius:8px;font-size:0.78rem;">
                    <i class="fas fa-plus me-1"></i>Add
                </button>
            </div>
            <div class="health-card-body">
                @if($latestMetrics->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-chart-line d-block"></i>
                        <p>No metrics recorded yet. Add your first reading!</p>
                    </div>
                @else
                    <div class="row g-3">
                        @foreach($latestMetrics->take(6) as $type => $metric)
                            @php
                                $ic     = $metricIcons[$type] ?? ['icon' => 'fa-stethoscope', 'color' => 'purple'];
                                $enName = $metricConfig[$type]['en'] ?? ucwords(str_replace('_', ' ', $type));
                                $bnName = $metricConfig[$type]['bn'] ?? '';
                                $unit   = $metricConfig[$type]['unit'] ?? '';
                            @endphp
                            <div class="col-sm-6 col-xl-4">
                                <div class="metric-type-card mb-0">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="stat-summary-icon {{ $ic['color'] }}"
                                             style="width:40px;height:40px;border-radius:10px;font-size:1rem;flex-shrink:0;">
                                            <i class="fas {{ $ic['icon'] }}"></i>
                                        </div>
                                        <div class="flex-grow-1" style="min-width:0;">
                                            <div class="metric-type-name text-truncate">{{ $enName }}</div>
                                            @if($bnName)
                                                <div class="bn-label">{{ $bnName }}</div>
                                            @endif
                                            <div class="mt-1">
                                                @if(is_array($metric->value))
                                                    @foreach($metric->value as $k => $v)
                                                        <div class="small">
                                                            <span class="text-muted">{{ ucfirst(str_replace('_', ' ', $k)) }}: </span>
                                                            <strong class="text-dark">{{ $v }}</strong>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <strong class="text-dark" style="font-size:1.05rem;">{{ $metric->value }}</strong>
                                                    @if($unit)
                                                        <span class="text-muted small"> {{ $unit }}</span>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-muted text-nowrap" style="font-size:0.72rem;">
                                            {{ $metric->recorded_at->format('M d') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Adherence donut (col-lg-4) ── --}}
    <div class="col-lg-4">
        <div class="health-card h-100">
            <div class="health-card-header">
                <h5>
                    <i class="fas fa-check-double"></i> Adherence
                    <span class="bn-label">{{ __('ui.health.adherence') }}</span>
                </h5>
                <span class="health-card-badge bg-light text-muted">30 days</span>
            </div>
            <div class="health-card-body d-flex flex-column align-items-center justify-content-center">
                @if($totalScheduled > 0)
                    <div style="position:relative;width:160px;height:160px;">
                        <canvas id="adherenceChart"></canvas>
                        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none;">
                            <div class="fw-bold lh-1"
                                 style="font-size:1.6rem;color:{{ $adherenceRate >= 80 ? '#38a169' : ($adherenceRate >= 50 ? '#dd6b20' : '#e53e3e') }};">
                                {{ $adherenceRate }}%
                            </div>
                            <div class="text-muted" style="font-size:0.65rem;">rate</div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center gap-4 mt-4">
                        <div class="text-center">
                            <div class="fw-bold text-success" style="font-size:1.1rem;">{{ $totalTaken }}</div>
                            <div class="small text-muted">Taken</div>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold text-danger" style="font-size:1.1rem;">{{ $totalMissed }}</div>
                            <div class="small text-muted">Missed</div>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold text-secondary" style="font-size:1.1rem;">{{ $totalScheduled }}</div>
                            <div class="small text-muted">Total</div>
                        </div>
                    </div>
                @else
                    <div class="empty-state py-4">
                        <i class="fas fa-clipboard-check d-block"></i>
                        <p>No medicine logs yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══ Row 2 — Symptoms · Conditions · Medicines ═══════════════════ --}}
<div class="row g-4 mb-4">

    {{-- Recent Symptoms --}}
    <div class="col-lg-4">
        <div class="health-card h-100">
            <div class="health-card-header">
                <h5>
                    <i class="fas fa-notes-medical"></i> Symptoms
                    <span class="bn-label">{{ __('ui.health.symptom') }}</span>
                </h5>
                <button class="btn btn-sm text-white px-2"
                        data-bs-toggle="modal" data-bs-target="#addSymptomModal"
                        style="background:linear-gradient(135deg,#667eea,#764ba2);border-radius:8px;font-size:0.78rem;">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="health-card-body p-0">
                @if($symptoms->isEmpty())
                    <div class="empty-state py-4">
                        <i class="fas fa-notes-medical d-block"></i>
                        <p>No symptoms logged yet.</p>
                    </div>
                @else
                    @foreach($symptoms->take(5) as $sym)
                        <div class="d-flex align-items-start gap-3 px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <span class="severity-badge severity-{{ $sym->severity_level ?? 1 }} mt-1"
                                  style="min-width:38px;justify-content:center;flex-shrink:0;">
                                {{ $sym->severity_level ?? '—' }}
                            </span>
                            <div class="flex-grow-1" style="min-width:0;">
                                @if($sym->symptom)
                                    <a href="{{ route('public.symptoms.show', $sym->symptom) }}" class="fw-semibold text-truncate text-decoration-none d-inline-block" style="font-size:0.88rem;color:#2d3748; max-width: 100%;">
                                        {{ $sym->symptom_name }}
                                    </a>
                                @else
                                    <div class="fw-semibold text-truncate" style="font-size:0.88rem;color:#2d3748;">
                                        {{ $sym->symptom_name }}
                                    </div>
                                @endif
                                @if(!empty($symptomsBn[$sym->symptom_name]))
                                    <div class="bn-label text-truncate">{{ $symptomsBn[$sym->symptom_name] }}</div>
                                @endif
                                <div class="small text-muted">{{ $sym->recorded_at->format('M d, Y') }}</div>
                                @if($sym->note)
                                    <div class="small text-muted fst-italic">{{ Str::limit($sym->note, 45) }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    {{-- Active Conditions --}}
    <div class="col-lg-4">
        <div class="health-card h-100">
            <div class="health-card-header">
                <h5>
                    <i class="fas fa-virus"></i> Conditions
                    <span class="bn-label">{{ __('ui.health.disease') }}</span>
                </h5>
                <button class="btn btn-sm text-white px-2"
                        data-bs-toggle="modal" data-bs-target="#addDiseaseModal"
                        style="background:linear-gradient(135deg,#667eea,#764ba2);border-radius:8px;font-size:0.78rem;">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="health-card-body p-0">
                @if($activeConditions->isEmpty())
                    <div class="empty-state py-4">
                        <i class="fas fa-virus-slash d-block"></i>
                        <p>No conditions recorded.</p>
                    </div>
                @else
                    @foreach($activeConditions->take(5) as $ud)
                        @php
                            $statusBadge = match($ud->status) {
                                'active'  => 'severity-8',
                                'chronic' => 'severity-5',
                                'managed' => 'severity-3',
                                default   => 'severity-1',
                            };
                        @endphp
                        <div class="d-flex align-items-start gap-3 px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="stat-summary-icon red mt-1"
                                 style="width:34px;height:34px;border-radius:8px;font-size:0.8rem;flex-shrink:0;">
                                <i class="fas fa-virus"></i>
                            </div>
                            <div class="flex-grow-1" style="min-width:0;">
                                @if($ud->disease)
                                    <a href="{{ route('public.diseases.show', $ud->disease) }}" class="fw-semibold text-truncate text-decoration-none d-inline-block" style="font-size:0.88rem;color:#2d3748; max-width: 100%;">
                                        {{ $ud->disease->disease_name }}
                                    </a>
                                @else
                                    <div class="fw-semibold text-truncate" style="font-size:0.88rem;color:#2d3748;">
                                        Unknown
                                    </div>
                                @endif
                                @if($ud->diagnosed_at)
                                    <div class="small text-muted">Since {{ $ud->diagnosed_at->format('M Y') }}</div>
                                @endif
                            </div>
                            <span class="severity-badge {{ $statusBadge }} text-capitalize flex-shrink-0">{{ $ud->status_label }}</span>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    {{-- Active Medicines --}}
    <div class="col-lg-4">
        <div class="health-card h-100">
            <div class="health-card-header">
                <h5>
                    <i class="fas fa-pills"></i> Medicines
                    <span class="bn-label">{{ __('ui.nav.medicine') }}</span>
                </h5>
                <span class="health-card-badge bg-light text-muted">{{ $activeMeds->count() }} active</span>
            </div>
            <div class="health-card-body p-0">
                @if($activeMeds->isEmpty())
                    <div class="empty-state py-4">
                        <i class="fas fa-pills d-block"></i>
                        <p>No active medicines.</p>
                    </div>
                @else
                    @foreach($activeMeds->take(6) as $m)
                        <div class="d-flex align-items-start gap-3 px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="stat-summary-icon green mt-1"
                                 style="width:34px;height:34px;border-radius:8px;font-size:0.8rem;flex-shrink:0;">
                                <i class="fas fa-capsules"></i>
                            </div>
                            <div class="flex-grow-1" style="min-width:0;">
                                <div class="fw-semibold text-truncate" style="font-size:0.88rem;color:#2d3748;">
                                    {{ $m->medicine_name }}
                                </div>
                                <div class="small text-muted">
                                    {{ $m->value_per_dose }}{{ $m->unit }} &middot; {{ $m->type ?? 'N/A' }}
                                </div>
                            </div>
                            <span class="med-status-active flex-shrink-0">Active</span>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══ Row 3 — Prescriptions + Reports ════════════════════════════ --}}
<div class="row g-4">

    {{-- Prescriptions --}}
    <div class="col-lg-6">
        <div class="health-card">
            <div class="health-card-header">
                <h5>
                    <i class="fas fa-prescription"></i> Prescriptions
                    <span class="bn-label">{{ __('ui.health.prescription') }}</span>
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="health-card-badge bg-light text-muted">{{ $prescriptionUploads->count() }}</span>
                    <button class="btn btn-sm text-white px-2"
                            data-bs-toggle="modal" data-bs-target="#addUploadModal"
                            style="background:linear-gradient(135deg,#667eea,#764ba2);border-radius:8px;font-size:0.78rem;">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="health-card-body p-0">
                @if($prescriptionUploads->isEmpty())
                    <div class="empty-state py-4">
                        <i class="fas fa-file-prescription d-block"></i>
                        <p>No prescriptions uploaded.</p>
                    </div>
                @else
                    @foreach($prescriptionUploads->take(4) as $u)
                        <div class="d-flex align-items-start gap-3 px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="stat-summary-icon purple mt-1"
                                 style="width:34px;height:34px;border-radius:8px;font-size:0.8rem;flex-shrink:0;">
                                <i class="fas fa-file-prescription"></i>
                            </div>
                            <div class="flex-grow-1" style="min-width:0;">
                                <div class="fw-semibold text-truncate" style="font-size:0.88rem;color:#2d3748;">
                                    {{ Str::limit($u->title, 40) }}
                                </div>
                                <div class="small text-muted">
                                    {{ $u->doctor_name ?? '' }}
                                    {{ $u->document_date ? ' · ' . $u->document_date->format('M d, Y') : '' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    {{-- Reports --}}
    <div class="col-lg-6">
        <div class="health-card">
            <div class="health-card-header">
                <h5>
                    <i class="fas fa-file-medical-alt"></i> Reports
                    <span class="bn-label">{{ __('ui.health.medical_report') }}</span>
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="health-card-badge bg-light text-muted">{{ $reportUploads->count() }}</span>
                    <button class="btn btn-sm text-white px-2"
                            data-bs-toggle="modal" data-bs-target="#addUploadModal"
                            style="background:linear-gradient(135deg,#667eea,#764ba2);border-radius:8px;font-size:0.78rem;">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="health-card-body p-0">
                @if($reportUploads->isEmpty())
                    <div class="empty-state py-4">
                        <i class="fas fa-file-medical-alt d-block"></i>
                        <p>No reports uploaded.</p>
                    </div>
                @else
                    @foreach($reportUploads->take(4) as $u)
                        <div class="d-flex align-items-start gap-3 px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="stat-summary-icon blue mt-1"
                                 style="width:34px;height:34px;border-radius:8px;font-size:0.8rem;flex-shrink:0;">
                                <i class="fas fa-file-medical-alt"></i>
                            </div>
                            <div class="flex-grow-1" style="min-width:0;">
                                <div class="fw-semibold text-truncate" style="font-size:0.88rem;color:#2d3748;">
                                    {{ Str::limit($u->title, 40) }}
                                </div>
                                <div class="small text-muted">
                                    {{ $u->doctor_name ?? '' }}
                                    {{ $u->document_date ? ' · ' . $u->document_date->format('M d, Y') : '' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

