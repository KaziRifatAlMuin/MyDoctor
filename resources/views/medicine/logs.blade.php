@extends('layouts.app')

@section('title', 'Medicine Logs - My Doctor')

@section('content')
<div class="container-fluid py-4 px-4">
    <!-- Header with better styling -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-inline-block bg-light px-3 py-1 rounded-pill mb-2">
                <span class="text-primary small fw-semibold"><i class="fas fa-chart-line me-1"></i>Adherence Tracking</span>
            </div>
            <h1 class="display-5 fw-bold text-dark mb-1">Adherence Logs</h1>
            <p class="text-secondary lead fs-5">Track and analyze your medication adherence over time</p>
        </div>
        <div>
            <a href="{{ route('medicine.logs.export', request()->all()) }}" class="btn btn-outline-success btn-lg px-4 py-3 shadow-sm" style="border-radius: 8px;">
                <i class="fas fa-download me-2"></i>Export CSV
            </a>
        </div>
    </div>

    <!-- Filter Card - Wider and cleaner -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4 p-lg-5">
            <form method="GET" action="{{ route('medicine.logs') }}" class="row g-4">
                <div class="col-lg-5">
                    <label for="medicine_id" class="form-label fw-semibold text-dark fs-6">Medicine</label>
                    <select name="medicine_id" id="medicine_id" class="form-select form-select-lg py-3" style="border-radius: 8px;">
                        <option value="">All Medicines</option>
                        @foreach($medicines as $medicine)
                            <option value="{{ $medicine->id }}" {{ $medicineId == $medicine->id ? 'selected' : '' }}>
                                {{ $medicine->medicine_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-5">
                    <label for="days" class="form-label fw-semibold text-dark fs-6">Time Period</label>
                    <select name="days" id="days" class="form-select form-select-lg py-3" style="border-radius: 8px;">
                        <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 days</option>
                        <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 days</option>
                        <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 days</option>
                        <option value="180" {{ $days == 180 ? 'selected' : '' }}>Last 6 months</option>
                        <option value="365" {{ $days == 365 ? 'selected' : '' }}>Last year</option>
                    </select>
                </div>
                <div class="col-lg-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-semibold" style="border-radius: 8px;">
                        <i class="fas fa-filter me-2"></i>Apply
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards - Fixed hover effect -->
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-lg-6">
            <div class="card border-0 shadow stat-card stat-card-primary h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-white-50 text-uppercase small fw-semibold">Total Scheduled</span>
                            <h2 class="text-white mb-0 mt-2 display-5 fw-bold">{{ $totalScheduled }}</h2>
                        </div>
                        <div class="stat-icon bg-white-10 rounded-3 p-3">
                            <i class="fas fa-calendar-check fa-2x text-white"></i>
                        </div>
                    </div>
                    <div class="mt-3 text-white-50 small">
                        <i class="fas fa-clock me-1"></i>All time
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="card border-0 shadow stat-card stat-card-success h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-white-50 text-uppercase small fw-semibold">Taken</span>
                            <h2 class="text-white mb-0 mt-2 display-5 fw-bold">{{ $totalTaken }}</h2>
                        </div>
                        <div class="stat-icon bg-white-10 rounded-3 p-3">
                            <i class="fas fa-check-circle fa-2x text-white"></i>
                        </div>
                    </div>
                    <div class="mt-3 text-white-50 small">
                        <i class="fas fa-percent me-1"></i>{{ $totalScheduled > 0 ? round(($totalTaken / $totalScheduled) * 100) : 0 }}% of total
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="card border-0 shadow stat-card stat-card-danger h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-white-50 text-uppercase small fw-semibold">Missed</span>
                            <h2 class="text-white mb-0 mt-2 display-5 fw-bold">{{ $totalMissed }}</h2>
                        </div>
                        <div class="stat-icon bg-white-10 rounded-3 p-3">
                            <i class="fas fa-times-circle fa-2x text-white"></i>
                        </div>
                    </div>
                    <div class="mt-3 text-white-50 small">
                        <i class="fas fa-percent me-1"></i>{{ $totalScheduled > 0 ? round(($totalMissed / $totalScheduled) * 100) : 0 }}% of total
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="card border-0 shadow stat-card stat-card-info h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-white-50 text-uppercase small fw-semibold">Adherence Rate</span>
                            <h2 class="text-white mb-0 mt-2 display-5 fw-bold">{{ $overallAdherence }}%</h2>
                        </div>
                        <div class="stat-icon bg-white-10 rounded-3 p-3">
                            <i class="fas fa-chart-pie fa-2x text-white"></i>
                        </div>
                    </div>
                    <div class="progress mt-3 bg-white-20" style="height: 8px; border-radius: 4px;">
                        <div class="progress-bar bg-white" role="progressbar" style="width: {{ $overallAdherence }}%; border-radius: 4px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Table - Full width with larger text -->
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-white py-4 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 fs-4"><i class="fas fa-history text-primary me-2"></i>Medication Logs</h5>
            <span class="badge bg-light text-dark px-4 py-2 fs-6">{{ $logs->total() }} Total Entries</span>
        </div>
        <div class="card-body p-0">
            @if($logs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-uppercase small fw-semibold text-secondary fs-7">Date</th>
                                <th class="py-3 text-uppercase small fw-semibold text-secondary fs-7">Medicine</th>
                                <th class="text-center py-3 text-uppercase small fw-semibold text-secondary fs-7">Scheduled</th>
                                <th class="text-center py-3 text-uppercase small fw-semibold text-secondary fs-7">Taken</th>
                                <th class="text-center py-3 text-uppercase small fw-semibold text-secondary fs-7">Missed</th>
                                <th class="text-center py-3 text-uppercase small fw-semibold text-secondary fs-7">Adherence</th>
                                <th class="text-center py-3 text-uppercase small fw-semibold text-secondary fs-7">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr class="border-bottom">
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="calendar-icon bg-light rounded-3 p-2 me-3">
                                                <i class="fas fa-calendar-alt text-primary"></i>
                                            </div>
                                            <div>
                                                <span class="fw-semibold fs-6">{{ \Carbon\Carbon::parse($log->date)->format('M d, Y') }}</span>
                                                <br>
                                                <small class="text-muted fs-7">{{ \Carbon\Carbon::parse($log->date)->format('l') }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="fw-semibold fs-6">{{ $log->medicine->medicine_name }}</span>
                                        <br>
                                        <small class="text-muted fs-7">{{ $log->medicine->typeLabel ?? 'Medicine' }} • {{ $log->medicine->value_per_dose ?? '' }} {{ $log->medicine->unitLabel ?? '' }}</small>
                                    </td>
                                    <td class="text-center py-3">
                                        <span class="badge bg-light text-dark px-3 py-2 fs-6">{{ $log->total_scheduled }}</span>
                                    </td>
                                    <td class="text-center py-3">
                                        <span class="fw-bold text-success fs-5">{{ $log->total_taken }}</span>
                                    </td>
                                    <td class="text-center py-3">
                                        <span class="fw-bold text-danger fs-5">{{ $log->total_missed }}</span>
                                    </td>
                                    <td class="text-center py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="progress w-75 me-2" style="height: 8px; border-radius: 4px;">
                                                <div class="progress-bar {{ $log->adherenceRate >= 80 ? 'bg-success' : ($log->adherenceRate >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                                     role="progressbar" style="width: {{ $log->adherenceRate }}%; border-radius: 4px;"></div>
                                            </div>
                                            <span class="fw-bold fs-6">{{ $log->adherenceRate }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-center py-3">
                                        @php
                                            $status = $log->adherenceStatus;
                                        @endphp
                                        <span class="badge bg-{{ $status['class'] }}-soft text-{{ $status['class'] }} px-3 py-2 rounded-pill fs-6">
                                            {{ $status['text'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="p-4 d-flex justify-content-between align-items-center">
                    <div class="text-muted small fs-6">
                        Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} entries
                    </div>
                    <div>
                        {{ $logs->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5 my-5">
                    <div class="empty-state-icon mb-4">
                        <div class="bg-light d-inline-block p-4 rounded-circle">
                            <i class="fas fa-chart-line fa-4x text-muted"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-3 fs-4">No Logs Found</h5>
                    <p class="text-muted mb-4 col-md-6 mx-auto fs-6">Start taking your medicines to see your adherence logs and track your progress over time.</p>
                    <a href="{{ route('medicine.my-medicines') }}" class="btn btn-primary px-5 py-3 fs-6" style="border-radius: 8px;">
                        <i class="fas fa-pills me-2"></i>View Medicines
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Custom styles for wider layout */
    .container-fluid {
        max-width: 1800px;
    }
    
    /* Stat Cards - Fixed hover effect */
    .stat-card {
        border-radius: 20px;
        transition: all 0.3s ease;
        overflow: hidden;
        border: none !important;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 30px rgba(0,0,0,0.15) !important;
    }
    
    /* Fixed: Colors stay on hover */
    .stat-card-primary { background: linear-gradient(135deg, #667eea, #764ba2); }
    .stat-card-primary:hover { background: linear-gradient(135deg, #667eea, #764ba2); }
    
    .stat-card-success { background: linear-gradient(135deg, #28a745, #20c997); }
    .stat-card-success:hover { background: linear-gradient(135deg, #28a745, #20c997); }
    
    .stat-card-danger { background: linear-gradient(135deg, #dc3545, #e74c3c); }
    .stat-card-danger:hover { background: linear-gradient(135deg, #dc3545, #e74c3c); }
    
    .stat-card-info { background: linear-gradient(135deg, #17a2b8, #0dcaf0); }
    .stat-card-info:hover { background: linear-gradient(135deg, #17a2b8, #0dcaf0); }
    
    .bg-white-10 { background: rgba(255, 255, 255, 0.1); }
    .bg-white-20 { background: rgba(255, 255, 255, 0.2); }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .text-white-50 {
        color: rgba(255, 255, 255, 0.7) !important;
    }
    
    /* Table styling with larger text */
    .table {
        font-size: 1rem;
    }
    
    .table > :not(caption) > * > * {
        vertical-align: middle;
        padding: 1.2rem 0.5rem;
    }
    
    .table thead th {
        background: #f8f9fa;
        border-bottom: none;
        letter-spacing: 0.5px;
    }
    
    .table tbody tr {
        transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
        background: #f8f9fa;
    }
    
    /* Font sizes */
    .fs-7 {
        font-size: 0.85rem;
    }
    
    .fs-6 {
        font-size: 1rem;
    }
    
    .fs-5 {
        font-size: 1.25rem;
    }
    
    .fs-4 {
        font-size: 1.5rem;
    }
    
    /* Badge styles */
    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
        border-radius: 30px;
    }
    
    .bg-primary-soft { background: rgba(102, 126, 234, 0.1); }
    .bg-success-soft { background: rgba(40, 167, 69, 0.1); }
    .bg-danger-soft { background: rgba(220, 53, 69, 0.1); }
    .bg-warning-soft { background: rgba(255, 193, 7, 0.1); }
    
    /* Calendar icon */
    .calendar-icon {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Progress bar */
    .progress {
        border-radius: 4px;
        background-color: #e9ecef;
    }
    
    .progress-bar {
        border-radius: 4px;
    }
    
    /* Empty state */
    .empty-state-icon {
        opacity: 0.8;
    }
    
    .empty-state-icon .rounded-circle {
        width: 120px;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Rectangular buttons */
    .btn {
        border-radius: 8px !important;
    }
    
    .btn-lg {
        padding: 12px 24px;
    }
    
    /* Form controls */
    .form-select, .form-control {
        border-radius: 8px !important;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .container-fluid {
            padding: 1rem !important;
        }
        
        .display-5 {
            font-size: 2rem;
        }
    }
</style>
@endpush
@endsection