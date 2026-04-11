@extends('layouts.app')

@section('title', 'Medicine Schedules')

@section('content')
<div class="container py-4">
    <!-- Header with better styling -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="display-5 fw-bold text-primary mb-1">
                <i class="fas fa-clock me-2"></i>
                @if(isset($medicine))
                    {{ $medicine->medicine_name }}
                @else
                    Medicine Schedules
                @endif
            </h1>
            <p class="text-muted lead fs-6">
                @if(isset($medicine))
                    Manage schedules for <span class="fw-semibold">{{ $medicine->medicine_name }}</span>
                @else
                    Manage all your medication schedules
                @endif
            </p>
        </div>
        <div>
            @if(isset($medicine))
                <a href="{{ route('medicine.schedules.create', ['medicine_id' => $medicine->id]) }}" 
                   class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm">
                    <i class="fas fa-plus-circle me-2"></i>Add Schedule
                </a>
            @else
                <a href="{{ route('medicine.my-medicines') }}" class="btn btn-outline-primary btn-lg rounded-pill px-4">
                    <i class="fas fa-pills me-2"></i>My Medicines
                </a>
            @endif
        </div>
    </div>

    <!-- Medicine Info Card (only when viewing specific medicine) -->
    @if(isset($medicine))
    <div class="card border-0 shadow-sm mb-4 bg-primary-soft">
        <div class="card-body p-4">
            <div class="d-flex align-items-center">
                <div class="medicine-icon bg-primary text-white p-3 rounded-3 me-3">
                    <i class="fas fa-pills fa-2x"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-1">{{ $medicine->medicine_name }}</h4>
                    <p class="text-muted mb-0">
                        {{ $medicine->typeLabel ?? 'Medicine' }} • 
                        {{ $medicine->value_per_dose ?? '' }} {{ $medicine->unitLabel ?? '' }} •
                        {{ $medicine->ruleLabel ?? 'Anytime' }}
                    </p>
                </div>
                <div class="ms-auto">
                    <span class="badge bg-primary px-3 py-2 rounded-pill fs-6">
                        <i class="fas fa-clock me-1"></i>{{ $schedules->count() }} Schedule(s)
                    </span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-4 border-success mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Schedules Grid -->
    @if(isset($schedules) && $schedules->count() > 0)
        <div class="row g-4">
            @foreach($schedules as $schedule)
                <!-- Left-aligned card with more width -->
                <div class="{{ $schedules->count() == 1 ? 'col-xl-9 col-lg-9' : 'col-xl-6 col-lg-6' }}">
                    <div class="card h-100 border-0 shadow schedule-card">
                        <div class="card-body p-4">
                            <!-- Header with status -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    @if(!isset($medicine))
                                        <div class="medicine-icon-medium bg-primary-soft rounded-3 p-2">
                                            <i class="fas fa-pills fa-xl text-primary"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-0">{{ $schedule->medicine->medicine_name }}</h5>
                                            <span class="badge bg-primary-soft text-primary mt-1">{{ $schedule->medicine->typeLabel ?? 'Medicine' }}</span>
                                        </div>
                                    @else
                                        <div class="schedule-number bg-primary-soft rounded-3 px-3 py-2">
                                            <span class="fw-bold text-primary fs-5">Schedule #{{ $loop->iteration }}</span>
                                        </div>
                                    @endif
                                </div>
                                <span class="badge {{ $schedule->is_active ? 'bg-success' : 'bg-secondary' }} px-3 py-2 rounded-pill">
                                    <i class="fas fa-{{ $schedule->is_active ? 'check-circle' : 'minus-circle' }} me-1"></i>
                                    {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            <!-- Schedule Details - Better spacing -->
                            <div class="schedule-details mb-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="bg-light rounded-3 p-3">
                                            <small class="text-muted d-block mb-1">
                                                <i class="fas fa-calendar me-1"></i>Period
                                            </small>
                                            <span class="fw-semibold fs-6">{{ $schedule->periodLabel }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="bg-light rounded-3 p-3">
                                            <small class="text-muted d-block mb-1">
                                                <i class="fas fa-sync-alt me-1"></i>Frequency
                                            </small>
                                            <span class="fw-semibold fs-6">{{ $schedule->frequency_per_day ?? 'N/A' }}/day</span>
                                        </div>
                                    </div>
                                    
                                    @if($schedule->interval_hours)
                                    <div class="col-12">
                                        <div class="bg-light rounded-3 p-3">
                                            <small class="text-muted d-block mb-1">
                                                <i class="fas fa-hourglass-half me-1"></i>Interval
                                            </small>
                                            <span class="fw-semibold fs-6">Every {{ $schedule->interval_hours }} hours</span>
                                        </div>
                                    </div>
                                    @endif
                                    
                                    <div class="col-12">
                                        <div class="bg-light rounded-3 p-3">
                                            <small class="text-muted d-block mb-2">
                                                <i class="fas fa-clock me-1"></i>Dosage Times
                                            </small>
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($schedule->dosageTimesArray as $time)
                                                    @php
                                                        $hour = explode(':', $time)[0];
                                                        $minute = explode(':', $time)[1];
                                                        $hourNum = intval($hour);
                                                        $ampm = $hourNum >= 12 ? 'PM' : 'AM';
                                                        $hour12 = $hourNum % 12 ?: 12;
                                                        $formattedTime = $hour12 . ':' . $minute . ' ' . $ampm;
                                                    @endphp
                                                    <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill fs-6">
                                                        {{ $formattedTime }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="bg-light rounded-3 p-3">
                                            <small class="text-muted d-block mb-1">
                                                <i class="fas fa-play me-1"></i>Start
                                            </small>
                                            <span class="fw-semibold">{{ \Carbon\Carbon::parse($schedule->start_date)->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="bg-light rounded-3 p-3">
                                            <small class="text-muted d-block mb-1">
                                                <i class="fas fa-stop me-1"></i>End
                                            </small>
                                            <span class="fw-semibold">{{ $schedule->end_date ? \Carbon\Carbon::parse($schedule->end_date)->format('M d, Y') : 'Ongoing' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Reminder Stats with Icons - More spacious -->
                            @php
                                $totalReminders = $schedule->reminders()->count();
                                $takenReminders = $schedule->reminders()->where('status', 'taken')->count();
                                $pendingReminders = $schedule->reminders()->where('status', 'pending')->count();
                                $missedReminders = $schedule->reminders()->where('status', 'missed')->count();
                            @endphp
                            
                            <div class="row g-3 mb-4">
                                <div class="col-4">
                                    <div class="text-center p-3 bg-success-soft rounded-3">
                                        <i class="fas fa-check-circle text-success fs-4 mb-1"></i>
                                        <div class="fw-bold fs-5">{{ $takenReminders }}</div>
                                        <small class="text-muted">Taken</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-3 bg-warning-soft rounded-3">
                                        <i class="fas fa-clock text-warning fs-4 mb-1"></i>
                                        <div class="fw-bold fs-5">{{ $pendingReminders }}</div>
                                        <small class="text-muted">Pending</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-3 bg-danger-soft rounded-3">
                                        <i class="fas fa-exclamation-circle text-danger fs-4 mb-1"></i>
                                        <div class="fw-bold fs-5">{{ $missedReminders }}</div>
                                        <small class="text-muted">Missed</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions - Larger buttons -->
                            <div class="d-flex gap-3">
                                <a href="{{ route('medicine.schedules.edit', $schedule->id) }}" 
                                   class="btn btn-outline-primary flex-grow-1 rounded-pill py-2">
                                    <i class="fas fa-edit me-2"></i>Edit
                                </a>
                                <form method="POST" action="{{ route('medicine.schedules.generate-reminders', $schedule->id) }}" 
                                      class="flex-grow-1">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success w-100 rounded-pill py-2" 
                                            onclick="return confirm('Generate new reminders for this schedule?')">
                                        <i class="fas fa-bell me-2"></i>Generate Reminders
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('medicine.schedules.destroy', $schedule->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger rounded-pill px-4 py-2" 
                                            onclick="return confirm('Delete this schedule?')">
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
        <div class="text-center py-5 my-5">
            <div class="empty-state-icon mb-4">
                <div class="bg-primary-soft d-inline-block p-4 rounded-circle">
                    <i class="fas fa-clock fa-4x text-primary"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-3">No Schedules Found</h3>
            <p class="text-muted mb-4 col-md-6 mx-auto">
                @if(isset($medicine))
                    <strong>{{ $medicine->medicine_name }}</strong> doesn't have any schedules yet.
                @else
                    You haven't created any medicine schedules yet.
                @endif
            </p>
            @if(isset($medicine))
                <a href="{{ route('medicine.schedules.create', ['medicine_id' => $medicine->id]) }}" 
                   class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                    <i class="fas fa-plus-circle me-2"></i>Add First Schedule
                </a>
            @else
                <a href="{{ route('medicine.my-medicines') }}" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                    <i class="fas fa-pills me-2"></i>Go to Medicines
                </a>
            @endif
        </div>
    @endif
</div>

@push('styles')
<style>
    .schedule-card {
        transition: all 0.3s ease;
        border-radius: 20px;
        overflow: hidden;
    }
    
    .schedule-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15) !important;
    }
    
    .bg-primary-soft {
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }
    
    .bg-success-soft {
        background: rgba(40, 167, 69, 0.1);
    }
    
    .bg-warning-soft {
        background: rgba(255, 193, 7, 0.1);
    }
    
    .bg-danger-soft {
        background: rgba(220, 53, 69, 0.1);
    }
    
    .medicine-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .medicine-icon-medium {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .schedule-number {
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }
    
    /* Grid layouts */
    @media (min-width: 992px) {
        .col-lg-6 {
            flex: 0 0 auto;
            width: 50%; /* 2 cards per row */
        }
        .col-lg-9 {
            flex: 0 0 auto;
            width: 75%; /* 3/4 of container - wider single card */
        }
    }
    
    .badge {
        font-weight: 500;
    }
    
    /* Better spacing inside cards */
    .bg-light {
        transition: all 0.2s ease;
    }
    
    .bg-light:hover {
        background: #e9ecef !important;
    }
</style>
@endpush
@endsection