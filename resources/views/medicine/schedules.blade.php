@extends('layouts.app')

@section('title', 'Medicine Schedules - My Doctor')

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold text-primary mb-1">
                @if(isset($medicine))
                    Schedules for {{ $medicine->medicine_name }}
                @else
                    Medicine Schedules
                @endif
            </h1>
            <p class="text-muted">Manage your medication schedules</p>
        </div>
        <div>
            @if(isset($medicine))
                <a href="{{ route('medicine.schedules.create', ['medicine_id' => $medicine->id]) }}" 
                   class="btn btn-primary btn-lg rounded-pill">
                    <i class="fas fa-plus-circle me-2"></i>Add Schedule
                </a>
            @else
                <a href="{{ route('medicine.my-medicines') }}" class="btn btn-outline-primary btn-lg rounded-pill">
                    <i class="fas fa-pills me-2"></i>My Medicines
                </a>
            @endif
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Schedules Grid -->
    @if(isset($schedules) && $schedules->count() > 0)
        <div class="row g-4">
            @foreach($schedules as $schedule)
                <div class="col-lg-6">
                    <div class="card h-100 border-0 shadow-sm schedule-card">
                        <div class="card-body p-4">
                            <!-- Header -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    @if(!isset($medicine))
                                        <h5 class="fw-bold mb-1">{{ $schedule->medicine->medicine_name }}</h5>
                                        <small class="text-muted">{{ $schedule->medicine->typeLabel ?? '' }}</small>
                                    @else
                                        <h5 class="fw-bold mb-1">Schedule #{{ $loop->iteration }}</h5>
                                    @endif
                                </div>
                                <span class="badge {{ $schedule->is_active ? 'bg-success' : 'bg-secondary' }} px-3 py-2">
                                    {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            <!-- Schedule Details -->
                            <div class="schedule-details mb-3">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="detail-item">
                                            <small class="text-muted d-block">Period</small>
                                            <strong>{{ $schedule->periodLabel }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="detail-item">
                                            <small class="text-muted d-block">Frequency</small>
                                            <strong>{{ $schedule->frequency_per_day ?? 'N/A' }} time(s) per day</strong>
                                        </div>
                                    </div>
                                    @if($schedule->interval_hours)
                                    <div class="col-12">
                                        <div class="detail-item">
                                            <small class="text-muted d-block">Interval</small>
                                            <strong>Every {{ $schedule->interval_hours }} hours</strong>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="col-12">
                                        <div class="detail-item">
                                            <small class="text-muted d-block">Dosage Times</small>
                                            <div class="d-flex flex-wrap gap-2 mt-1">
                                                @foreach($schedule->dosageTimesArray as $time)
                                                    <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill">
                                                        {{ $time }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="detail-item">
                                            <small class="text-muted d-block">Start Date</small>
                                            <strong>{{ \Carbon\Carbon::parse($schedule->start_date)->format('M d, Y') }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="detail-item">
                                            <small class="text-muted d-block">End Date</small>
                                            <strong>{{ $schedule->end_date ? \Carbon\Carbon::parse($schedule->end_date)->format('M d, Y') : 'Ongoing' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Reminder Stats -->
                            <div class="reminder-stats bg-light rounded-3 p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted d-block">Total Reminders</small>
                                        <strong>{{ $schedule->reminders()->count() }}</strong>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Pending</small>
                                        <strong class="text-warning">{{ $schedule->reminders()->where('status', 'pending')->count() }}</strong>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Taken</small>
                                        <strong class="text-success">{{ $schedule->reminders()->where('status', 'taken')->count() }}</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex gap-2">
                                <a href="{{ route('medicine.schedules.edit', $schedule->id) }}" 
                                   class="btn btn-outline-primary flex-grow-1">
                                    <i class="fas fa-edit me-2"></i>Edit
                                </a>
                                <form method="POST" action="{{ route('medicine.schedules.generate-reminders', $schedule->id) }}" 
                                      class="flex-grow-1" 
                                      onsubmit="return confirm('Generate new reminders for this schedule?')">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success w-100">
                                        <i class="fas fa-bell me-2"></i>Generate
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('medicine.schedules.destroy', $schedule->id) }}" 
                                      onsubmit="return confirm('Are you sure? This will delete all associated reminders.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-5">
            <div class="empty-state-icon mb-4">
                <i class="fas fa-clock fa-5x text-muted"></i>
            </div>
            <h3 class="fw-bold mb-3">No Schedules Found</h3>
            <p class="text-muted mb-4">
                @if(isset($medicine))
                    This medicine doesn't have any schedules yet. Add a schedule to set up reminders.
                @else
                    You haven't created any medicine schedules yet.
                @endif
            </p>
            @if(isset($medicine))
                <a href="{{ route('medicine.schedules.create', ['medicine_id' => $medicine->id]) }}" 
                   class="btn btn-primary btn-lg rounded-pill">
                    <i class="fas fa-plus-circle me-2"></i>Add First Schedule
                </a>
            @else
                <a href="{{ route('medicine.my-medicines') }}" class="btn btn-primary btn-lg rounded-pill">
                    <i class="fas fa-pills me-2"></i>Go to Medicines
                </a>
            @endif
        </div>
    @endif
</div>

@push('styles')
<style>
    .schedule-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-radius: 20px;
        overflow: hidden;
    }
    
    .schedule-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
    }
    
    .bg-primary-soft {
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }
    
    .detail-item {
        padding: 4px 0;
    }
    
    .reminder-stats {
        border-left: 4px solid #667eea;
    }
    
    .empty-state-icon {
        opacity: 0.5;
    }
</style>
@endpush
@endsection