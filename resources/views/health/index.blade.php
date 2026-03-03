@extends('layouts.app')

@section('title', 'My Health')

@push('styles')
    <style>
        /* ── Page Section ── */
        .health-section {
            background: #f8f9fb;
            min-height: calc(100vh - 80px);
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
    </style>
@endpush

@section('content')
    <div class="health-section">
        <div class="container" style="max-width: 1140px;">

            {{-- ── Page Header ── --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="fw-bold mb-1" style="color: #2d3748;">
                        <i class="fas fa-heartbeat me-2" style="color: #667eea;"></i>My Health
                    </h3>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">
                        Your complete health overview &middot; last updated {{ now()->format('M d, Y') }}
                    </p>
                </div>
            </div>

            {{-- ── Summary Cards Row ── --}}
            @include('health.partials.summary-cards')

            {{-- ── Tab Navigation ── --}}
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
                    <button class="nav-link" id="prescriptions-tab" data-bs-toggle="tab" data-bs-target="#prescriptions"
                        type="button" role="tab">
                        <i class="fas fa-prescription-bottle-alt me-1"></i> Prescriptions
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button"
                        role="tab">
                        <i class="fas fa-clipboard-list me-1"></i> Medicine Logs
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

                {{-- Prescriptions Tab --}}
                <div class="tab-pane fade" id="prescriptions" role="tabpanel">
                    @include('health.partials.prescriptions')
                </div>

                {{-- Medicine Logs Tab --}}
                <div class="tab-pane fade" id="logs" role="tabpanel">
                    @include('health.partials.medicine-logs')
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ── Metric trend charts (one per metric type) ──
            @foreach ($metricsByType as $type => $records)
                (function() {
                    const ctx = document.getElementById('chart-{{ Str::slug($type) }}');
                    if (!ctx) return;

                    const labels = @json($records->pluck('recorded_at')->map(fn($d) => $d->format('M d')));
                    // Extract first numeric value from the JSON value column
                    const rawValues = @json($records->pluck('value'));
                    const data = rawValues.map(v => {
                        if (typeof v === 'number') return v;
                        if (typeof v === 'object' && v !== null) {
                            const first = Object.values(v)[0];
                            return parseFloat(first) || 0;
                        }
                        return parseFloat(v) || 0;
                    }).reverse();
                    const labelsReversed = [...labels].reverse();

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labelsReversed,
                            datasets: [{
                                label: '{{ ucfirst($type) }}',
                                data: data,
                                borderColor: '#667eea',
                                backgroundColor: 'rgba(102,126,234,0.08)',
                                borderWidth: 2,
                                tension: 0.4,
                                fill: true,
                                pointRadius: 3,
                                pointBackgroundColor: '#667eea',
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
                                    grid: {
                                        color: '#f0f0f0'
                                    },
                                    ticks: {
                                        font: {
                                            size: 10
                                        },
                                        color: '#a0aec0'
                                    }
                                }
                            }
                        }
                    });
                })();
            @endforeach

            // ── Adherence donut chart ──
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
                            hoverOffset: 6,
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

            // ── Severity distribution bar chart ──
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
                            barThickness: 28,
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
        });
    </script>
@endpush
