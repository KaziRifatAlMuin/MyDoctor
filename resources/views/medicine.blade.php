@extends('layouts.app')

@section('title', 'Medicine - My Doctor')

@section('content')
<div class="container py-5">
    <!-- Header Section -->
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold text-primary">Medicine Management</h1>
        <p class="lead text-muted">Track your medicines, set reminders, and monitor adherence</p>
    </div>
    
    <!-- Feature Cards -->
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-lg hover-card">
                <div class="card-body text-center p-4">
                    <div class="feature-icon-wrapper bg-primary-soft rounded-circle mx-auto mb-4">
                        <i class="fas fa-pills fa-3x text-primary"></i>
                    </div>
                    <h4 class="fw-bold mb-3">My Medicines</h4>
                    <p class="text-muted mb-4">View and manage all your medicines in one place</p>
                    <div class="d-grid">
                        <a href="{{ route('medicine.my-medicines') }}" class="btn btn-primary btn-lg rounded-pill">
                            <i class="fas fa-arrow-right me-2"></i>View Medicines
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-lg hover-card">
                <div class="card-body text-center p-4">
                    <div class="feature-icon-wrapper bg-success-soft rounded-circle mx-auto mb-4">
                        <i class="fas fa-bell fa-3x text-success"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Reminders</h4>
                    <p class="text-muted mb-4">Never miss a dose with smart reminders</p>
                    <div class="d-grid">
                        <a href="{{ route('medicine.reminders') }}" class="btn btn-success btn-lg rounded-pill">
                            <i class="fas fa-arrow-right me-2"></i>View Reminders
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-lg hover-card">
                <div class="card-body text-center p-4">
                    <div class="feature-icon-wrapper bg-info-soft rounded-circle mx-auto mb-4">
                        <i class="fas fa-chart-line fa-3x text-info"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Adherence Logs</h4>
                    <p class="text-muted mb-4">Track your medication adherence over time</p>
                    <div class="d-grid">
                        <a href="{{ route('medicine.logs') }}" class="btn btn-info btn-lg rounded-pill text-white">
                            <i class="fas fa-arrow-right me-2"></i>View Logs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body p-4">
                    <h5 class="mb-3"><i class="fas fa-bolt me-2 text-primary"></i>Quick Actions</h5>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ route('medicine.add') }}" class="btn btn-outline-primary rounded-pill">
                            <i class="fas fa-plus-circle me-2"></i>Add New Medicine
                        </a>
                        <a href="{{ route('medicine.schedules') }}" class="btn btn-outline-success rounded-pill">
                            <i class="fas fa-clock me-2"></i>View Schedules
                        </a>
                        <a href="{{ route('medicine.logs', ['days' => 7]) }}" class="btn btn-outline-info rounded-pill">
                            <i class="fas fa-calendar-week me-2"></i>Weekly Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .hover-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .hover-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
    }
    
    .feature-icon-wrapper {
        width: 100px;
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .bg-primary-soft { background: rgba(102, 126, 234, 0.1); }
    .bg-success-soft { background: rgba(40, 167, 69, 0.1); }
    .bg-info-soft { background: rgba(23, 162, 184, 0.1); }
</style>
@endpush
@endsection