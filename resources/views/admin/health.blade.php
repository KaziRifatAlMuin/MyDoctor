@extends('layouts.app')

@section('title', 'Admin Health Metrics')

@push('styles')
<style>
    .admin-catalog-surface {
        min-height: 100vh;
        padding: 2rem 0 4rem;
        background:
            radial-gradient(circle at 85% 15%, rgba(14, 116, 144, 0.22), transparent 34%),
            radial-gradient(circle at 8% 82%, rgba(2, 132, 199, 0.2), transparent 36%),
            linear-gradient(155deg, #edf9ff 0%, #f8fdff 44%, #eefaff 100%);
    }

    .catalog-hero {
        border-radius: 26px;
        padding: 1.9rem;
        margin-bottom: 1.4rem;
        color: #fff;
        background: linear-gradient(135deg, #075985 0%, #0284c7 52%, #0ea5e9 100%);
        box-shadow: 0 20px 54px rgba(7, 89, 133, 0.24);
    }

    .catalog-hero h1 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 800;
    }

    .catalog-hero p {
        margin: 0.4rem 0 0;
        opacity: 0.93;
    }

    .catalog-toolbar,
    .catalog-panel,
    .item-card {
        background: #fff;
        border: 1px solid rgba(7, 89, 133, 0.12);
        border-radius: 20px;
        box-shadow: 0 12px 32px rgba(7, 89, 133, 0.08);
    }

    .catalog-toolbar {
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .toolbar-grid {
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: 0.65rem;
    }

    .toolbar-grid input,
    .form-input {
        min-height: 44px;
        border: 1px solid rgba(7, 89, 133, 0.24);
        border-radius: 11px;
        padding: 0.6rem 0.84rem;
        width: 100%;
    }

    .btn-main,
    .btn-ghost,
    .btn-edit,
    .btn-delete,
    .btn-open,
    .btn-add-field {
        min-height: 44px;
        border: 0;
        border-radius: 11px;
        padding: 0.55rem 0.9rem;
        font-size: 0.86rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
    }

    .btn-main { color: #fff; background: linear-gradient(135deg, #0369a1, #0ea5e9); }
    .btn-ghost { color: #0c4a6e; background: #e0f4ff; }
    .btn-open { color: #0c4a6e; background: rgba(2, 132, 199, 0.15); }
    .btn-edit { color: #fff; background: #0284c7; }
    .btn-delete { color: #fff; background: #d14343; }
    .btn-add-field { color: #0c4a6e; background: #ecf8ff; min-height: 38px; font-size: 0.8rem; }

    .catalog-panel {
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .create-grid {
        display: grid;
        grid-template-columns: 1fr 1.2fr auto;
        gap: 0.65rem;
    }

    .field-builder {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .field-row {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 0.5rem;
    }

    .field-row .btn-remove-field {
        border: 0;
        border-radius: 9px;
        background: #fee2e2;
        color: #991b1b;
        font-weight: 700;
        padding: 0.35rem 0.6rem;
        min-width: 38px;
    }

    .cards-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.9rem;
    }

    .item-card {
        padding: 1rem;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .item-title {
        margin: 0;
        font-size: 1.02rem;
        font-weight: 800;
        color: #0c4a6e;
    }

    .item-badges {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-top: 0.55rem;
    }

    .item-badge {
        border-radius: 999px;
        padding: 0.25rem 0.62rem;
        background: #f0f9ff;
        color: #0369a1;
        border: 1px solid rgba(2, 132, 199, 0.22);
        font-size: 0.75rem;
        font-weight: 700;
    }

    .item-copy {
        margin-top: 0.65rem;
        color: #456272;
        font-size: 0.88rem;
        flex-grow: 1;
    }

    .item-actions {
        display: flex;
        gap: 0.45rem;
        flex-wrap: wrap;
        margin-top: 0.85rem;
    }

    .edit-box {
        border-top: 1px dashed rgba(7, 89, 133, 0.24);
        margin-top: 0.9rem;
        padding-top: 0.9rem;
        display: none;
    }

    .edit-box.is-open {
        display: block;
    }

    @media (max-width: 1400px) {
        .cards-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991px) {
        .toolbar-grid,
        .create-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .cards-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="admin-catalog-surface">
    <div class="container" style="max-width: 1400px;">
        <div class="catalog-hero">
            <h1><i class="fas fa-heartbeat me-2"></i>Admin Health Metrics Catalog</h1>
            <p>Manage the most important metric definitions. Field labels support units in brackets and are fully editable.</p>
        </div>

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

        <section class="catalog-toolbar">
            <form class="toolbar-grid" method="GET" action="{{ route('admin.health.index') }}">
                <input type="text" name="q" value="{{ $search }}" placeholder="Search health metrics by name...">
                <button class="btn-main" type="submit"><i class="fas fa-magnifying-glass"></i>Search</button>
                <a href="{{ route('admin.dashboard') }}" class="btn-ghost"><i class="fas fa-arrow-left"></i>Back</a>
            </form>
        </section>

        <section class="catalog-panel">
            <form class="create-grid" method="POST" action="{{ route('admin.health.store') }}" id="createMetricForm">
                @csrf
                <input class="form-input" type="text" name="metric_name" placeholder="metric name (e.g. blood_pressure)" required>

                <div class="field-builder" data-field-builder="create">
                    <div class="field-row">
                        <input class="form-input" type="text" name="fields[]" placeholder="Field label (e.g. Heart Rate (bpm))" required>
                    </div>
                </div>

                <div class="d-flex flex-column gap-2">
                    <button class="btn-add-field" type="button" data-add-field="create"><i class="fas fa-plus"></i>Add Field</button>
                    <button class="btn-main" type="submit"><i class="fas fa-plus"></i>Create</button>
                </div>
            </form>
        </section>

        <div class="cards-grid">
            @forelse ($metrics as $metric)
                <article class="item-card">
                    <h2 class="item-title">{{ ucwords(str_replace('_', ' ', $metric->metric_name)) }}</h2>
                    <div class="item-badges">
                        <span class="item-badge">{{ number_format($metric->user_health_records_count) }} user logs</span>
                        <span class="item-badge">{{ count((array) $metric->fields) }} field(s)</span>
                    </div>
                    <p class="item-copy">{{ implode(' | ', (array) $metric->fields) }}</p>
                    <div class="item-actions">
                        <a href="{{ route('admin.metrics.show', $metric) }}" class="btn-open"><i class="fas fa-eye"></i>Open</a>
                        <button type="button" class="btn-edit js-toggle-edit" data-target="metric-edit-{{ $metric->id }}"><i class="fas fa-pen"></i>Edit</button>
                        <form method="POST" action="{{ route('admin.metrics.destroy', $metric) }}" onsubmit="return confirm('Delete this metric definition?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn-delete" type="submit"><i class="fas fa-trash"></i>Delete</button>
                        </form>
                    </div>

                    <div id="metric-edit-{{ $metric->id }}" class="edit-box">
                        <form method="POST" action="{{ route('admin.metrics.update', $metric) }}" class="metric-edit-form" data-metric-edit="{{ $metric->id }}">
                            @csrf
                            @method('PATCH')

                            <div class="mb-2">
                                <input class="form-input" type="text" name="metric_name" value="{{ $metric->metric_name }}" required>
                            </div>

                            <div class="field-builder" data-field-builder="edit-{{ $metric->id }}">
                                @foreach ((array) $metric->fields as $field)
                                    <div class="field-row">
                                        <input class="form-input" type="text" name="fields[]" value="{{ $field }}" required>
                                        <button type="button" class="btn-remove-field" title="Remove field">-</button>
                                    </div>
                                @endforeach
                            </div>

                            <div class="d-flex gap-2 mt-2">
                                <button class="btn-add-field" type="button" data-add-field="edit-{{ $metric->id }}"><i class="fas fa-plus"></i>Add Field</button>
                                <button class="btn-main" type="submit"><i class="fas fa-floppy-disk"></i>Save</button>
                            </div>
                        </form>
                    </div>
                </article>
            @empty
                <div class="item-card" style="grid-column: 1 / -1; text-align: center;">
                    <p class="mb-0 text-muted">No health metrics found for this search.</p>
                </div>
            @endforelse
        </div>

        @if ($metrics->hasPages())
            <div class="mt-3">{{ $metrics->links() }}</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function createFieldRow() {
            const row = document.createElement('div');
            row.className = 'field-row';
            row.innerHTML = '<input class="form-input" type="text" name="fields[]" placeholder="Field label (e.g. Glucose Level (mg/dL))" required><button type="button" class="btn-remove-field" title="Remove field">-</button>';
            return row;
        }

        function bindRemoveButton(container) {
            container.querySelectorAll('.btn-remove-field').forEach(function (button) {
                button.onclick = function () {
                    const rows = container.querySelectorAll('.field-row');
                    if (rows.length <= 1) {
                        return;
                    }
                    button.closest('.field-row').remove();
                };
            });
        }

        document.querySelectorAll('[data-add-field]').forEach(function (button) {
            button.addEventListener('click', function () {
                const key = button.getAttribute('data-add-field');
                const container = document.querySelector('[data-field-builder="' + key + '"]');
                if (!container) {
                    return;
                }
                container.appendChild(createFieldRow());
                bindRemoveButton(container);
            });
        });

        document.querySelectorAll('[data-field-builder]').forEach(function (container) {
            bindRemoveButton(container);
        });

        document.querySelectorAll('.js-toggle-edit').forEach(function (button) {
            button.addEventListener('click', function () {
                const target = document.getElementById(button.getAttribute('data-target'));
                if (target) {
                    target.classList.toggle('is-open');
                }
            });
        });
    });
</script>
@endpush
