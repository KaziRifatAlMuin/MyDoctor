@extends('layouts.app')

@section('title', 'Medicine Logs - My Doctor')

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold text-primary mb-1">Adherence Logs</h1>
            <p class="text-muted">Track your medication adherence over time</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('medicine.logs.export', request()->all()) }}" class="btn btn-outline-success btn-lg rounded-pill">
                <i class="fas fa-download me-2"></i>Export CSV
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('medicine.logs') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="medicine_id" class="form-label fw-bold">Medicine</label>
                    <select name="medicine_id" id="medicine_id" class="form-select">
                        <option value="">All Medicines</option>
                        @foreach($medicines as $medicine)
                            <option value="{{ $medicine->id }}" {{ $medicineId == $medicine->id ? 'selected' : '' }}>
                                {{ $medicine->medicine_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="days" class="form-label fw-bold">Time Period</label>
                    <select name="days" id="days" class="form-select">
                        <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 days</option>
                        <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 days</option>
                        <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 days</option>
                        <option value="180" {{ $days == 180 ? 'selected' : '' }}>Last 6 months</option>
                        <option value="365" {{ $days == 365 ? 'selected' : '' }}>Last year</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-4">
                    <h6 class="text-white-50 mb-2">Total Scheduled</h6>
                    <h2 class="mb-0">{{ $totalScheduled }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body p-4">
                    <h6 class="text-white-50 mb-2">Taken</h6>
                    <h2 class="mb-0">{{ $totalTaken }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body p-4">
                    <h6 class="text-white-50 mb-2">Missed</h6>
                    <h2 class="mb-0">{{ $totalMissed }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body p-4">
                    <h6 class="text-white-50 mb-2">Adherence Rate</h6>
                    <h2 class="mb-0">{{ $overallAdherence }}%</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Medication Logs</h5>
        </div>
        <div class="card-body p-0">
            @if($logs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3">Date</th>
                                <th class="py-3">Medicine</th>
                                <th class="text-center py-3">Scheduled</th>
                                <th class="text-center py-3">Taken</th>
                                <th class="text-center py-3">Missed</th>
                                <th class="text-center py-3">Adherence</th>
                                <th class="text-center py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td class="px-4 py-3">
                                        <strong>{{ \Carbon\Carbon::parse($log->date)->format('M d, Y') }}</strong>
                                    </td>
                                    <td class="py-3">
                                        {{ $log->medicine->medicine_name }}
                                        <br>
                                        <small class="text-muted">{{ $log->medicine->typeLabel ?? '' }}</small>
                                    </td>
                                    <td class="text-center py-3">{{ $log->total_scheduled }}</td>
                                    <td class="text-center py-3 text-success fw-bold">{{ $log->total_taken }}</td>
                                    <td class="text-center py-3 text-danger fw-bold">{{ $log->total_missed }}</td>
                                    <td class="text-center py-3">
                                        <strong>{{ $log->adherenceRate }}%</strong>
                                    </td>
                                    <td class="text-center py-3">
                                        @php
                                            $status = $log->adherenceStatus;
                                        @endphp
                                        <span class="badge bg-{{ $status['class'] }} px-3 py-2">
                                            {{ $status['text'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="p-4">
                    {{ $logs->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
                    <h5 class="fw-bold mb-2">No Logs Found</h5>
                    <p class="text-muted mb-0">Start taking your medicines to see logs here.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .text-white-50 {
        color: rgba(255, 255, 255, 0.7) !important;
    }
    
    .table > :not(caption) > * > * {
        vertical-align: middle;
    }
    
    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
    }
</style>
@endpush
@endsection