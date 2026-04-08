@extends('layouts.app')

@section('title', 'Smart Suggestions - My Doctor')

@push('styles')
    <style>
        .suggestions-section {
            background: #f8f9fb;
            min-height: 100vh;
            padding: 2.5rem 0 3rem;
        }

        .suggestion-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 5px solid;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .suggestion-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.1);
        }

        .suggestion-card.border-danger {
            border-left-color: #e53e3e;
        }

        .suggestion-card.border-warning {
            border-left-color: #dd6b20;
        }

        .suggestion-card.border-info {
            border-left-color: #3182ce;
        }

        .suggestion-card.border-success {
            border-left-color: #38a169;
        }

        .suggestion-card.border-primary {
            border-left-color: #667eea;
        }

        .suggestion-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .suggestion-icon.bg-danger {
            background: rgba(229, 62, 62, 0.12);
            color: #e53e3e;
        }

        .suggestion-icon.bg-warning {
            background: rgba(221, 107, 32, 0.12);
            color: #dd6b20;
        }

        .suggestion-icon.bg-info {
            background: rgba(49, 130, 206, 0.12);
            color: #3182ce;
        }

        .suggestion-icon.bg-success {
            background: rgba(56, 161, 105, 0.12);
            color: #38a169;
        }

        .suggestion-icon.bg-primary {
            background: rgba(102, 126, 234, 0.12);
            color: #667eea;
        }

        .suggestion-category {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 700;
            padding: 0.15rem 0.6rem;
            border-radius: 20px;
            display: inline-block;
        }

        .suggestion-category.cat-danger {
            background: rgba(229, 62, 62, 0.1);
            color: #e53e3e;
        }

        .suggestion-category.cat-warning {
            background: rgba(221, 107, 32, 0.1);
            color: #dd6b20;
        }

        .suggestion-category.cat-info {
            background: rgba(49, 130, 206, 0.1);
            color: #3182ce;
        }

        .suggestion-category.cat-success {
            background: rgba(56, 161, 105, 0.1);
            color: #38a169;
        }

        .suggestion-category.cat-primary {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }

        .suggestion-title {
            font-weight: 700;
            font-size: 1rem;
            color: #2d3748;
            margin-bottom: 0.35rem;
        }

        .suggestion-message {
            font-size: 0.88rem;
            color: #4a5568;
            line-height: 1.6;
            margin: 0;
        }

        /* Sidebar summary */
        .summary-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .summary-card-header {
            padding: 1rem 1.25rem 0.6rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .summary-card-header h6 {
            font-weight: 700;
            color: #667eea;
            font-size: 0.92rem;
            margin: 0;
        }

        .summary-card-body {
            padding: 1rem 1.25rem;
        }

        .summary-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0.45rem 0;
            border-bottom: 1px solid #f7f7f7;
            font-size: 0.85rem;
            color: #4a5568;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .summary-dot.green {
            background: #38a169;
        }

        .summary-dot.red {
            background: #e53e3e;
        }

        .summary-dot.orange {
            background: #dd6b20;
        }

        .summary-dot.blue {
            background: #3182ce;
        }

        .summary-dot.purple {
            background: #667eea;
        }

        .filter-btn {
            border: none;
            border-radius: 20px;
            padding: 0.4rem 1rem;
            font-size: 0.82rem;
            font-weight: 600;
            color: #718096;
            background: #edf2f7;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #a0aec0;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #cbd5e0;
        }
    </style>
@endpush

@section('content')
    <div class="suggestions-section">
        <div class="container" style="max-width: 1140px;">

            {{-- Page Header --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="fw-bold mb-1" style="color: #2d3748;">
                        <i class="fas fa-lightbulb me-2" style="color: #667eea;"></i>Smart Suggestions
                    </h3>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">
                        Personalized health recommendations based on your data
                    </p>
                </div>
                <a href="{{ route('health') }}" class="btn btn-sm text-white"
                    style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 10px;">
                    <i class="fas fa-heartbeat me-1"></i> Health Dashboard
                </a>
            </div>

            {{-- Filter Buttons --}}
            <div class="d-flex flex-wrap gap-2 mb-4">
                <button class="filter-btn active" onclick="filterSuggestions('all', this)">All</button>
                <button class="filter-btn" onclick="filterSuggestions('Metric Alert', this)">Metric Alerts</button>
                <button class="filter-btn" onclick="filterSuggestions('Adherence', this)">Adherence</button>
                <button class="filter-btn" onclick="filterSuggestions('Symptom', this)">Symptoms</button>
                <button class="filter-btn" onclick="filterSuggestions('Condition', this)">Conditions</button>
                <button class="filter-btn" onclick="filterSuggestions('Lifestyle', this)">Lifestyle</button>
                <button class="filter-btn" onclick="filterSuggestions('Wellness', this)">Wellness</button>
                <button class="filter-btn" onclick="filterSuggestions('Getting Started', this)">Getting Started</button>
            </div>

            <div class="row">
                {{-- Main Suggestions Column --}}
                <div class="col-lg-8">
                    @if (count($suggestions) === 0)
                        <div class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <p class="fw-semibold" style="font-size: 1.1rem; color: #2d3748;">Everything Looks Good!</p>
                            <p>No specific suggestions right now. Keep up the good work!</p>
                        </div>
                    @else
                        @foreach ($suggestions as $s)
                            <div class="suggestion-card border-{{ $s['color'] }}" data-category="{{ $s['category'] }}">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="suggestion-icon bg-{{ $s['color'] }}">
                                        <i class="fas {{ $s['icon'] }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span
                                                class="suggestion-category cat-{{ $s['color'] }}">{{ $s['category'] }}</span>
                                        </div>
                                        <div class="suggestion-title">{{ $s['title'] }}</div>
                                        <p class="suggestion-message">{{ $s['message'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- Sidebar --}}
                <div class="col-lg-4">

                    {{-- Adherence Summary --}}
                    <div class="summary-card">
                        <div class="summary-card-header">
                            <h6><i class="fas fa-pills me-2"></i>Adherence (30 days)</h6>
                        </div>
                        <div class="summary-card-body text-center">
                            @if ($adherenceRate !== null)
                                @php
                                    $ringColor =
                                        $adherenceRate >= 80
                                            ? '#38a169'
                                            : ($adherenceRate >= 50
                                                ? '#dd6b20'
                                                : '#e53e3e');
                                @endphp
                                <div class="position-relative d-inline-block mb-2">
                                    <canvas id="adherenceDonut" width="120" height="120"></canvas>
                                    <div
                                        style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:1.4rem;font-weight:700;color:{{ $ringColor }};">
                                        {{ $adherenceRate }}%
                                    </div>
                                </div>
                                <p class="text-muted mb-0" style="font-size: 0.82rem;">Medicine adherence rate</p>
                            @else
                                <p class="text-muted mb-0" style="font-size: 0.85rem;">No medicine data yet</p>
                            @endif
                        </div>
                    </div>

                    {{-- Active Conditions --}}
                    <div class="summary-card">
                        <div class="summary-card-header">
                            <h6><i class="fas fa-virus me-2"></i>Active Conditions</h6>
                        </div>
                        <div class="summary-card-body">
                            @forelse($activeConditions as $cond)
                                <div class="summary-item">
                                    <div
                                        class="summary-dot {{ $cond->status === 'active' ? 'red' : ($cond->status === 'chronic' ? 'orange' : 'green') }}">
                                    </div>
                                    <div>
                                        <span class="fw-semibold">{{ $cond->disease->disease_name ?? 'Unknown' }}</span>
                                        <span class="text-muted" style="font-size: 0.75rem;"> &middot;
                                            {{ $cond->status_label }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted mb-0" style="font-size: 0.85rem;">No active conditions</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Recent Symptoms --}}
                    <div class="summary-card">
                        <div class="summary-card-header">
                            <h6><i class="fas fa-notes-medical me-2"></i>Recent Symptoms (14d)</h6>
                        </div>
                        <div class="summary-card-body">
                            @forelse($recentSymptoms->take(5) as $sym)
                                <div class="summary-item">
                                    <div
                                        class="summary-dot {{ $sym->severity_level >= 7 ? 'red' : ($sym->severity_level >= 4 ? 'orange' : 'green') }}">
                                    </div>
                                    <div class="flex-grow-1">
                                        <span>{{ $sym->symptom_name }}</span>
                                    </div>
                                    <span class="text-muted"
                                        style="font-size: 0.75rem;">{{ $sym->severity_level }}/10</span>
                                </div>
                            @empty
                                <p class="text-muted mb-0" style="font-size: 0.85rem;">No symptoms in last 14 days</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Quick Links --}}
                    <div class="summary-card">
                        <div class="summary-card-header">
                            <h6><i class="fas fa-link me-2"></i>Quick Actions</h6>
                        </div>
                        <div class="summary-card-body">
                            <a href="{{ route('health') }}#metrics" class="d-block text-decoration-none mb-2"
                                style="font-size: 0.85rem; color: #667eea;">
                                <i class="fas fa-chart-line me-2"></i>Record a Metric
                            </a>
                            <a href="{{ route('health') }}#symptomsPane" class="d-block text-decoration-none mb-2"
                                style="font-size: 0.85rem; color: #667eea;">
                                <i class="fas fa-notes-medical me-2"></i>Log a Symptom
                            </a>
                            <a href="{{ route('medicine.reminders') }}" class="d-block text-decoration-none mb-2"
                                style="font-size: 0.85rem; color: #667eea;">
                                <i class="fas fa-bell me-2"></i>View Reminders
                            </a>
                            <a href="{{ route('help') }}" class="d-block text-decoration-none"
                                style="font-size: 0.85rem; color: #667eea;">
                                <i class="fas fa-question-circle me-2"></i>Need Help?
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Adherence Donut
            const canvas = document.getElementById('adherenceDonut');
            if (canvas) {
                const rate = {{ $adherenceRate ?? 0 }};
                const ringColor = rate >= 80 ? '#38a169' : (rate >= 50 ? '#dd6b20' : '#e53e3e');
                new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [rate, 100 - rate],
                            backgroundColor: [ringColor, '#edf2f7'],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: false,
                        cutout: '78%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: false
                            }
                        },
                        animation: {
                            animateRotate: true,
                            duration: 1200
                        }
                    }
                });
            }
        });

        function filterSuggestions(category, btn) {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            document.querySelectorAll('.suggestion-card').forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
@endpush
