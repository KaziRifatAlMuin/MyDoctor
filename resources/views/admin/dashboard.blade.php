@extends('layouts.app')

@section('title', __('ui.admin_dashboard.title'))

@push('styles')
<style>
    .admin-hub-surface {
        min-height: 100vh;
        padding: 2rem 0 4rem;
        background:
            radial-gradient(circle at 15% 15%, rgba(37, 99, 235, 0.24), transparent 36%),
            radial-gradient(circle at 85% 18%, rgba(14, 165, 233, 0.22), transparent 32%),
            radial-gradient(circle at 50% 88%, rgba(30, 64, 175, 0.2), transparent 36%),
            linear-gradient(160deg, #eef4ff 0%, #f8fbff 45%, #edf3ff 100%);
    }

    .hub-hero {
        border-radius: 26px;
        padding: 2rem;
        color: #fff;
        margin-bottom: 1.4rem;
        background: linear-gradient(135deg, #123fbb 0%, #1f5fd1 52%, #0ea5e9 100%);
        box-shadow: 0 20px 54px rgba(18, 63, 187, 0.25);
        position: relative;
        overflow: hidden;
    }

    .hub-hero::before {
        content: '';
        position: absolute;
        width: 340px;
        height: 340px;
        border-radius: 50%;
        right: -90px;
        top: -120px;
        background: rgba(255, 255, 255, 0.1);
    }

    .hub-hero-title {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 800;
        position: relative;
        z-index: 1;
    }

    .hub-hero-sub {
        margin: 0.45rem 0 0;
        opacity: 0.92;
        position: relative;
        z-index: 1;
    }

    .hub-pill-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.75rem;
        margin-top: 1.2rem;
        position: relative;
        z-index: 1;
    }

    .hub-pill {
        border: 1px solid rgba(255, 255, 255, 0.24);
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.12);
        padding: 0.65rem 0.9rem;
    }

    .hub-pill strong {
        display: block;
        line-height: 1.1;
        font-size: 1.25rem;
    }

    .hub-card,
    .hub-block {
        background: #fff;
        border-radius: 22px;
        border: 1px solid rgba(30, 64, 175, 0.11);
        box-shadow: 0 12px 32px rgba(15, 33, 87, 0.08);
    }

    .nav-card {
        display: block;
        text-decoration: none;
        color: inherit;
        padding: 1.25rem;
        height: 100%;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .nav-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 34px rgba(15, 33, 87, 0.12);
    }

    .nav-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        margin-bottom: 0.8rem;
        font-size: 1.1rem;
    }

    .accent-users .nav-icon { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
    .accent-diseases .nav-icon { background: linear-gradient(135deg, #0f766e, #14b8a6); }
    .accent-symptoms .nav-icon { background: linear-gradient(135deg, #c2410c, #f97316); }
    .accent-public .nav-icon { background: linear-gradient(135deg, #7c3aed, #0ea5e9); }

    .nav-title {
        margin: 0;
        font-size: 1.04rem;
        font-weight: 800;
        color: #112445;
    }

    .nav-copy {
        margin: 0.5rem 0 0;
        font-size: 0.9rem;
        color: #627396;
    }

    .hub-block-head {
        padding: 1rem 1.2rem;
        border-bottom: 1px solid rgba(30, 64, 175, 0.1);
    }

    .hub-block-head h2 {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        color: #132a53;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.85rem;
        padding: 1rem 1.2rem 1.2rem;
    }

    .stat-item {
        border: 1px solid rgba(30, 64, 175, 0.12);
        border-radius: 14px;
        padding: 0.75rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }

    .stat-item small {
        color: #6b7da2;
        display: block;
    }

    .stat-item strong {
        color: #17356c;
        font-size: 1.2rem;
        line-height: 1.1;
    }

    .table-shell {
        padding: 1rem 1.2rem 1.2rem;
    }

    .table-hub {
        width: 100%;
        border-collapse: collapse;
    }

    .table-hub th {
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-size: 0.72rem;
        color: #678;
        background: #f3f7ff;
        padding: 0.7rem;
    }

    .table-hub td {
        padding: 0.72rem;
        border-bottom: 1px solid rgba(30, 64, 175, 0.09);
        color: #243c68;
    }

    .table-hub tr:last-child td {
        border-bottom: 0;
    }

    .activity-list {
        list-style: none;
        margin: 0;
        padding: 0.7rem 1.2rem 1.2rem;
    }

    .activity-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.6rem 0;
        border-bottom: 1px dashed rgba(30, 64, 175, 0.14);
    }

    .activity-item:last-child {
        border-bottom: 0;
    }

    .activity-icon {
        width: 30px;
        height: 30px;
        border-radius: 10px;
        background: #e9f1ff;
        color: #1d4ed8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .activity-copy {
        font-size: 0.9rem;
        color: #1f3765;
    }

    .activity-time {
        display: block;
        color: #7488b0;
        font-size: 0.8rem;
        margin-top: 0.1rem;
    }

    @media (max-width: 991px) {
        .hub-pill-grid,
        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575px) {
        .hub-pill-grid,
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="admin-hub-surface">
    <div class="container" style="max-width: 1240px;">
        <div class="hub-hero">
            <h1 class="hub-hero-title"><i class="fas fa-chart-line me-2"></i>{{ __('ui.admin_dashboard.title') }}</h1>
            <p class="hub-hero-sub">{{ __('ui.admin_dashboard.subtitle') }}</p>
            <div class="hub-pill-grid">
                <div class="hub-pill">
                    <small>{{ __('ui.admin_dashboard.total_users') }}</small>
                    <strong>{{ number_format($stats['users']['total'] ?? 0) }}</strong>
                </div>
                <div class="hub-pill">
                    <small>{{ __('ui.admin_dashboard.active_reminders') }}</small>
                    <strong>{{ number_format($stats['operations']['active_reminders'] ?? 0) }}</strong>
                </div>
                <div class="hub-pill">
                    <small>{{ __('ui.admin_dashboard.community_posts') }}</small>
                    <strong>{{ number_format($stats['community']['posts'] ?? 0) }}</strong>
                </div>
                <div class="hub-pill" style="background: rgba(255, 255, 255, 0.2); border-color: rgba(255, 208, 0, 0.45);">
                    <small>{{ __('ui.admin_dashboard.pending_posts') }}</small>
                    <strong>{{ number_format($stats['community']['pending_posts'] ?? 0) }}</strong>
                </div>
                <div class="hub-pill">
                    <small>{{ __('ui.admin_dashboard.health_metrics') }}</small>
                    <strong>{{ number_format($stats['medical']['health_metrics'] ?? 0) }}</strong>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            @foreach ($navigationCards as $card)
                <div class="col-sm-6 col-lg-4">
                    <div class="hub-card {{ $card['accent'] }}">
                        <a href="{{ $card['route'] }}" class="nav-card">
                            <span class="nav-icon"><i class="fas {{ $card['icon'] }}"></i></span>
                            <h2 class="nav-title">{{ $card['title'] }}</h2>
                            <p class="nav-copy">{{ $card['description'] }}</p>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <section class="hub-block h-100">
                    <div class="hub-block-head">
                        <h2><i class="fas fa-users me-2"></i>{{ __('ui.admin_dashboard.user_statistics') }}</h2>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-item"><small>{{ __('ui.admin_dashboard.admins') }}</small><strong>{{ number_format($stats['users']['admins'] ?? 0) }}</strong></div>
                        <div class="stat-item"><small>{{ __('ui.admin_dashboard.members') }}</small><strong>{{ number_format($stats['users']['members'] ?? 0) }}</strong></div>
                        <div class="stat-item"><small>{{ __('ui.admin_dashboard.joined_today') }}</small><strong>{{ number_format($stats['users']['new_today'] ?? 0) }}</strong></div>
                        <div class="stat-item"><small>{{ __('ui.admin_dashboard.joined_this_week') }}</small><strong>{{ number_format($stats['users']['new_this_week'] ?? 0) }}</strong></div>
                    </div>
                </section>
            </div>
            <div class="col-lg-6">
                <section class="hub-block h-100">
                    <div class="hub-block-head">
                        <h2><i class="fas fa-comments me-2"></i>{{ __('ui.admin_dashboard.community_statistics') }}</h2>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-item"><small>{{ __('ui.admin_dashboard.posts') }}</small><strong>{{ number_format($stats['community']['posts'] ?? 0) }}</strong></div>
                        <div class="stat-item"><small>{{ __('ui.admin_dashboard.pending_posts_stat') }}</small><strong style="color:#b45309;">{{ number_format($stats['community']['pending_posts'] ?? 0) }}</strong></div>
                        <div class="stat-item"><small>{{ __('ui.admin_dashboard.approved_posts') }}</small><strong>{{ number_format($stats['community']['approved_posts'] ?? 0) }}</strong></div>
                        <div class="stat-item"><small>{{ __('ui.admin_dashboard.approved_today') }}</small><strong>{{ number_format($stats['community']['approved_today'] ?? 0) }}</strong></div>
                        <div class="stat-item"><small>{{ __('ui.admin_dashboard.comments') }}</small><strong>{{ number_format($stats['community']['comments'] ?? 0) }}</strong></div>
                        <div class="stat-item"><small>{{ __('ui.admin_dashboard.post_likes') }}</small><strong>{{ number_format($stats['community']['post_likes'] ?? 0) }}</strong></div>
                        <div class="stat-item"><small>{{ __('ui.admin_dashboard.comment_likes') }}</small><strong>{{ number_format($stats['community']['comment_likes'] ?? 0) }}</strong></div>
                    </div>
                </section>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12">
                <section class="hub-block" style="border-left: 6px solid #f59e0b;">
                    <div class="hub-block-head d-flex justify-content-between align-items-center">
                        <h2><i class="fas fa-hourglass-half me-2"></i>{{ __('ui.admin_dashboard.pending_moderation_queue') }}</h2>
                        <a href="{{ route('admin.community.posts.pending') }}" class="btn btn-sm btn-outline-warning rounded-pill">
                            <i class="fas fa-list me-1"></i>{{ __('ui.admin_dashboard.view_all_pending') }}
                        </a>
                    </div>
                    <div class="table-shell table-responsive">
                        @if($pendingPosts->isEmpty())
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-check-circle me-2"></i>{{ __('ui.admin_dashboard.no_pending_posts') }}
                            </div>
                        @else
                            <table class="table-hub">
                                <thead>
                                    <tr>
                                        <th>{{ __('ui.admin_dashboard.author') }}</th>
                                        <th>{{ __('ui.admin_dashboard.disease') }}</th>
                                        <th>{{ __('ui.admin_dashboard.preview') }}</th>
                                        <th>{{ __('ui.admin_dashboard.submitted') }}</th>
                                        <th>{{ __('ui.admin_dashboard.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingPosts as $pendingPost)
                                        <tr style="background: linear-gradient(90deg, rgba(245, 158, 11, 0.08) 0%, rgba(255, 255, 255, 1) 28%);">
                                            <td>{{ $pendingPost->is_anonymous ? __('ui.admin_dashboard.anonymous_member') : ($pendingPost->user->name ?? __('ui.admin_dashboard.unknown')) }}</td>
                                            <td>{{ $pendingPost->disease->disease_name ?? __('ui.admin_dashboard.general') }}</td>
                                            <td>{{ \Illuminate\Support\Str::limit($pendingPost->description, 80) }}</td>
                                            <td>{{ $pendingPost->created_at->diffForHumans() }}</td>
                                            <td>
                                                <a href="{{ route('community.post.show', $pendingPost) }}" class="btn btn-sm btn-outline-primary">
                                                    {{ __('ui.admin_dashboard.review') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </section>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-8">
                <section class="hub-block h-100">
                    <div class="hub-block-head">
                        <h2><i class="fas fa-heart-pulse me-2"></i>{{ __('ui.admin_dashboard.medical_engagement_statistics') }}</h2>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-item"><small>{{ __('ui.admin_dashboard.medicines') }}</small><strong>{{ number_format($stats['medical']['medicines'] ?? 0) }}</strong></div>
                        <div class="stat-item"><small>{{ __('ui.admin_dashboard.medicine_logs') }}</small><strong>{{ number_format($stats['medical']['medicine_logs'] ?? 0) }}</strong></div>
                        <div class="stat-item"><small>{{ __('ui.admin_dashboard.health_metrics_stat') }}</small><strong>{{ number_format($stats['medical']['health_metrics'] ?? 0) }}</strong></div>
                        <div class="stat-item"><small>{{ __('ui.admin_dashboard.user_symptoms') }}</small><strong>{{ number_format($stats['medical']['user_symptoms'] ?? 0) }}</strong></div>
                        <div class="stat-item"><small>{{ __('ui.admin_dashboard.user_diseases') }}</small><strong>{{ number_format($stats['medical']['user_diseases'] ?? 0) }}</strong></div>
                        <div class="stat-item"><small>{{ __('ui.admin_dashboard.disease_catalog') }}</small><strong>{{ number_format($stats['medical']['reference_diseases'] ?? 0) }}</strong></div>
                        <div class="stat-item"><small>{{ __('ui.admin_dashboard.symptom_catalog') }}</small><strong>{{ number_format($stats['medical']['reference_symptoms'] ?? 0) }}</strong></div>
                        <div class="stat-item"><small>{{ __('ui.admin_dashboard.notifications') }}</small><strong>{{ number_format($stats['engagement']['notifications'] ?? 0) }}</strong></div>
                        <div class="stat-item"><small>{{ __('ui.admin_dashboard.mailings') }}</small><strong>{{ number_format($stats['engagement']['mailings'] ?? 0) }}</strong></div>
                        <div class="stat-item"><small>{{ __('ui.admin_dashboard.uploads') }}</small><strong>{{ number_format($stats['engagement']['uploads'] ?? 0) }}</strong></div>
                        <div class="stat-item"><small>{{ __('ui.admin_dashboard.new_logs_7d') }}</small><strong>{{ number_format($stats['operations']['new_logs_this_week'] ?? 0) }}</strong></div>
                    </div>
                </section>
            </div>
            <div class="col-lg-4">
                <section class="hub-block h-100">
                    <div class="hub-block-head">
                        <h2><i class="fas fa-clock-rotate-left me-2"></i>{{ __('ui.admin_dashboard.recent_activities') }}</h2>
                    </div>
                    <ul class="activity-list">
                        @forelse ($recentActivities as $activity)
                            <li class="activity-item">
                                <span class="activity-icon"><i class="fas {{ $activity['icon'] ?? 'fa-circle' }}"></i></span>
                                <div class="activity-copy">
                                    {{ $activity['message'] }}
                                    <span class="activity-time">{{ $activity['time'] }}</span>
                                </div>
                            </li>
                        @empty
                            <li class="activity-item">
                                <span class="activity-icon"><i class="fas fa-info"></i></span>
                                <div class="activity-copy">{{ __('ui.admin_dashboard.no_recent_activities') }}</div>
                            </li>
                        @endforelse
                    </ul>
                </section>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12">
                <section class="hub-block">
                    <div class="hub-block-head">
                        <h2><i class="fas fa-notes-medical me-2"></i>{{ __('ui.admin_dashboard.latest_records') }}</h2>
                    </div>
                    <div class="table-shell table-responsive">
                        <table class="table-hub">
                            <thead>
                                <tr>
                                    <th>{{ __('ui.admin_dashboard.type') }}</th>
                                    <th>{{ __('ui.admin_dashboard.entry') }}</th>
                                    <th>{{ __('ui.admin_dashboard.user') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($latestDiseaseRecords as $record)
                                    <tr>
                                        <td>{{ __('ui.admin_dashboard.disease_type') }}</td>
                                        <td>{{ $record->disease->disease_name ?? 'N/A' }}</td>
                                        <td>{{ $record->user->name ?? __('ui.admin_dashboard.unknown') }}</td>
                                    </tr>
                                @endforeach
                                @foreach ($latestMedicines as $record)
                                    <tr>
                                        <td>{{ __('ui.admin_dashboard.medicine_type') }}</td>
                                        <td>{{ $record->medicine_name }}</td>
                                        <td>{{ $record->user->name ?? __('ui.admin_dashboard.unknown') }}</td>
                                    </tr>
                                @endforeach
                                @foreach ($latestMetrics as $record)
                                    <tr>
                                        <td>{{ __('ui.admin_dashboard.metric_type') }}</td>
                                        <td>{{ ucfirst($record->metric_type ?? __('ui.admin_dashboard.metric')) }}</td>
                                        <td>{{ $record->user->name ?? __('ui.admin_dashboard.unknown') }}</td>
                                    </tr>
                                @endforeach
                                @if ($latestDiseaseRecords->isEmpty() && $latestMedicines->isEmpty() && $latestMetrics->isEmpty())
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">{{ __('ui.admin_dashboard.no_latest_records') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection