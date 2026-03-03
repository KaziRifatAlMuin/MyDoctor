{{-- ── Overview Tab ── --}}
<div class="row g-4">

    {{-- Left Column: Latest Metrics + Adherence --}}
    <div class="col-lg-8">

        {{-- Latest Metrics Summary --}}
        <div class="health-card mb-4">
            <div class="health-card-header">
                <h5><i class="fas fa-tachometer-alt"></i> Latest Health Metrics</h5>
                <span class="health-card-badge bg-light text-muted">{{ $latestMetrics->count() }} types</span>
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
                                                {{ ucfirst(str_replace('_', ' ', $type)) }}
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
        <div class="health-card">
            <div class="health-card-header">
                <h5><i class="fas fa-notes-medical"></i> Recent Symptoms</h5>
                <span class="health-card-badge bg-light text-muted">last 5</span>
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
                                    <td class="fw-semibold">{{ $symptom->symptom_name }}</td>
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
