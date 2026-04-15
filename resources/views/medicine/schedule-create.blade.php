@extends('layouts.app')

@section('title', __('ui.medicine.add_schedule_title'))

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb bg-light p-3 rounded-3">
                    <li class="breadcrumb-item"><a href="{{ route('medicine.index') }}" class="text-decoration-none"><i class="fas fa-home me-1"></i>{{ __('ui.medicine.medicine') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('medicine.my-medicines') }}" class="text-decoration-none"><i class="fas fa-pills me-1"></i>{{ __('ui.medicine.my_medicines') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('medicine.schedules', ['medicine_id' => $medicine->id]) }}" class="text-decoration-none"><i class="fas fa-clock me-1"></i>{{ __('ui.medicine.schedules') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('ui.medicine.add_schedule') }}</li>
                </ol>
            </nav>

            <div class="card border-0 shadow-sm mb-4 bg-primary-soft">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="medicine-icon bg-primary text-white p-3 rounded-3 me-3">
                            <i class="fas fa-pills fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">{{ $medicine->medicine_name }}</h5>
                            <p class="text-muted mb-0">
                                {{ $medicine->typeLabel ?? __('ui.medicine.medicine') }} • 
                                {{ $medicine->value_per_dose ?? '' }} {{ $medicine->unitLabel ?? '' }} •
                                {{ $medicine->ruleLabel ?? __('ui.medicine.anytime') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0"><i class="fas fa-plus-circle me-2"></i>{{ __('ui.medicine.add_new_schedule') }}</h4>
                </div>
                
                <div class="card-body p-4 p-lg-5">
                    <form action="{{ route('medicine.schedules.store') }}" method="POST">
                        @csrf
                        
                        <input type="hidden" name="medicine_id" value="{{ $medicine->id }}">
                        
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="dosage_period_days" class="form-label fw-bold">
                                    <i class="fas fa-calendar me-2 text-primary"></i>{{ __('ui.medicine.dosage_period') }}
                                </label>
                                <select class="form-select form-select-lg @error('dosage_period_days') is-invalid @enderror" 
                                        id="dosage_period_days" name="dosage_period_days" required>
                                    <option value="1" {{ old('dosage_period_days') == 1 ? 'selected' : '' }}>{{ __('ui.medicine.daily') }}</option>
                                    <option value="7" {{ old('dosage_period_days') == 7 ? 'selected' : '' }}>{{ __('ui.medicine.weekly') }}</option>
                                    <option value="14" {{ old('dosage_period_days') == 14 ? 'selected' : '' }}>{{ __('ui.medicine.every_14_days') }}</option>
                                    <option value="30" {{ old('dosage_period_days') == 30 ? 'selected' : '' }}>{{ __('ui.medicine.monthly') }}</option>
                                    <option value="0" {{ old('dosage_period_days') == 0 ? 'selected' : '' }}>{{ __('ui.medicine.as_needed_once') }}</option>
                                </select>
                                <small class="text-muted">{{ __('ui.medicine.dosage_period_help') }}</small>
                                @error('dosage_period_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="frequency_per_day" class="form-label fw-bold">
                                    <i class="fas fa-sync-alt me-2 text-primary"></i>{{ __('ui.medicine.times_per_day') }}
                                </label>
                                <input type="number" class="form-control form-control-lg @error('frequency_per_day') is-invalid @enderror" 
                                    id="frequency_per_day" name="frequency_per_day" 
                                    value="{{ old('frequency_per_day', 1) }}" 
                                    min="1" max="24" required>
                                <small class="text-muted">{{ __('ui.medicine.times_per_day_help') }}</small>
                                @error('frequency_per_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="interval_hours" class="form-label fw-bold">
                                <i class="fas fa-hourglass-half me-2 text-primary"></i>{{ __('ui.medicine.interval_between_doses') }}
                            </label>
                            <input type="number" class="form-control form-control-lg @error('interval_hours') is-invalid @enderror" 
                                id="interval_hours" name="interval_hours" value="{{ old('interval_hours') }}" 
                                min="1" max="24" placeholder="{{ __('ui.medicine.interval_placeholder') }}">
                            <small class="text-muted">{{ __('ui.medicine.interval_help') }}</small>
                            @error('interval_hours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-clock me-2 text-primary"></i>{{ __('ui.medicine.dosage_times') }}
                            </label>
                            <div class="alert alert-info d-flex align-items-center">
                                <i class="fas fa-info-circle fa-2x me-3"></i>
                                <div>
                                    <strong>{{ __('ui.medicine.select_medication_times') }}</strong><br>
                                    <small>{{ __('ui.medicine.select_times_help') }}</small>
                                </div>
                            </div>
                            
                            <div class="time-selector bg-light p-4 rounded-3">
                                <div class="row g-3">
                                    @for($hour = 0; $hour < 24; $hour++)
                                        @php
                                            $time1 = sprintf('%02d:00', $hour);
                                            $time2 = sprintf('%02d:30', $hour);
                                        @endphp
                                        <div class="col-lg-2 col-md-3 col-6">
                                            <div class="time-slot mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input time-checkbox" type="checkbox" 
                                                        name="times[]" value="{{ $time1 }}" id="time_{{ $hour }}_00"
                                                        {{ in_array($time1, old('times', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label w-100" for="time_{{ $hour }}_00">
                                                        <span class="badge bg-white text-dark border w-100 py-2">{{ $time1 }}</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="time-slot mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input time-checkbox" type="checkbox" 
                                                        name="times[]" value="{{ $time2 }}" id="time_{{ $hour }}_30"
                                                        {{ in_array($time2, old('times', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label w-100" for="time_{{ $hour }}_30">
                                                        <span class="badge bg-white text-dark border w-100 py-2">{{ $time2 }}</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                            <input type="hidden" name="dosage_time_binary" id="dosage_time_binary" value="{{ old('dosage_time_binary', '') }}">
                            @error('dosage_time_binary')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                            <div class="mt-3">
                                <span class="selected-count badge bg-primary p-2">{{ __('ui.medicine.selected') }}: <span id="selectedCount">0</span> {{ __('ui.medicine.times') }}</span>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label fw-bold">
                                    <i class="fas fa-play me-2 text-primary"></i>{{ __('ui.medicine.start_date') }}
                                </label>
                                <input type="date" class="form-control form-control-lg @error('start_date') is-invalid @enderror" 
                                    id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="end_date" class="form-label fw-bold">
                                    <i class="fas fa-stop me-2 text-primary"></i>{{ __('ui.medicine.end_date') }}
                                </label>
                                <input type="date" class="form-control form-control-lg @error('end_date') is-invalid @enderror" 
                                    id="end_date" name="end_date" value="{{ old('end_date') }}">
                                <small class="text-muted">{{ __('ui.medicine.end_date_help') }}</small>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4 p-3 bg-light rounded-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                    {{ old('is_active', true) ? 'checked' : '' }} value="1" style="transform: scale(1.2);">
                                <label class="form-check-label fw-bold ms-2" for="is_active">
                                    <i class="fas fa-power-off me-2 text-primary"></i>{{ __('ui.medicine.active_schedule') }}
                                </label>
                            </div>
                            <small class="text-muted d-block mt-2 ms-4">{{ __('ui.medicine.inactive_schedule_help') }}</small>
                        </div>

                        <div class="alert alert-primary bg-primary-soft border-0 rounded-3 p-4 mb-4" id="scheduleSummary" style="display: none;">
                            <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2"></i>{{ __('ui.medicine.schedule_summary') }}</h6>
                            <p class="mb-2" id="summaryText"></p>
                            <div class="summary-details small" id="summaryDetails"></div>
                        </div>

                        <div class="d-flex gap-3">
                            <a href="{{ route('medicine.schedules', ['medicine_id' => $medicine->id]) }}" 
                               class="btn btn-outline-secondary btn-lg flex-grow-1 rounded-pill">
                                <i class="fas fa-times me-2"></i>{{ __('ui.medicine.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg flex-grow-1 rounded-pill">
                                <i class="fas fa-save me-2"></i>{{ __('ui.medicine.save_schedule') }}
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
    // All JavaScript remains exactly the same
    document.addEventListener('DOMContentLoaded', function() {
        updateBinaryTime();
        updateSummary();
        updateSelectedCount();
        
        document.querySelectorAll('.time-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateBinaryTime();
                updateSummary();
                updateSelectedCount();
                
                const badge = this.nextElementSibling.querySelector('.badge');
                if (this.checked) {
                    badge.classList.remove('bg-white', 'text-dark', 'border');
                    badge.classList.add('bg-primary', 'text-white');
                } else {
                    badge.classList.remove('bg-primary', 'text-white');
                    badge.classList.add('bg-white', 'text-dark', 'border');
                }
            });
        });
        
        document.getElementById('frequency_per_day').addEventListener('input', updateSummary);
        document.getElementById('dosage_period_days').addEventListener('change', updateSummary);
        
        document.querySelectorAll('.time-checkbox:checked').forEach(checkbox => {
            const badge = checkbox.nextElementSibling.querySelector('.badge');
            badge.classList.remove('bg-white', 'text-dark', 'border');
            badge.classList.add('bg-primary', 'text-white');
        });
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

    function updateSelectedCount() {
        const selected = document.querySelectorAll('.time-checkbox:checked').length;
        document.getElementById('selectedCount').textContent = selected;
    }

    function updateSummary() {
        const selectedTimes = Array.from(document.querySelectorAll('.time-checkbox:checked'))
            .map(cb => cb.value)
            .sort();
        
        const frequency = document.getElementById('frequency_per_day').value;
        const periodSelect = document.getElementById('dosage_period_days');
        const period = periodSelect.options[periodSelect.selectedIndex].text;
        
        const summaryDiv = document.getElementById('scheduleSummary');
        const summaryText = document.getElementById('summaryText');
        const summaryDetails = document.getElementById('summaryDetails');
        
        if (selectedTimes.length > 0) {
            let summary = `{{ __('ui.medicine.you_will_take') }} <strong>${selectedTimes.length} {{ __('ui.medicine.times_per_day_lower') }}</strong>`;
            if (frequency && frequency != selectedTimes.length) {
                summary += ` <span class="text-warning">({{ __('ui.medicine.warning_selected_vs_frequency', ['selected' => '', 'frequency' => '']) }}${selectedTimes.length} {{ __('ui.medicine.times') }}, {{ __('ui.medicine.but_frequency_is') }} ${frequency})</span>`;
            }
            
            summaryText.innerHTML = summary;
            
            let details = '<strong>{{ __("ui.medicine.selected_times") }}</strong> ';
            details += selectedTimes.map(time => {
                const [hour, minute] = time.split(':');
                const hourNum = parseInt(hour);
                const ampm = hourNum >= 12 ? 'PM' : 'AM';
                const hour12 = hourNum % 12 || 12;
                return `${hour12}:${minute} ${ampm}`;
            }).join(', ');
            
            details += `<br><strong>{{ __("ui.medicine.schedule_repeats") }}</strong> ${period.toLowerCase()}`;
            summaryDetails.innerHTML = details;
            
            summaryDiv.style.display = 'block';
        } else {
            summaryDiv.style.display = 'none';
        }
    }
</script>
@endpush

@push('styles')
<style>
    /* All existing styles remain exactly the same */
    .bg-primary-soft {
        background: rgba(102, 126, 234, 0.05);
    }
    
    .medicine-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .time-selector {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 12px;
    }
    
    .time-selector .form-check {
        margin-bottom: 0.5rem;
    }
    
    .time-selector .form-check-input {
        display: none;
    }
    
    .time-selector .badge {
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .time-selector .badge:hover {
        background-color: #e9ecef !important;
        transform: translateY(-1px);
    }
    
    .time-selector .form-check-input:checked + .form-check-label .badge {
        background-color: #667eea !important;
        color: white !important;
        border-color: #667eea !important;
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
    
    .selected-count {
        font-size: 0.9rem;
    }
    
    .breadcrumb {
        border-radius: 12px;
    }
    
    .form-select-lg, .form-control-lg {
        border-radius: 12px;
    }
    
    .alert {
        border-radius: 12px;
    }
</style>
@endpush
@endsection