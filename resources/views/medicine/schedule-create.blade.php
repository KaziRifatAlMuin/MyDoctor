@extends('layouts.app')

@section('title', 'Add Schedule - My Doctor')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('medicine.index') }}" class="text-decoration-none">Medicine</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('medicine.my-medicines') }}" class="text-decoration-none">My Medicines</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('medicine.schedules', ['medicine_id' => $medicine->id]) }}" class="text-decoration-none">Schedules</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add Schedule</li>
                </ol>
            </nav>

            <!-- Form Card -->
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add Schedule for {{ $medicine->medicine_name }}</h4>
                </div>
                
                <div class="card-body p-4">
                    <form action="{{ route('medicine.schedules.store') }}" method="POST">
                        @csrf
                        
                        <input type="hidden" name="medicine_id" value="{{ $medicine->id }}">
                        
                        <!-- Basic Schedule Info -->
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="dosage_period_days" class="form-label fw-bold">
                                    <i class="fas fa-calendar me-2 text-primary"></i>Dosage Period
                                </label>
                                <select class="form-select form-select-lg @error('dosage_period_days') is-invalid @enderror" 
                                        id="dosage_period_days" name="dosage_period_days" required>
                                    <option value="1" {{ old('dosage_period_days') == 1 ? 'selected' : '' }}>Daily</option>
                                    <option value="7" {{ old('dosage_period_days') == 7 ? 'selected' : '' }}>Weekly</option>
                                    <option value="14" {{ old('dosage_period_days') == 14 ? 'selected' : '' }}>Every 14 days</option>
                                    <option value="30" {{ old('dosage_period_days') == 30 ? 'selected' : '' }}>Monthly</option>
                                    <option value="0" {{ old('dosage_period_days') == 0 ? 'selected' : '' }}>As needed (once)</option>
                                </select>
                                <small class="text-muted">How often does this schedule repeat?</small>
                                @error('dosage_period_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="frequency_per_day" class="form-label fw-bold">
                                    <i class="fas fa-sync-alt me-2 text-primary"></i>Times Per Day
                                </label>
                                <input type="number" class="form-control form-control-lg @error('frequency_per_day') is-invalid @enderror" 
                                    id="frequency_per_day" name="frequency_per_day" 
                                    value="{{ old('frequency_per_day', 1) }}" 
                                    min="1" max="24" required>
                                <small class="text-muted">How many times a day?</small>
                                @error('frequency_per_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Interval Hours (Optional) -->
                        <div class="mb-4">
                            <label for="interval_hours" class="form-label fw-bold">
                                <i class="fas fa-hourglass-half me-2 text-primary"></i>Interval Between Doses (Hours)
                            </label>
                            <input type="number" class="form-control form-control-lg @error('interval_hours') is-invalid @enderror" 
                                id="interval_hours" name="interval_hours" value="{{ old('interval_hours') }}" 
                                min="1" max="24" placeholder="e.g., 8 for every 8 hours">
                            <small class="text-muted">Optional: Leave empty if not needed</small>
                            @error('interval_hours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Dosage Times Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-clock me-2 text-primary"></i>Dosage Times
                            </label>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Select the times you need to take this medicine. You can select multiple times.
                            </div>
                            
                            <div class="time-selector bg-light p-4 rounded-3">
                                <div class="row">
                                    @for($hour = 0; $hour < 24; $hour++)
                                        @php
                                            $time1 = sprintf('%02d:00', $hour);
                                            $time2 = sprintf('%02d:30', $hour);
                                        @endphp
                                        <div class="col-md-2 col-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input time-checkbox" type="checkbox" 
                                                    name="times[]" value="{{ $time1 }}" id="time_{{ $hour }}_00"
                                                    {{ in_array($time1, old('times', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="time_{{ $hour }}_00">
                                                    {{ $time1 }}
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input time-checkbox" type="checkbox" 
                                                    name="times[]" value="{{ $time2 }}" id="time_{{ $hour }}_30"
                                                    {{ in_array($time2, old('times', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="time_{{ $hour }}_30">
                                                    {{ $time2 }}
                                                </label>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                            <input type="hidden" name="dosage_time_binary" id="dosage_time_binary" value="{{ old('dosage_time_binary', '') }}">
                            @error('dosage_time_binary')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-2">Selected times will be used for reminders</small>
                        </div>

                        <!-- Date Range -->
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="start_date" class="form-label fw-bold">
                                    <i class="fas fa-play me-2 text-primary"></i>Start Date
                                </label>
                                <input type="date" class="form-control form-control-lg @error('start_date') is-invalid @enderror" 
                                    id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="end_date" class="form-label fw-bold">
                                    <i class="fas fa-stop me-2 text-primary"></i>End Date
                                </label>
                                <input type="date" class="form-control form-control-lg @error('end_date') is-invalid @enderror" 
                                    id="end_date" name="end_date" value="{{ old('end_date') }}">
                                <small class="text-muted">Leave empty for ongoing schedule</small>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Active Status -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                    {{ old('is_active', true) ? 'checked' : '' }} value="1">
                                <label class="form-check-label fw-bold" for="is_active">
                                    <i class="fas fa-power-off me-2 text-primary"></i>Active Schedule
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">Inactive schedules won't generate reminders</small>
                        </div>

                        <!-- Summary -->
                        <div class="alert alert-primary bg-light border-0 rounded-3 p-3 mb-4" id="scheduleSummary" style="display: none;">
                            <h6 class="fw-bold mb-2"><i class="fas fa-info-circle me-2"></i>Schedule Summary</h6>
                            <p class="mb-1" id="summaryText"></p>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-3">
                            <a href="{{ route('medicine.schedules', ['medicine_id' => $medicine->id]) }}" 
                               class="btn btn-outline-secondary btn-lg flex-grow-1">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                                <i class="fas fa-save me-2"></i>Save Schedule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        updateBinaryTime();
        updateSummary();
        
        // Add event listeners
        document.querySelectorAll('.time-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateBinaryTime();
                updateSummary();
            });
        });
        
        document.getElementById('frequency_per_day').addEventListener('input', updateSummary);
        document.getElementById('dosage_period_days').addEventListener('change', updateSummary);
    });

    function updateBinaryTime() {
        let binary = '';
        for (let i = 0; i < 48; i++) {
            const hour = Math.floor(i / 2);
            const minute = (i % 2) * 30;
            const timeStr = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
            const checkbox = document.querySelector(`input[value="${timeStr}"]`);
            binary += checkbox && checkbox.checked ? '1' : '0';
        }
        document.getElementById('dosage_time_binary').value = binary;
    }

    function updateSummary() {
        const selectedTimes = Array.from(document.querySelectorAll('.time-checkbox:checked'))
            .map(cb => cb.value)
            .sort();
        
        const frequency = document.getElementById('frequency_per_day').value;
        const periodSelect = document.getElementById('dosage_period_days');
        const period = periodSelect.options[periodSelect.selectedIndex].text;
        
        const summaryDiv = document.getElementById('scheduleSummary');
        
        if (selectedTimes.length > 0) {
            let summary = `You'll take this medicine ${selectedTimes.length} time(s) per day`;
            if (frequency && frequency != selectedTimes.length) {
                summary += ` (selected ${selectedTimes.length} times, but frequency is set to ${frequency})`;
            }
            summary += `. Schedule repeats ${period.toLowerCase()}.`;
            
            document.getElementById('summaryText').textContent = summary;
            summaryDiv.style.display = 'block';
        } else {
            summaryDiv.style.display = 'none';
        }
    }
</script>
@endpush

@push('styles')
<style>
    .time-selector {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
    }
    
    .time-selector .form-check {
        margin-bottom: 0.25rem;
    }
    
    .time-selector::-webkit-scrollbar {
        width: 8px;
    }
    
    .time-selector::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .time-selector::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .time-selector::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>
@endpush
@endsection