@extends('layouts.app')

@section('title', 'My Medicines - My Doctor')

@section('content')
<div class="container py-4">
    <!-- Header with better styling -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="display-5 fw-bold text-primary mb-1">
                <i class="fas fa-pills me-2"></i>My Medicines
            </h1>
            <p class="text-muted lead fs-6">Track and manage your medications</p>
        </div>
        <a href="{{ route('medicine.add') }}" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm">
            <i class="fas fa-plus-circle me-2"></i>Add New Medicine
        </a>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-4 border-success" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Medicines Grid -->
    @if($medicines->count() > 0)
        <div class="row g-4">
            @foreach($medicines as $medicine)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm medicine-card">
                        <div class="card-body p-4">
                            <!-- Header with type and menu -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill">
                                    <i class="fas fa-capsules me-1"></i>{{ $medicine->typeLabel ?? 'Medicine' }}
                                </span>
                                <div class="dropdown">
                                    <button class="btn btn-link text-secondary p-0" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v fs-5"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('medicine.edit', $medicine->id) }}">
                                                <i class="fas fa-edit me-2 text-primary"></i>Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('medicine.schedules', ['medicine_id' => $medicine->id]) }}">
                                                <i class="fas fa-clock me-2 text-success"></i>Schedules
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('medicine.logs', ['medicine_id' => $medicine->id]) }}">
                                                <i class="fas fa-history me-2 text-info"></i>Logs
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('medicine.destroy', $medicine->id) }}" 
                                                  onsubmit="return confirm('Delete this medicine? This will remove all associated schedules and reminders.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item py-2 text-danger">
                                                    <i class="fas fa-trash-alt me-2"></i>Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
                            <!-- Medicine Name -->
                            <h4 class="fw-bold mb-3 text-truncate" title="{{ $medicine->medicine_name }}">
                                {{ $medicine->medicine_name }}
                            </h4>
                            
                            <!-- Details in a clean grid -->
                            <div class="bg-light p-3 rounded-3 mb-3">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <small class="text-muted d-block mb-1">
                                            <i class="fas fa-weight me-1"></i>Dosage
                                        </small>
                                        <span class="fw-semibold">{{ $medicine->value_per_dose ?? '—' }} {{ $medicine->unitLabel ?? '' }}</span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block mb-1">
                                            <i class="fas fa-clock me-1"></i>When
                                        </small>
                                        <span class="fw-semibold">{{ $medicine->ruleLabel ?? 'Anytime' }}</span>
                                    </div>
                                    @if($medicine->dose_limit)
                                    <div class="col-6">
                                        <small class="text-muted d-block mb-1">
                                            <i class="fas fa-ban me-1"></i>Daily Limit
                                        </small>
                                        <span class="fw-semibold">{{ $medicine->dose_limit }} doses</span>
                                    </div>
                                    @endif
                                    <div class="col-6">
                                        <small class="text-muted d-block mb-1">
                                            <i class="fas fa-calendar me-1"></i>Added
                                        </small>
                                        <span class="fw-semibold">{{ $medicine->created_at->format('M d, Y') }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Schedule Status -->
                            @if($medicine->activeSchedule)
                                <div class="schedule-status active p-3 rounded-3 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="status-dot bg-success me-3"></div>
                                        <div>
                                            <span class="text-success fw-bold d-block small">Active Schedule</span>
                                            <small class="text-muted">{{ $medicine->activeSchedule->formattedDosageTimes }}</small>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="schedule-status inactive p-3 rounded-3 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="status-dot bg-warning me-3"></div>
                                        <div>
                                            <span class="text-warning fw-bold d-block small">No Schedule</span>
                                            <small class="text-muted">Set up reminders</small>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Action Buttons -->
                            <div class="d-flex gap-2">
                                <a href="{{ route('medicine.schedules', ['medicine_id' => $medicine->id]) }}" 
                                   class="btn btn-outline-primary flex-grow-1 rounded-pill">
                                    <i class="fas fa-clock me-1"></i>Schedules
                                </a>
                                @if($medicine->activeSchedule)
                                    <a href="{{ route('medicine.reminders') }}" class="btn btn-outline-success flex-grow-1 rounded-pill">
                                        <i class="fas fa-bell me-1"></i>Reminders
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State - Centered and clean -->
        <div class="text-center py-5 my-5">
            <div class="empty-state-icon mb-4">
                <div class="bg-primary-soft d-inline-block p-4 rounded-circle">
                    <i class="fas fa-pills fa-4x text-primary"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-3">No Medicines Added</h3>
            <p class="text-muted mb-4 col-md-6 mx-auto">Start by adding your first medicine to set up reminders and track your medication schedule.</p>
            <a href="{{ route('medicine.add') }}" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                <i class="fas fa-plus-circle me-2"></i>Add Your First Medicine
            </a>
        </div>
    @endif
</div>

@push('styles')
<style>
    .medicine-card {
        transition: all 0.2s ease-in-out;
        border-radius: 16px;
    }
    
    .medicine-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.1) !important;
    }
    
    .bg-primary-soft {
        background: rgba(102, 126, 234, 0.1);
    }
    
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }
    
    .schedule-status {
        transition: all 0.2s ease;
    }
    
    .schedule-status.active {
        background: rgba(40, 167, 69, 0.08);
        border-left: 3px solid #28a745;
    }
    
    .schedule-status.inactive {
        background: rgba(255, 193, 7, 0.08);
        border-left: 3px solid #ffc107;
    }
    
    .dropdown-item {
        transition: all 0.2s ease;
    }
    
    .dropdown-item:hover {
        padding-left: 1.8rem !important;
    }
    
    .empty-state-icon {
        opacity: 0.9;
    }
    
    /* Better responsive grid */
    @media (min-width: 1400px) {
        .col-xl-3 {
            flex: 0 0 auto;
            width: 25%;
        }
    }
    
    /* Keep the main content nicely centered */
    .main-content {
        max-width: 1400px;
        margin: 0 auto;
        width: 100%;
    }
</style>
@endpush
@endsection