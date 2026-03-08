{{-- ── Symptoms Tab ── --}}
@php $symptomsBn = config('health.symptoms'); @endphp
<div class="d-flex justify-content-end mb-3">
    <button class="btn text-white" data-bs-toggle="modal" data-bs-target="#addSymptomModal"
        style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 10px; font-size: 0.88rem;">
        <i class="fas fa-plus me-1"></i> Log Symptom
    </button>
</div>
<div class="row g-4">

    {{-- Severity Distribution Chart --}}
    <div class="col-md-4">
        <div class="health-card">
            <div class="health-card-header">
                <h5><i class="fas fa-chart-bar"></i> Severity</h5>
            </div>
            <div class="health-card-body">
                @if ($severityDistribution->isEmpty())
                    <div class="empty-state py-3">
                        <i class="fas fa-chart-bar d-block"></i>
                        <p>No data yet.</p>
                    </div>
                @else
                    <div class="chart-container" style="height: 200px;">
                        <canvas id="severityChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between" style="font-size: 0.8rem; color: #718096;">
                            <span><i class="fas fa-circle me-1" style="color: #38a169; font-size: 0.5rem;"></i> Mild
                                (1-2)</span>
                            <span><i class="fas fa-circle me-1" style="color: #dd6b20; font-size: 0.5rem;"></i> Moderate
                                (3-6)</span>
                            <span><i class="fas fa-circle me-1" style="color: #e53e3e; font-size: 0.5rem;"></i> Severe
                                (7-10)</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Symptoms List --}}
    <div class="col-md-8">
        <div class="health-card">
            <div class="health-card-header">
                <h5><i class="fas fa-list-ul"></i> Symptom Records</h5>
                <span class="health-card-badge bg-light text-muted">{{ $symptoms->count() }} total</span>
            </div>
            <div class="health-card-body p-0">
                @if ($symptoms->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-notes-medical d-block"></i>
                        <p>No symptoms recorded yet.<br>Log symptoms to keep track of patterns.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="health-table">
                            <thead>
                                <tr>
                                    <th>Symptom</th>
                                    <th>Severity</th>
                                    <th>Date</th>
                                    <th>Note</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($symptoms as $symptom)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="stat-summary-icon orange"
                                                    style="width: 30px; height: 30px; font-size: 0.7rem; border-radius: 8px;">
                                                    <i class="fas fa-thermometer-half"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-semibold">{{ $symptom->symptom_name }}</span>
                                                    @if (!empty($symptomsBn[$symptom->symptom_name]))
                                                        <span class="bn-label d-block">({{ $symptomsBn[$symptom->symptom_name] }})</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($symptom->severity_level)
                                                <span class="severity-badge severity-{{ $symptom->severity_level }}">
                                                    <i class="fas fa-circle" style="font-size: 0.4rem;"></i>
                                                    {{ $symptom->severity_level }}/10
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $symptom->recorded_at->format('M d, Y') }}</div>
                                            <div style="font-size: 0.72rem; color: #a0aec0;">
                                                {{ $symptom->recorded_at->format('h:i A') }}</div>
                                        </td>
                                        <td style="max-width: 250px;">
                                            @if ($symptom->note)
                                                <span
                                                    title="{{ $symptom->note }}">{{ Str::limit($symptom->note, 60) }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick="openEditSymptom({{ $symptom->id }}, '{{ addslashes($symptom->symptom_name) }}', {{ $symptom->severity_level ?? 5 }}, '{{ $symptom->recorded_at->format('Y-m-d\TH:i') }}', '{{ addslashes($symptom->note ?? '') }}')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('health.symptom.destroy', $symptom) }}" method="POST"
                                                    onsubmit="return confirm('Delete this symptom?')" class="d-inline">
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
                @endif
            </div>
        </div>
    </div>
</div>
