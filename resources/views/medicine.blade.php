@extends('layouts.app')

@section('title', 'Medicine Management - My Doctor')

@section('content')
<div class="container py-5">
    <!-- Header Section with subtle branding -->
    <div class="text-center mb-5">
        <div class="d-inline-block bg-light px-4 py-2 rounded-pill mb-3">
            <span class="text-primary fw-semibold"><i class="fas fa-pills me-2"></i>Medicine Manager</span>
        </div>
        <h1 class="display-5 fw-bold text-dark mb-3">Medicine Management</h1>
        <p class="lead text-secondary col-lg-8 mx-auto">Track your medications, set reminders, and monitor adherence all in one place</p>
    </div>
    
    <!-- Feature Cards - Clean and professional -->
    <div class="row g-4">
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm feature-card">
                <div class="card-body p-4 p-xl-5">
                    <div class="feature-icon-wrapper bg-light rounded-4 mb-4">
                        <i class="fas fa-pills fa-3x text-primary"></i>
                    </div>
                    <h4 class="fw-semibold mb-3">My Medicines</h4>
                    <p class="text-secondary mb-4">View and manage all your medicines with dosage details, schedules, and important information.</p>
                    <div class="d-flex align-items-center justify-content-between">
                        <a href="{{ route('medicine.my-medicines') }}" class="btn btn-primary rounded-pill px-4">
                            <span>View Medicines</span>
                            <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                        <span class="text-primary small">+ Add New</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm feature-card">
                <div class="card-body p-4 p-xl-5">
                    <div class="feature-icon-wrapper bg-light rounded-4 mb-4">
                        <i class="fas fa-bell fa-3x text-success"></i>
                    </div>
                    <h4 class="fw-semibold mb-3">Reminders</h4>
                    <p class="text-secondary mb-4">Never miss a dose with smart reminders that notify you at the right times throughout the day.</p>
                    <div class="d-flex align-items-center justify-content-between">
                        <a href="{{ route('medicine.reminders') }}" class="btn btn-success rounded-pill px-4">
                            <span>View Reminders</span>
                            <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                        <span class="text-success small">Active</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm feature-card">
                <div class="card-body p-4 p-xl-5">
                    <div class="feature-icon-wrapper bg-light rounded-4 mb-4">
                        <i class="fas fa-chart-line fa-3x text-info"></i>
                    </div>
                    <h4 class="fw-semibold mb-3">Adherence Logs</h4>
                    <p class="text-secondary mb-4">Track your medication adherence over time with detailed logs and insights into your progress.</p>
                    <div class="d-flex align-items-center justify-content-between">
                        <a href="{{ route('medicine.logs') }}" class="btn btn-info rounded-pill px-4 text-white">
                            <span>View Logs</span>
                            <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                        <span class="text-info small">Weekly</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions Section - Clean and minimal -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 bg-light quick-actions-card">
                <div class="card-body p-4">
                    <div class="row align-items-center g-3">
                        <div class="col-lg-4">
                            <h5 class="fw-semibold mb-1"><i class="fas fa-bolt text-primary me-2"></i>Quick Actions</h5>
                            <p class="text-secondary small mb-0">Common tasks to manage your medications</p>
                        </div>
                        <div class="col-lg-8">
                            <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                                <a href="{{ route('medicine.add') }}" class="btn btn-outline-primary rounded-pill px-4">
                                    <i class="fas fa-plus-circle me-2"></i>Add Medicine
                                </a>
                                <a href="{{ route('medicine.schedules') }}" class="btn btn-outline-success rounded-pill px-4">
                                    <i class="fas fa-clock me-2"></i>Schedules
                                </a>
                                <a href="{{ route('medicine.logs', ['days' => 7]) }}" class="btn btn-outline-info rounded-pill px-4">
                                    <i class="fas fa-calendar-week me-2"></i>Weekly Report
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
    /* Clean and professional styling */
    .feature-card {
        border-radius: 20px;
        transition: all 0.3s ease;
        background: white;
    }
    
    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 30px rgba(0,0,0,0.1) !important;
    }
    
    .feature-icon-wrapper {
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .feature-card:hover .feature-icon-wrapper {
        transform: scale(1.05);
    }
    
    .quick-actions-card {
        border-radius: 16px;
    }
    
    /* Button styling */
    .btn {
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .btn-outline-primary:hover,
    .btn-outline-success:hover,
    .btn-outline-info:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 12px rgba(0,0,0,0.1);
    }
    
    .btn-primary, .btn-success, .btn-info {
        border: none;
    }
    
    .btn-primary:hover {
        background: #5a67d8;
        transform: translateY(-2px);
        box-shadow: 0 5px 12px rgba(102, 126, 234, 0.3);
    }
    
    .btn-success:hover {
        background: #2ecc71;
        transform: translateY(-2px);
        box-shadow: 0 5px 12px rgba(46, 204, 113, 0.3);
    }
    
    .btn-info:hover {
        background: #00b5d9;
        transform: translateY(-2px);
        box-shadow: 0 5px 12px rgba(23, 162, 184, 0.3);
    }
    
    /* Typography */
    h1, h2, h3, h4, h5, h6 {
        letter-spacing: -0.02em;
    }
    
    .text-secondary {
        color: #6c757d !important;
        font-size: 0.95rem;
        line-height: 1.6;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .display-5 {
            font-size: 2.2rem;
        }
        
        .feature-card {
            padding: 0.5rem;
        }
        
        .btn {
            width: 100%;
        }
        
        .d-flex.flex-wrap {
            flex-direction: column;
        }
    }
    
    /* Subtle hover effect */
    .feature-card {
        border: 1px solid rgba(0,0,0,0.02);
    }
</style>
@endpush
@endsection