@extends('layouts.app')

@section('title', __('ui.medicine.my_medicines_title'))
@section('main_content_class', 'main-content main-content--wide')

@section('content')
<div class="medicine-section">
    <div class="container-fluid px-4 px-xl-5" style="max-width: 1600px;">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="display-5 fw-bold text-primary mb-1">
                    <i class="fas fa-pills me-2"></i>{{ __('ui.medicine.my_medicines') }}
                </h1>
                <p class="text-muted lead fs-6">{{ __('ui.medicine.my_medicines_desc') }}</p>
            </div>
            <a href="{{ route('medicine.add') }}" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm">
                <i class="fas fa-plus-circle me-2"></i>{{ __('ui.medicine.add_new_medicine') }}
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-4 border-success mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($medicines->count() > 0)
            <div class="row g-4">
                @foreach($medicines as $medicine)
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="medicine-card h-100">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill">
                                        <i class="fas fa-capsules me-1"></i>{{ $medicine->typeLabel ?? __('ui.medicine.medicine') }}
                                    </span>
                                    <div class="dropdown">
                                        <button class="btn btn-link text-secondary p-0" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v fs-5"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                            <li><a class="dropdown-item py-2" href="{{ route('medicine.edit', $medicine->id) }}"><i class="fas fa-edit me-2 text-primary"></i>{{ __('ui.medicine.edit') }}</a></li>
                                            <li><a class="dropdown-item py-2" href="{{ route('medicine.schedules', ['medicine_id' => $medicine->id]) }}"><i class="fas fa-clock me-2 text-success"></i>{{ __('ui.medicine.schedules') }}</a></li>
                                            <li><a class="dropdown-item py-2" href="{{ route('medicine.logs', ['medicine_id' => $medicine->id]) }}"><i class="fas fa-history me-2 text-info"></i>{{ __('ui.medicine.logs') }}</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="{{ route('medicine.destroy', $medicine->id) }}" onsubmit="return confirm('{{ __('ui.medicine.delete_confirm_message') }}')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="dropdown-item py-2 text-danger"><i class="fas fa-trash-alt me-2"></i>{{ __('ui.medicine.delete') }}</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <h4 class="fw-bold mb-3 text-truncate" title="{{ $medicine->medicine_name }}">
                                    {{ $medicine->medicine_name }}
                                </h4>
                                
                                <div class="info-panel mb-3">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <small class="text-muted d-block mb-1"><i class="fas fa-weight me-1"></i>{{ __('ui.medicine.dosage') }}</small>
                                            <span class="fw-semibold">{{ $medicine->value_per_dose ?? '—' }} {{ $medicine->unitLabel ?? '' }}</span>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block mb-1"><i class="fas fa-clock me-1"></i>{{ __('ui.medicine.when') }}</small>
                                            <span class="fw-semibold">{{ $medicine->ruleLabel ?? __('ui.medicine.anytime') }}</span>
                                        </div>
                                        @if($medicine->dose_limit)
                                        <div class="col-6">
                                            <small class="text-muted d-block mb-1"><i class="fas fa-ban me-1"></i>{{ __('ui.medicine.daily_limit') }}</small>
                                            <span class="fw-semibold">{{ $medicine->dose_limit }} {{ __('ui.medicine.doses') }}</span>
                                        </div>
                                        @endif
                                        <div class="col-6">
                                            <small class="text-muted d-block mb-1"><i class="fas fa-calendar me-1"></i>{{ __('ui.medicine.added') }}</small>
                                            <span class="fw-semibold">{{ $medicine->created_at->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                @if($medicine->activeSchedule)
                                    <div class="schedule-status active mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="status-dot bg-success me-3"></div>
                                            <div>
                                                <span class="text-success fw-bold d-block small">{{ __('ui.medicine.active_schedule') }}</span>
                                                <small class="text-muted">{{ $medicine->activeSchedule->formattedDosageTimes }}</small>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="schedule-status inactive mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="status-dot bg-warning me-3"></div>
                                            <div>
                                                <span class="text-warning fw-bold d-block small">{{ __('ui.medicine.no_schedule') }}</span>
                                                <small class="text-muted">{{ __('ui.medicine.set_up_reminders') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="d-flex gap-2">
                                    <a href="{{ route('medicine.schedules', ['medicine_id' => $medicine->id]) }}" class="btn btn-outline-primary flex-grow-1 rounded-pill">
                                        <i class="fas fa-clock me-1"></i>{{ __('ui.medicine.schedules') }}
                                    </a>
                                    @if($medicine->activeSchedule)
                                        <a href="{{ route('medicine.reminders') }}" class="btn btn-outline-success flex-grow-1 rounded-pill">
                                            <i class="fas fa-bell me-1"></i>{{ __('ui.medicine.reminders') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon mb-4">
                    <div class="bg-primary-soft d-inline-block p-4 rounded-circle">
                        <i class="fas fa-pills fa-4x text-primary"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-3">{{ __('ui.medicine.no_medicines_title') }}</h3>
                <p class="text-muted mb-4 col-md-6 mx-auto">{{ __('ui.medicine.no_medicines_message') }}</p>
                <a href="{{ route('medicine.add') }}" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                    <i class="fas fa-plus-circle me-2"></i>{{ __('ui.medicine.add_first_medicine') }}
                </a>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .medicine-section {
        background: #f8f9fb;
        min-height: calc(100vh - 200px);
        padding: 2.5rem 0 3rem;
    }
    
    .medicine-card {
        background: white;
        border-radius: 16px;
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 0, 0, 0.04);
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
    }
    
    .medicine-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
    }
    
    .bg-primary-soft {
        background: rgba(102, 126, 234, 0.12);
    }
    
    .info-panel {
        background: #f8f9fb;
        padding: 1rem;
        border-radius: 12px;
    }
    
    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }
    
    .schedule-status {
        padding: 0.75rem;
        border-radius: 12px;
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
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
    }
    
    .empty-state-icon {
        opacity: 0.9;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }
    
    .btn-outline-primary,
    .btn-outline-success {
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .btn-outline-primary:hover,
    .btn-outline-success:hover {
        transform: translateY(-2px);
    }
</style>
@endpush
@endsection