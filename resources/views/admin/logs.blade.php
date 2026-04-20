@extends('layouts.app')

@section('title', __('ui.admin_activity_logs.title'))

@push('styles')
<style>
    .logs-surface {
        min-height: 100vh;
        padding: 2rem 0 3rem;
        background:
            radial-gradient(circle at 6% 10%, rgba(30, 64, 175, 0.18), transparent 34%),
            radial-gradient(circle at 95% 4%, rgba(14, 165, 233, 0.18), transparent 30%),
            linear-gradient(160deg, #eff6ff 0%, #f8fbff 46%, #edf4ff 100%);
    }

    .logs-hero {
        border-radius: 24px;
        margin-bottom: 1rem;
        padding: 1.65rem;
        color: #fff;
        background: linear-gradient(135deg, #0f3ea7 0%, #1d4ed8 45%, #0ea5e9 100%);
        box-shadow: 0 20px 48px rgba(15, 62, 167, 0.24);
    }

    .logs-hero h1 {
        margin: 0;
        font-size: 1.7rem;
        font-weight: 800;
    }

    .logs-hero p {
        margin: 0.45rem 0 0;
        opacity: 0.92;
    }

    .logs-shell {
        background: #fff;
        border-radius: 20px;
        border: 1px solid rgba(30, 64, 175, 0.1);
        box-shadow: 0 12px 34px rgba(15, 33, 87, 0.08);
        overflow: hidden;
    }

    .logs-head {
        padding: 1rem;
        border-bottom: 1px solid rgba(30, 64, 175, 0.11);
        background: linear-gradient(180deg, #fff 0%, #f7faff 100%);
    }

    .logs-filter {
        display: grid;
        grid-template-columns: 1fr auto auto auto;
        gap: 0.6rem;
    }

    .logs-filter input,
    .logs-filter select {
        min-height: 44px;
        border-radius: 12px;
        border: 1px solid rgba(30, 64, 175, 0.2);
        padding: 0.5rem 0.75rem;
        background: #fff;
    }

    .btn-main,
    .btn-soft {
        min-height: 44px;
        border: 0;
        border-radius: 12px;
        font-weight: 700;
        padding: 0.5rem 0.95rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-main {
        color: #fff;
        background: linear-gradient(135deg, #1d4ed8, #0ea5e9);
    }

    .btn-soft {
        color: #17356c;
        background: #e9f2ff;
    }

    .logs-table-wrap {
        padding: 0.85rem;
    }

    .logs-table {
        width: 100%;
        border-collapse: collapse;
    }

    .logs-table th {
        background: #f4f7ff;
        color: #5e739c;
        padding: 0.75rem;
        font-size: 0.74rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        border-bottom: 1px solid rgba(30, 64, 175, 0.11);
        white-space: nowrap;
    }

    .logs-table td {
        padding: 0.78rem;
        border-bottom: 1px solid rgba(30, 64, 175, 0.08);
        vertical-align: top;
        font-size: 0.9rem;
    }

    .logs-table tr:hover td {
        background: #f9fbff;
    }

    .pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.3rem 0.68rem;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .pill-category {
        background: rgba(14, 165, 233, 0.15);
        color: #0c4a6e;
    }

    .pill-action {
        background: rgba(29, 78, 216, 0.13);
        color: #1e3a8a;
    }

    .muted {
        color: #7183a7;
        font-size: 0.82rem;
    }

    .pagination-wrap {
        padding: 0.4rem 1rem 1.15rem;
    }

    @media (max-width: 991px) {
        .logs-filter {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="logs-surface">
    <div class="container" style="max-width: 1300px;">
        <section class="logs-hero">
            <h1><i class="fas fa-chart-line me-2"></i>{{ __('ui.admin_activity_logs.title') }}</h1>
            <p>{{ __('ui.admin_activity_logs.subtitle') }}</p>
        </section>

        <section class="logs-shell">
            <div class="logs-head">
                <form class="logs-filter" method="GET" action="{{ route('admin.logs.index') }}">
                    <input type="text" name="q" value="{{ $search }}" placeholder="{{ __('ui.admin_activity_logs.search_placeholder') }}">

                    <select name="category">
                        <option value="all">{{ __('ui.admin_activity_logs.all_categories') }}</option>
                        @foreach ($categories as $item)
                            <option value="{{ $item }}" @selected($category === $item)>{{ ucfirst($item) }}</option>
                        @endforeach
                    </select>

                    <select name="sort">
                        <option value="recent" @selected($sort === 'recent')>{{ __('ui.admin_activity_logs.sort_recent') }}</option>
                        <option value="category" @selected($sort === 'category')>{{ __('ui.admin_activity_logs.sort_category') }}</option>
                        <option value="oldest" @selected($sort === 'oldest')>{{ __('ui.admin_activity_logs.sort_oldest') }}</option>
                    </select>

                    <button class="btn-main" type="submit"><i class="fas fa-filter me-1"></i>{{ __('ui.admin_activity_logs.apply') }}</button>
                </form>
            </div>

            <div class="logs-table-wrap table-responsive">
                <table class="logs-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.admin_activity_logs.when') }}</th>
                            <th>{{ __('ui.admin_activity_logs.user') }}</th>
                            <th>{{ __('ui.admin_activity_logs.category') }}</th>
                            <th>{{ __('ui.admin_activity_logs.action') }}</th>
                            <th>{{ __('ui.admin_activity_logs.reference') }}</th>
                            <th>{{ __('ui.admin_activity_logs.details') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>
                                    <div>{{ optional($log->updated_at)->format('M d, Y h:i A') }}</div>
                                    <div class="muted">{{ $log->method ?? '-' }} | {{ $log->ip_address ?? '-' }}</div>
                                </td>
                                <td>
                                    <div>{{ $log->user?->name ?? __('ui.admin_activity_logs.system') }}</div>
                                    <div class="muted">{{ $log->user?->email ?? '-' }}</div>
                                </td>
                                <td><span class="pill pill-category">{{ ucfirst($log->category) }}</span></td>
                                <td><span class="pill pill-action">{{ $log->action }}</span></td>
                                <td>
                                    <div>{{ class_basename((string) ($log->subject_type ?? '-')) }}</div>
                                    <div class="muted">ID: {{ $log->subject_id ?? '-' }}</div>
                                </td>
                                <td>
                                    <div>{{ $log->description ?? '-' }}</div>
                                    <div class="muted">{{ $log->route_name ?? '-' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">{{ __('ui.admin_activity_logs.empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrap d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                <div class="muted">
                    {{ __('ui.admin_activity_logs.showing') }} {{ $logs->firstItem() ?? 0 }} {{ __('ui.admin_activity_logs.to') }} {{ $logs->lastItem() ?? 0 }} {{ __('ui.admin_activity_logs.of') }} {{ $logs->total() }}
                </div>
                <div>
                    {{ $logs->onEachSide(1)->links() }}
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
