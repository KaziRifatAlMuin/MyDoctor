@extends('layouts.app')

@section('title', 'My Medicines - My Doctor')

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold text-primary mb-1">My Medicines</h1>
            <p class="text-muted">Manage your medication inventory</p>
        </div>
        <a href="{{ route('medicine.add') }}" class="btn btn-primary btn-lg rounded-pill">
            <i class="fas fa-plus-circle me-2"></i>Add New Medicine
        </a>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Medicines Grid -->
    @if($medicines->count() > 0)
        <div class="row g-4">
            @foreach($medicines as $medicine)
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm medicine-card">
                        <div class="card-body p-4">
                            <!-- Medicine Type Badge -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill">
                                    <i class="fas fa-pills me-1"></i>{{ $medicine->typeLabel ?? 'Medicine' }}
                                </span>
                                <div class="dropdown">
                                    <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('medicine.edit', $medicine->id) }}">
                                                <i class="fas fa-edit me-2 text-primary"></i>Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('medicine.schedules', ['medicine_id' => $medicine->id]) }}">
                                                <i class="fas fa-clock me-2 text-success"></i>Schedules
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('medicine.logs', ['medicine_id' => $medicine->id]) }}">
                                                <i class="fas fa-chart-line me-2 text-info"></i>Logs
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('medicine.destroy', $medicine->id) }}" 
                                                  onsubmit="return confirm('Are you sure? This will delete all associated schedules and reminders.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash-alt me-2"></i>Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
                            <!-- Medicine Name -->
                            <h4 class="fw-bold mb-3">{{ $medicine->medicine_name }}</h4>
                            
                            <!-- Details Grid -->
                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <div class="detail-item">
                                        <small class="text-muted d-block">Dosage</small>
                                        <strong>{{ $medicine->value_per_dose ?? '' }} {{ $medicine->unitLabel ?? '' }}</strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="detail-item">
                                        <small class="text-muted d-block">When to take</small>
                                        <strong>{{ $medicine->ruleLabel ?? 'Not specified' }}</strong>
                                    </div>
                                </div>
                                @if($medicine->dose_limit)
                                <div class="col-6">
                                    <div class="detail-item">
                                        <small class="text-muted d-block">Daily limit</small>
                                        <strong>{{ $medicine->dose_limit }} doses</strong>
                                    </div>
                                </div>
                                @endif
                                <div class="col-6">
                                    <div class="detail-item">
                                        <small class="text-muted d-block">Added</small>
                                        <strong>{{ $medicine->created_at->format('M d, Y') }}</strong>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Schedule Status -->
                            @if($medicine->activeSchedule)
                                <div class="schedule-status active p-3 rounded-3 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="status-indicator bg-success me-3"></div>
                                        <div>
                                            <small class="text-success d-block fw-bold">Active Schedule</small>
                                            <small class="text-muted">{{ $medicine->activeSchedule->formattedDosageTimes }}</small>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="schedule-status inactive p-3 rounded-3 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="status-indicator bg-warning me-3"></div>
                                        <div>
                                            <small class="text-warning d-block fw-bold">No Active Schedule</small>
                                            <small class="text-muted">Add a schedule to set reminders</small>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 mt-3">
                                <a href="{{ route('medicine.schedules', ['medicine_id' => $medicine->id]) }}" 
                                   class="btn btn-outline-primary flex-grow-1">
                                    <i class="fas fa-clock me-2"></i>Schedules
                                </a>
                                @if($medicine->activeSchedule)
                                    <a href="{{ route('medicine.reminders') }}" class="btn btn-outline-success flex-grow-1">
                                        <i class="fas fa-bell me-2"></i>Reminders
                                    </a>
                                @endif
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
                <i class="fas fa-pills fa-5x text-muted"></i>
            </div>
            <h3 class="fw-bold mb-3">No Medicines Added Yet</h3>
            <p class="text-muted mb-4">Start by adding your first medicine to set up reminders and track your medication.</p>
            <a href="{{ route('medicine.add') }}" class="btn btn-primary btn-lg rounded-pill">
                <i class="fas fa-plus-circle me-2"></i>Add Your First Medicine
            </a>
        </div>
    @endif
</div>

@push('styles')
<style>
    .medicine-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-radius: 20px;
        overflow: hidden;
    }
    
    .medicine-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
    }
    
    .bg-primary-soft {
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }
    
    .detail-item {
        padding: 8px 0;
    }
    
    .schedule-status {
        transition: all 0.3s ease;
    }
    
    .schedule-status.active {
        background: rgba(40, 167, 69, 0.1);
        border-left: 4px solid #28a745;
    }
    
    .schedule-status.inactive {
        background: rgba(255, 193, 7, 0.1);
        border-left: 4px solid #ffc107;
    }
    
    .status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }
    
    .empty-state-icon {
        opacity: 0.5;
    }
    
    .dropdown-item i {
        width: 20px;
    }
</style>
@endpush
@endsection