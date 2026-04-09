{{-- ── Diseases Tab ── --}}
<div class="row g-4">

    {{-- Disease Summary --}}
    <div class="col-md-4">
        <div class="health-card">
            <div class="health-card-header">
                <h5><i class="fas fa-chart-pie"></i> Status Overview</h5>
            </div>
            <div class="health-card-body">
                @if ($userDiseases->isEmpty())
                    <div class="empty-state py-3">
                        <i class="fas fa-virus-slash d-block"></i>
                        <p>No disease records yet.</p>
                    </div>
                @else
                    @php
                        $statusCounts = $userDiseases->groupBy('status')->map->count();
                        $statusColors = [
                            'active' => '#e53e3e',
                            'chronic' => '#dd6b20',
                            'managed' => '#3182ce',
                            'recovered' => '#38a169',
                        ];
                    @endphp
                    <div class="mb-3">
                        @foreach (['active', 'chronic', 'managed', 'recovered'] as $status)
                            <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-circle" style="font-size: 0.5rem; color: {{ $statusColors[$status] }};"></i>
                                    <span class="fw-semibold text-capitalize" style="font-size: 0.88rem;">{{ $status }}</span>
                                </div>
                                <span class="fw-bold" style="color: {{ $statusColors[$status] }};">
                                    {{ $statusCounts->get($status, 0) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-3">
                        <span class="fw-bold" style="font-size: 1.8rem; color: #2d3748;">{{ $userDiseases->count() }}</span>
                        <div class="text-muted" style="font-size: 0.82rem;">Total Conditions</div>
                    </div>
                @endif

                <div class="mt-3">
                    <button class="btn w-100 text-white" data-bs-toggle="modal" data-bs-target="#addDiseaseModal"
                        style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 10px; font-size: 0.88rem;">
                        <i class="fas fa-plus me-1"></i> Add Disease Record
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Disease List --}}
    <div class="col-md-8">
        <div class="health-card">
            <div class="health-card-header">
                <h5><i class="fas fa-list-ul"></i> My Disease Records</h5>
                <span class="health-card-badge bg-light text-muted">{{ $userDiseases->count() }} total</span>
            </div>
            <div class="health-card-body p-0">
                @if ($userDiseases->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-virus d-block"></i>
                        <p>No disease records found.<br>Track your medical conditions here.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="health-table">
                            <thead>
                                <tr>
                                    <th>Disease</th>
                                    <th>Status</th>
                                    <th>Diagnosed</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($userDiseases as $ud)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="stat-summary-icon red"
                                                    style="width: 30px; height: 30px; font-size: 0.7rem; border-radius: 8px;">
                                                    <i class="fas fa-virus"></i>
                                                </div>
                                                <div>
                                                    @if($ud->disease)
                                                        <a href="{{ route('public.disease.show', $ud->disease) }}" class="fw-semibold text-decoration-none">
                                                            {{ $ud->disease->disease_name }}
                                                        </a>
                                                    @else
                                                        <span class="fw-semibold">Unknown</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $badgeClass = match($ud->status) {
                                                    'active' => 'severity-8',
                                                    'chronic' => 'severity-5',
                                                    'managed' => 'severity-3',
                                                    'recovered' => 'severity-1',
                                                    default => 'severity-3',
                                                };
                                            @endphp
                                            <span class="severity-badge {{ $badgeClass }} text-capitalize">
                                                <i class="fas fa-circle" style="font-size: 0.4rem;"></i>
                                                {{ $ud->status }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $ud->diagnosed_at ? $ud->diagnosed_at->format('M d, Y') : '—' }}
                                        </td>
                                        <td style="max-width: 200px;">
                                            @if ($ud->notes)
                                                <span title="{{ $ud->notes }}">{{ Str::limit($ud->notes, 50) }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick="openEditDisease({{ $ud->id }}, {{ $ud->disease_id }}, '{{ addslashes($ud->disease->disease_name ?? '') }}', '{{ $ud->status }}', '{{ $ud->diagnosed_at ? $ud->diagnosed_at->format('Y-m-d') : '' }}', '{{ addslashes($ud->notes ?? '') }}')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('health.disease.destroy', $ud) }}" method="POST"
                                                    onsubmit="return confirm('Remove this disease record?')" class="d-inline">
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

