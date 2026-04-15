@extends('layouts.app')

@section('title', __('ui.auto.Medicine'))

@section('content')
<div class="medicine-section">
    <div class="container" style="max-width: 1140px;">
        <!-- Header Section -->
        <div class="text-center mb-5">
            <div class="d-inline-block bg-light px-4 py-2 rounded-pill mb-3">
                <span class="text-primary fw-semibold"><i class="fas fa-pills me-2"></i>{{ __('ui.medicine.medicine_manager') }}</span>
            </div>
            <h1 class="display-5 fw-bold text-dark mb-3">{{ __('ui.medicine.medicine_management') }}</h1>
            <p class="lead text-secondary col-lg-8 mx-auto">{{ __('ui.medicine.medicine_management_desc') }}</p>
        </div>
        
        <!-- Feature Cards -->
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="feature-card h-100">
                    <div class="feature-icon-wrapper mb-4">
                        <div class="feature-icon purple"><i class="fas fa-pills fa-2x"></i></div>
                    </div>
                    <h4 class="fw-bold mb-3">{{ __('ui.medicine.my_medicines') }}</h4>
                    <p class="text-muted mb-4">{{ __('ui.medicine.my_medicines_desc') }}</p>
                    <div class="d-flex align-items-center justify-content-between">
                        <a href="{{ route('medicine.my-medicines') }}" class="btn btn-primary rounded-pill px-4">
                            <span>{{ __('ui.medicine.view_medicines') }}</span>
                            <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                        <span class="text-primary small">{{ __('ui.medicine.add_new') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card h-100">
                    <div class="feature-icon-wrapper mb-4">
                        <div class="feature-icon green"><i class="fas fa-bell fa-2x"></i></div>
                    </div>
                    <h4 class="fw-bold mb-3">{{ __('ui.medicine.reminders') }}</h4>
                    <p class="text-muted mb-4">{{ __('ui.medicine.reminders_desc') }}</p>
                    <div class="d-flex align-items-center justify-content-between">
                        <a href="{{ route('medicine.reminders') }}" class="btn btn-success rounded-pill px-4">
                            <span>{{ __('ui.medicine.view_reminders') }}</span>
                            <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                        <span class="text-success small">{{ __('ui.medicine.active') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card h-100">
                    <div class="feature-icon-wrapper mb-4">
                        <div class="feature-icon info"><i class="fas fa-chart-line fa-2x"></i></div>
                    </div>
                    <h4 class="fw-bold mb-3">{{ __('ui.medicine.adherence_logs') }}</h4>
                    <p class="text-muted mb-4">{{ __('ui.medicine.adherence_logs_desc') }}</p>
                    <div class="d-flex align-items-center justify-content-between">
                        <a href="{{ route('medicine.logs') }}" class="btn btn-info rounded-pill px-4 text-white">
                            <span>{{ __('ui.medicine.view_logs') }}</span>
                            <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                        <span class="text-info small">{{ __('ui.medicine.weekly') }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="quick-actions-card">
                    <div class="row align-items-center g-3">
                        <div class="col-lg-4">
                            <h5 class="fw-bold mb-1"><i class="fas fa-bolt text-primary me-2"></i>{{ __('ui.medicine.quick_actions') }}</h5>
                            <p class="text-muted small mb-0">{{ __('ui.medicine.quick_actions_desc') }}</p>
                        </div>
                        <div class="col-lg-8">
                            <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                                <a href="{{ route('medicine.add') }}" class="btn btn-outline-primary rounded-pill px-4">
                                    <i class="fas fa-plus-circle me-2"></i>{{ __('ui.medicine.add_medicine') }}
                                </a>
                                <a href="{{ route('medicine.schedules') }}" class="btn btn-outline-success rounded-pill px-4">
                                    <i class="fas fa-clock me-2"></i>{{ __('ui.medicine.schedules') }}
                                </a>
                                <a href="{{ route('medicine.logs', ['days' => 7]) }}" class="btn btn-outline-info rounded-pill px-4">
                                    <i class="fas fa-calendar-week me-2"></i>{{ __('ui.medicine.weekly_report') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
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
    
    .feature-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 0, 0, 0.04);
    }
    
    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
    }
    
    .feature-icon-wrapper {
        margin-bottom: 1.5rem;
    }
    
    .feature-icon {
        width: 70px;
        height: 70px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .feature-icon.purple {
        background: rgba(102, 126, 234, 0.12);
        color: #667eea;
    }
    
    .feature-icon.green {
        background: rgba(40, 167, 69, 0.12);
        color: #28a745;
    }
    
    .feature-icon.info {
        background: rgba(23, 162, 184, 0.12);
        color: #17a2b8;
    }
    
    .feature-card:hover .feature-icon {
        transform: scale(1.05);
    }
    
    .quick-actions-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
        border: 1px solid rgba(0, 0, 0, 0.04);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }
    
    .btn-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
    }
    
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
    }
    
    .btn-info {
        background: linear-gradient(135deg, #17a2b8 0%, #0dcaf0 100%);
        border: none;
    }
    
    .btn-info:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(23, 162, 184, 0.3);
    }
    
    .btn-outline-primary,
    .btn-outline-success,
    .btn-outline-info {
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .btn-outline-primary:hover,
    .btn-outline-success:hover,
    .btn-outline-info:hover {
        transform: translateY(-2px);
    }
    
    @media (max-width: 768px) {
        .feature-card {
            padding: 1.5rem;
        }
        
        .btn-outline-primary,
        .btn-outline-success,
        .btn-outline-info {
            width: 100%;
        }
        
        .d-flex.flex-wrap {
            flex-direction: column;
        }
    }
</style>
@endpush
@endsection