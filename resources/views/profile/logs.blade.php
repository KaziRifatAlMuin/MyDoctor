@extends('layouts.app')

@section('title', __('ui.profile_activity_logs.title'))

@push('styles')
    <style>
        .activity-page {
            padding: 1.25rem 0 2rem;
        }

        .activity-hero {
            background: linear-gradient(145deg, #ffffff 0%, #f9fbff 100%);
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 18px;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
            padding: 1.2rem 1.25rem;
            margin-bottom: 1rem;
        }

        .activity-hero h1 {
            margin: 0;
            font-weight: 800;
            font-size: 1.4rem;
            color: #0f172a;
        }

        .activity-hero p {
            margin: 0.35rem 0 0;
            color: #475569;
        }

        .activity-filter {
            display: grid;
            grid-template-columns: 1fr 230px;
            gap: 0.65rem;
        }

        .activity-filter input,
        .activity-filter select {
            min-height: 44px;
            border-radius: 12px;
            border: 1px solid rgba(100, 116, 139, 0.35);
            background: #fff;
            padding: 0.5rem 0.75rem;
        }

        .timeline {
            position: relative;
            margin-top: 1rem;
        }

        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 34px;
            width: 2px;
            background: linear-gradient(to bottom, #bfdbfe 0%, #dbeafe 60%, transparent 100%);
        }

        .timeline-item {
            position: relative;
            display: grid;
            grid-template-columns: 68px 1fr;
            gap: 0.65rem;
            margin-bottom: 0.95rem;
        }

        .actor-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #fff;
            font-size: 0.9rem;
            background: linear-gradient(145deg, #2563eb 0%, #0ea5e9 100%);
            border: 3px solid #fff;
            box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.2);
            z-index: 1;
            margin-left: 10px;
        }

        .actor-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .activity-card {
            border-radius: 16px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: #fff;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
            padding: 0.95rem 1rem;
        }

        .activity-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.6rem;
            margin-bottom: 0.35rem;
        }

        .activity-actor,
        .activity-link {
            color: #1d4ed8;
            text-decoration: none;
            font-weight: 700;
        }

        .activity-actor:hover,
        .activity-link:hover {
            color: #1e3a8a;
            text-decoration: underline;
        }

        .activity-time {
            color: #64748b;
            font-size: 0.82rem;
            white-space: nowrap;
        }

        .activity-sentence {
            color: #0f172a;
            line-height: 1.5;
            font-size: 0.94rem;
        }

        .activity-meta {
            margin-top: 0.55rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
        }

        .meta-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.28rem 0.62rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-decoration: none;
            border: 1px solid transparent;
        }

        .meta-pill.category {
            background: #dbeafe;
            color: #1e3a8a;
            border-color: #bfdbfe;
        }

        .meta-pill.action {
            background: #dcfce7;
            color: #166534;
            border-color: #bbf7d0;
        }

        .meta-pill.fields {
            background: #f1f5f9;
            color: #334155;
            border-color: #e2e8f0;
        }

        .pagination-wrap {
            margin-top: 1rem;
            padding: 0.35rem 0;
        }

        .empty-feed {
            border: 1px dashed rgba(100, 116, 139, 0.4);
            border-radius: 14px;
            color: #64748b;
            padding: 2rem 1rem;
            text-align: center;
            background: #fff;
        }

        @media (max-width: 992px) {
            .activity-filter {
                grid-template-columns: 1fr;
            }

            .timeline::before {
                left: 30px;
            }

            .timeline-item {
                grid-template-columns: 60px 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <div class="activity-page container-fluid px-3 px-xl-5">
        <section class="activity-hero">
            <h1><i class="fas fa-stream me-2"></i>{{ __('ui.profile_activity_logs.title') }}</h1>
            <p>{{ __('ui.profile_activity_logs.subtitle') }}</p>

            <form id="activityFilterForm" class="activity-filter mt-3" method="GET" action="{{ route('profile.logs') }}">
                <input id="activitySearchInput" type="text" name="q" value="{{ $search }}"
                    placeholder="{{ __('ui.admin_activity_logs.search_placeholder') }}" autocomplete="off">

                <select id="activityTypeSelect" name="type">
                    @foreach ($activityTypes as $value => $label)
                        <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </section>

        <section class="timeline">
            @forelse ($logs as $log)
                @php
                    $actor = $log->user;
                    $actorName = $actor?->name ?? __('ui.admin_activity_logs.system');
                    $actorUrl = $actor ? route('users.show', $actor) : null;
                    $subjectLabel = $log->subject_label ?? 'system';
                    $subjectUrl = $log->subject_url ?? null;
                    $changedFields = (array) data_get($log->context, 'changed_fields', []);
                    $initial = strtoupper(substr((string) $actorName, 0, 1));
                @endphp

                <article class="timeline-item">
                    <div class="actor-avatar" title="{{ $actorName }}">
                        @if ($actor?->picture)
                            <img src="{{ asset('storage/' . $actor->picture) }}" alt="{{ $actorName }}">
                        @else
                            {{ $initial !== '' ? $initial : 'S' }}
                        @endif
                    </div>

                    <div class="activity-card">
                        <div class="activity-top">
                            <div>
                                @if ($actorUrl)
                                    <a href="{{ $actorUrl }}" class="activity-actor">{{ $actorName }}</a>
                                @else
                                    <span>{{ $actorName }}</span>
                                @endif
                            </div>
                            <div class="activity-time">
                                {{ optional($log->created_at)->format('M d, Y h:i A') }}
                            </div>
                        </div>

                        <div class="activity-sentence">
                            {{ $log->description ?: 'Performed an activity.' }}
                            @if ($subjectUrl)
                                on <a href="{{ $subjectUrl }}" class="activity-link">{{ $subjectLabel }}</a>
                            @elseif($subjectLabel !== 'system')
                                on <strong>{{ $subjectLabel }}</strong>
                            @endif
                        </div>

                        <div class="activity-meta">
                            <a class="meta-pill category"
                                href="{{ route('profile.logs', ['type' => $log->category]) }}">{{ ucfirst($log->category) }}</a>
                            <a class="meta-pill action"
                                href="{{ route('profile.logs', ['q' => $log->action, 'type' => $type]) }}">{{ str_replace('_', ' ', $log->action) }}</a>
                            @if (!empty($changedFields))
                                <span
                                    class="meta-pill fields">{{ implode(', ', array_map(fn($field) => str_replace('_', ' ', (string) $field), $changedFields)) }}</span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="empty-feed">{{ __('ui.admin_activity_logs.empty') }}</div>
            @endforelse
        </section>

        <div class="pagination-wrap d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
            <div class="text-muted small">
                {{ __('ui.admin_activity_logs.showing') }} {{ $logs->firstItem() ?? 0 }}
                {{ __('ui.admin_activity_logs.to') }} {{ $logs->lastItem() ?? 0 }} {{ __('ui.admin_activity_logs.of') }}
                {{ $logs->total() }}
            </div>
            <div>
                {{ $logs->onEachSide(1)->links() }}
            </div>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('activityFilterForm');
            const searchInput = document.getElementById('activitySearchInput');
            const typeSelect = document.getElementById('activityTypeSelect');

            if (!form || !searchInput || !typeSelect) {
                return;
            }

            let debounceTimer;

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    form.submit();
                }, 450);
            });

            typeSelect.addEventListener('change', function() {
                form.submit();
            });
        })();
    </script>
@endsection
