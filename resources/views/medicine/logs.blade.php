@extends('layouts.app')

@section('title', __('ui.medicine.logs_title'))
@section('main_content_class', 'main-content main-content--wide')

@section('content')
<div class="medicine-section">
    <div class="container-fluid px-4 px-xl-5" style="max-width: 1600px;">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <div class="d-inline-block bg-light px-3 py-1 rounded-pill mb-2">
                    <span class="text-primary small fw-semibold"><i class="fas fa-chart-line me-1"></i>{{ __('ui.medicine.adherence_tracking') }}</span>
                </div>
                <h1 class="display-5 fw-bold text-dark mb-1">{{ __('ui.medicine.adherence_logs') }}</h1>
                <p class="text-secondary lead fs-5">{{ __('ui.medicine.adherence_logs_desc') }}</p>
            </div>
            <div>
                <a href="{{ route('medicine.logs.export', request()->all()) }}" class="btn btn-outline-success btn-lg px-4 py-3 shadow-sm" style="border-radius: 12px;">
                    <i class="fas fa-download me-2"></i>{{ __('ui.medicine.export_csv') }}
                </a>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="filter-card mb-4">
            <form method="GET" action="{{ route('medicine.logs') }}" class="row g-4">
                <div class="col-lg-5 col-md-6">
                    <label for="medicine_id" class="form-label fw-semibold text-dark">{{ __('ui.medicine.medicine') }}</label>
                    <select name="medicine_id" id="medicine_id" class="form-select form-select-lg">
                        <option value="">{{ __('ui.medicine.all_medicines') }}</option>
                        @foreach($medicines as $medicine)
                            <option value="{{ $medicine->id }}" {{ $medicineId == $medicine->id ? 'selected' : '' }}>
                                {{ $medicine->medicine_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-5 col-md-6">
                    <label for="days" class="form-label fw-semibold text-dark">{{ __('ui.medicine.time_period') }}</label>
                    <select name="days" id="days" class="form-select form-select-lg">
                        <option value="7" {{ $days == 7 ? 'selected' : '' }}>{{ __('ui.medicine.last_7_days') }}</option>
                        <option value="30" {{ $days == 30 ? 'selected' : '' }}>{{ __('ui.medicine.last_30_days') }}</option>
                        <option value="90" {{ $days == 90 ? 'selected' : '' }}>{{ __('ui.medicine.last_90_days') }}</option>
                        <option value="180" {{ $days == 180 ? 'selected' : '' }}>{{ __('ui.medicine.last_6_months') }}</option>
                        <option value="365" {{ $days == 365 ? 'selected' : '' }}>{{ __('ui.medicine.last_year') }}</option>
                    </select>
                </div>
                <div class="col-lg-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 py-3 fw-semibold" style="border-radius: 12px;">
                        <i class="fas fa-filter me-2"></i>{{ __('ui.medicine.apply') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-5">
            <div class="col-xl-3 col-lg-6">
                <div class="stat-card stat-primary">
                    <div class="stat-card-inner">
                        <div>
                            <span class="stat-label">{{ __('ui.medicine.total_scheduled') }}</span>
                            <h2 class="stat-value">{{ $totalScheduled }}</h2>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <small><i class="fas fa-clock me-1"></i>{{ __('ui.medicine.all_time') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="stat-card stat-success">
                    <div class="stat-card-inner">
                        <div>
                            <span class="stat-label">{{ __('ui.medicine.taken') }}</span>
                            <h2 class="stat-value">{{ $totalTaken }}</h2>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <small><i class="fas fa-percent me-1"></i>{{ $totalScheduled > 0 ? round(($totalTaken / $totalScheduled) * 100) : 0 }}% {{ __('ui.medicine.of_total') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="stat-card stat-danger">
                    <div class="stat-card-inner">
                        <div>
                            <span class="stat-label">{{ __('ui.medicine.missed') }}</span>
                            <h2 class="stat-value">{{ $totalMissed }}</h2>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <small><i class="fas fa-percent me-1"></i>{{ $totalScheduled > 0 ? round(($totalMissed / $totalScheduled) * 100) : 0 }}% {{ __('ui.medicine.of_total') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="stat-card stat-info">
                    <div class="stat-card-inner">
                        <div>
                            <span class="stat-label">{{ __('ui.medicine.adherence_rate') }}</span>
                            <h2 class="stat-value">{{ $overallAdherence }}%</h2>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: {{ $overallAdherence }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="logs-card">
            <div class="logs-card-header">
                <h5 class="fw-bold mb-0"><i class="fas fa-history text-primary me-2"></i>{{ __('ui.medicine.medication_logs') }}</h5>
                <span class="badge bg-light text-dark px-4 py-2">{{ $logs->total() }} {{ __('ui.medicine.total_entries') }}</span>
            </div>
            <div class="logs-card-body p-0">
                @if($logs->count() > 0)
                    <div class="table-responsive">
                        <table class="logs-table">
                            <thead>
                                <tr>
                                    <th>{{ __('ui.medicine.date') }}</th>
                                    <th>{{ __('ui.medicine.medicine') }}</th>
                                    <th class="text-center">{{ __('ui.medicine.scheduled') }}</th>
                                    <th class="text-center">{{ __('ui.medicine.taken') }}</th>
                                    <th class="text-center">{{ __('ui.medicine.missed') }}</th>
                                    <th class="text-center">{{ __('ui.medicine.adherence') }}</th>
                                    <th class="text-center">{{ __('ui.medicine.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    @php
                                        $status = $log->adherenceStatus;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="date-cell">
                                                <i class="fas fa-calendar-alt text-primary"></i>
                                                <div>
                                                    <div class="fw-semibold">{{ \Carbon\Carbon::parse($log->date)->format('M d, Y') }}</div>
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($log->date)->format('l') }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $log->medicine->medicine_name }}</div>
                                            <small class="text-muted">{{ $log->medicine->typeLabel ?? __('ui.medicine.medicine') }}</small>
                                        </td>
                                        <td class="text-center"><span class="badge bg-light text-dark px-3 py-2">{{ $log->total_scheduled }}</span></td>
                                        <td class="text-center"><span class="fw-bold text-success fs-5">{{ $log->total_taken }}</span></td>
                                        <td class="text-center"><span class="fw-bold text-danger fs-5">{{ $log->total_missed }}</span></td>
                                        <td class="text-center">
                                            <div class="adherence-progress">
                                                <div class="progress flex-grow-1">
                                                    <div class="progress-bar {{ $log->adherenceRate >= 80 ? 'bg-success' : ($log->adherenceRate >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                                         style="width: {{ $log->adherenceRate }}%;"></div>
                                                </div>
                                                <span class="fw-bold ms-2">{{ $log->adherenceRate }}%</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge status-{{ $status['class'] }} px-3 py-2 rounded-pill">
                                                {{ $status['text'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="pagination-wrapper">
                        <div class="text-muted small">
                            {{ __('ui.medicine.showing') }} {{ $logs->firstItem() ?? 0 }} {{ __('ui.medicine.to') }} {{ $logs->lastItem() ?? 0 }} {{ __('ui.medicine.of') }} {{ $logs->total() }} {{ __('ui.medicine.entries') }}
                        </div>
                        <div>
                            {{ $logs->appends(request()->query())->links() }}
                        </div>
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-chart-line fa-4x text-muted"></i>
                        </div>
                        <h5 class="fw-bold mb-3">{{ __('ui.medicine.no_logs_found') }}</h5>
                        <p class="text-muted mb-4">{{ __('ui.medicine.no_logs_message') }}</p>
                        <a href="{{ route('medicine.my-medicines') }}" class="btn btn-primary px-5 py-3">
                            <i class="fas fa-pills me-2"></i>{{ __('ui.medicine.view_medicines') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .medicine-section {
        background: #f8f9fb;
        min-height: calc(100vh - 200px);
        padding: 2.5rem 0 3rem;
    }
    
    .filter-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
        border: 1px solid rgba(0, 0, 0, 0.04);
    }
    
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.25rem;
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 0, 0, 0.04);
        position: relative;
        overflow: hidden;
    }
    
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
    }
    
    .stat-primary::before { background: linear-gradient(90deg, #667eea, #764ba2); }
    .stat-success::before { background: linear-gradient(90deg, #28a745, #20c997); }
    .stat-danger::before { background: linear-gradient(90deg, #dc3545, #e74c3c); }
    .stat-info::before { background: linear-gradient(90deg, #17a2b8, #0dcaf0); }
    
    .stat-card-inner {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }
    
    .stat-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #a0aec0;
    }
    
    .stat-value {
        font-size: 1.75rem;
        font-weight: 800;
        margin: 0.25rem 0 0;
        line-height: 1.2;
    }
    
    .stat-primary .stat-value { color: #667eea; }
    .stat-success .stat-value { color: #28a745; }
    .stat-danger .stat-value { color: #dc3545; }
    .stat-info .stat-value { color: #17a2b8; }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .stat-primary .stat-icon { background: rgba(102, 126, 234, 0.12); color: #667eea; }
    .stat-success .stat-icon { background: rgba(40, 167, 69, 0.12); color: #28a745; }
    .stat-danger .stat-icon { background: rgba(220, 53, 69, 0.12); color: #dc3545; }
    .stat-info .stat-icon { background: rgba(23, 162, 184, 0.12); color: #17a2b8; }
    
    .stat-footer {
        padding-top: 0.75rem;
        border-top: 1px solid #f0f0f0;
        font-size: 0.75rem;
        color: #a0aec0;
    }
    
    .stat-footer .progress {
        height: 6px;
        border-radius: 3px;
        background: #edf2f7;
    }
    
    .logs-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
        border: 1px solid rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }
    
    .logs-card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .logs-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .logs-table th {
        padding: 1rem 1rem;
        background: #f8f9fb;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #a0aec0;
        font-weight: 600;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .logs-table td {
        padding: 1rem;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }
    
    .logs-table tr:hover {
        background: #f8f9fb;
    }
    
    .date-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .adherence-progress {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .adherence-progress .progress {
        flex: 1;
        height: 8px;
        border-radius: 4px;
        background: #edf2f7;
    }
    
    .pagination-wrapper {
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        border-top: 1px solid #f0f0f0;
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }
    
    .empty-icon {
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    
    .status-success { background: rgba(40, 167, 69, 0.12); color: #28a745; }
    .status-warning { background: rgba(255, 193, 7, 0.12); color: #ffc107; }
    .status-danger { background: rgba(220, 53, 69, 0.12); color: #dc3545; }
    .status-info { background: rgba(23, 162, 184, 0.12); color: #17a2b8; }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 12px;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }
    
    @media (max-width: 768px) {
        .pagination-wrapper {
            flex-direction: column;
            text-align: center;
        }
        
        .logs-table th,
        .logs-table td {
            padding: 0.75rem;
        }
    }
</style>
@endpush
@endsection