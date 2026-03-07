@extends('layouts.app')

@section('title', 'Medicine Reminders - My Doctor')

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold text-primary mb-1">Medicine Reminders</h1>
            <p class="text-muted">Never miss a dose with smart reminders</p>
        </div>
        <a href="{{ route('medicine.my-medicines') }}" class="btn btn-outline-primary btn-lg rounded-pill">
            <i class="fas fa-pills me-2"></i>My Medicines
        </a>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Missed Reminders -->
    @if(isset($missedReminders) && $missedReminders->count() > 0)
        <div class="card border-0 shadow-lg mb-4">
            <div class="card-header bg-danger text-white py-3">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Missed Reminders</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($missedReminders as $reminder)
                        <div class="list-group-item p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold mb-1">{{ $reminder->schedule->medicine->medicine_name }}</h6>
                                    <div class="d-flex align-items-center gap-3">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>{{ \Carbon\Carbon::parse($reminder->reminder_at)->format('M d, Y - h:i A') }}
                                        </small>
                                        <span class="badge bg-danger">{{ $reminder->statusLabel }}</span>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        {{ $reminder->schedule->medicine->ruleLabel }} • {{ $reminder->schedule->medicine->value_per_dose ?? '' }} {{ $reminder->schedule->medicine->unitLabel ?? '' }}
                                    </small>
                                </div>
                                <button class="btn btn-outline-success btn-sm" onclick="markAsTaken({{ $reminder->id }})">
                                    <i class="fas fa-check me-1"></i>Mark Taken
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Today's Reminders -->
    <div class="card border-0 shadow-lg mb-4">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0"><i class="fas fa-calendar-day me-2"></i>Today's Reminders</h5>
        </div>
        <div class="card-body p-0">
            @if(isset($todayReminders) && $todayReminders->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($todayReminders as $reminder)
                        <div class="list-group-item p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold mb-1">{{ $reminder->schedule->medicine->medicine_name }}</h6>
                                    <div class="d-flex align-items-center gap-3">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>{{ \Carbon\Carbon::parse($reminder->reminder_at)->format('h:i A') }}
                                        </small>
                                        @if($reminder->status == 'taken')
                                            <span class="badge bg-success">Taken</span>
                                        @elseif($reminder->status == 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @endif
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        {{ $reminder->schedule->medicine->ruleLabel }} • {{ $reminder->schedule->medicine->value_per_dose ?? '' }} {{ $reminder->schedule->medicine->unitLabel ?? '' }}
                                    </small>
                                </div>
                                @if($reminder->status == 'pending')
                                    <button class="btn btn-success btn-sm" onclick="markAsTaken({{ $reminder->id }})">
                                        <i class="fas fa-check me-1"></i>Mark Taken
                                    </button>
                                @elseif($reminder->status == 'taken')
                                    <span class="text-success">
                                        <i class="fas fa-check-circle me-1"></i>Taken at {{ $reminder->taken_at->setTimezone('Asia/Dhaka')->format('h:i A') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h6 class="fw-bold mb-2">All Caught Up!</h6>
                    <p class="text-muted mb-0">No pending reminders for today.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Upcoming Reminders -->
    @if(isset($upcomingReminders) && $upcomingReminders->count() > 0)
        <div class="card border-0 shadow-lg">
            <div class="card-header bg-info text-white py-3">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Upcoming Reminders (Next 3 Days)</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($upcomingReminders as $reminder)
                        <div class="list-group-item p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold mb-1">{{ $reminder->schedule->medicine->medicine_name }}</h6>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>{{ \Carbon\Carbon::parse($reminder->reminder_at)->format('M d, Y - h:i A') }}
                                    </small>
                                    <small class="text-muted d-block mt-1">
                                        {{ $reminder->schedule->medicine->ruleLabel }} • {{ $reminder->schedule->medicine->value_per_dose ?? '' }} {{ $reminder->schedule->medicine->unitLabel ?? '' }}
                                    </small>
                                </div>
                                <span class="badge bg-info text-white">Upcoming</span>
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

@push('scripts')
<script>
    function markAsTaken(id) {
        if (confirm('Have you taken this medicine?')) {
            const form = document.getElementById('taken-form');
            form.action = '{{ config("app.url") }}/medicine/reminders/' + id + '/taken';
            form.submit();
        }
    }
</script>
@endpush
@endsection