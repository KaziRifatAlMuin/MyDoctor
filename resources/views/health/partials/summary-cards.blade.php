{{-- ── Summary Stat Cards ── --}}
<div class="row g-3 mb-4">

    {{-- Total Metrics Recorded --}}
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-summary-card">
            <div class="stat-summary-icon purple">
                <i class="fas fa-chart-line"></i>
            </div>
            <div>
                <div class="stat-summary-label">{{ __('ui.health.total_metrics') }}</div>
                <div class="stat-summary-value">{{ $healthMetrics->count() }}</div>
                <div class="stat-summary-sub">{{ __('ui.health.recorded') }}</div>
            </div>
        </div>
    </div>

    {{-- Metric Types --}}
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-summary-card">
            <div class="stat-summary-icon blue">
                <i class="fas fa-layer-group"></i>
            </div>
            <div>
                <div class="stat-summary-label">{{ __('ui.health.metric_types') }}</div>
                <div class="stat-summary-value">{{ $metricsByType->count() }}</div>
                <div class="stat-summary-sub">{{ __('ui.health.tracked') }}</div>
            </div>
        </div>
    </div>

    {{-- Symptoms --}}
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-summary-card">
            <div class="stat-summary-icon orange">
                <i class="fas fa-notes-medical"></i>
            </div>
            <div>
                <div class="stat-summary-label">{{ __('ui.health.total_symptoms') }}</div>
                <div class="stat-summary-value">{{ $symptoms->count() }}</div>
                <div class="stat-summary-sub">{{ __('ui.health.logged') }}</div>
            </div>
        </div>
    </div>

    {{-- Medicines --}}
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-summary-card">
            <div class="stat-summary-icon green">
                <i class="fas fa-pills"></i>
            </div>
            <div>
                <div class="stat-summary-label">{{ __('ui.health.total_medicines') }}</div>
                <div class="stat-summary-value">{{ $medicines->count() }}</div>
                <div class="stat-summary-sub">{{ __('ui.health.prescribed') }}</div>
            </div>
        </div>
    </div>

    {{-- Adherence Rate --}}
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-summary-card">
            <div class="stat-summary-icon {{ $adherenceRate >= 80 ? 'green' : ($adherenceRate >= 50 ? 'orange' : 'red') }}">
                <i class="fas fa-check-double"></i>
            </div>
            <div>
                <div class="stat-summary-label">{{ __('ui.health.adherence_rate') }}</div>
                <div class="stat-summary-value">{{ $adherenceRate }}%</div>
                <div class="stat-summary-sub">{{ __('ui.health.days_30') }}</div>
            </div>
        </div>
    </div>

    {{-- Doses Taken --}}
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-summary-card">
            <div class="stat-summary-icon pink">
                <i class="fas fa-capsules"></i>
            </div>
            <div>
                <div class="stat-summary-label">{{ __('ui.health.total_taken') }}</div>
                <div class="stat-summary-value">{{ $totalTaken }}</div>
                <div class="stat-summary-sub">{{ __('ui.health.doses') }}</div>
            </div>
        </div>
    </div>

</div>