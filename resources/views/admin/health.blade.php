@extends('layouts.app')

@section('title', 'Admin Health Metrics')

@push('styles')
<style>
    .admin-metric-surface {
        min-height: 100vh;
        padding: 2rem 0 4rem;
        background:
            radial-gradient(circle at 86% 14%, rgba(14, 116, 144, 0.24), transparent 35%),
            radial-gradient(circle at 10% 86%, rgba(2, 132, 199, 0.2), transparent 36%),
            linear-gradient(150deg, #eff9ff 0%, #f7fdff 45%, #ecf8ff 100%);
    }

    .metric-hero {
        border-radius: 26px;
        padding: 2rem;
        margin-bottom: 1.2rem;
        color: #fff;
        background: linear-gradient(135deg, #075985 0%, #0284c7 50%, #38bdf8 100%);
        box-shadow: 0 20px 54px rgba(7, 89, 133, 0.24);
    }

    .metric-hero h1 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 800;
    }

    .metric-hero p {
        margin: 0.45rem 0 0;
        opacity: 0.94;
    }

    .metric-toolbar,
    .metric-create,
    .metric-card {
        background: #fff;
        border: 1px solid rgba(7, 89, 133, 0.13);
        border-radius: 20px;
        box-shadow: 0 12px 34px rgba(7, 89, 133, 0.08);
    }

    .metric-toolbar,
    .metric-create {
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .toolbar-grid,
    .create-grid {
        display: grid;
        gap: 0.65rem;
    }

    .toolbar-grid {
        grid-template-columns: 1fr auto auto;
    }

    .create-grid {
        grid-template-columns: 1fr 1fr auto;
    }

    .toolbar-grid input,
    .create-grid input {
        min-height: 44px;
        border: 1px solid rgba(7, 89, 133, 0.23);
        border-radius: 11px;
        padding: 0.58rem 0.85rem;
        width: 100%;
    }

    .btn-main,
    .btn-soft,
    .btn-open,
    .btn-edit,
    .btn-delete {
        min-height: 44px;
        border: 0;
        border-radius: 11px;
        padding: 0.54rem 0.92rem;
        font-size: 0.86rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
    }

    .btn-main { color: #fff; background: linear-gradient(135deg, #0369a1, #0ea5e9); }
    .btn-soft { color: #0c4a6e; background: #e8f6ff; }
    .btn-open { color: #075985; background: rgba(2, 132, 199, 0.13); }
    .btn-edit { color: #fff; background: #0284c7; }
    .btn-delete { color: #fff; background: #dc2626; }

    .metric-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.9rem;
    }

    .metric-card {
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.7rem;
    }

    .metric-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        color: #0c4a6e;
    }

    .metric-fields {
        display: flex;
        flex-wrap: wrap;
        gap: 0.42rem;
    }

    .metric-pill {
        border-radius: 999px;
        padding: 0.24rem 0.64rem;
        background: #f0f9ff;
        color: #0369a1;
        border: 1px solid rgba(2, 132, 199, 0.24);
        font-size: 0.75rem;
        font-weight: 700;
    }

    .metric-meta {
        color: #4a7082;
        font-size: 0.86rem;
    }

    .metric-actions {
        margin-top: auto;
        display: flex;
        gap: 0.45rem;
        flex-wrap: wrap;
    }

    @media (max-width: 1199px) {
        .metric-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 991px) {
        .toolbar-grid,
        .create-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 640px) {
        .metric-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="admin-metric-surface">
    <div class="container" style="max-width:1240px;">
        <section class="metric-hero">
            <h1><i class="fas fa-heartbeat me-2"></i>Admin Health Metrics</h1>
            <p>Create reusable metric definitions with fields, then members can record values against these metrics.</p>
        </section>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="metric-toolbar">
            <form class="toolbar-grid" method="GET" action="{{ route('admin.health.index') }}">
                <input type="text" name="q" value="{{ $search }}" placeholder="Search health metrics...">
                <button class="btn-main" type="submit"><i class="fas fa-magnifying-glass"></i>Search</button>
                <a href="{{ route('admin.dashboard') }}" class="btn-soft"><i class="fas fa-arrow-left"></i>Back</a>
            </form>
        </section>

        <section class="metric-create">
            <form class="create-grid" method="POST" action="{{ route('admin.health.store') }}">
                @csrf
                <input type="text" name="metric_name" placeholder="metric name (e.g. blood_pressure)" required>
                <input type="text" name="fields" placeholder="fields comma-separated (e.g. systolic,diastolic)" required>
                <button class="btn-main" type="submit"><i class="fas fa-plus"></i>Create</button>
            </form>
        </section>

        <div class="metric-grid">
            @forelse ($metrics as $metric)
                <article class="metric-card">
                    <h2 class="metric-title">{{ ucwords(str_replace('_', ' ', $metric->metric_name)) }}</h2>
                    <div class="metric-fields">
                        @foreach ((array) $metric->fields as $field)
                            <span class="metric-pill">{{ $field }}</span>
                        @endforeach
                    </div>
                    <div class="metric-meta">
                        <strong>{{ number_format($metric->user_health_records_count) }}</strong> user records
                    </div>
                    <div class="metric-actions">
                        <a href="{{ route('admin.metrics.show', $metric) }}" class="btn-open"><i class="fas fa-eye"></i>Open</a>
                        <form method="POST" action="{{ route('admin.metrics.destroy', $metric) }}" onsubmit="return confirm('Delete this metric definition?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn-delete" type="submit"><i class="fas fa-trash"></i>Delete</button>
                        </form>
                    </div>
                </article>
            @empty
                <article class="metric-card" style="grid-column:1 / -1; text-align:center;">
                    <p class="mb-0 text-muted">No health metric definitions found.</p>
                </article>
            @endforelse
        </div>

        @if ($metrics->hasPages())
            <div class="mt-3">{{ $metrics->links() }}</div>
        @endif
    </div>
</div>
@endsection
