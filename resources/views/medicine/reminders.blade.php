@extends('layouts.app')

@section('title', 'Medicine Reminders')

@section('content')
<div class="container py-4">
    <!-- Header with better spacing -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="display-5 fw-bold text-primary mb-1">
                <i class="fas fa-bell me-2"></i>Medicine Reminders
            </h1>
            <p class="text-muted lead fs-6">Never miss a dose with smart reminders</p>
        </div>
        <a href="{{ route('medicine.my-medicines') }}" class="btn btn-outline-primary btn-lg rounded-pill px-4">
            <i class="fas fa-pills me-2"></i>My Medicines
        </a>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-4 border-success mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Missed Reminders Section -->
    @if(isset($missedReminders) && $missedReminders->count() > 0)
        <div class="card border-0 shadow-lg mb-4 overflow-hidden">
            <div class="card-header bg-danger text-white py-3">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Missed Reminders</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($missedReminders as $reminder)
                        <div class="list-group-item p-4">
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <h6 class="fw-bold mb-0">{{ $reminder->schedule->medicine->medicine_name }}</h6>
                                        <span class="badge bg-danger">{{ $reminder->statusLabel }}</span>
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center gap-3">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>{{ \Carbon\Carbon::parse($reminder->reminder_at)->format('M d, Y - h:i A') }}
                                        </small>
                                        <small class="text-muted">
                                            <i class="fas fa-capsules me-1"></i>{{ $reminder->schedule->medicine->ruleLabel ?? 'Anytime' }}
                                        </small>
                                        @if($reminder->schedule->medicine->value_per_dose)
                                            <small class="text-muted">
                                                <i class="fas fa-weight me-1"></i>{{ $reminder->schedule->medicine->value_per_dose }} {{ $reminder->schedule->medicine->unitLabel ?? '' }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                                <button class="btn btn-outline-success btn-lg rounded-pill px-4" onclick="markAsTaken({{ $reminder->id }})">
                                    <i class="fas fa-check me-2"></i>Mark Taken
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Today's Reminders Section -->
    <div class="card border-0 shadow-lg mb-4 overflow-hidden">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0"><i class="fas fa-calendar-day me-2"></i>Today's Reminders</h5>
        </div>
        <div class="card-body p-0">
            @if(isset($todayReminders) && $todayReminders->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($todayReminders as $reminder)
                        <div class="list-group-item p-4">
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <h6 class="fw-bold mb-0">{{ $reminder->schedule->medicine->medicine_name }}</h6>
                                        @if($reminder->status == 'taken')
                                            <span class="badge bg-success">Taken</span>
                                        @elseif($reminder->status == 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center gap-3">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>{{ \Carbon\Carbon::parse($reminder->reminder_at)->format('h:i A') }}
                                        </small>
                                        <small class="text-muted">
                                            <i class="fas fa-capsules me-1"></i>{{ $reminder->schedule->medicine->ruleLabel ?? 'Anytime' }}
                                        </small>
                                        @if($reminder->schedule->medicine->value_per_dose)
                                            <small class="text-muted">
                                                <i class="fas fa-weight me-1"></i>{{ $reminder->schedule->medicine->value_per_dose }} {{ $reminder->schedule->medicine->unitLabel ?? '' }}
                                            </small>
                                        @endif
                                    </div>
                                    @if($reminder->status == 'taken' && $reminder->taken_at)
                                        <small class="text-success d-block mt-2">
                                            <i class="fas fa-check-circle me-1"></i>Taken at {{ $reminder->taken_at->setTimezone('Asia/Dhaka')->format('h:i A') }}
                                        </small>
                                    @endif
                                </div>
                                @if($reminder->status == 'pending')
                                    <button class="btn btn-success btn-lg rounded-pill px-4" onclick="markAsTaken({{ $reminder->id }})">
                                        <i class="fas fa-check me-2"></i>Mark Taken
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <div class="bg-success-soft d-inline-block p-4 rounded-circle mb-3">
                        <i class="fas fa-check-circle fa-3x text-success"></i>
                    </div>
                    <h5 class="fw-bold mb-2">All Caught Up!</h5>
                    <p class="text-muted mb-0">No pending reminders for today.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Upcoming Reminders Section -->
    @if(isset($upcomingReminders) && $upcomingReminders->count() > 0)
        <div class="card border-0 shadow-lg overflow-hidden">
            <div class="card-header bg-info text-white py-3">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Upcoming Reminders (Next 3 Days)</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($upcomingReminders as $reminder)
                        <div class="list-group-item p-4">
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-2">{{ $reminder->schedule->medicine->medicine_name }}</h6>
                                    <div class="d-flex flex-wrap align-items-center gap-3">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>{{ \Carbon\Carbon::parse($reminder->reminder_at)->format('M d, Y - h:i A') }}
                                        </small>
                                        <small class="text-muted">
                                            <i class="fas fa-capsules me-1"></i>{{ $reminder->schedule->medicine->ruleLabel ?? 'Anytime' }}
                                        </small>
                                        @if($reminder->schedule->medicine->value_per_dose)
                                            <small class="text-muted">
                                                <i class="fas fa-weight me-1"></i>{{ $reminder->schedule->medicine->value_per_dose }} {{ $reminder->schedule->medicine->unitLabel ?? '' }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                                <span class="badge bg-info text-white px-3 py-2">Upcoming</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Hidden Form for Marking Taken -->
<form id="taken-form" method="POST" style="display: none;">
    @csrf
</form>

@push('styles')
<style>
    .bg-success-soft {
        background: rgba(40, 167, 69, 0.1);
    }
    
    .list-group-item {
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }
    
    .list-group-item:hover {
        background-color: #f8f9fa;
    }
    
    .list-group-item:has(.bg-danger) {
        border-left-color: #dc3545;
    }
    
    .list-group-item:has(.bg-warning) {
        border-left-color: #ffc107;
    }
    
    .list-group-item:has(.bg-success) {
        border-left-color: #28a745;
    }
    
    .badge {
        font-weight: 500;
        padding: 0.5em 1em;
    }
    
    .card {
        border-radius: 16px;
    }
    
    .card-header {
        border-top-left-radius: 16px !important;
        border-top-right-radius: 16px !important;
    }
    
    @media (max-width: 576px) {
        .btn-lg {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function markAsTaken(id) {
        if (confirm('Have you taken this medicine?')) {
            const form = document.getElementById('taken-form');
            form.action = '{{ url("/medicine/reminders") }}/' + id + '/taken';
            form.submit();
        }
    }
</script>
@endpush
@endsection