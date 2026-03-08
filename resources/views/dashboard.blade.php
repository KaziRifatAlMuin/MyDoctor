@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
    /* ══════════════════════════════════════════════════════════════
       Dashboard — Spectacular Redesign
    ══════════════════════════════════════════════════════════════ */

    .dashboard-section {
        background: linear-gradient(180deg, #f0f2f8 0%, #e8ecf4 40%, #f5f7fb 100%);
        min-height: 100vh;
        padding: 2rem 0 4rem;
    }

    /* ── Animated Welcome Hero ── */
    .welcome-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 40%, #f093fb 80%, #667eea 100%);
        background-size: 300% 300%;
        animation: heroGradient 8s ease infinite;
        border-radius: 28px;
        padding: 2.5rem 2.5rem 2rem;
        color: white;
        position: relative;
        overflow: hidden;
        margin-bottom: 2rem;
        box-shadow: 0 20px 60px rgba(102,126,234,0.3);
    }

    @keyframes heroGradient {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }

    .welcome-hero::before {
        content: '';
        position: absolute;
        top: -60%;
        right: -15%;
        width: 500px;
        height: 500px;
        border-radius: 50%;
        background: rgba(255,255,255,0.06);
        pointer-events: none;
        animation: float 6s ease-in-out infinite;
    }

    .welcome-hero::after {
        content: '';
        position: absolute;
        bottom: -40%;
        left: 10%;
        width: 350px;
        height: 350px;
        border-radius: 50%;
        background: rgba(255,255,255,0.04);
        pointer-events: none;
        animation: float 8s ease-in-out infinite reverse;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0) scale(1); }
        50% { transform: translateY(-20px) scale(1.05); }
    }

    .welcome-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: 4px solid rgba(255,255,255,0.5);
        object-fit: cover;
        background: rgba(255,255,255,0.15);
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    }

    .welcome-avatar i {
        font-size: 36px;
        line-height: 72px;
        width: 100%;
        text-align: center;
        color: rgba(255,255,255,0.85);
    }

    .welcome-greeting {
        font-size: 1.7rem;
        font-weight: 800;
        margin-bottom: 0.25rem;
        text-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    .welcome-sub {
        font-size: 0.95rem;
        opacity: 0.9;
    }

    .welcome-date {
        font-size: 0.82rem;
        opacity: 0.75;
        margin-top: 0.25rem;
    }

    /* ── Health Score Circle (in Hero) ── */
    .health-score-wrap {
        position: relative;
        width: 110px;
        height: 110px;
        flex-shrink: 0;
    }

    .health-score-circle {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background: conic-gradient(
            var(--score-color) calc(var(--score) * 3.6deg),
            rgba(255,255,255,0.15) 0deg
        );
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 30px rgba(255,255,255,0.15);
        animation: scoreReveal 1.5s ease-out;
    }

    @keyframes scoreReveal {
        from { opacity: 0; transform: scale(0.7) rotate(-90deg); }
        to { opacity: 1; transform: scale(1) rotate(0deg); }
    }

    .health-score-inner {
        width: 86px;
        height: 86px;
        border-radius: 50%;
        background: rgba(255,255,255,0.12);
        backdrop-filter: blur(10px);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .health-score-value {
        font-size: 2rem;
        font-weight: 900;
        line-height: 1;
    }

    .health-score-label {
        font-size: 0.6rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        opacity: 0.85;
        margin-top: 2px;
    }

    /* ── Hero Stats ── */
    .hero-stats {
        display: flex;
        gap: 0.75rem;
        margin-top: 1.5rem;
        flex-wrap: wrap;
    }

    .hero-stat-pill {
        background: rgba(255,255,255,0.12);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.18);
        border-radius: 18px;
        padding: 0.7rem 1.1rem;
        min-width: 115px;
        transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
        cursor: default;
    }

    .hero-stat-pill:hover {
        background: rgba(255,255,255,0.25);
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    }

    .hero-stat-value {
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1;
    }

    .hero-stat-label {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        opacity: 0.8;
        margin-top: 4px;
    }

    /* ── Section Headers ── */
    .section-title {
        font-weight: 800;
        color: #1a202c;
        font-size: 1.05rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-title i {
        color: #667eea;
    }

    .section-title .title-badge {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        font-size: 0.6rem;
        padding: 3px 10px;
        border-radius: 20px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* ── Quick Actions ── */
    .quick-actions-row {
        margin-bottom: 2rem;
    }

    .quick-action-card {
        background: white;
        border-radius: 18px;
        padding: 1.25rem 1rem;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
        text-decoration: none;
        display: block;
        height: 100%;
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
    }

    .quick-action-card::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, transparent 60%, rgba(102,126,234,0.03));
        pointer-events: none;
    }

    .quick-action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(102,126,234,0.18);
        border-color: rgba(102,126,234,0.2);
    }

    .quick-action-icon {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        margin: 0 auto 0.75rem;
        transition: all 0.3s;
    }

    .quick-action-card:hover .quick-action-icon {
        transform: scale(1.1) rotate(-5deg);
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
    .qa-icon-indigo  { background: rgba(99,102,241,0.12);  color: #6366f1; }
    .qa-icon-cyan    { background: rgba(6,182,212,0.12);   color: #06b6d4; }

    /* ── Dashboard Cards ── */
    .dash-card {
        background: white;
        border-radius: 22px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.05);
        overflow: hidden;
        height: 100%;
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.04);
    }

    .dash-card:hover {
        box-shadow: 0 12px 40px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .dash-card-header {
        padding: 1.25rem 1.5rem 0.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #f0f0f0;
    }

    .dash-card-header h6 {
        font-weight: 800;
        color: #1a202c;
        margin: 0;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dash-card-header h6 i {
        color: #667eea;
        font-size: 0.9rem;
    }

    .dash-card-body {
        padding: 1.25rem 1.5rem 1.5rem;
    }

    .dash-card-link {
        font-size: 0.78rem;
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .dash-card-link:hover {
        color: #764ba2;
        gap: 8px;
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
        font-weight: 900;
        line-height: 1;
    }

    .adherence-ring-label {
        font-size: 0.68rem;
        color: #a0aec0;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    /* ── Latest Metrics Grid ── */
    .metric-mini-card {
        background: linear-gradient(135deg, #f8f9fb 0%, #fff 100%);
        border-radius: 16px;
        padding: 1rem 1.15rem;
        border-left: 4px solid #667eea;
        transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
        position: relative;
        overflow: hidden;
    }

    .metric-mini-card::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        width: 60px;
        background: linear-gradient(90deg, transparent, rgba(102,126,234,0.03));
        pointer-events: none;
    }

    .metric-mini-card:hover {
        background: linear-gradient(135deg, #eef1f8 0%, #fff 100%);
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.06);
    }

    .metric-mini-name {
        font-weight: 700;
        color: #2d3748;
        font-size: 0.85rem;
        text-transform: capitalize;
    }

    .metric-mini-val {
        font-size: 1.15rem;
        font-weight: 800;
        color: #667eea;
    }

    .metric-mini-date {
        font-size: 0.7rem;
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
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
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
        transition: background 0.2s;
    }

    .symptom-timeline-item:last-child {
        border-bottom: none;
    }

    .symptom-timeline-item:hover {
        background: #fafbfd;
        margin: 0 -0.5rem;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
        border-radius: 8px;
    }

    .symptom-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 5px;
    }

    .symptom-dot-low    { background: #38a169; box-shadow: 0 0 6px rgba(56,161,105,0.4); }
    .symptom-dot-mid    { background: #dd6b20; box-shadow: 0 0 6px rgba(221,107,32,0.4); }
    .symptom-dot-high   { background: #e53e3e; box-shadow: 0 0 6px rgba(229,62,62,0.4); }

    /* ── Adherence Sparkline ── */
    .adherence-bar-wrap {
        display: flex;
        gap: 2px;
        align-items: flex-end;
        height: 60px;
        padding: 0 4px;
    }

    .adherence-bar {
        flex: 1;
        min-width: 4px;
        border-radius: 3px 3px 0 0;
        transition: all 0.3s ease;
        cursor: default;
        position: relative;
    }

    .adherence-bar:hover {
        opacity: 0.8;
        transform: scaleY(1.05);
    }

    /* ── Reminder Cards ── */
    .reminder-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 0.75rem;
        border-radius: 14px;
        background: #f8f9fb;
        margin-bottom: 0.5rem;
        transition: all 0.2s;
    }

    .reminder-item:hover {
        background: #eef1f8;
        transform: translateX(4px);
    }

    .reminder-time {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 6px 12px;
        border-radius: 10px;
        font-size: 0.78rem;
        font-weight: 700;
        white-space: nowrap;
        min-width: 65px;
        text-align: center;
    }

    .reminder-name {
        font-weight: 600;
        font-size: 0.88rem;
        color: #2d3748;
    }

    .reminder-dose {
        font-size: 0.72rem;
        color: #a0aec0;
    }

    /* ── Feature Navigation Grid ── */
    .feature-nav-card {
        background: white;
        border-radius: 20px;
        padding: 1.5rem 1.25rem;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        transition: all 0.35s cubic-bezier(0.4,0,0.2,1);
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
        background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
        opacity: 0;
        transition: opacity 0.35s;
    }

    .feature-nav-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 16px 48px rgba(102,126,234,0.2);
        border-color: rgba(102,126,234,0.12);
    }

    .feature-nav-card:hover::before {
        opacity: 1;
    }

    .feature-nav-icon {
        width: 64px;
        height: 64px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin: 0 auto 1rem;
        transition: all 0.35s;
    }

    .feature-nav-card:hover .feature-nav-icon {
        transform: scale(1.12) rotate(-5deg);
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    }

    .feature-nav-title {
        font-size: 0.92rem;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 0.35rem;
    }

    .feature-nav-desc {
        font-size: 0.76rem;
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
        transition: all 0.2s;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-item:hover {
        padding-left: 8px;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
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

    /* ── Upload item ── */
    .upload-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 0.6rem 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .upload-item:last-child { border-bottom: none; }

    .upload-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        flex-shrink: 0;
    }

    /* ── Pulse animation ── */
    @keyframes pulseGlow {
        0%, 100% { box-shadow: 0 0 0 0 rgba(102,126,234,0.25); }
        50% { box-shadow: 0 0 0 12px rgba(102,126,234,0); }
    }

    .pulse-ring {
        animation: pulseGlow 2.5s ease-in-out infinite;
        border-radius: 50%;
    }

    /* ── Animated counters ── */
    [data-count] {
        transition: all 0.3s;
    }

    /* ── Metric trend sparkline ── */
    .sparkline-wrap {
        height: 35px;
        display: flex;
        align-items: flex-end;
        gap: 3px;
        margin-top: 6px;
    }

    .sparkline-bar {
        flex: 1;
        border-radius: 2px;
        min-height: 4px;
        transition: height 0.4s ease;
    }

    /* ── Feature section divider ── */
    .section-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(102,126,234,0.2), transparent);
        margin: 0.5rem 0 2rem;
    }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .welcome-hero { padding: 1.75rem 1.25rem 1.5rem; border-radius: 20px; }
        .welcome-greeting { font-size: 1.3rem; }
        .hero-stats { gap: 0.5rem; }
        .hero-stat-pill { min-width: 100px; padding: 0.6rem 0.9rem; }
        .hero-stat-value { font-size: 1.2rem; }
        .feature-nav-card { padding: 1.25rem 1rem; }
        .feature-nav-icon { width: 52px; height: 52px; font-size: 1.2rem; }
        .health-score-wrap { width: 90px; height: 90px; }
        .health-score-circle { width: 90px; height: 90px; }
        .health-score-inner { width: 70px; height: 70px; }
        .health-score-value { font-size: 1.5rem; }
        .hero-flex-wrap { flex-direction: column; }
    }

    /* ── Fade-in-up for cards ── */
    .fade-in-up {
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.6s ease forwards;
    }

    @keyframes fadeInUp {
        to { opacity: 1; transform: translateY(0); }
    }

    .delay-1 { animation-delay: 0.1s; }
    .delay-2 { animation-delay: 0.2s; }
    .delay-3 { animation-delay: 0.3s; }
    .delay-4 { animation-delay: 0.4s; }
    .delay-5 { animation-delay: 0.5s; }
    .delay-6 { animation-delay: 0.6s; }
</style>
@endpush

@section('content')
<div class="dashboard-section">
    <div class="container" style="max-width: 1180px;">

        {{-- ══════════════════════════════════════════════════════
             WELCOME HERO + HEALTH SCORE
        ══════════════════════════════════════════════════════ --}}
        <div class="welcome-hero fade-in-up">
            <div class="d-flex align-items-center justify-content-between position-relative hero-flex-wrap" style="z-index:2;flex-wrap:wrap;gap:1.5rem;">
                {{-- Left: Greeting --}}
                <div class="d-flex align-items-center gap-3">
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

                {{-- Right: Health Score --}}
                @php
                    $scoreColor = $healthScore >= 75 ? '#38a169' : ($healthScore >= 50 ? '#dd6b20' : '#e53e3e');
                @endphp
                <div class="health-score-wrap" title="Your overall health score">
                    <div class="health-score-circle" style="--score: {{ $healthScore }}; --score-color: {{ $scoreColor }};">
                        <div class="health-score-inner">
                            <div class="health-score-value">{{ $healthScore }}</div>
                            <div class="health-score-label">Health Score</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hero-stats position-relative" style="z-index:2;">
                <div class="hero-stat-pill">
                    <div class="hero-stat-value" data-count="{{ $healthMetrics->count() }}">{{ $healthMetrics->count() }}</div>
                    <div class="hero-stat-label"><i class="fas fa-chart-line me-1"></i>Metrics</div>
                </div>
                <div class="hero-stat-pill">
                    <div class="hero-stat-value" data-count="{{ $symptoms->count() }}">{{ $symptoms->count() }}</div>
                    <div class="hero-stat-label"><i class="fas fa-notes-medical me-1"></i>Symptoms</div>
                </div>
                <div class="hero-stat-pill">
                    <div class="hero-stat-value" data-count="{{ $medicines->count() }}">{{ $medicines->count() }}</div>
                    <div class="hero-stat-label"><i class="fas fa-pills me-1"></i>Medicines</div>
                </div>
                <div class="hero-stat-pill">
                    <div class="hero-stat-value">{{ $adherenceRate }}%</div>
                    <div class="hero-stat-label"><i class="fas fa-check-double me-1"></i>Adherence</div>
                </div>
                <div class="hero-stat-pill">
                    <div class="hero-stat-value" data-count="{{ $activeConditions->count() }}">{{ $activeConditions->count() }}</div>
                    <div class="hero-stat-label"><i class="fas fa-heartbeat me-1"></i>Conditions</div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════
             QUICK ACTIONS
        ══════════════════════════════════════════════════════ --}}
        <div class="quick-actions-row fade-in-up delay-1">
            <div class="section-title">
                <i class="fas fa-bolt"></i> Quick Actions
            </div>
            <div class="row g-2">
                <div class="col-6 col-md-3 col-xl">
                    <a href="{{ route('health') }}" class="quick-action-card">
                        <div class="quick-action-icon qa-icon-purple">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="quick-action-label">Record Metric</div>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <a href="{{ route('health') }}" class="quick-action-card">
                        <div class="quick-action-icon qa-icon-orange">
                            <i class="fas fa-notes-medical"></i>
                        </div>
                        <div class="quick-action-label">Log Symptom</div>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <a href="{{ route('medicine.reminders') }}" class="quick-action-card">
                        <div class="quick-action-icon qa-icon-green">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="quick-action-label">Reminders</div>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <a href="{{ route('health') }}" class="quick-action-card">
                        <div class="quick-action-icon qa-icon-red">
                            <i class="fas fa-prescription-bottle-alt"></i>
                        </div>
                        <div class="quick-action-label">Upload Rx</div>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <a href="{{ route('medicine.index') }}" class="quick-action-card">
                        <div class="quick-action-icon qa-icon-cyan">
                            <i class="fas fa-pills"></i>
                        </div>
                        <div class="quick-action-label">Medicines</div>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <a href="{{ route('profile') }}" class="quick-action-card">
                        <div class="quick-action-icon qa-icon-teal">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <div class="quick-action-label">My Profile</div>
                    </a>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════
             ROW 1 — Adherence Donut + 30-Day Sparkline + Today's Reminders
        ══════════════════════════════════════════════════════ --}}
        <div class="row g-4 mb-4">

            {{-- Medicine Adherence (Donut) --}}
            <div class="col-lg-4 fade-in-up delay-2">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6><i class="fas fa-check-double"></i> Medicine Adherence</h6>
                        <span class="badge bg-light text-muted" style="font-size:0.68rem;">30 days</span>
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

                            {{-- 30-Day Adherence Sparkline --}}
                            <div class="mt-3 w-100">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted" style="font-size:0.68rem;">30-Day Trend</small>
                                    <small class="text-muted" style="font-size:0.68rem;">Today</small>
                                </div>
                                <div class="adherence-bar-wrap">
                                    @foreach($adherenceByDay as $day)
                                        @php
                                            $rate = $day['rate'] ?? 0;
                                            $h = max(4, ($rate / 100) * 55);
                                            $color = $rate === null ? '#e2e8f0' : ($rate >= 80 ? '#38a169' : ($rate >= 50 ? '#dd6b20' : '#e53e3e'));
                                        @endphp
                                        <div class="adherence-bar"
                                             style="height:{{ $h }}px; background:{{ $color }};"
                                             title="{{ $day['date'] }}: {{ $day['rate'] !== null ? $day['rate'].'%' : 'No data' }}"></div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4" style="color:#a0aec0;">
                                <i class="fas fa-clipboard-check fa-3x mb-3" style="color:#cbd5e0;"></i>
                                <p class="mb-1" style="font-size:0.92rem;">No medicine logs yet</p>
                                <a href="{{ route('medicine.reminders') }}" class="dash-card-link">Set up reminders <i class="fas fa-arrow-right"></i></a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Today's Reminders --}}
            <div class="col-lg-4 fade-in-up delay-3">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6><i class="fas fa-clock"></i> Today's Reminders</h6>
                        <a href="{{ route('medicine.reminders') }}" class="dash-card-link">All <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="dash-card-body">
                        @if($todayReminders->isEmpty())
                            <div class="text-center py-3" style="color:#a0aec0;">
                                <i class="fas fa-check-circle fa-2x mb-2" style="color:#38a169;"></i>
                                <p class="mb-1 fw-semibold" style="font-size:0.92rem;color:#38a169;">All clear!</p>
                                <p class="mb-0" style="font-size:0.78rem;">No pending reminders today</p>
                            </div>
                        @else
                            @foreach($todayReminders->take(5) as $rem)
                                <div class="reminder-item">
                                    <div class="reminder-time">
                                        {{ $rem->reminder_at->format('h:i A') }}
                                    </div>
                                    <div>
                                        <div class="reminder-name">{{ $rem->schedule->medicine->medicine_name ?? 'Medicine' }}</div>
                                        <div class="reminder-dose">
                                            {{ $rem->schedule->medicine->value_per_dose ?? '' }}
                                            {{ $rem->schedule->medicine->unit ?? '' }}
                                            &middot; {{ ucfirst(str_replace('_', ' ', $rem->schedule->medicine->rule ?? '')) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            @if($todayReminders->count() > 5)
                                <div class="text-center mt-2">
                                    <a href="{{ route('medicine.reminders') }}" class="dash-card-link justify-content-center">
                                        +{{ $todayReminders->count() - 5 }} more <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            {{-- Weekly Activity Summary --}}
            <div class="col-lg-4 fade-in-up delay-4">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6><i class="fas fa-calendar-week"></i> This Week</h6>
                        <span class="badge bg-light text-muted" style="font-size:0.68rem;">7 days</span>
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

        {{-- ══════════════════════════════════════════════════════
             ROW 2 — Latest Health Metrics (Full Width)
        ══════════════════════════════════════════════════════ --}}
        <div class="row g-4 mb-4">
            <div class="col-12 fade-in-up delay-3">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6><i class="fas fa-heartbeat"></i> Latest Health Metrics</h6>
                        <a href="{{ route('health') }}" class="dash-card-link">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="dash-card-body">
                        @if($latestMetrics->isEmpty())
                            <div class="text-center py-4" style="color:#a0aec0;">
                                <i class="fas fa-chart-line fa-3x mb-3" style="color:#cbd5e0;"></i>
                                <p class="mb-1" style="font-size:0.92rem;">No metrics recorded yet</p>
                                <a href="{{ route('health') }}" class="dash-card-link justify-content-center">Record your first metric <i class="fas fa-arrow-right"></i></a>
                            </div>
                        @else
                            @php
                                $metricColors = [
                                    'blood_pressure' => 'red', 'blood_glucose' => 'orange', 'heart_rate' => 'red',
                                    'body_weight' => 'blue', 'bmi' => 'pink', 'temperature' => 'orange',
                                    'oxygen_saturation' => 'blue', 'cholesterol' => 'green', 'hemoglobin' => 'purple',
                                    'creatinine' => 'green', 'respiratory_rate' => 'blue',
                                ];
                                $metricIcons = [
                                    'blood_pressure' => 'fa-heartbeat', 'blood_glucose' => 'fa-tint',
                                    'heart_rate' => 'fa-heart', 'body_weight' => 'fa-weight',
                                    'bmi' => 'fa-balance-scale', 'temperature' => 'fa-thermometer-half',
                                    'oxygen_saturation' => 'fa-lungs', 'cholesterol' => 'fa-flask',
                                    'hemoglobin' => 'fa-vial', 'creatinine' => 'fa-vials',
                                    'respiratory_rate' => 'fa-wind',
                                ];
                            @endphp
                            <div class="row g-3">
                                @foreach($latestMetrics->take(8) as $type => $metric)
                                    @php
                                        $color  = $metricColors[$type] ?? 'purple';
                                        $icon   = $metricIcons[$type] ?? 'fa-stethoscope';
                                        $enName = $metricConfig[$type]['en'] ?? ucwords(str_replace('_', ' ', $type));
                                        $unit   = $metricConfig[$type]['unit'] ?? '';
                                        $colorHex = match($color) {
                                            'red' => '#e53e3e', 'orange' => '#dd6b20', 'blue' => '#3182ce',
                                            'green' => '#38a169', 'pink' => '#d53f8c', default => '#667eea'
                                        };
                                    @endphp
                                    <div class="col-sm-6 col-lg-4 col-xl-3">
                                        <div class="metric-mini-card metric-border-{{ $color }}">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <i class="fas {{ $icon }}" style="color:{{ $colorHex }};font-size:0.9rem;"></i>
                                                <span class="metric-mini-name">{{ $enName }}</span>
                                            </div>
                                            <div class="d-flex align-items-end justify-content-between">
                                                <div>
                                                    @if(is_array($metric->value))
                                                        @foreach($metric->value as $k => $v)
                                                            @if($k !== 'unit')
                                                                <span class="metric-mini-val">{{ $v }}</span>
                                                                <span class="text-muted" style="font-size:0.7rem;">{{ ucfirst(str_replace('_',' ',$k)) }}</span>
                                                                @if(!$loop->last) <span class="text-muted mx-1">/</span> @endif
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <span class="metric-mini-val">{{ $metric->value }}</span>
                                                        @if($unit)
                                                            <span class="text-muted" style="font-size:0.72rem;"> {{ $unit }}</span>
                                                        @endif
                                                    @endif
                                                </div>
                                                <div class="metric-mini-date">{{ $metric->recorded_at->format('M d') }}</div>
                                            </div>
                                            {{-- Sparkline trend --}}
                                            @if(isset($metricTrends[$type]) && count($metricTrends[$type]) > 1)
                                                @php
                                                    $trends = $metricTrends[$type];
                                                    $max = max(array_column($trends, 'value')) ?: 1;
                                                @endphp
                                                <div class="sparkline-wrap">
                                                    @foreach($trends as $t)
                                                        <div class="sparkline-bar"
                                                             style="height:{{ max(15, ($t['value']/$max)*100) }}%; background:{{ $colorHex }}; opacity:0.4;"></div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════
             ROW 3 — Active Conditions + Recent Symptoms + Recent Uploads
        ══════════════════════════════════════════════════════ --}}
        <div class="row g-4 mb-4">

            {{-- Active Conditions --}}
            <div class="col-lg-4 fade-in-up delay-4">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6><i class="fas fa-virus"></i> Active Conditions</h6>
                        <a href="{{ route('health') }}" class="dash-card-link">Manage <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="dash-card-body">
                        @if($activeConditions->isEmpty())
                            <div class="text-center py-3" style="color:#a0aec0;">
                                <i class="fas fa-shield-alt fa-2x mb-2" style="color:#38a169;"></i>
                                <p class="mb-0 fw-semibold" style="font-size:0.88rem;color:#38a169;">No active conditions</p>
                                <p class="mb-0 text-muted" style="font-size:0.75rem;">Looking healthy!</p>
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
            <div class="col-lg-4 fade-in-up delay-5">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6><i class="fas fa-notes-medical"></i> Recent Symptoms</h6>
                        <a href="{{ route('health') }}" class="dash-card-link">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="dash-card-body p-0">
                        @if($symptoms->isEmpty())
                            <div class="text-center py-4 px-3" style="color:#a0aec0;">
                                <i class="fas fa-check-circle fa-2x mb-2" style="color:#38a169;"></i>
                                <p class="mb-0 fw-semibold" style="font-size:0.88rem;color:#38a169;">No symptoms logged</p>
                                <p class="mb-0 text-muted" style="font-size:0.75rem;">Feeling great!</p>
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

            {{-- Recent Uploads --}}
            <div class="col-lg-4 fade-in-up delay-6">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6><i class="fas fa-file-medical-alt"></i> Recent Uploads</h6>
                        <a href="{{ route('health') }}" class="dash-card-link">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="dash-card-body">
                        @if($recentUploads->isEmpty())
                            <div class="text-center py-3" style="color:#a0aec0;">
                                <i class="fas fa-cloud-upload-alt fa-2x mb-2" style="color:#cbd5e0;"></i>
                                <p class="mb-1" style="font-size:0.88rem;">No uploads yet</p>
                                <a href="{{ route('health') }}" class="dash-card-link justify-content-center">Upload a document <i class="fas fa-arrow-right"></i></a>
                            </div>
                        @else
                            @foreach($recentUploads as $upload)
                                <div class="upload-item">
                                    <div class="upload-icon {{ $upload->type === 'prescription' ? 'qa-icon-red' : 'qa-icon-blue' }}">
                                        <i class="fas {{ $upload->type === 'prescription' ? 'fa-prescription' : 'fa-file-alt' }}"></i>
                                    </div>
                                    <div class="flex-grow-1" style="min-width:0;">
                                        <div class="fw-semibold text-truncate" style="font-size:0.85rem;color:#2d3748;">
                                            {{ $upload->title }}
                                        </div>
                                        <div class="text-muted" style="font-size:0.7rem;">
                                            {{ ucfirst($upload->type) }}
                                            @if($upload->doctor_name) &middot; Dr. {{ $upload->doctor_name }} @endif
                                            @if($upload->document_date) &middot; {{ $upload->document_date->format('M d, Y') }} @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════
             FEATURE NAVIGATION
        ══════════════════════════════════════════════════════ --}}
        <div class="section-divider"></div>

        <div class="section-title">
            <i class="fas fa-th-large"></i> Explore Features
            <span class="title-badge">All Access</span>
        </div>

        <div class="row g-3 mb-4">

            {{-- 1 Health Dashboard --}}
            <div class="col-6 col-md-4 col-lg-3 fade-in-up delay-3">
                <a href="{{ route('health') }}" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-purple">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <div class="feature-nav-title">Health Dashboard</div>
                    <div class="feature-nav-desc">Track metrics, symptoms & diseases</div>
                </a>
            </div>

            {{-- 2 Metrics (tab) --}}
            <div class="col-6 col-md-4 col-lg-3 fade-in-up delay-3">
                <a href="{{ route('health') }}#metrics" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-blue">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="feature-nav-title">Metrics</div>
                    <div class="feature-nav-desc">View your recorded metrics</div>
                </a>
            </div>

            {{-- 3 Symptoms (tab) --}}
            <div class="col-6 col-md-4 col-lg-3 fade-in-up delay-4">
                <a href="{{ route('health') }}#symptomsPane" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-orange">
                        <i class="fas fa-notes-medical"></i>
                    </div>
                    <div class="feature-nav-title">Symptoms</div>
                    <div class="feature-nav-desc">Log & review symptoms</div>
                </a>
            </div>

            {{-- 4 Diseases (tab) --}}
            <div class="col-6 col-md-4 col-lg-3 fade-in-up delay-4">
                <a href="{{ route('health') }}#diseasesPane" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-red">
                        <i class="fas fa-virus"></i>
                    </div>
                    <div class="feature-nav-title">Diseases</div>
                    <div class="feature-nav-desc">Manage diagnosed conditions</div>
                </a>
            </div>

            {{-- 5 Prescriptions (tab) --}}
            <div class="col-6 col-md-4 col-lg-3 fade-in-up delay-4">
                <a href="{{ route('health') }}#prescriptions" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-red">
                        <i class="fas fa-prescription-bottle-alt"></i>
                    </div>
                    <div class="feature-nav-title">Prescriptions</div>
                    <div class="feature-nav-desc">Store & manage prescriptions</div>
                </a>
            </div>

            {{-- 6 Reports (tab) --}}
            <div class="col-6 col-md-4 col-lg-3 fade-in-up delay-5">
                <a href="{{ route('health') }}#reportsPane" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-blue">
                        <i class="fas fa-file-medical-alt"></i>
                    </div>
                    <div class="feature-nav-title">Reports</div>
                    <div class="feature-nav-desc">View medical reports</div>
                </a>
            </div>

            {{-- 7 Medicine Logs (tab) --}}
            <div class="col-6 col-md-4 col-lg-3 fade-in-up delay-5">
                <a href="{{ route('health') }}#logs" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-green">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="feature-nav-title">Medicine Logs</div>
                    <div class="feature-nav-desc">Daily medication history</div>
                </a>
            </div>

            {{-- 8 Medicine Reminders --}}
            <div class="col-6 col-md-4 col-lg-3 fade-in-up delay-5">
                <a href="{{ route('medicine.reminders') }}" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-green">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="feature-nav-title">Medicine Reminders</div>
                    <div class="feature-nav-desc">Never miss a dose again</div>
                </a>
            </div>

            {{-- 9 Medicine Search --}}
            <div class="col-6 col-md-4 col-lg-3 fade-in-up delay-6">
                <a href="{{ route('medicine.search') }}" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-indigo">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="feature-nav-title">Medicine Search</div>
                    <div class="feature-nav-desc">Find medicines & details</div>
                </a>
            </div>

            {{-- 10 Medical Records --}}
            <div class="col-6 col-md-4 col-lg-3 fade-in-up delay-6">
                <a href="{{ route('health') }}#logs" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-blue">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div class="feature-nav-title">Medical Records</div>
                    <div class="feature-nav-desc">All your health files in one place</div>
                </a>
            </div>

            {{-- 11 Smart Suggestions --}}
            <div class="col-6 col-md-4 col-lg-3 fade-in-up delay-6">
                <a href="{{ route('health.suggestions') }}" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-teal">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <div class="feature-nav-title">Smart Suggestions</div>
                    <div class="feature-nav-desc">Personalized health insights</div>
                </a>
            </div>

            {{-- 12 Community --}}
            <div class="col-6 col-md-4 col-lg-3 fade-in-up delay-6">
                <a href="{{ route('community') }}" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-pink">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="feature-nav-title">Community</div>
                    <div class="feature-nav-desc">Connect with others</div>
                </a>
            </div>

            {{-- 13 Find Hospitals --}}
            <div class="col-6 col-md-4 col-lg-3 fade-in-up delay-6">
                <a href="{{ route('health.hospitals') }}" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-yellow">
                        <i class="fas fa-hospital"></i>
                    </div>
                    <div class="feature-nav-title">Find Hospitals</div>
                    <div class="feature-nav-desc">Nearby hospitals & clinics</div>
                </a>
            </div>

            {{-- 14 Health Tips --}}
            <div class="col-6 col-md-4 col-lg-3 fade-in-up delay-6">
                <a href="{{ route('health.tips') }}" class="feature-nav-card">
                    <div class="feature-nav-icon" style="background:rgba(56,178,172,0.12);color:#319795;">
                        <i class="fas fa-book-medical"></i>
                    </div>
                    <div class="feature-nav-title">Health Tips</div>
                    <div class="feature-nav-desc">Daily wellness articles</div>
                </a>
            </div>

            {{-- 15 Appointments --}}
            <div class="col-6 col-md-4 col-lg-3 fade-in-up delay-6">
                <a href="{{ route('appointments') }}" class="feature-nav-card">
                    <div class="feature-nav-icon qa-icon-cyan">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="feature-nav-title">Appointments</div>
                    <div class="feature-nav-desc">Book & manage visits</div>
                </a>
            </div>

            {{-- 16 My Profile --}}
            <div class="col-6 col-md-4 col-lg-3 fade-in-up delay-6">
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

    /* ── Adherence Donut Chart ── */
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
                    duration: 1400,
                }
            }
        });
    }

    /* ── Animated Number Counters ── */
    document.querySelectorAll('[data-count]').forEach(function(el) {
        const target = parseInt(el.getAttribute('data-count'));
        if (target === 0) return;
        let current = 0;
        const step = Math.max(1, Math.ceil(target / 30));
        const interval = setInterval(function() {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(interval);
            }
            el.textContent = current;
        }, 40);
    });

    /* ── Intersection Observer for fade-in-up ── */
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.fade-in-up').forEach(function(el) {
        el.style.animationPlayState = 'paused';
        observer.observe(el);
    });
});
</script>
@endpush
