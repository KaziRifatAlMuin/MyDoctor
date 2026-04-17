@extends('layouts.app')

@section('content')
<div style="background: linear-gradient(180deg, #fff8ef 0%, #fffdf7 60%, #ffffff 100%); min-height: 100vh; padding: 2rem 0 4rem;">
    <div class="container" style="max-width: 1080px;">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 22px; overflow: hidden; box-shadow: 0 20px 55px rgba(90,45,6,0.12) !important;">
            <div class="card-body p-4 p-md-5" style="background: linear-gradient(135deg, #b45309 0%, #d97706 52%, #f59e0b 100%); color: #fff;">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="mb-2" style="font-size: .85rem; opacity: .92;">{{ __('ui.symptom.symptom_profile') }}</p>
                        <h1 class="mb-2" style="font-size: 2rem; font-weight: 800;">{{ $symptom->display_name }}</h1>
                        <p class="mb-0" style="opacity: 0.93;">{{ __('ui.symptom.public_symptom_details') }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('public.symptoms.index') }}" class="btn btn-light" style="border-radius: 12px; font-weight: 700; color: #92400e;">{{ __('ui.symptom.all_symptoms') }}</a>
                        <a href="{{ route('public.diseases.index') }}" class="btn btn-outline-light" style="border-radius: 12px; font-weight: 700;">{{ __('ui.symptom.all_diseases') }}</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-body p-4">
                <h2 class="h5 mb-3" style="font-weight: 800; color: #7c2d12;">{{ __('ui.symptom.connected_diseases') }}</h2>
                @if($symptom->diseases->isEmpty())
                    <p class="text-muted mb-0">{{ __('ui.symptom.no_connected_diseases') }}</p>
                @else
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($symptom->diseases as $disease)
                            <a href="{{ route('public.disease.show', $disease) }}" class="badge rounded-pill text-bg-light border text-decoration-none px-3 py-2">
                                {{ $disease->display_name }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection