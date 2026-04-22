@extends('layouts.app')

@section('content')
<div style="background: linear-gradient(180deg, #f4f8ff 0%, #ffffff 55%, #ffffff 100%); min-height: 100vh; padding: 20px 0 48px;">
    <div class="container" style="max-width: 1080px;">
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 16px; overflow: hidden;">
            <div class="card-body" style="background: linear-gradient(120deg, #0f63dd 0%, #2a7df0 65%, #4b91f1 100%); color: #fff;">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h1 class="mb-1" style="font-size: clamp(1.15rem, 3vw, 1.8rem); font-weight: 800;">
                            <i class="fas fa-history me-2"></i>{{ __('ui.community.starred_diseases_history') }}
                        </h1>
                        <p class="mb-0" style="opacity: .92; font-size: 0.95rem;">{{ __('ui.community.history_auto_filter_hint') }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('community.home') }}" class="btn btn-outline-light btn-sm" style="border-radius: 10px; font-weight: 700;">
                            <i class="fas fa-th-large me-1"></i>{{ __('ui.community.disease_cards') }}
                        </a>
                        <a href="{{ route('community.posts.index') }}" class="btn btn-light btn-sm" style="border-radius: 10px; font-weight: 700; color: #0f63dd;">
                            <i class="fas fa-comments me-1"></i>{{ __('ui.community.all_posts') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3" style="border-radius: 14px;">
            <div class="card-body d-flex flex-wrap gap-2 align-items-center justify-content-between">
                <div class="d-flex flex-wrap gap-2">
                    <select id="statusFilter" class="form-select" style="min-width: 220px;" onchange="applyHistoryFilters()">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>{{ __('ui.community.filter_all_status') }}</option>
                        <option value="current" {{ $status === 'current' ? 'selected' : '' }}>{{ __('ui.community.filter_current_only') }}</option>
                        <option value="previous" {{ $status === 'previous' ? 'selected' : '' }}>{{ __('ui.community.filter_previously_starred') }}</option>
                    </select>

                    <select id="diseaseFilter" class="form-select" style="min-width: 260px;" onchange="applyHistoryFilters()">
                        <option value="all">{{ __('ui.community.all_posts') }}</option>
                        @foreach($diseases as $disease)
                            <option value="{{ $disease->id }}" {{ $selectedDiseaseId === $disease->id ? 'selected' : '' }}>
                                {{ $disease->display_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <span class="badge text-bg-light border" style="font-size: 0.9rem;">{{ $rows->count() }} {{ __('ui.community.posts') }}</span>
            </div>
        </div>

        @if($rows->isEmpty())
            <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                <div class="card-body text-center py-5 text-muted">
                    <i class="fas fa-star-half-alt fa-3x mb-3" style="color: #c8d5ef;"></i>
                    <h5>{{ __('ui.community.no_starred_disease_history') }}</h5>
                </div>
            </div>
        @else
            <div class="row g-3">
                @foreach($rows as $row)
                    <div class="col-12">
                        <div class="card border-0 shadow-sm" style="border-radius: 14px; border-left: 4px solid {{ $row['is_current'] ? '#f0ad00' : '#9fb4dd' }};">
                            <div class="card-body">
                                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                                    <div>
                                        <h5 class="mb-1" style="font-weight: 800; color: #11336e;">
                                            <a href="{{ route('community.disease.posts', $row['disease']) }}" class="text-decoration-none" style="color: #11336e;">
                                                {{ $row['disease']->display_name }}
                                            </a>
                                        </h5>
                                        <div class="text-muted" style="font-size: 0.9rem;">
                                            <i class="fas fa-clock me-1"></i>{{ __('ui.community.starred_at') }}: {{ \Illuminate\Support\Carbon::parse($row['starred_at'])->format('d M Y, h:i A') }}
                                        </div>
                                        <div class="text-muted" style="font-size: 0.9rem;">
                                            <i class="fas fa-clock me-1"></i>{{ __('ui.community.unstarred_at') }}:
                                            @if($row['unstarred_at'])
                                                {{ \Illuminate\Support\Carbon::parse($row['unstarred_at'])->format('d M Y, h:i A') }}
                                            @else
                                                <span class="text-success">{{ __('ui.community.currently_starred_badge') }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($row['is_current'])
                                        <span class="badge rounded-pill text-bg-warning" style="font-weight: 700;">{{ __('ui.community.currently_starred_badge') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function applyHistoryFilters() {
    const status = document.getElementById('statusFilter')?.value || 'all';
    const disease = document.getElementById('diseaseFilter')?.value || 'all';
    const params = new URLSearchParams();

    if (status !== 'all') {
        params.set('status', status);
    }

    if (disease !== 'all') {
        params.set('disease', disease);
    }

    const query = params.toString();
    const baseUrl = '{{ route('community.diseases.starred.history') }}';
    window.location.href = query ? `${baseUrl}?${query}` : baseUrl;
}
</script>
@endpush
