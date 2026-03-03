{{-- ── Medicine Logs Tab ── --}}
<div class="row g-4">

    {{-- Adherence Summary --}}
    <div class="col-md-4">
        <div class="health-card">
            <div class="health-card-header">
                <h5><i class="fas fa-chart-pie"></i> 30-Day Summary</h5>
            </div>
            <div class="health-card-body">
                @if ($totalScheduled > 0)
                    <div class="text-center mb-3">
                        <span class="fw-bold"
                            style="font-size: 2.5rem; color: {{ $adherenceRate >= 80 ? '#38a169' : ($adherenceRate >= 50 ? '#dd6b20' : '#e53e3e') }};">
                            {{ $adherenceRate }}%
                        </span>
                        <div class="text-muted" style="font-size: 0.85rem;">Overall Adherence</div>
                    </div>

                    {{-- Progress bars --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1" style="font-size: 0.8rem;">
                            <span class="fw-semibold" style="color: #38a169;"><i class="fas fa-check me-1"></i>
                                Taken</span>
                            <span>{{ $totalTaken }}</span>
                        </div>
                        <div class="adherence-bar">
                            <div class="adherence-fill"
                                style="width: {{ $totalScheduled > 0 ? ($totalTaken / $totalScheduled) * 100 : 0 }}%; background: #38a169;">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1" style="font-size: 0.8rem;">
                            <span class="fw-semibold" style="color: #e53e3e;"><i class="fas fa-times me-1"></i>
                                Missed</span>
                            <span>{{ $totalMissed }}</span>
                        </div>
                        <div class="adherence-bar">
                            <div class="adherence-fill"
                                style="width: {{ $totalScheduled > 0 ? ($totalMissed / $totalScheduled) * 100 : 0 }}%; background: #e53e3e;">
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between mb-1" style="font-size: 0.8rem;">
                            <span class="fw-semibold" style="color: #667eea;"><i class="fas fa-calendar-check me-1"></i>
                                Total Scheduled</span>
                            <span>{{ $totalScheduled }}</span>
                        </div>
                        <div class="adherence-bar">
                            <div class="adherence-fill" style="width: 100%; background: #667eea;"></div>
                        </div>
                    </div>
                @else
                    <div class="empty-state py-3">
                        <i class="fas fa-clipboard-list d-block"></i>
                        <p>No medicine logs in the last 30 days.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Logs Table --}}
    <div class="col-md-8">
        <div class="health-card">
            <div class="health-card-header">
                <h5><i class="fas fa-history"></i> Daily Medicine Logs</h5>
                <span class="health-card-badge bg-light text-muted">{{ $medicineLogs->count() }} entries</span>
            </div>
            <div class="health-card-body p-0">
                @if ($medicineLogs->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-clipboard-list d-block"></i>
                        <p>No medicine logs recorded in the last 30 days.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="health-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Medicine</th>
                                    <th>Scheduled</th>
                                    <th>Taken</th>
                                    <th>Missed</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($medicineLogs as $log)
                                    @php
                                        $logRate =
                                            $log->total_scheduled > 0
                                                ? round(($log->total_taken / $log->total_scheduled) * 100)
                                                : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $log->date->format('M d') }}</div>
                                            <div style="font-size: 0.72rem; color: #a0aec0;">
                                                {{ $log->date->format('D') }}</div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="stat-summary-icon purple"
                                                    style="width: 28px; height: 28px; font-size: 0.65rem; border-radius: 7px;">
                                                    <i class="fas fa-pills"></i>
                                                </div>
                                                <span
                                                    class="fw-semibold">{{ $log->medicine->medicine_name ?? 'Unknown' }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $log->total_scheduled }}</td>
                                        <td><span class="log-taken">{{ $log->total_taken }}</span></td>
                                        <td><span class="log-missed">{{ $log->total_missed }}</span></td>
                                        <td>
                                            @if ($logRate >= 100)
                                                <span class="severity-badge severity-1"><i class="fas fa-check"></i>
                                                    Perfect</span>
                                            @elseif($logRate >= 50)
                                                <span class="severity-badge severity-4"><i class="fas fa-minus"></i>
                                                    Partial</span>
                                            @else
                                                <span class="severity-badge severity-8"><i class="fas fa-times"></i>
                                                    Missed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
