@extends('layouts.app')

@section('title', 'Admin Metric Details')

@push('styles')
<style>
    .metric-detail-surface {
        min-height: 100vh;
        padding: 2rem 0 4rem;
        background:
            radial-gradient(circle at 90% 8%, rgba(8, 145, 178, 0.22), transparent 34%),
            radial-gradient(circle at 8% 90%, rgba(6, 182, 212, 0.2), transparent 36%),
            linear-gradient(150deg, #f0fcff 0%, #f8feff 44%, #ecfdff 100%);
    }

    .detail-hero,
    .detail-card {
        border-radius: 22px;
        border: 1px solid rgba(14, 116, 144, 0.16);
        background: #fff;
        box-shadow: 0 14px 38px rgba(14, 116, 144, 0.1);
    }

    .detail-hero {
        padding: 1.4rem;
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
    }

    .detail-hero h1 {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 800;
        color: #0e7490;
    }

    .detail-sub {
        margin-top: 0.4rem;
        color: #4a6c75;
    }

    .pill-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.6rem;
    }

    .field-pill {
        border-radius: 999px;
        padding: 0.24rem 0.62rem;
        background: #ecfeff;
        color: #0e7490;
        border: 1px solid rgba(14, 116, 144, 0.25);
        font-size: 0.75rem;
        font-weight: 700;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 1rem;
    }

    .detail-card {
        padding: 1.1rem;
    }

    .detail-card h2 {
        margin: 0 0 0.8rem;
        font-size: 1.05rem;
        font-weight: 800;
        color: #155e75;
    }

    .form-input {
        width: 100%;
        min-height: 44px;
        border: 1px solid rgba(14, 116, 144, 0.24);
        border-radius: 11px;
        padding: 0.58rem 0.8rem;
        margin-bottom: 0.75rem;
    }

    .btn-main,
    .btn-soft,
    .btn-delete,
    .btn-add-field {
        min-height: 44px;
        border: 0;
        border-radius: 11px;
        padding: 0.55rem 0.92rem;
        font-size: 0.86rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.34rem;
    }

    .btn-main { color: #fff; background: linear-gradient(135deg, #0e7490, #06b6d4); }
    .btn-soft { color: #164e63; background: #e0f7ff; }
    .btn-delete { color: #fff; background: #dc2626; }
    .btn-add-field { color: #164e63; background: #e0f7ff; min-height: 38px; }

    .field-builder {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 0.8rem;
    }

    .field-row {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 0.5rem;
    }

    .btn-remove-field {
        border: 0;
        border-radius: 9px;
        background: #fee2e2;
        color: #991b1b;
        font-weight: 700;
        padding: 0.35rem 0.6rem;
        min-width: 38px;
    }

    .table-wrap {
        overflow-x: auto;
        border-radius: 14px;
        border: 1px solid rgba(14, 116, 144, 0.18);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.88rem;
    }

    th,
    td {
        border-bottom: 1px solid #ecf8fb;
        padding: 0.62rem 0.7rem;
        text-align: left;
        vertical-align: top;
    }

    th {
        background: #f0fbff;
        color: #155e75;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    @media (max-width: 1080px) {
        .detail-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="metric-detail-surface">
    <div class="container" style="max-width:1240px;">
        <section class="detail-hero">
            <div>
                <h1><i class="fas fa-wave-square me-2"></i>{{ ucwords(str_replace('_', ' ', $healthMetric->metric_name)) }}</h1>
                <div class="detail-sub">Metric id: {{ $healthMetric->id }} | {{ number_format($healthMetric->user_health_records_count) }} user records</div>
                <div class="pill-wrap">
                    @foreach ((array) $healthMetric->fields as $field)
                        <span class="field-pill">{{ $field }}</span>
                    @endforeach
                </div>
            </div>
            <a href="{{ route('admin.health.index') }}" class="btn-soft"><i class="fas fa-arrow-left"></i>Back To Admin Health</a>
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

        <div class="detail-grid">
            <section class="detail-card">
                <h2><i class="fas fa-pen me-2"></i>Edit Definition</h2>
                <form method="POST" action="{{ route('admin.metrics.update', $healthMetric) }}">
                    @csrf
                    @method('PATCH')
                    <label class="form-label fw-semibold">Metric Name</label>
                    <input class="form-input" type="text" name="metric_name" value="{{ $healthMetric->metric_name }}" required>

                    <label class="form-label fw-semibold">Fields</label>
                    <div class="field-builder" data-field-builder="metric-show">
                        @foreach ((array) $healthMetric->fields as $field)
                            <div class="field-row">
                                <input class="form-input" type="text" name="fields[]" value="{{ $field }}" required>
                                <button type="button" class="btn-remove-field" title="Remove field">-</button>
                            </div>
                        @endforeach
                    </div>

                    <button class="btn-add-field mb-2" type="button" data-add-field="metric-show"><i class="fas fa-plus"></i>Add Field</button>

                    <button class="btn-main" type="submit"><i class="fas fa-floppy-disk"></i>Save Changes</button>
                </form>

                <form method="POST" action="{{ route('admin.metrics.destroy', $healthMetric) }}" class="mt-2" onsubmit="return confirm('Delete this metric definition?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn-delete" type="submit"><i class="fas fa-trash"></i>Delete Definition</button>
                </form>
            </section>

            <section class="detail-card">
                <h2><i class="fas fa-table me-2"></i>Recent User Records</h2>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Values</th>
                                <th>Recorded</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentEntries as $entry)
                                <tr>
                                    <td>{{ $entry->user->name ?? 'Unknown' }}</td>
                                    <td>
                                        @foreach ((array) $entry->value as $key => $val)
                                            <div><strong>{{ $key }}:</strong> {{ $val }}</div>
                                        @endforeach
                                    </td>
                                    <td>{{ optional($entry->recorded_at)->format('M d, Y h:i A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No user records yet for this metric.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($recentEntries->hasPages())
                    <div class="mt-3">{{ $recentEntries->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.querySelector('[data-field-builder="metric-show"]');
        const addButton = document.querySelector('[data-add-field="metric-show"]');

        if (!container || !addButton) {
            return;
        }

        function bindRemoveButtons() {
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

        addButton.addEventListener('click', function () {
            const row = document.createElement('div');
            row.className = 'field-row';
            row.innerHTML = '<input class="form-input" type="text" name="fields[]" placeholder="Field label (e.g. Heart Rate (bpm))" required><button type="button" class="btn-remove-field" title="Remove field">-</button>';
            container.appendChild(row);
            bindRemoveButtons();
        });

        bindRemoveButtons();
    });
</script>
@endpush
