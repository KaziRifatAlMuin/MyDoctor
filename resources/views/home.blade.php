@extends('layouts.app')

@section('title', __('ui.auto.Home'))
@section('main_content_class', 'p-0')

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
    $loginUrl = route('login', [], false);
    $protectUrl = static function (string $url) use ($isAuthenticated, $loginUrl): string {
        return $isAuthenticated ? $url : $loginUrl . '?redirect=' . urlencode($url);
    };

    $communityUrl = $protectUrl(route('community.landing', [], false));
    $medicineReminderUrl = $protectUrl(route('medicine.reminders', [], false));
    $healthTrackingUrl = $protectUrl(route('health', [], false));
    $healthLogsUrl = $protectUrl(route('health', [], false) . '#logs');
    $healthSymptomsUrl = $protectUrl(route('health', [], false) . '#symptomsPane');
    $healthSuggestionsUrl = $protectUrl(route('suggestions', [], false));
    $healthTipsUrl = $protectUrl(route('help', [], false));
    $programsUrl = $protectUrl(route('appointments', [], false));
    $ctaPrimaryUrl = $isAuthenticated ? route('medicine.index', [], false) : ($loginUrl . '?redirect=' . urlencode(route('medicine.index', [], false)));
    $ctaSecondaryUrl = $isAuthenticated ? route('community.home', [], false) : ($loginUrl . '?redirect=' . urlencode(route('community.home', [], false)));
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
                        <span class="hero-kicker"><i class="fas fa-circle me-2"></i>{{ __('ui.home.hero_kicker') }}</span>
                        <h1 class="hero-title mt-3 mb-3">{{ __('ui.home.hero_title') }}</h1>
                        <p class="hero-subtitle mb-4">
                            {{ __('ui.home.hero_subtitle') }}
                        </p>
                        <div class="d-flex flex-wrap gap-2 gap-md-3 mb-4">
                            <a href="{{ $ctaPrimaryUrl }}" class="btn btn-teal rounded-pill px-4 px-md-5 py-2 py-md-3">
                                <i class="fas fa-heartbeat me-2"></i>{{ $isAuthenticated ? __('ui.home.open_health') : __('ui.home.login_to_continue') }}
                            </a>
                        </div>
                        <div class="hero-inline-stats">
                            <div class="hero-inline-stat">
                                <div class="hero-inline-value" data-counter="{{ (int) $homeStats['active_users'] }}">0</div>
                                <small>{{ __('ui.home.active_users') }}</small>
                            </div>
                            <div class="hero-inline-stat">
                                <div class="hero-inline-value" data-counter="{{ (int) $homeStats['approved_posts'] }}">0</div>
                                <small>{{ __('ui.home.community_posts') }}</small>
                            </div>
                            <div class="hero-inline-stat">
                                <div class="hero-inline-value" data-counter="{{ (int) $homeStats['health_catalog'] }}">0</div>
                                <small>{{ __('ui.home.health_topics') }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="hero-insight-card reveal-block" data-tilt-card>
                            <div class="insight-head">
                                <h6 class="mb-0">{{ __('ui.home.care_readiness_pulse') }}</h6>
                                <span class="badge-soft">{{ __('ui.home.live') }}</span>
                            </div>
                            <div class="insight-grid mt-3">
                                <div class="insight-kpi">
                                    <small>{{ __('ui.home.total_reminders') }}</small>
                                    <strong data-counter="{{ (int) $homeStats['total_reminders'] }}">0</strong>
                                </div>
                                <div class="insight-kpi">
                                    <small>{{ __('ui.home.stored_files') }}</small>
                                    <strong data-counter="{{ (int) $homeStats['total_uploads'] }}">0</strong>
                                </div>
                            </div>
                            <div class="adherence-ring-wrap mt-3">
                                <div class="adherence-ring" style="--adherence: {{ max(0, min(100, (int) $homeStats['reminder_adherence'])) }};">
                                    <div class="ring-inner">{{ (int) $homeStats['reminder_adherence'] }}%</div>
                                </div>
                                <p class="mb-0 text-muted small">{{ __('ui.home.dose_adherence') }}</p>
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
                    <span><strong data-counter="{{ (int) $homeStats['active_users'] }}">0</strong> {{ __('ui.home.active_members') }}</span>
                </div>
                <div class="ticker-item">
                    <i class="fas fa-file-medical"></i>
                    <span><strong data-counter="{{ (int) $homeStats['total_uploads'] }}">0</strong> {{ __('ui.home.health_files_secured') }}</span>
                </div>
                <div class="ticker-item">
                    <i class="fas fa-bell"></i>
                    <span><strong data-counter="{{ (int) $homeStats['total_reminders'] }}">0</strong> {{ __('ui.home.reminders_managed') }}</span>
                </div>
                <div class="ticker-item">
                    <i class="fas fa-check-circle"></i>
                    <span><strong>{{ (int) $homeStats['reminder_adherence'] }}%</strong> {{ __('ui.home.adherence_rate') }}</span>
                </div>
            </div>
        </div>
    </section>

    <section class="py-4 py-md-5">
        <div class="container">
            <div class="section-head reveal-block mb-4 mb-md-5">
                <span class="section-kicker">{{ __('ui.home.core_capabilities') }}</span>
                <h2 class="section-title">{{ __('ui.home.everything_connected') }}</h2>
            </div>

            <div class="row g-3 g-md-4">
                <div class="col-md-6 col-xl-3">
                    <a href="{{ $communityUrl }}" class="feature-neo feature-neo-link h-100 d-block reveal-block" data-tilt-card>
                        <span class="feature-chip">{{ __('ui.home.community') }}</span>
                        <div class="feature-icon"><i class="fas fa-user-friends"></i></div>
                        <h5>{{ __('ui.home.community_posting') }}</h5>
                        <p>{{ __('ui.home.community_desc') }}</p>
                        <small class="feature-meta"><i class="fas fa-comments me-1"></i>{{ number_format($homeStats['approved_posts']) }} {{ __('ui.home.approved_posts') }}</small>
                    </a>
                </div>

                <div class="col-md-6 col-xl-3">
                    <a href="{{ $medicineReminderUrl }}" class="feature-neo feature-neo-link h-100 d-block reveal-block" data-tilt-card>
                        <span class="feature-chip">{{ __('ui.home.medication') }}</span>
                        <div class="feature-icon"><i class="fas fa-pills"></i></div>
                        <h5>{{ __('ui.home.medicine_reminders') }}</h5>
                        <p>{{ __('ui.home.medicine_desc') }}</p>
                        <small class="feature-meta"><i class="fas fa-bell me-1"></i>{{ number_format($homeStats['total_reminders']) }} {{ __('ui.home.reminders_configured') }}</small>
                    </a>
                </div>

                <div class="col-md-6 col-xl-3">
                    <a href="{{ $healthTrackingUrl }}" class="feature-neo feature-neo-link h-100 d-block reveal-block" data-tilt-card>
                        <span class="feature-chip">{{ __('ui.home.metrics') }}</span>
                        <div class="feature-icon"><i class="fas fa-wave-square"></i></div>
                        <h5>{{ __('ui.home.health_tracking') }}</h5>
                        <p>{{ __('ui.home.health_tracking_desc') }}</p>
                        <small class="feature-meta"><i class="fas fa-chart-line me-1"></i>{{ number_format($homeStats['health_catalog']) }} {{ __('ui.home.tracked_topics') }}</small>
                    </a>
                </div>

                <div class="col-md-6 col-xl-3">
                    @auth
                        <button onclick="toggleChatbot()" class="feature-neo feature-neo-link h-100 d-block w-100 border-0 text-start reveal-block" data-tilt-card>
                    @else
                        <a href="{{ route('login', [], false) }}?redirect={{ urlencode(request()->fullUrl()) }}" class="feature-neo feature-neo-link h-100 d-block reveal-block" data-tilt-card>
                    @endauth
                        <span class="feature-chip">{{ __('ui.home.ai') }}</span>
                        <div class="feature-icon"><i class="fas fa-user-md"></i></div>
                        <h5>{{ __('ui.home.ai_health_assistant') }}</h5>
                        <p>{{ __('ui.home.ai_desc') }}</p>
                        <small class="feature-meta"><i class="fas fa-shield-alt me-1"></i>{{ __('ui.home.ai_safety') }}</small>
                    @auth
                        </button>
                    @else
                        </a>
                    @endauth
                </div>

                <div class="col-md-6 col-xl-3">
                    <a href="{{ $healthLogsUrl }}" class="feature-neo feature-neo-link h-100 d-block reveal-block" data-tilt-card>
                        <span class="feature-chip">{{ __('ui.home.records') }}</span>
                        <div class="feature-icon"><i class="fas fa-folder-open"></i></div>
                        <h5>{{ __('ui.home.medical_records') }}</h5>
                        <p>{{ __('ui.home.medical_records_desc') }}</p>
                        <small class="feature-meta"><i class="fas fa-file-medical-alt me-1"></i>{{ number_format($homeStats['total_uploads']) }} {{ __('ui.home.uploads_stored') }}</small>
                    </a>
                </div>

                <div class="col-md-6 col-xl-3">
                    <a href="{{ $healthSymptomsUrl }}" class="feature-neo feature-neo-link h-100 d-block reveal-block" data-tilt-card>
                        <span class="feature-chip">{{ __('ui.home.symptoms') }}</span>
                        <div class="feature-icon"><i class="fas fa-stethoscope"></i></div>
                        <h5>{{ __('ui.home.symptom_tracker') }}</h5>
                        <p>{{ __('ui.home.symptom_desc') }}</p>
                        <small class="feature-meta"><i class="fas fa-notes-medical me-1"></i>{{ __('ui.home.timeline_health_notes') }}</small>
                    </a>
                </div>

                <div class="col-md-6 col-xl-3">
                    <a href="{{ $healthSuggestionsUrl }}" class="feature-neo feature-neo-link h-100 d-block reveal-block" data-tilt-card>
                        <span class="feature-chip">{{ __('ui.home.insights') }}</span>
                        <div class="feature-icon"><i class="fas fa-compass"></i></div>
                        <h5>{{ __('ui.home.smart_suggestions') }}</h5>
                        <p>{{ __('ui.home.suggestions_desc') }}</p>
                        <small class="feature-meta"><i class="fas fa-brain me-1"></i>{{ __('ui.home.data_driven_suggestions') }}</small>
                    </a>
                </div>

                <div class="col-md-6 col-xl-3">
                    <a href="{{ $healthTipsUrl }}" class="feature-neo feature-neo-link h-100 d-block reveal-block" data-tilt-card>
                        <span class="feature-chip">{{ __('ui.home.help') }}</span>
                        <div class="feature-icon"><i class="fas fa-life-ring"></i></div>
                        <h5>{{ __('ui.home.help_support') }}</h5>
                        <p>{{ __('ui.home.help_desc') }}</p>
                        <small class="feature-meta"><i class="fas fa-life-ring me-1"></i>{{ __('ui.home.get_help') }}</small>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="py-4 py-md-5 journey-zone">
        <div class="container">
            <div class="section-head reveal-block mb-4 mb-md-5">
                <span class="section-kicker">{{ __('ui.home.how_it_works') }}</span>
                <h2 class="section-title">{{ __('ui.home.wellness_loop') }}</h2>
            </div>
            <div class="row g-3 g-md-4 journey-grid">
                <div class="col-md-6 col-xl-3 reveal-block journey-step">
                    <div class="journey-card h-100">
                        <div class="journey-badge journey-badge-profile" aria-hidden="true">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <span class="journey-num">01</span>
                        <h6>{{ __('ui.home.create_profile') }}</h6>
                        <p>{{ __('ui.home.create_profile_desc') }}</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 reveal-block journey-step">
                    <div class="journey-card h-100">
                        <div class="journey-badge journey-badge-track" aria-hidden="true">
                            <i class="fas fa-wave-square"></i>
                        </div>
                        <span class="journey-num">02</span>
                        <h6>{{ __('ui.home.track_daily_data') }}</h6>
                        <p>{{ __('ui.home.track_daily_data_desc') }}</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 reveal-block journey-step">
                    <div class="journey-card h-100">
                        <div class="journey-badge journey-badge-insights" aria-hidden="true">
                            <i class="fas fa-brain"></i>
                        </div>
                        <span class="journey-num">03</span>
                        <h6>{{ __('ui.home.review_insights') }}</h6>
                        <p>{{ __('ui.home.review_insights_desc') }}</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 reveal-block journey-step">
                    <div class="journey-card h-100">
                        <div class="journey-badge journey-badge-consistency" aria-hidden="true">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <span class="journey-num">04</span>
                        <h6>{{ __('ui.home.improve_consistency') }}</h6>
                        <p>{{ __('ui.home.improve_consistency_desc') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-4 py-md-5">
        <div class="container">
            <div class="cta-panel text-center reveal-block">
                <h2 class="mb-3">{{ __('ui.home.cta_title') }}</h2>
                <p class="mb-4">{{ __('ui.home.cta_subtitle') }}</p>
                <div class="d-flex flex-wrap justify-content-center gap-2 gap-md-3">
                    <a href="{{ $ctaPrimaryUrl }}" class="btn btn-light rounded-pill px-4 px-md-5 py-2 py-md-3">
                        <i class="fas fa-tachometer-alt me-2"></i>{{ $isAuthenticated ? __('ui.home.go_to_dashboard') : __('ui.home.login_to_continue') }}
                    </a>
                    <a href="{{ $ctaSecondaryUrl }}" class="btn btn-outline-light rounded-pill px-4 px-md-5 py-2 py-md-3">
                        <i class="fas fa-users me-2"></i>{{ $isAuthenticated ? __('ui.home.open_community') : __('ui.home.login_for_community') }}
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    /* YOUR ORIGINAL STYLES - KEPT EXACTLY AS IS */
    .home-next {
        /* use same purple gradient family as auth pages for accents */
        --admin-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --tone-bg: #ffffff; /* make background light */
        --tone-surface: #ffffff;
        --tone-main: #4f46e5;
        --tone-main-dark: #4b306a;
        --tone-accent: #7c3aed;
        --tone-text: #2b2a33;
        --tone-muted: #6b7280;
        --tone-border: rgba(111, 66, 193, 0.08);
        background: var(--tone-bg);
        color: var(--tone-text);
        min-height: 100vh;
        width: 100%;
        position: relative;
        isolation: isolate;
    }

    .home-next::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 14% 12%, rgba(255, 255, 255, 0.22), transparent 34%),
            radial-gradient(circle at 86% 88%, rgba(255, 255, 255, 0.18), transparent 34%);
        pointer-events: none;
        z-index: -1;
    }

    .hero-shell {
        overflow: hidden;
    }

    .hero-panel {
        position: relative;
        border-radius: 32px;
        padding: 2rem 1.25rem;
        background: var(--tone-surface);
        color: var(--tone-text);
        box-shadow: 0 18px 40px rgba(75, 50, 120, 0.06);
        border: 1px solid var(--tone-border);
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
        background: rgba(192, 132, 252, 0.46);
        top: -14px;
        left: 22px;
    }

    .orb-b {
        width: 190px;
        height: 190px;
        background: rgba(168, 85, 247, 0.34);
        top: 20%;
        right: -30px;
        animation-delay: 1.4s;
    }

    .orb-c {
        width: 120px;
        height: 120px;
        background: rgba(129, 140, 248, 0.33);
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
        background: rgba(118, 75, 162, 0.06);
        border: 1px solid rgba(118, 75, 162, 0.12);
        border-radius: 999px;
        padding: 0.45rem 0.85rem;
        color: var(--tone-main-dark);
    }

    .hero-kicker i {
        font-size: 0.48rem;
        color: var(--tone-main);
    }

    .hero-title {
        font-size: clamp(2rem, 3.8vw, 3.25rem);
        font-weight: 800;
        line-height: 1.08;
        letter-spacing: -0.02em;
    }

    .hero-subtitle {
        color: var(--tone-main-dark);
        max-width: 58ch;
        font-size: 1rem;
    }

    .btn-teal {
        background: var(--admin-gradient);
        border: none;
        color: #ffffff;
        font-weight: 700;
        box-shadow: 0 10px 24px rgba(75, 0, 130, 0.35);
    }

    .btn-teal:hover {
        color: #ffffff;
        transform: translateY(-1px);
    }

    .btn-outline-teal {
        border: 1px solid rgba(255, 255, 255, 0.86);
        color: #ffffff;
        font-weight: 600;
    }

    .btn-outline-teal:hover {
        color: #4b0082;
        background: #ffffff;
        border-color: #ffffff;
    }

    .hero-inline-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.6rem;
    }

    .hero-inline-stat {
        background: rgba(250, 250, 252, 1);
        border: 1px solid var(--tone-border);
        border-radius: 14px;
        padding: 0.75rem 0.8rem;
    }

    .hero-inline-value {
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--tone-main);
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
        color: #2f1a57;
        border-radius: 20px;
        padding: 1.1rem;
        border: 1px solid rgba(255, 255, 255, 0.8);
        box-shadow: 0 14px 28px rgba(75, 29, 149, 0.2);
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
        color: #6d28d9;
        background: rgba(168, 85, 247, 0.18);
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
        border: 1px solid rgba(109, 40, 217, 0.16);
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
        color: #6d28d9;
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
        background: conic-gradient(#7c3aed calc(var(--adherence) * 1%), #ede9fe 0);
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
        color: #6d28d9;
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
        color: #432b74;
        font-size: 0.86rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 8px 18px rgba(109, 40, 217, 0.1);
    }

    .ticker-item i {
        color: #6d28d9;
    }

    .ticker-item strong {
        color: #4c1d95;
    }

    .section-head {
        text-align: center;
    }

    .section-kicker {
        display: inline-block;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.09rem;
        color: #7c3aed;
        font-weight: 700;
    }

    .section-title {
        margin-top: 0.45rem;
        margin-bottom: 0;
        font-size: clamp(1.5rem, 3.2vw, 2.25rem);
        font-weight: 800;
        color: #3d1d73;
    }

    .feature-neo {
        position: relative;
        padding: 1rem 1rem 0.95rem;
        border-radius: 18px;
        background: linear-gradient(165deg, rgba(255, 255, 255, 0.99), rgba(250, 245, 255, 0.96));
        border: 1px solid rgba(109, 40, 217, 0.16);
        box-shadow: 0 12px 30px rgba(109, 40, 217, 0.1);
        overflow: hidden;
        transform-style: preserve-3d;
        transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
    }

    .feature-neo::after {
        content: "";
        position: absolute;
        inset: auto -10% -35% -10%;
        height: 110px;
        background: radial-gradient(circle, rgba(168, 85, 247, 0.2), rgba(168, 85, 247, 0));
        transition: opacity 220ms ease;
        opacity: 0;
        pointer-events: none;
    }

    .feature-neo:hover,
    .feature-neo:focus-visible {
        transform: translateY(-5px);
        box-shadow: 0 18px 36px rgba(109, 40, 217, 0.18);
        border-color: rgba(109, 40, 217, 0.35);
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
        outline: 3px solid rgba(109, 40, 217, 0.28);
        outline-offset: 2px;
    }

    .feature-chip {
        display: inline-block;
        margin-bottom: 0.62rem;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.08rem;
        color: #6d28d9;
        font-weight: 700;
        background: rgba(109, 40, 217, 0.11);
        border-radius: 999px;
        padding: 0.25rem 0.6rem;
    }

    .feature-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        background: var(--admin-gradient);
        color: #ffffff;
        margin-bottom: 0.8rem;
        box-shadow: 0 8px 18px rgba(168, 85, 247, 0.28);
    }

    .feature-icon-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 8px;
        display: block;
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
        color: #6d28d9;
        font-size: 0.76rem;
        font-weight: 600;
    }

    .journey-zone {
        background: linear-gradient(180deg, rgba(243, 232, 255, 0.72), rgba(248, 245, 255, 0.72));
    }

    .journey-grid {
        position: relative;
    }

    .journey-step {
        position: relative;
    }

    .journey-step::after {
        content: "";
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        right: -40px;
        width: 80px;
        height: 10px;
        border-radius: 6px;
        background: linear-gradient(90deg, rgba(118,75,162,0.12), rgba(118,75,162,0.95), rgba(102,126,234,0.12));
        box-shadow: 0 6px 18px rgba(118,75,162,0.12);
    }

    .journey-step:last-child::after {
        display: none;
    }

    .journey-card {
        background: transparent; /* remove square background */
        border: none;
        border-radius: 22px;
        padding: 1.15rem 0.75rem;
        box-shadow: none;
        transition: transform 220ms ease, box-shadow 220ms ease;
        text-align: center;
    }

    .journey-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 30px rgba(109, 40, 217, 0.16);
    }

    .journey-badge {
        width: 132px;
        height: 132px;
        margin: 0 auto 0.9rem;
        border-radius: 50%;
        display: grid;
        place-items: center;
        color: #ffffff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 22px 44px rgba(109, 40, 217, 0.28);
        border: 4px solid rgba(255,255,255,0.06);
    }

    .journey-badge::before {
        content: "";
        position: absolute;
        inset: 7px;
        border-radius: 50%;
        border: 1px solid rgba(255, 255, 255, 0.45);
    }

    .journey-badge i {
        position: relative;
        z-index: 1;
        font-size: 2.4rem;
    }

    .journey-badge-profile {
        background: radial-gradient(circle at 30% 20%, #8b5cf6, #667eea 52%, #4f46e5 100%);
    }

    .journey-badge-track {
        background: radial-gradient(circle at 30% 20%, #a855f7, #7c3aed 52%, #5b21b6 100%);
    }

    .journey-badge-insights {
        background: radial-gradient(circle at 30% 20%, #7c3aed, #6366f1 52%, #4f46e5 100%);
    }

    .journey-badge-consistency {
        background: radial-gradient(circle at 30% 20%, #667eea, #764ba2 52%, #5b21b6 100%);
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
        background: var(--admin-gradient);
        margin-bottom: 0.7rem;
    }

    .journey-card h6 {
        font-size: 0.98rem;
        margin-bottom: 0.3rem;
        color: var(--tone-main-dark);
        font-weight: 700;
    }

    .journey-card p {
        margin: 0;
        font-size: 0.85rem;
        color: #5f5872;
    }

    .cta-panel {
        background: var(--admin-gradient);
        border-radius: 26px;
        padding: clamp(1.4rem, 4vw, 2.6rem);
        color: #f7f3ff;
        box-shadow: 0 22px 44px rgba(76, 29, 149, 0.26);
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
        .journey-step::after {
            display: none;
        }

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