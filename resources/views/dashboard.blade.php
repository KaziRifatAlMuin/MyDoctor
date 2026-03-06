@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
    /* ══════════════════════════════════════════════
       Dashboard — Custom Styles
    ══════════════════════════════════════════════ */

    .dashboard-section {
        background: #f8f9fb;
        min-height: 100vh;
        padding: 2rem 0 3rem;
    }

    /* ── Welcome Hero ── */
    .welcome-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        border-radius: 24px;
        padding: 2.5rem 2.5rem 2rem;
        color: white;
        position: relative;
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .welcome-hero::before {
        content: '';
        position: absolute;
        top: -40%;
        right: -10%;
        width: 400px;
        height: 400px;
        border-radius: 50%;
        background: rgba(255,255,255,0.06);
        pointer-events: none;
    }

    .welcome-hero::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: 15%;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        background: rgba(255,255,255,0.04);
        pointer-events: none;
    }

    .welcome-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        border: 4px solid rgba(255,255,255,0.4);
        object-fit: cover;
        background: rgba(255,255,255,0.15);
    }

    .welcome-avatar i {
        font-size: 36px;
        line-height: 64px;
        width: 100%;
        text-align: center;
        color: rgba(255,255,255,0.85);
    }

    .welcome-greeting {
        font-size: 1.6rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .welcome-sub {
        font-size: 0.95rem;
        opacity: 0.85;
    }

    .welcome-date {
        font-size: 0.82rem;
        opacity: 0.7;
        margin-top: 0.25rem;
    }

    /* Quick Stats in Hero */
    .hero-stats {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
        flex-wrap: wrap;
    }

    .hero-stat-pill {
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 16px;
        padding: 0.75rem 1.25rem;
        min-width: 130px;
        transition: transform 0.2s, background 0.2s;
    }

    .hero-stat-pill:hover {
        background: rgba(255,255,255,0.22);
        transform: translateY(-2px);
    }

    .hero-stat-value {
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1;
    }

    .hero-stat-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        opacity: 0.8;
        margin-top: 4px;
    }

    /* ── Quick Actions ── */
    .quick-actions-row {
        margin-bottom: 2rem;
    }

    .quick-action-card {
        background: white;
        border-radius: 16px;
        padding: 1.25rem 1rem;
        text-align: center;
        box-shadow: 0 2px 16px rgba(0,0,0,0.04);
        transition: all 0.25s ease;
        text-decoration: none;
        display: block;
        height: 100%;
        border: 2px solid transparent;
    }

    .quick-action-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 30px rgba(102,126,234,0.15);
        border-color: rgba(102,126,234,0.2);
    }

    .quick-action-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        margin: 0 auto 0.75rem;
        transition: transform 0.2s;
    }

    .quick-action-card:hover .quick-action-icon {
        transform: scale(1.08);
    }

    .quick-action-label {
        font-size: 0.82rem;
        font-weight: 600;
        color: #2d3748;
        line-height: 1.3;
    }

    /* Icon colour variants */
    .qa-icon-purple  { background: rgba(102,126,234,0.12); color: #667eea; }
    .qa-icon-red     { background: rgba(245,101,101,0.12); color: #e53e3e; }
    .qa-icon-green   { background: rgba(72,187,120,0.12);  color: #38a169; }
    .qa-icon-orange  { background: rgba(237,137,54,0.12);  color: #dd6b20; }
    .qa-icon-blue    { background: rgba(66,153,225,0.12);  color: #3182ce; }
    .qa-icon-pink    { background: rgba(237,100,166,0.12); color: #d53f8c; }
    .qa-icon-teal    { background: rgba(56,178,172,0.12);  color: #319795; }
    .qa-icon-yellow  { background: rgba(236,201,75,0.15);  color: #b7791f; }

    /* ── Dashboard Cards ── */
    .dash-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 2px 20px rgba(0,0,0,0.05);
        overflow: hidden;
        height: 100%;
        transition: box-shadow 0.2s;
    }

    .dash-card:hover {
        box-shadow: 0 6px 28px rgba(0,0,0,0.09);
    }

    .dash-card-header {
        padding: 1.25rem 1.5rem 0.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #f0f0f0;
    }

    .dash-card-header h6 {
        font-weight: 700;
        color: #2d3748;
        margin: 0;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dash-card-header h6 i {
        color: #667eea;
    }

    .dash-card-body {
        padding: 1.25rem 1.5rem 1.5rem;
    }

    .dash-card-link {
        font-size: 0.78rem;
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s;
    }

    .dash-card-link:hover {
        color: #764ba2;
    }

    /* ── Adherence Ring ── */
    .adherence-ring-wrap {
        position: relative;
        width: 170px;
        height: 170px;
        margin: 0 auto;
    }

    .adherence-ring-center {
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%,-50%);
        text-align: center;
        pointer-events: none;
    }

    .adherence-ring-value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
    }

    .adherence-ring-label {
        font-size: 0.7rem;
        color: #a0aec0;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    /* ── Latest Metrics Grid ── */
    .metric-mini-card {
        background: #f8f9fb;
        border-radius: 14px;
        padding: 1rem 1.15rem;
        border-left: 4px solid #667eea;
        transition: background 0.2s, transform 0.2s;
    }

    .metric-mini-card:hover {
        background: #eef1f8;
        transform: translateY(-2px);
    }

    .metric-mini-name {
        font-weight: 700;
        color: #2d3748;
        font-size: 0.88rem;
        text-transform: capitalize;
    }

    .metric-mini-val {
        font-size: 1.1rem;
        font-weight: 700;
        color: #667eea;
    }

    .metric-mini-date {
        font-size: 0.72rem;
        color: #a0aec0;
    }

    /* Metric border colour by type */
    .metric-border-red    { border-left-color: #e53e3e; }
    .metric-border-orange { border-left-color: #dd6b20; }
    .metric-border-blue   { border-left-color: #3182ce; }
    .metric-border-green  { border-left-color: #38a169; }
    .metric-border-pink   { border-left-color: #d53f8c; }
    .metric-border-purple { border-left-color: #667eea; }

    /* ── Condition Status Badges ── */
    .condition-badge {
        display: inline-block;
        padding: 0.2rem 0.65rem;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 600;
    }

    .condition-active  { background: rgba(245,101,101,0.12); color: #e53e3e; }
    .condition-chronic { background: rgba(237,137,54,0.12);  color: #dd6b20; }
    .condition-managed { background: rgba(72,187,120,0.12);  color: #38a169; }

    /* ── Symptom Timeline ── */
    .symptom-timeline-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 0.7rem 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .symptom-timeline-item:last-child {
        border-bottom: none;
    }

    .symptom-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 5px;
    }

    .symptom-dot-low    { background: #38a169; }
    .symptom-dot-mid    { background: #dd6b20; }
    .symptom-dot-high   { background: #e53e3e; }

    /* ── Feature Navigation Grid ── */
    .feature-nav-card {
        background: white;
        border-radius: 18px;
        padding: 1.5rem 1.25rem;
        text-align: center;
        box-shadow: 0 2px 16px rgba(0,0,0,0.04);
        transition: all 0.3s ease;
        text-decoration: none;
        display: block;
        height: 100%;
        position: relative;
        overflow: hidden;
        border: 2px solid transparent;
    }

    .feature-nav-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .feature-nav-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 40px rgba(102,126,234,0.18);
        border-color: rgba(102,126,234,0.15);
    }

    .feature-nav-card:hover::before {
        opacity: 1;
    }

    .feature-nav-icon {
        width: 64px;
        height: 64px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin: 0 auto 1rem;
        transition: transform 0.3s;
    }

    .feature-nav-card:hover .feature-nav-icon {
        transform: scale(1.1) rotate(-3deg);
    }

    .feature-nav-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.35rem;
    }

    .feature-nav-desc {
        font-size: 0.78rem;
        color: #a0aec0;
        line-height: 1.4;
    }

    /* ── Activity Feed ── */
    .activity-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f5f5f5;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        flex-shrink: 0;
    }

    .activity-text {
        font-size: 0.85rem;
        color: #4a5568;
        flex: 1;
    }

    .activity-time {
        font-size: 0.72rem;
        color: #a0aec0;
        white-space: nowrap;
    }

    /* ── Pulse animation on adherence ── */
    @keyframes pulse-glow {
        0%, 100% { box-shadow: 0 0 0 0 rgba(102,126,234,0.3); }
        50% { box-shadow: 0 0 0 12px rgba(102,126,234,0); }
    }

    .pulse-ring {
        animation: pulse-glow 2.5s ease-in-out infinite;
        border-radius: 50%;
    }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .welcome-hero { padding: 1.75rem 1.25rem 1.5rem; border-radius: 18px; }
        .welcome-greeting { font-size: 1.3rem; }
        .hero-stats { gap: 0.5rem; }
        .hero-stat-pill { min-width: 100px; padding: 0.6rem 1rem; }
        .hero-stat-value { font-size: 1.2rem; }
        .feature-nav-card { padding: 1.25rem 1rem; }
        .feature-nav-icon { width: 52px; height: 52px; font-size: 1.2rem; }
    }
</style>
@endpush

@section('content')
<div class="dashboard-section">
    <div class="container" style="max-width: 1140px;">

        {{-- ══════════════════════════════════════════
             WELCOME HERO
        ══════════════════════════════════════════ --}}
        <div class="welcome-hero">
            <div class="d-flex align-items-center gap-3 position-relative" style="z-index:2;">
                @if($user->picture)
                    <img src="{{ asset('storage/' . $user->picture) }}" alt="Avatar" class="welcome-avatar">
                @else
                    <div class="welcome-avatar d-flex align-items-center justify-content-center">
                        <i class="fas fa-user"></i>
                    </div>
                @endif
                <div>
                    <div class="welcome-greeting">
                        @php
                            $hour = now()->hour;
                            $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
                        @endphp
                        {{ $greeting }}, {{ $user->name ?? 'there' }}! 👋
                    </div>
                    <div class="welcome-sub">Here's your health snapshot for today</div>
                    <div class="welcome-date">
                        <i class="far fa-calendar-alt me-1"></i>{{ now()->format('l, F j, Y') }}
                    </div>
                </div>
            </div>

            <div class="hero-stats position-relative" style="z-index:2;">
                <div class="hero-stat-pill">
                    <div class="hero-stat-value">{{ $healthMetrics->count() }}</div>
                    <div class="hero-stat-label"><i class="fas fa-chart-line me-1"></i>Metrics</div>
                </div>
                <div class="hero-stat-pill">
                    <div class="hero-stat-value">{{ $symptoms->count() }}</div>
                    <div class="hero-stat-label"><i class="fas fa-notes-medical me-1"></i>Symptoms</div>
                </div>
                <div class="hero-stat-pill">
                    <div class="hero-stat-value">{{ $medicines->count() }}</div>
                    <div class="hero-stat-label"><i class="fas fa-pills me-1"></i>Medicines</div>
                </div>
                <div class="hero-stat-pill">
                    <div class="hero-stat-value">{{ $adherenceRate }}%</div>
                    <div class="hero-stat-label"><i class="fas fa-check-double me-1"></i>Adherence</div>
                </div>
                <div class="hero-stat-pill">
                    <div class="hero-stat-value">{{ $activeConditions->count() }}</div>
                    <div class="hero-stat-label"><i class="fas fa-heartbeat me-1"></i>Conditions</div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             QUICK ACTIONS
        ══════════════════════════════════════════ --}}
        <div class="quick-actions-row">
            <h6 class="fw-bold mb-3" style="color:#2d3748;font-size:0.95rem;">
                <i class="fas fa-bolt me-1" style="color:#667eea;"></i> Quick Actions
            </h6>
            <div class="row g-2">
                <div class="col-6 col-md-3 col-lg">
                    <a href="{{ route('health') }}" class="quick-action-card">
                        <div class="quick-action-icon qa-icon-purple">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="quick-action-label">Record Metric</div>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-lg">
                    <a href="{{ route('health') }}" class="quick-action-card">
                        <div class="quick-action-icon qa-icon-orange">
                            <i class="fas fa-notes-medical"></i>
                        </div>
                        <div class="quick-action-label">Log Symptom</div>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-lg">
                    <a href="{{ route('medicine.reminders') }}" class="quick-action-card">
                        <div class="quick-action-icon qa-icon-green">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="quick-action-label">Reminders</div>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-lg">
                    <a href="{{ route('health') }}" class="quick-action-card">
                        <div class="quick-action-icon qa-icon-red">
                            <i class="fas fa-prescription-bottle-alt"></i>
                        </div>
                        <div class="quick-action-label">Upload Rx</div>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-lg">
                    <a href="{{ route('profile') }}" class="quick-action-card">
                        <div class="quick-action-icon qa-icon-teal">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <div class="quick-action-label">My Profile</div>
                    </a>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             ROW 1: Adherence + Latest Metrics
        ══════════════════════════════════════════ --}}
        <div class="row g-4 mb-4">

            {{-- Adherence Ring --}}
            <div class="col-lg-4">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6><i class="fas fa-check-double"></i> Medicine Adherence</h6>
                        <span class="badge bg-light text-muted" style="font-size:0.7rem;">30 days</span>
                    </div>
                    <div class="dash-card-body d-flex flex-column align-items-center justify-content-center">
                        @if($totalScheduled > 0)
                            <div class="adherence-ring-wrap pulse-ring">
                                <canvas id="dashAdherenceChart"></canvas>
                                <div class="adherence-ring-center">
                                    <div class="adherence-ring-value"
                                         style="color:{{ $adherenceRate >= 80 ? '#38a169' : ($adherenceRate >= 50 ? '#dd6b20' : '#e53e3e') }};">
                                        {{ $adherenceRate }}%
                                    </div>
                                    <div class="adherence-ring-label">adherence</div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-center gap-4 mt-3">
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
                                    <div class="small text-muted">Scheduled</div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4" style="color:#a0aec0;">
                                <i class="fas fa-clipboard-check fa-3x mb-3" style="color:#cbd5e0;"></i>
                                <p class="mb-1" style="font-size:0.92rem;">No medicine logs yet</p>
                                <a href="{{ route('medicine.reminders') }}" class="dash-card-link">Set up reminders &rarr;</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Latest Health Metrics --}}
            <div class="col-lg-8">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6><i class="fas fa-heartbeat"></i> Latest Health Metrics</h6>
                        <a href="{{ route('health') }}" class="dash-card-link">View All &rarr;</a>
                    </div>
                    <div class="dash-card-body">
                        @if($latestMetrics->isEmpty())
                            <div class="text-center py-4" style="color:#a0aec0;">
                                <i class="fas fa-chart-line fa-3x mb-3" style="color:#cbd5e0;"></i>
                                <p class="mb-1" style="font-size:0.92rem;">No metrics recorded yet</p>
                                <a href="{{ route('health') }}" class="dash-card-link">Record your first metric &rarr;</a>
                            </div>
                        @else
                            @php
                                $metricColors = [
                                    'blood_pressure' => 'red', 'blood_sugar' => 'orange', 'heart_rate' => 'red',
                                    'weight' => 'blue', 'bmi' => 'pink', 'temperature' => 'orange',
                                    'oxygen' => 'blue', 'cholesterol' => 'green', 'hemoglobin' => 'purple',
                                    'creatinine' => 'green', 'respiratory_rate' => 'blue',
                                ];
                                $metricIcons = [
                                    'blood_pressure' => 'fa-heartbeat', 'blood_sugar' => 'fa-tint',
                                    'heart_rate' => 'fa-heart', 'weight' => 'fa-weight',
                                    'bmi' => 'fa-balance-scale', 'temperature' => 'fa-thermometer-half',
                                    'oxygen' => 'fa-lungs', 'cholesterol' => 'fa-flask',
                                    'hemoglobin' => 'fa-vial', 'creatinine' => 'fa-vials',
                                    'respiratory_rate' => 'fa-wind',
                                ];
                            @endphp
                            <div class="row g-3">
                                @foreach($latestMetrics->take(6) as $type => $metric)
                                    @php
                                        $color  = $metricColors[$type] ?? 'purple';
                                        $icon   = $metricIcons[$type] ?? 'fa-stethoscope';
                                        $enName = $metricConfig[$type]['en'] ?? ucwords(str_replace('_', ' ', $type));
                                        $unit   = $metricConfig[$type]['unit'] ?? '';
                                    @endphp
                                    <div class="col-sm-6 col-xl-4">
                                        <div class="metric-mini-card metric-border-{{ $color }}">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <i class="fas {{ $icon }}" style="color:{{ $color === 'red' ? '#e53e3e' : ($color === 'orange' ? '#dd6b20' : ($color === 'blue' ? '#3182ce' : ($color === 'green' ? '#38a169' : ($color === 'pink' ? '#d53f8c' : '#667eea')))) }};font-size:0.9rem;"></i>
                                                <span class="metric-mini-name">{{ $enName }}</span>
                                            </div>
                                            <div class="d-flex align-items-end justify-content-between">
                                                <div>
                                                    @if(is_array($metric->value))
                                                        @foreach($metric->value as $k => $v)
                                                            @if($k !== 'unit')
                                                                <span class="metric-mini-val">{{ $v }}</span>
                                                                <span class="text-muted" style="font-size:0.72rem;">{{ ucfirst(str_replace('_',' ',$k)) }}</span>
                                                                @if(!$loop->last) <span class="text-muted mx-1">/</span> @endif
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <span class="metric-mini-val">{{ $metric->value }}</span>
                                                        @if($unit)
                                                            <span class="text-muted" style="font-size:0.75rem;"> {{ $unit }}</span>
                                                        @endif
                                                    @endif
                                                </div>
                                                <div class="metric-mini-date">{{ $metric->recorded_at->format('M d') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             ROW 2: Active Conditions + Recent Symptoms + Activity
        ══════════════════════════════════════════ --}}
        <div class="row g-4 mb-4">

            {{-- Active Conditions --}}
            <div class="col-lg-4">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6><i class="fas fa-virus"></i> Active Conditions</h6>
                        <a href="{{ route('health') }}" class="dash-card-link">Manage &rarr;</a>
                    </div>
                    <div class="dash-card-body">
                        @if($activeConditions->isEmpty())
                            <div class="text-center py-3" style="color:#a0aec0;">
                                <i class="fas fa-shield-alt fa-2x mb-2" style="color:#cbd5e0;"></i>
                                <p class="mb-0" style="font-size:0.88rem;">No active conditions</p>
                            </div>
                        @else
                            @foreach($activeConditions->take(5) as $ud)
                                <div class="d-flex align-items-center justify-content-between py-2 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color:#f0f0f0 !important;">
                                    <div>
                                        <div class="fw-semibold" style="font-size:0.88rem;color:#2d3748;">
                                            {{ $ud->disease->disease_name ?? 'Unknown' }}
                                        </div>
                                        @if($ud->diagnosed_at)
                                            <div class="text-muted" style="font-size:0.72rem;">
                                                Since {{ \Carbon\Carbon::parse($ud->diagnosed_at)->format('M Y') }}
                                            </div>
                                        @endif
                                    </div>
                                    <span class="condition-badge condition-{{ $ud->status }}">
                                        {{ ucfirst($ud->status) }}
                                    </span>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            {{-- Recent Symptoms --}}
            <div class="col-lg-4">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6><i class="fas fa-notes-medical"></i> Recent Symptoms</h6>
                        <a href="{{ route('health') }}" class="dash-card-link">View All &rarr;</a>
                    </div>
                    <div class="dash-card-body p-0">
                        @if($symptoms->isEmpty())
                            <div class="text-center py-4 px-3" style="color:#a0aec0;">
                                <i class="fas fa-check-circle fa-2x mb-2" style="color:#cbd5e0;"></i>
                                <p class="mb-0" style="font-size:0.88rem;">No symptoms logged</p>
                            </div>
                        @else
                            <div class="px-3 pt-2 pb-1">
                                @foreach($symptoms->take(5) as $sym)
                                    <div class="symptom-timeline-item">
                                        @php
                                            $sev = $sym->severity_level ?? 1;
                                            $dotClass = $sev <= 3 ? 'symptom-dot-low' : ($sev <= 6 ? 'symptom-dot-mid' : 'symptom-dot-high');
                                        @endphp
                                        <div class="symptom-dot {{ $dotClass }}"></div>
                                        <div class="flex-grow-1" style="min-width:0;">
                                            <div class="fw-semibold text-truncate" style="font-size:0.85rem;color:#2d3748;">
                                                {{ $sym->symptom_name }}
                                            </div>
                                            <div class="text-muted" style="font-size:0.72rem;">
                                                Severity {{ $sev }}/10 &middot; {{ $sym->recorded_at->format('M d') }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Weekly Activity Summary --}}
            <div class="col-lg-4">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6><i class="fas fa-calendar-week"></i> This Week</h6>
                        <span class="badge bg-light text-muted" style="font-size:0.7rem;">7 days</span>
                    </div>
                    <div class="dash-card-body">
                        <div class="activity-item">
                            <div class="activity-icon qa-icon-purple">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="activity-text">
                                <strong>{{ $recentMetricsCount }}</strong> health metrics recorded
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon qa-icon-orange">
                                <i class="fas fa-notes-medical"></i>
                            </div>
                            <div class="activity-text">
                                <strong>{{ $recentSymptomsCount }}</strong> symptoms logged
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon qa-icon-green">
                                <i class="fas fa-pills"></i>
                            </div>
                            <div class="activity-text">
                                <strong>{{ $medicines->count() }}</strong> active medicines
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon qa-icon-red">
                                <i class="fas fa-file-medical"></i>
                            </div>
                            <div class="activity-text">
                                <strong>{{ $prescriptionCount }}</strong> prescriptions &middot; <strong>{{ $reportCount }}</strong> reports
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon qa-icon-pink">
                                <i class="fas fa-heartbeat"></i>
                            </div>
                            <div class="activity-text">
                                <strong>{{ $activeConditions->count() }}</strong> active conditions
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             FEATURE NAVIGATION
        ══════════════════════════════════════════ --}}
        <h6 class="fw-bold mb-3" style="color:#2d3748;font-size:0.95rem;">
            <i class="fas fa-th me-1" style="color:#667eea;"></i> Explore Features
        </h6>
        <div class="row g-3 mb-4">

            {{-- Health Dashboard --}}
            <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ route('health') }}" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-purple">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <div class="feature-nav-title">Health Dashboard</div>
                    <div class="feature-nav-desc">Track metrics, symptoms & diseases</div>
                </a>
            </div>

            {{-- Medicine Reminders --}}
            <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ route('medicine.reminders') }}" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-green">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="feature-nav-title">Medicine Reminders</div>
                    <div class="feature-nav-desc">Never miss a dose again</div>
                </a>
            </div>

            {{-- My Prescriptions --}}
            <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ route('medicine.prescriptions') }}" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-red">
                        <i class="fas fa-prescription-bottle-alt"></i>
                    </div>
                    <div class="feature-nav-title">Prescriptions</div>
                    <div class="feature-nav-desc">Store & manage prescriptions</div>
                </a>
            </div>

            {{-- Health Records --}}
            <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ route('health.records') }}" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-blue">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div class="feature-nav-title">Medical Records</div>
                    <div class="feature-nav-desc">All your health files in one place</div>
                </a>
            </div>

            {{-- Symptom Tracker --}}
            <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ route('health.symptoms') }}" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-orange">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <div class="feature-nav-title">Symptom Tracker</div>
                    <div class="feature-nav-desc">Log & monitor your symptoms</div>
                </a>
            </div>

            {{-- Smart Suggestions --}}
            <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ route('health.suggestions') }}" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-teal">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <div class="feature-nav-title">Smart Suggestions</div>
                    <div class="feature-nav-desc">Personalized health insights</div>
                </a>
            </div>

            {{-- Community --}}
            <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ route('community') }}" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-pink">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="feature-nav-title">Community</div>
                    <div class="feature-nav-desc">Connect with others</div>
                </a>
            </div>

            {{-- Find Hospitals --}}
            <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ route('health.hospitals') }}" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-yellow">
                        <i class="fas fa-hospital"></i>
                    </div>
                    <div class="feature-nav-title">Find Hospitals</div>
                    <div class="feature-nav-desc">Nearby hospitals & clinics</div>
                </a>
            </div>

            {{-- Health Tips --}}
            <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ route('health.tips') }}" class="feature-nav-card">
                    <div class="feature-nav-icon" style="background:rgba(56,178,172,0.12);color:#319795;">
                        <i class="fas fa-book-medical"></i>
                    </div>
                    <div class="feature-nav-title">Health Tips</div>
                    <div class="feature-nav-desc">Daily wellness articles</div>
                </a>
            </div>

            {{-- Medicine Search --}}
            <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ route('medicine.search') }}" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-purple">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="feature-nav-title">Medicine Search</div>
                    <div class="feature-nav-desc">Find medicines & details</div>
                </a>
            </div>

            {{-- Health Tracking --}}
            <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ route('health.tracking') }}" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-blue">
                        <i class="fas fa-chart-area"></i>
                    </div>
                    <div class="feature-nav-title">Health Tracking</div>
                    <div class="feature-nav-desc">Visualize your progress</div>
                </a>
            </div>

            {{-- Profile --}}
            <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ route('profile') }}" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-teal">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <div class="feature-nav-title">My Profile</div>
                    <div class="feature-nav-desc">Manage your account</div>
                </a>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Adherence Donut Chart
    const canvas = document.getElementById('dashAdherenceChart');
    if (canvas) {
        const taken = {{ $totalTaken }};
        const missed = {{ $totalMissed }};
        const rate = {{ $adherenceRate }};

        const ringColor = rate >= 80 ? '#38a169' : (rate >= 50 ? '#dd6b20' : '#e53e3e');

        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: ['Taken', 'Missed'],
                datasets: [{
                    data: [taken, missed],
                    backgroundColor: [ringColor, '#edf2f7'],
                    borderWidth: 0,
                    cutout: '78%',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ctx.label + ': ' + ctx.raw + ' doses';
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    duration: 1200,
                }
            }
        });
    }
});
</script>
@endpush
