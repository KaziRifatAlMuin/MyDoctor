{{-- ── Overview Tab ── --}}
@php
    $metricConfig = config('health.metric_types');
    $symptomsBn = config('health.symptoms');
    $diseasesBnMap = config('health.diseases');
@endphp
<div class="row g-4">

    {{-- Left Column: Latest Metrics + Recent Symptoms + Recent Uploads --}}
    <div class="col-lg-8">

        {{-- Latest Metrics Summary --}}
        <div class="health-card mb-4">
            <div class="health-card-header">
                <h5><i class="fas fa-tachometer-alt"></i> Latest Health Metrics (সর্বশেষ স্বাস্থ্য মেট্রিক্স)</h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="health-card-badge bg-light text-muted">{{ $latestMetrics->count() }} types</span>
                    <button class="btn btn-sm text-white"
                        data-bs-toggle="modal" data-bs-target="#addMetricModal"
                        style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 8px; font-size: 0.75rem; padding: 0.25rem 0.6rem;">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="health-card-body">
                @if ($latestMetrics->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-chart-line d-block"></i>
                        <p>No health metrics recorded yet.<br>Start tracking to see your data here.</p>
                    </div>
                @else
                    <div class="row g-3">
                        @foreach ($latestMetrics as $type => $metric)
                            <div class="col-md-6">
                                <div class="metric-type-card">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="metric-type-name">
                                                <i class="fas fa-circle me-1"
                                                    style="font-size: 0.5rem; color: #667eea;"></i>
                                                {{ $metricConfig[$type]['en'] ?? ucfirst(str_replace('_', ' ', $type)) }}
                                                <span class="bn-label">({{ $metricConfig[$type]['bn'] ?? '' }})</span>
                                            </div>
                                            <div class="metric-value-pills">
                                                @if (is_array($metric->value))
                                                    @foreach ($metric->value as $key => $val)
                                                        <span class="metric-value-pill">
                                                            <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                                            {{ $val }}
                                                        </span>
                                                    @endforeach
                                                @else
                                                    <span class="metric-value-pill">{{ $metric->value }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <span class="metric-type-date">{{ $metric->recorded_at->format('M d') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Recent Symptoms --}}
        <div class="health-card mb-4">
            <div class="health-card-header">
                <h5><i class="fas fa-notes-medical"></i> Recent Symptoms (সাম্প্রতিক লক্ষণ)</h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="health-card-badge bg-light text-muted">last 5</span>
                    <button class="btn btn-sm text-white"
                        data-bs-toggle="modal" data-bs-target="#addSymptomModal"
                        style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 8px; font-size: 0.75rem; padding: 0.25rem 0.6rem;">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="health-card-body">
                @if ($symptoms->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-notes-medical d-block"></i>
                        <p>No symptoms recorded yet.</p>
                    </div>
                @else
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>Symptom</th>
                                <th>Severity</th>
                                <th>Date</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($symptoms->take(5) as $symptom)
                                <tr>
                                    <td>
                                        <span class="fw-semibold">{{ $symptom->symptom_name }}</span>
                                        @if (!empty($symptomsBn[$symptom->symptom_name]))
                                            <span class="bn-label">({{ $symptomsBn[$symptom->symptom_name] }})</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($symptom->severity_level)
                                            <span class="severity-badge severity-{{ $symptom->severity_level }}">
                                                <i class="fas fa-circle" style="font-size: 0.4rem;"></i>
                                                {{ $symptom->severity_level }}/10
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $symptom->recorded_at->format('M d, Y') }}</td>
                                    <td>{{ Str::limit($symptom->note, 40) ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        {{-- Active Conditions --}}
        <div class="health-card mb-4">
            <div class="health-card-header">
                <h5><i class="fas fa-virus"></i> Active Conditions (সক্রিয় রোগ)</h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="health-card-badge bg-light text-muted">{{ $userDiseases->whereIn('status', ['active', 'chronic'])->count() }} active</span>
                    <button class="btn btn-sm text-white"
                        data-bs-toggle="modal" data-bs-target="#addDiseaseModal"
                        style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 8px; font-size: 0.75rem; padding: 0.25rem 0.6rem;">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="health-card-body">
                @php
                    $activeConditions = $userDiseases->whereIn('status', ['active', 'chronic', 'managed'])->take(5);
                @endphp
                @if ($activeConditions->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-virus-slash d-block"></i>
                        <p>No active conditions recorded.</p>
                    </div>
                @else
                    @foreach ($activeConditions as $ud)
                        <div class="d-flex align-items-center gap-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="stat-summary-icon red" style="width: 36px; height: 36px; font-size: 0.9rem;">
                                <i class="fas fa-virus"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold" style="font-size: 0.88rem; color: #2d3748;">
                                    {{ $ud->disease->disease_name ?? 'Unknown' }}
                                    @php
                                        $bnName = $ud->disease->disease_name_bn ?? ($diseasesBnMap[$ud->disease->disease_name ?? ''] ?? '');
                                    @endphp
                                    @if ($bnName)
                                        <span class="bn-label">({{ $bnName }})</span>
                                    @endif
                                </div>
                                <div style="font-size: 0.75rem; color: #a0aec0;">
                                    {{ $ud->diagnosed_at ? 'Since ' . $ud->diagnosed_at->format('M Y') : '' }}
                                </div>
                            </div>
                            @php
                                $statusColor = match($ud->status) {
                                    'active' => 'severity-8',
                                    'chronic' => 'severity-5',
                                    'managed' => 'severity-3',
                                    default => 'severity-1',
                                };
                            @endphp
                            <span class="severity-badge {{ $statusColor }} text-capitalize">{{ $ud->status }}</span>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- Recent Documents --}}
        <div class="health-card">
            <div class="health-card-header">
                <h5><i class="fas fa-file-medical"></i> Recent Documents</h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="health-card-badge bg-light text-muted">{{ $uploads->count() }} total</span>
                    <button class="btn btn-sm text-white"
                        data-bs-toggle="modal" data-bs-target="#addUploadModal"
                        style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 8px; font-size: 0.75rem; padding: 0.25rem 0.6rem;">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="health-card-body">
                @if ($uploads->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-file-medical d-block"></i>
                        <p>No documents uploaded yet.</p>
                    </div>
                @else
                    @foreach ($uploads->take(4) as $upload)
                        <div class="d-flex align-items-center gap-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="stat-summary-icon {{ $upload->type === 'prescription' ? 'purple' : 'blue' }}" style="width: 36px; height: 36px; font-size: 0.9rem;">
                                <i class="fas fa-{{ $upload->type === 'prescription' ? 'prescription' : 'file-medical-alt' }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold" style="font-size: 0.88rem; color: #2d3748;">
                                    {{ Str::limit($upload->title, 35) }}
                                </div>
                                <div style="font-size: 0.75rem; color: #a0aec0;">
                                    {{ $upload->doctor_name ?? '' }}
                                    {{ $upload->document_date ? '· ' . $upload->document_date->format('M d, Y') : '' }}
                                </div>
                            </div>
                            <span class="health-card-badge {{ $upload->type === 'prescription' ? 'bg-primary bg-opacity-10 text-primary' : 'bg-success bg-opacity-10 text-success' }}">
                                {{ ucfirst($upload->type) }}
                            </span>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    {{-- Right Column: Adherence + Active Medicines --}}
    <div class="col-lg-4">

        {{-- Medicine Adherence --}}
        <div class="health-card mb-4">
            <div class="health-card-header">
                <h5><i class="fas fa-check-double"></i> Adherence</h5>
                <span class="health-card-badge bg-light text-muted">30 days</span>
            </div>
            <div class="health-card-body text-center">
                @if ($totalScheduled > 0)
                    <div class="chart-container" style="height: 170px; max-width: 170px; margin: 0 auto;">
                        <canvas id="adherenceChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <span class="fw-bold fs-3"
                            style="color: {{ $adherenceRate >= 80 ? '#38a169' : ($adherenceRate >= 50 ? '#dd6b20' : '#e53e3e') }};">
                            {{ $adherenceRate }}%
                        </span>
                        <div class="text-muted" style="font-size: 0.82rem;">
                            {{ $totalTaken }} taken &middot; {{ $totalMissed }} missed
                        </div>
                    </div>
                @else
                    <div class="empty-state py-3">
                        <i class="fas fa-clipboard-check d-block"></i>
                        <p>No medicine logs yet.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Active Medicines --}}
        <div class="health-card">
            <div class="health-card-header">
                <h5><i class="fas fa-pills"></i> Active Medicines</h5>
            </div>
            <div class="health-card-body">
                @php
                    $activeMeds = $medicines->filter(fn($m) => $m->schedules->where('is_active', true)->isNotEmpty());
                @endphp

                @if ($activeMeds->isEmpty())
                    <div class="empty-state py-3">
                        <i class="fas fa-pills d-block"></i>
                        <p>No active prescriptions.</p>
                    </div>
                @else
                    @foreach ($activeMeds->take(6) as $med)
                        <div class="d-flex align-items-center gap-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="stat-summary-icon green" style="width: 36px; height: 36px; font-size: 0.9rem;">
                                <i class="fas fa-capsules"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold" style="font-size: 0.88rem; color: #2d3748;">
                                    {{ $med->medicine_name }}</div>
                                <div style="font-size: 0.75rem; color: #a0aec0;">
                                    {{ $med->value_per_dose }}{{ $med->unit }} &middot; {{ $med->type ?? 'N/A' }}
                                </div>
                            </div>
                            <span class="med-status-active">Active</span>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
