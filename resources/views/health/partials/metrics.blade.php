{{-- ── Metrics Tab ── --}}
@php $metricConfig = $metricConfig ?? config('health.metric_types'); @endphp
@if ($metricsByType->isEmpty())
    <div class="health-card">
        <div class="health-card-body">
            <div class="empty-state">
                <i class="fas fa-chart-line d-block"></i>
                <p>No health metrics recorded yet.<br>Start tracking your blood pressure, heart rate, weight, and more.</p>
                <button class="btn text-white mt-3" data-bs-toggle="modal" data-bs-target="#addMetricModal"
                    style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 10px; font-size: 0.88rem;">
                    <i class="fas fa-plus me-1"></i> Record Your First Metric
                </button>
            </div>
        </div>
    </div>
@else
    {{-- Add Button --}}
    <div class="d-flex justify-content-end mb-3">
        <button class="btn text-white" data-bs-toggle="modal" data-bs-target="#addMetricModal"
            style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 10px; font-size: 0.88rem;">
            <i class="fas fa-plus me-1"></i> Record Metric
        </button>
    </div>

    {{-- Charts per metric type --}}
    <div class="row g-4 mb-4">
        @foreach ($metricsByType as $type => $records)
            <div class="col-md-6">
                <div class="health-card">
                    <div class="health-card-header">
                        <h5>
                            <i class="fas fa-wave-square"></i>
                            {{ $metricConfig[$type]['en'] ?? ucfirst(str_replace('_', ' ', $type)) }}
                            <span class="bn-label">({{ $metricConfig[$type]['bn'] ?? '' }})</span>
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($healthMetrics as $metric)
                            <tr>
                                <td>
                                    <span class="fw-semibold text-capitalize">{{ $metricConfig[$metric->metric_type]['en'] ?? str_replace('_', ' ', $metric->metric_type) }}</span>
                                    <span class="bn-label d-block">({{ $metricConfig[$metric->metric_type]['bn'] ?? '' }})</span>
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
                                <td>
                                    <div class="action-btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="openEditMetric({{ $metric->id }}, '{{ $metric->metric_type }}', {{ json_encode($metric->value) }}, '{{ $metric->recorded_at->format('Y-m-d\TH:i') }}')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('health.metric.destroy', $metric) }}" method="POST"
                                            onsubmit="return confirm('Delete this metric?')" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
