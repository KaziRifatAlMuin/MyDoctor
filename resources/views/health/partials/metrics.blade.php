{{-- ── Metrics Tab ── --}}
@if ($metricsByType->isEmpty())
    <div class="health-card">
        <div class="health-card-body">
            <div class="empty-state">
                <i class="fas fa-chart-line d-block"></i>
                <p>No health metrics recorded yet.<br>Start tracking your blood pressure, heart rate, weight, and more.
                </p>
            </div>
        </div>
    </div>
@else
    {{-- Charts per metric type --}}
    <div class="row g-4 mb-4">
        @foreach ($metricsByType as $type => $records)
            <div class="col-md-6">
                <div class="health-card">
                    <div class="health-card-header">
                        <h5>
                            <i class="fas fa-wave-square"></i>
                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                        </h5>
                        <span class="health-card-badge bg-light text-muted">{{ $records->count() }} records</span>
                    </div>
                    <div class="health-card-body">
                        <div class="chart-container">
                            <canvas id="chart-{{ Str::slug($type) }}"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Full metrics history table --}}
    <div class="health-card">
        <div class="health-card-header">
            <h5><i class="fas fa-table"></i> Metrics History</h5>
            <span class="health-card-badge bg-light text-muted">{{ $healthMetrics->count() }} entries</span>
        </div>
        <div class="health-card-body p-0">
            <div class="table-responsive">
                <table class="health-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Values</th>
                            <th>Recorded At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($healthMetrics as $metric)
                            <tr>
                                <td>
                                    <span
                                        class="fw-semibold text-capitalize">{{ str_replace('_', ' ', $metric->metric_type) }}</span>
                                </td>
                                <td>
                                    <div class="metric-value-pills">
                                        @if (is_array($metric->value))
                                            @foreach ($metric->value as $key => $val)
                                                <span class="metric-value-pill">
                                                    <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                                    {{ $val }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="metric-value-pill">{{ $metric->value }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $metric->recorded_at->format('M d, Y h:i A') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
