@extends('layouts.app')

@section('title', __('ui.medicine.reminders_title'))
@section('main_content_class', 'main-content main-content--wide')

@section('content')
<div class="medicine-section">
    <div class="container-fluid px-4 px-xl-5" style="max-width: 1600px;">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="display-5 fw-bold text-primary mb-1">
                    <i class="fas fa-bell me-2"></i>{{ __('ui.medicine.medicine_reminders') }}
                </h1>
                <p class="text-muted lead fs-6">{{ __('ui.medicine.reminders_subtitle') }}</p>
            </div>
            <a href="{{ route('medicine.my-medicines') }}" class="btn btn-outline-primary btn-lg rounded-pill px-4">
                <i class="fas fa-pills me-2"></i>{{ __('ui.medicine.my_medicines') }}
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-4 border-success mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Three Column Grid for Reminders -->
        <div class="row g-4">
            <!-- Missed Reminders Column -->
            @if(isset($missedReminders) && $missedReminders->count() > 0)
                <div class="col-xl-4 col-lg-12">
                    <div class="reminder-card missed h-100">
                        <div class="reminder-card-header danger">
                            <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>{{ __('ui.medicine.missed_reminders') }}</h5>
                            <span class="badge bg-white text-danger ms-2">{{ $missedReminders->count() }}</span>
                        </div>
                        <div class="reminder-card-body p-0">
                            @foreach($missedReminders as $reminder)
                                <div class="reminder-item missed">
                                    <div class="reminder-info">
                                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                            <h6 class="fw-bold mb-0">{{ $reminder->schedule->medicine->medicine_name }}</h6>
                                            <span class="badge bg-danger">{{ __('ui.medicine.missed') }}</span>
                                        </div>
                                        <div class="reminder-details">
                                            <small class="text-muted"><i class="fas fa-clock me-1"></i>{{ \Carbon\Carbon::parse($reminder->reminder_at)->format('M d, Y - h:i A') }}</small>
                                            <small class="text-muted"><i class="fas fa-capsules me-1"></i>{{ $reminder->schedule->medicine->ruleLabel ?? __('ui.medicine.anytime') }}</small>
                                            @if($reminder->schedule->medicine->value_per_dose)
                                                <small class="text-muted"><i class="fas fa-weight me-1"></i>{{ $reminder->schedule->medicine->value_per_dose }} {{ $reminder->schedule->medicine->unitLabel ?? '' }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    <button class="btn btn-success rounded-pill px-4" onclick="markAsTaken({{ $reminder->id }})">
                                        <i class="fas fa-check me-2"></i>{{ __('ui.medicine.mark_taken') }}
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Today's Reminders Column -->
            <div class="{{ (isset($missedReminders) && $missedReminders->count() > 0) ? 'col-xl-4 col-lg-12' : 'col-xl-6 col-lg-12' }}">
                <div class="reminder-card today h-100">
                    <div class="reminder-card-header primary">
                        <h5 class="mb-0"><i class="fas fa-calendar-day me-2"></i>{{ __('ui.medicine.today_reminders') }}</h5>
                        @if(isset($todayReminders) && $todayReminders->count() > 0)
                            <span class="badge bg-white text-primary ms-2">{{ $todayReminders->count() }}</span>
                        @endif
                    </div>
                    <div class="reminder-card-body p-0">
                        @if(isset($todayReminders) && $todayReminders->count() > 0)
                            @foreach($todayReminders as $reminder)
                                <div class="reminder-item">
                                    <div class="reminder-info">
                                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                            <h6 class="fw-bold mb-0">{{ $reminder->schedule->medicine->medicine_name }}</h6>
                                            @if($reminder->status == 'taken')
                                                <span class="badge bg-success">{{ __('ui.medicine.taken') }}</span>
                                            @elseif($reminder->status == 'pending')
                                                <span class="badge bg-warning text-dark">{{ __('ui.medicine.pending') }}</span>
                                            @endif
                                        </div>
                                        <div class="reminder-details">
                                            <small class="text-muted"><i class="fas fa-clock me-1"></i>{{ \Carbon\Carbon::parse($reminder->reminder_at)->format('h:i A') }}</small>
                                            <small class="text-muted"><i class="fas fa-capsules me-1"></i>{{ $reminder->schedule->medicine->ruleLabel ?? __('ui.medicine.anytime') }}</small>
                                            @if($reminder->schedule->medicine->value_per_dose)
                                                <small class="text-muted"><i class="fas fa-weight me-1"></i>{{ $reminder->schedule->medicine->value_per_dose }} {{ $reminder->schedule->medicine->unitLabel ?? '' }}</small>
                                            @endif
                                        </div>
                                        @if($reminder->status == 'taken' && $reminder->taken_at)
                                            <small class="text-success d-block mt-2">
                                                <i class="fas fa-check-circle me-1"></i>{{ __('ui.medicine.taken_at') }} {{ $reminder->taken_at->setTimezone('Asia/Dhaka')->format('h:i A') }}
                                            </small>
                                        @endif
                                    </div>
                                    @if($reminder->status == 'pending')
                                        <button class="btn btn-success rounded-pill px-4" onclick="markAsTaken({{ $reminder->id }})">
                                            <i class="fas fa-check me-2"></i>{{ __('ui.medicine.mark_taken') }}
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="empty-reminders">
                                <div class="empty-icon">
                                    <i class="fas fa-check-circle fa-3x text-success"></i>
                                </div>
                                <h5 class="fw-bold mb-2">{{ __('ui.medicine.all_caught_up') }}</h5>
                                <p class="text-muted mb-0">{{ __('ui.medicine.no_pending_reminders') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Upcoming Reminders Column -->
            @if(isset($upcomingReminders) && $upcomingReminders->count() > 0)
                <div class="{{ (isset($missedReminders) && $missedReminders->count() > 0) ? 'col-xl-4 col-lg-12' : 'col-xl-6 col-lg-12' }}">
                    <div class="reminder-card upcoming h-100">
                        <div class="reminder-card-header info">
                            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>{{ __('ui.medicine.upcoming_reminders') }}</h5>
                            <span class="badge bg-white text-info ms-2">{{ $upcomingReminders->count() }}</span>
                        </div>
                        <div class="reminder-card-body p-0">
                            @foreach($upcomingReminders as $reminder)
                                <div class="reminder-item upcoming">
                                    <div class="reminder-info">
                                        <h6 class="fw-bold mb-2">{{ $reminder->schedule->medicine->medicine_name }}</h6>
                                        <div class="reminder-details">
                                            <small class="text-muted"><i class="fas fa-clock me-1"></i>{{ \Carbon\Carbon::parse($reminder->reminder_at)->format('M d, Y - h:i A') }}</small>
                                            <small class="text-muted"><i class="fas fa-capsules me-1"></i>{{ $reminder->schedule->medicine->ruleLabel ?? __('ui.medicine.anytime') }}</small>
                                            @if($reminder->schedule->medicine->value_per_dose)
                                                <small class="text-muted"><i class="fas fa-weight me-1"></i>{{ $reminder->schedule->medicine->value_per_dose }} {{ $reminder->schedule->medicine->unitLabel ?? '' }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="badge bg-info text-white px-3 py-2 rounded-pill">{{ __('ui.medicine.upcoming') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<form id="taken-form" method="POST" style="display: none;">
    @csrf
</form>

@push('styles')
<style>
    .medicine-section {
        background: #f8f9fb;
        min-height: calc(100vh - 200px);
        padding: 2.5rem 0 3rem;
    }
    
    .reminder-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
        border: 1px solid rgba(0, 0, 0, 0.04);
        display: flex;
        flex-direction: column;
    }
    
    .reminder-card-header {
        padding: 1rem 1.5rem;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .reminder-card-header.primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .reminder-card-header.danger {
        background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
    }
    
    .reminder-card-header.info {
        background: linear-gradient(135deg, #17a2b8 0%, #0dcaf0 100%);
    }
    
    .reminder-card-body {
        flex: 1;
    }
    
    .reminder-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s ease;
        gap: 1rem;
    }
    
    .reminder-item:hover {
        background: #f8f9fb;
    }
    
    .reminder-item:last-child {
        border-bottom: none;
    }
    
    .reminder-item.missed {
        border-left: 3px solid #dc3545;
    }
    
    .reminder-item.upcoming {
        border-left: 3px solid #17a2b8;
    }
    
    .reminder-info {
        flex: 1;
    }
    
    .reminder-details {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .empty-reminders {
        text-align: center;
        padding: 3rem 2rem;
    }
    
    .empty-icon {
        margin-bottom: 1rem;
    }
    
    .badge {
        font-weight: 500;
        padding: 0.5em 1em;
    }
    
    .btn-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        white-space: nowrap;
    }
    
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
    }
    
    .btn-outline-primary {
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .btn-outline-primary:hover {
        transform: translateY(-2px);
    }
    
    @media (max-width: 1200px) {
        .reminder-item {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .btn-success {
            width: 100%;
        }
    }
    
    @media (max-width: 768px) {
        .reminder-details {
            flex-direction: column;
            gap: 0.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function markAsTaken(id) {
        if (confirm('{{ __("ui.medicine.mark_taken_confirm") }}')) {
            const form = document.getElementById('taken-form');
            form.action = '{{ url("/medicine/reminders") }}/' + id + '/taken';
            form.submit();
        }
    }
</script>
@endpush
@endsection