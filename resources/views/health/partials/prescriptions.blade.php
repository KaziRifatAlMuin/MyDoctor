{{-- ── Prescriptions Tab ── --}}
@if ($medicines->isEmpty())
    <div class="health-card">
        <div class="health-card-body">
            <div class="empty-state">
                <i class="fas fa-prescription-bottle-alt d-block"></i>
                <p>No prescriptions found.<br>Your medicines and schedules will appear here.</p>
            </div>
        </div>
    </div>
@else
    <div class="row g-4">
        @foreach ($medicines as $medicine)
            <div class="col-md-6 col-lg-4">
                <div class="health-card">
                    {{-- Medicine Header --}}
                    <div class="health-card-header">
                        <h5>
                            <i class="fas fa-capsules"></i>
                            {{ $medicine->medicine_name }}
                        </h5>
                        @php
                            $isActive = $medicine->schedules->where('is_active', true)->isNotEmpty();
                        @endphp
                        <span class="{{ $isActive ? 'med-status-active' : 'med-status-inactive' }}">
                            {{ $isActive ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="health-card-body">
                        {{-- Medicine Details --}}
                        <div class="mb-3">
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                @if ($medicine->type)
                                    <span class="metric-value-pill">
                                        <strong>Type:</strong> {{ ucfirst($medicine->type) }}
                                    </span>
                                @endif
                                @if ($medicine->value_per_dose)
                                    <span class="metric-value-pill">
                                        <strong>Dose:</strong> {{ $medicine->value_per_dose }}{{ $medicine->unit }}
                                    </span>
                                @endif
                                @if ($medicine->dose_limit)
                                    <span class="metric-value-pill">
                                        <strong>Limit:</strong> {{ $medicine->dose_limit }}/day
                                    </span>
                                @endif
                            </div>
                            @if ($medicine->rule)
                                <div style="font-size: 0.82rem; color: #718096;">
                                    <i class="fas fa-info-circle me-1" style="color: #667eea;"></i>
                                    {{ $medicine->rule }}
                                </div>
                            @endif
                        </div>

                        {{-- Schedules --}}
                        @if ($medicine->schedules->isNotEmpty())
                            <div style="border-top: 1px solid #edf2f7; padding-top: 0.75rem;">
                                <div
                                    style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #a0aec0; margin-bottom: 0.5rem; font-weight: 600;">
                                    Schedules
                                </div>
                                @foreach ($medicine->schedules->take(3) as $schedule)
                                    <div class="d-flex justify-content-between align-items-center py-1 {{ !$loop->last ? 'border-bottom' : '' }}"
                                        style="font-size: 0.82rem;">
                                        <div>
                                            <span class="fw-semibold" style="color: #4a5568;">
                                                {{ $schedule->frequency_per_day }}x daily
                                            </span>
                                            @if ($schedule->interval_hours)
                                                <span class="text-muted"> &middot; every
                                                    {{ $schedule->interval_hours }}h</span>
                                            @endif
                                        </div>
                                        <div style="font-size: 0.75rem; color: #a0aec0;">
                                            {{ $schedule->start_date->format('M d') }}
                                            @if ($schedule->end_date)
                                                — {{ $schedule->end_date->format('M d') }}
                                            @else
                                                — ongoing
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div
                                style="font-size: 0.82rem; color: #a0aec0; border-top: 1px solid #edf2f7; padding-top: 0.75rem;">
                                <i class="fas fa-calendar-times me-1"></i> No schedules set
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
