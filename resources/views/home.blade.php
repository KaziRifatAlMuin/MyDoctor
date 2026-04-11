@extends('layouts.app')

@section('title', __('ui.auto.Home'))

@section('content')
@php
    $homeStats = $homeStats ?? [
        'active_users' => 0,
        'approved_posts' => 0,
        'total_uploads' => 0,
        'health_catalog' => 0,
        'reminder_adherence' => 0,
        'total_reminders' => 0,
    ];

    $isAuthenticated = auth()->check();
    $loginUrl = route('login');
    $protectUrl = static function (string $url) use ($isAuthenticated, $loginUrl): string {
        return $isAuthenticated ? $url : $loginUrl . '?redirect=' . urlencode($url);
    };

    $communityUrl = $protectUrl(route('community.landing'));
    $medicineReminderUrl = $protectUrl(route('medicine.reminders'));
    $healthTrackingUrl = $protectUrl(route('health.tracking'));
    $healthLogsUrl = $protectUrl(route('health') . '#logs');
    $healthSymptomsUrl = $protectUrl(route('health.symptoms'));
    $healthSuggestionsUrl = $protectUrl(route('health.suggestions'));
    $healthTipsUrl = $protectUrl(route('health.tips'));
    $ctaPrimaryUrl = $isAuthenticated ? route('health.tracking') : ($loginUrl . '?redirect=' . urlencode(route('dashboard')));
    $ctaSecondaryUrl = $isAuthenticated ? route('community.home') : ($loginUrl . '?redirect=' . urlencode(route('community.home')));
@endphp

<div class="home-next">
    <section class="hero-shell py-4 py-lg-5">
        <div class="container position-relative">
            <div class="hero-orb orb-a"></div>
            <div class="hero-orb orb-b"></div>
            <div class="hero-orb orb-c"></div>

            <div class="hero-panel reveal-block">
                <div class="row align-items-center g-4 g-xl-5">
                    <div class="col-lg-7">
                        <span class="hero-kicker"><i class="fas fa-circle me-2"></i>Health Intelligence Platform</span>
                        <h1 class="hero-title mt-3 mb-3">Smarter care, daily momentum, better outcomes.</h1>
                        <p class="hero-subtitle mb-4">
                            MyDoctor unifies reminders, health metrics, medical records, symptom tracking, and AI-powered support in one
                            modern patient-first workspace.
                        </p>
                        <div class="d-flex flex-wrap gap-2 gap-md-3 mb-4">
                            <a href="{{ $ctaPrimaryUrl }}" class="btn btn-teal rounded-pill px-4 px-md-5 py-2 py-md-3">
                                <i class="fas fa-heartbeat me-2"></i>{{ $isAuthenticated ? 'Open Dashboard' : 'Login to Continue' }}
                            </a>
                            <a href="{{ $ctaSecondaryUrl }}" class="btn btn-outline-teal rounded-pill px-4 px-md-5 py-2 py-md-3">
                                <i class="fas fa-users me-2"></i>{{ $isAuthenticated ? 'Open Community' : 'Login for Community' }}
                            </a>
                        </div>
                        <div class="hero-inline-stats">
                            <div class="hero-inline-stat">
                                <div class="hero-inline-value" data-counter="{{ (int) $homeStats['active_users'] }}">0</div>
                                <small>Active users</small>
                            </div>
                            <div class="hero-inline-stat">
                                <div class="hero-inline-value" data-counter="{{ (int) $homeStats['approved_posts'] }}">0</div>
                                <small>Community posts</small>
                            </div>
                            <div class="hero-inline-stat">
                                <div class="hero-inline-value" data-counter="{{ (int) $homeStats['health_catalog'] }}">0</div>
                                <small>Health topics</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="hero-insight-card reveal-block" data-tilt-card>
                            <div class="insight-head">
                                <h6 class="mb-0">Care Readiness Pulse</h6>
                                <span class="badge-soft">LIVE</span>
                            </div>
                            <div class="insight-grid mt-3">
                                <div class="insight-kpi">
                                    <small>Total reminders</small>
                                    <strong data-counter="{{ (int) $homeStats['total_reminders'] }}">0</strong>
                                </div>
                                <div class="insight-kpi">
                                    <small>Stored files</small>
                                    <strong data-counter="{{ (int) $homeStats['total_uploads'] }}">0</strong>
                                </div>
                            </div>
                            <div class="adherence-ring-wrap mt-3">
                                <div class="adherence-ring" style="--adherence: {{ max(0, min(100, (int) $homeStats['reminder_adherence'])) }};">
                                    <div class="ring-inner">{{ (int) $homeStats['reminder_adherence'] }}%</div>
                                </div>
                                <p class="mb-0 text-muted small">Dose adherence this cycle</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-3 mb-2">
        <div class="container">
            <div class="ticker-wrap reveal-block">
                <div class="ticker-item">
                    <i class="fas fa-users"></i>
                    <span><strong data-counter="{{ (int) $homeStats['active_users'] }}">0</strong> active members</span>
                </div>
                <div class="ticker-item">
                    <i class="fas fa-file-medical"></i>
                    <span><strong data-counter="{{ (int) $homeStats['total_uploads'] }}">0</strong> health files secured</span>
                </div>
                <div class="ticker-item">
                    <i class="fas fa-bell"></i>
                    <span><strong data-counter="{{ (int) $homeStats['total_reminders'] }}">0</strong> reminders managed</span>
                </div>
                <div class="ticker-item">
                    <i class="fas fa-check-circle"></i>
                    <span><strong>{{ (int) $homeStats['reminder_adherence'] }}%</strong> adherence rate</span>
                </div>
            </div>
        </div>
    </section>

    <section class="py-4 py-md-5">
        <div class="container">
            <div class="section-head reveal-block mb-4 mb-md-5">
                <span class="section-kicker">Core Capabilities</span>
                <h2 class="section-title">Everything connected, nothing fragmented</h2>
            </div>

            <div class="row g-3 g-md-4">
                <div class="col-md-6 col-xl-3">
                    <a href="{{ $communityUrl }}" class="feature-neo feature-neo-link h-100 d-block reveal-block" data-tilt-card>
                        <span class="feature-chip">Community</span>
                        <div class="feature-icon"><i class="fas fa-user-friends"></i></div>
                        <h5>Community Posting</h5>
                        <p>Connect with people facing similar health conditions.</p>
                        <small class="feature-meta"><i class="fas fa-comments me-1"></i>{{ number_format($homeStats['approved_posts']) }} approved posts</small>
                    </a>
                </div>

                <div class="col-md-6 col-xl-3">
                    <a href="{{ $medicineReminderUrl }}" class="feature-neo feature-neo-link h-100 d-block reveal-block" data-tilt-card>
                        <span class="feature-chip">Medication</span>
                        <div class="feature-icon"><i class="fas fa-pills"></i></div>
                        <h5>Medicine Reminders</h5>
                        <p>Automated alerts so doses are never missed.</p>
                        <small class="feature-meta"><i class="fas fa-bell me-1"></i>{{ number_format($homeStats['total_reminders']) }} reminders configured</small>
                    </a>
                </div>

                <div class="col-md-6 col-xl-3">
                    <a href="{{ $healthTrackingUrl }}" class="feature-neo feature-neo-link h-100 d-block reveal-block" data-tilt-card>
                        <span class="feature-chip">Metrics</span>
                        <div class="feature-icon"><i class="fas fa-wave-square"></i></div>
                        <h5>Health Tracking</h5>
                        <p>Track BP, glucose, pulse, and more over time.</p>
                        <small class="feature-meta"><i class="fas fa-chart-line me-1"></i>{{ number_format($homeStats['health_catalog']) }} tracked topics</small>
                    </a>
                </div>

                <div class="col-md-6 col-xl-3">
                    @auth
                        <button onclick="toggleChatbot()" class="feature-neo feature-neo-link h-100 d-block w-100 border-0 text-start reveal-block" data-tilt-card>
                    @else
                        <a href="{{ route('login') }}?redirect={{ urlencode(request()->fullUrl()) }}" class="feature-neo feature-neo-link h-100 d-block reveal-block" data-tilt-card>
                    @endauth
                        <span class="feature-chip">AI</span>
                        <div class="feature-icon"><i class="fas fa-robot"></i></div>
                        <h5>AI Health Assistant</h5>
                        <p>Ask symptom and condition questions instantly.</p>
                        <small class="feature-meta"><i class="fas fa-shield-alt me-1"></i>Safe, structured guidance</small>
                    @auth
                        </button>
                    @else
                        </a>
                    @endauth
                </div>

                <div class="col-md-6 col-xl-3">
                    <a href="{{ $healthLogsUrl }}" class="feature-neo feature-neo-link h-100 d-block reveal-block" data-tilt-card>
                        <span class="feature-chip">Records</span>
                        <div class="feature-icon"><i class="fas fa-folder-open"></i></div>
                        <h5>Medical Records</h5>
                        <p>Securely store reports, prescriptions, and files.</p>
                        <small class="feature-meta"><i class="fas fa-file-medical-alt me-1"></i>{{ number_format($homeStats['total_uploads']) }} uploads stored</small>
                    </a>
                </div>

                <div class="col-md-6 col-xl-3">
                    <a href="{{ $healthSymptomsUrl }}" class="feature-neo feature-neo-link h-100 d-block reveal-block" data-tilt-card>
                        <span class="feature-chip">Symptoms</span>
                        <div class="feature-icon"><i class="fas fa-stethoscope"></i></div>
                        <h5>Symptom Tracker</h5>
                        <p>Log daily symptoms and watch patterns emerge.</p>
                        <small class="feature-meta"><i class="fas fa-notes-medical me-1"></i>Timeline-based health notes</small>
                    </a>
                </div>

                <div class="col-md-6 col-xl-3">
                    <a href="{{ $healthSuggestionsUrl }}" class="feature-neo feature-neo-link h-100 d-block reveal-block" data-tilt-card>
                        <span class="feature-chip">Insights</span>
                        <div class="feature-icon"><i class="fas fa-compass"></i></div>
                        <h5>Smart Suggestions</h5>
                        <p>Get practical recommendations based on your data.</p>
                        <small class="feature-meta"><i class="fas fa-brain me-1"></i>Data-driven suggestions</small>
                    </a>
                </div>

                <div class="col-md-6 col-xl-3">
                    <a href="{{ $healthTipsUrl }}" class="feature-neo feature-neo-link h-100 d-block reveal-block" data-tilt-card>
                        <span class="feature-chip">Education</span>
                        <div class="feature-icon"><i class="fas fa-lightbulb"></i></div>
                        <h5>Health Tips</h5>
                        <p>Daily guidance to maintain a healthier routine.</p>
                        <small class="feature-meta"><i class="fas fa-seedling me-1"></i>Habits that actually stick</small>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="py-4 py-md-5 journey-zone">
        <div class="container">
            <div class="section-head reveal-block mb-4 mb-md-5">
                <span class="section-kicker">How It Works</span>
                <h2 class="section-title">Your wellness loop in four steps</h2>
            </div>
            <div class="row g-3 g-md-4">
                <div class="col-md-6 col-xl-3 reveal-block">
                    <div class="journey-card h-100">
                        <span class="journey-num">01</span>
                        <h6>Create profile</h6>
                        <p>Start in under a minute and set baseline details.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 reveal-block">
                    <div class="journey-card h-100">
                        <span class="journey-num">02</span>
                        <h6>Track daily data</h6>
                        <p>Log metrics, symptoms, medicine intake, and files.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 reveal-block">
                    <div class="journey-card h-100">
                        <span class="journey-num">03</span>
                        <h6>Review insights</h6>
                        <p>Use trends and suggestions to optimize your routine.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 reveal-block">
                    <div class="journey-card h-100">
                        <span class="journey-num">04</span>
                        <h6>Improve consistency</h6>
                        <p>Rely on reminders and community motivation every day.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-4 py-md-5">
        <div class="container">
            <div class="cta-panel text-center reveal-block">
                <h2 class="mb-3">Turn your health data into healthier days</h2>
                <p class="mb-4">Access your dashboard, follow reminders, and stay in sync with your care goals.</p>
                <div class="d-flex flex-wrap justify-content-center gap-2 gap-md-3">
                    <a href="{{ $ctaPrimaryUrl }}" class="btn btn-light rounded-pill px-4 px-md-5 py-2 py-md-3">
                        <i class="fas fa-tachometer-alt me-2"></i>{{ $isAuthenticated ? 'Go to Dashboard' : 'Login to Continue' }}
                    </a>
                    <a href="{{ $ctaSecondaryUrl }}" class="btn btn-outline-light rounded-pill px-4 px-md-5 py-2 py-md-3">
                        <i class="fas fa-users me-2"></i>{{ $isAuthenticated ? 'Open Community' : 'Login for Community' }}
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    .home-next {
        --tone-bg: #f5fbfa;
        --tone-surface: #ffffff;
        --tone-main: #0f766e;
        --tone-main-dark: #0b5e57;
        --tone-accent: #0891b2;
        --tone-text: #10393a;
        --tone-muted: #4e7070;
        --tone-border: rgba(15, 118, 110, 0.16);
        background:
            radial-gradient(circle at 10% 8%, rgba(16, 185, 129, 0.13), transparent 32%),
            radial-gradient(circle at 90% 92%, rgba(14, 165, 233, 0.12), transparent 32%),
            var(--tone-bg);
        color: var(--tone-text);
    }

    .hero-shell {
        overflow: hidden;
    }

    .hero-panel {
        position: relative;
        border-radius: 32px;
        padding: 2rem 1.25rem;
        background: linear-gradient(130deg, rgba(7, 64, 61, 0.96), rgba(12, 94, 87, 0.92));
        color: #e9fffc;
        box-shadow: 0 24px 50px rgba(5, 51, 48, 0.25);
        isolation: isolate;
    }

    .hero-panel::before {
        content: "";
        position: absolute;
        inset: 1px;
        border-radius: 31px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        pointer-events: none;
        z-index: -1;
    }

    .hero-orb {
        position: absolute;
        border-radius: 999px;
        filter: blur(0.2px);
        opacity: 0.5;
        animation: drift 8s ease-in-out infinite;
        pointer-events: none;
    }

    .orb-a {
        width: 140px;
        height: 140px;
        background: rgba(52, 211, 153, 0.45);
        top: -14px;
        left: 22px;
    }

    .orb-b {
        width: 190px;
        height: 190px;
        background: rgba(45, 212, 191, 0.32);
        top: 20%;
        right: -30px;
        animation-delay: 1.4s;
    }

    .orb-c {
        width: 120px;
        height: 120px;
        background: rgba(14, 165, 233, 0.3);
        bottom: -30px;
        left: 35%;
        animation-delay: 0.6s;
    }

    .hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.78rem;
        letter-spacing: 0.11rem;
        text-transform: uppercase;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 999px;
        padding: 0.45rem 0.85rem;
    }

    .hero-kicker i {
        font-size: 0.48rem;
        color: #6ee7d8;
    }

    .hero-title {
        font-size: clamp(2rem, 3.8vw, 3.25rem);
        font-weight: 800;
        line-height: 1.08;
        letter-spacing: -0.02em;
    }

    .hero-subtitle {
        color: rgba(224, 253, 249, 0.9);
        max-width: 58ch;
        font-size: 1rem;
    }

    .btn-teal {
        background: linear-gradient(120deg, #34d399, #2dd4bf);
        border: none;
        color: #05312e;
        font-weight: 700;
        box-shadow: 0 10px 24px rgba(45, 212, 191, 0.28);
    }

    .btn-teal:hover {
        color: #032726;
        transform: translateY(-1px);
    }

    .btn-outline-teal {
        border: 1px solid rgba(165, 243, 252, 0.65);
        color: #e6fffb;
        font-weight: 600;
    }

    .btn-outline-teal:hover {
        color: #083737;
        background: #ccfbf1;
        border-color: #ccfbf1;
    }

    .hero-inline-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.6rem;
    }

    .hero-inline-stat {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 14px;
        padding: 0.75rem 0.8rem;
    }

    .hero-inline-value {
        font-size: 1.2rem;
        font-weight: 800;
        color: #a7f3d0;
        line-height: 1;
    }

    .hero-inline-stat small {
        display: block;
        font-size: 0.75rem;
        opacity: 0.88;
        margin-top: 0.3rem;
    }

    .hero-insight-card {
        background: rgba(255, 255, 255, 0.9);
        color: #113f3d;
        border-radius: 20px;
        padding: 1.1rem;
        border: 1px solid rgba(255, 255, 255, 0.8);
        box-shadow: 0 14px 28px rgba(7, 64, 61, 0.2);
        transform-style: preserve-3d;
        transition: transform 220ms ease, box-shadow 220ms ease;
    }

    .insight-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .badge-soft {
        font-size: 0.68rem;
        font-weight: 700;
        color: #0f766e;
        background: rgba(20, 184, 166, 0.16);
        border-radius: 999px;
        padding: 0.2rem 0.55rem;
    }

    .insight-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.55rem;
    }

    .insight-kpi {
        background: #ffffff;
        border: 1px solid rgba(8, 145, 178, 0.16);
        border-radius: 12px;
        padding: 0.65rem;
    }

    .insight-kpi small {
        color: #437273;
        font-size: 0.72rem;
        display: block;
    }

    .insight-kpi strong {
        font-size: 1.25rem;
        color: #0f766e;
        font-weight: 800;
        line-height: 1;
    }

    .adherence-ring-wrap {
        display: flex;
        align-items: center;
        gap: 0.95rem;
    }

    .adherence-ring {
        width: 74px;
        height: 74px;
        border-radius: 50%;
        background: conic-gradient(#0f766e calc(var(--adherence) * 1%), #d1f5f0 0);
        display: grid;
        place-items: center;
        position: relative;
        flex-shrink: 0;
    }

    .adherence-ring::before {
        content: "";
        position: absolute;
        inset: 8px;
        background: #ffffff;
        border-radius: 50%;
    }

    .ring-inner {
        position: relative;
        z-index: 1;
        font-weight: 800;
        font-size: 0.9rem;
        color: #0f766e;
    }

    .ticker-wrap {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.6rem;
    }

    .ticker-item {
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid var(--tone-border);
        border-radius: 14px;
        padding: 0.7rem 0.85rem;
        color: #194d4c;
        font-size: 0.86rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 8px 18px rgba(15, 118, 110, 0.08);
    }

    .ticker-item i {
        color: #0f766e;
    }

    .ticker-item strong {
        color: #0b5e57;
    }

    .section-head {
        text-align: center;
    }

    .section-kicker {
        display: inline-block;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.09rem;
        color: #0d9488;
        font-weight: 700;
    }

    .section-title {
        margin-top: 0.45rem;
        margin-bottom: 0;
        font-size: clamp(1.5rem, 3.2vw, 2.25rem);
        font-weight: 800;
        color: #104a4b;
    }

    .feature-neo {
        position: relative;
        padding: 1rem 1rem 0.95rem;
        border-radius: 18px;
        background: linear-gradient(165deg, rgba(255, 255, 255, 0.98), rgba(247, 254, 252, 0.95));
        border: 1px solid rgba(15, 118, 110, 0.14);
        box-shadow: 0 12px 30px rgba(15, 118, 110, 0.08);
        overflow: hidden;
        transform-style: preserve-3d;
        transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
    }

    .feature-neo::after {
        content: "";
        position: absolute;
        inset: auto -10% -35% -10%;
        height: 110px;
        background: radial-gradient(circle, rgba(45, 212, 191, 0.18), rgba(45, 212, 191, 0));
        transition: opacity 220ms ease;
        opacity: 0;
        pointer-events: none;
    }

    .feature-neo:hover,
    .feature-neo:focus-visible {
        transform: translateY(-5px);
        box-shadow: 0 18px 36px rgba(15, 118, 110, 0.14);
        border-color: rgba(15, 118, 110, 0.3);
    }

    .feature-neo:hover::after,
    .feature-neo:focus-visible::after {
        opacity: 1;
    }

    .feature-neo-link {
        text-decoration: none;
        color: #0f3a3a;
    }

    .feature-neo-link:hover,
    .feature-neo-link:focus {
        color: #0f3a3a;
    }

    .feature-neo-link:focus-visible {
        outline: 3px solid rgba(15, 118, 110, 0.25);
        outline-offset: 2px;
    }

    .feature-chip {
        display: inline-block;
        margin-bottom: 0.62rem;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.08rem;
        color: #0f766e;
        font-weight: 700;
        background: rgba(15, 118, 110, 0.09);
        border-radius: 999px;
        padding: 0.25rem 0.6rem;
    }

    .feature-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        background: linear-gradient(130deg, #14b8a6, #0ea5e9);
        color: #ffffff;
        margin-bottom: 0.8rem;
        box-shadow: 0 8px 18px rgba(14, 165, 233, 0.26);
    }

    .feature-neo h5 {
        font-size: 1.02rem;
        font-weight: 700;
        margin-bottom: 0.45rem;
    }

    .feature-neo p {
        font-size: 0.88rem;
        color: #446c6d;
        margin-bottom: 0.55rem;
        line-height: 1.55;
    }

    .feature-meta {
        color: #0f766e;
        font-size: 0.76rem;
        font-weight: 600;
    }

    .journey-zone {
        background: linear-gradient(180deg, rgba(226, 250, 245, 0.65), rgba(245, 251, 250, 0.65));
    }

    .journey-card {
        background: #ffffff;
        border: 1px solid rgba(15, 118, 110, 0.14);
        border-radius: 16px;
        padding: 1rem;
        box-shadow: 0 10px 24px rgba(15, 118, 110, 0.08);
        transition: transform 220ms ease, box-shadow 220ms ease;
    }

    .journey-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 30px rgba(15, 118, 110, 0.14);
    }

    .journey-num {
        display: inline-flex;
        width: 38px;
        height: 38px;
        border-radius: 11px;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: #ffffff;
        background: linear-gradient(130deg, #0f766e, #0891b2);
        margin-bottom: 0.7rem;
    }

    .journey-card h6 {
        font-size: 0.98rem;
        margin-bottom: 0.3rem;
        color: #12494a;
        font-weight: 700;
    }

    .journey-card p {
        margin: 0;
        font-size: 0.85rem;
        color: #4d7171;
    }

    .cta-panel {
        background: linear-gradient(120deg, #0f766e, #0e7490);
        border-radius: 26px;
        padding: clamp(1.4rem, 4vw, 2.6rem);
        color: #ecfeff;
        box-shadow: 0 22px 44px rgba(10, 78, 83, 0.24);
    }

    .cta-panel h2 {
        font-weight: 800;
        letter-spacing: -0.01em;
    }

    .cta-panel p {
        color: rgba(236, 254, 255, 0.9);
        max-width: 62ch;
        margin-left: auto;
        margin-right: auto;
    }

    .reveal-block {
        opacity: 0;
        transform: translateY(18px);
        transition: opacity 600ms ease, transform 600ms ease;
    }

    .reveal-block.is-visible {
        opacity: 1;
        transform: translateY(0);
    }

    @keyframes drift {
        0%,
        100% {
            transform: translate3d(0, 0, 0);
        }
        50% {
            transform: translate3d(0, -12px, 0);
        }
    }

    @media (max-width: 1199.98px) {
        .ticker-wrap {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .hero-panel {
            padding: 1.5rem 1rem;
            border-radius: 24px;
        }

        .hero-inline-stats {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .ticker-wrap {
            grid-template-columns: 1fr;
        }

        .hero-title {
            font-size: clamp(1.8rem, 9vw, 2.35rem);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .hero-orb,
        .reveal-block,
        .feature-neo,
        .journey-card,
        .hero-insight-card {
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var revealItems = document.querySelectorAll('.reveal-block');
        if ('IntersectionObserver' in window) {
            var revealObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        revealObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12 });

            revealItems.forEach(function (item, index) {
                item.style.transitionDelay = Math.min(index * 35, 320) + 'ms';
                revealObserver.observe(item);
            });
        } else {
            revealItems.forEach(function (item) {
                item.classList.add('is-visible');
            });
        }

        function animateCounter(el) {
            var target = parseInt(el.getAttribute('data-counter') || '0', 10);
            if (isNaN(target)) {
                return;
            }

            var duration = 1200;
            var startTime = null;

            function tick(timestamp) {
                if (!startTime) {
                    startTime = timestamp;
                }
                var progress = Math.min((timestamp - startTime) / duration, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = Math.floor(target * eased).toLocaleString();
                if (progress < 1) {
                    window.requestAnimationFrame(tick);
                } else {
                    el.textContent = target.toLocaleString();
                }
            }

            window.requestAnimationFrame(tick);
        }

        var counters = document.querySelectorAll('[data-counter]');
        if ('IntersectionObserver' in window) {
            var counterObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting && !entry.target.dataset.counted) {
                        entry.target.dataset.counted = '1';
                        animateCounter(entry.target);
                        counterObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });

            counters.forEach(function (counter) {
                counterObserver.observe(counter);
            });
        } else {
            counters.forEach(animateCounter);
        }

        var tiltCards = document.querySelectorAll('[data-tilt-card]');
        tiltCards.forEach(function (card) {
            card.addEventListener('mousemove', function (event) {
                var rect = card.getBoundingClientRect();
                var px = (event.clientX - rect.left) / rect.width;
                var py = (event.clientY - rect.top) / rect.height;
                var rx = (0.5 - py) * 4;
                var ry = (px - 0.5) * 4;
                card.style.transform = 'rotateX(' + rx.toFixed(2) + 'deg) rotateY(' + ry.toFixed(2) + 'deg) translateY(-4px)';
            });

            card.addEventListener('mouseleave', function () {
                card.style.transform = '';
            });
        });
    });
</script>
@endpush