@extends('layouts.app')

@section('content')
<div style="background: linear-gradient(180deg, #fff8ef 0%, #fffdf7 60%, #ffffff 100%); min-height: 100vh; padding: 2rem 0 4rem;">
    <div class="container" style="max-width: 1080px;">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 22px; overflow: hidden; box-shadow: 0 20px 55px rgba(90,45,6,0.12) !important;">
            <div class="card-body p-4 p-md-5" style="background: linear-gradient(135deg, #b45309 0%, #d97706 52%, #f59e0b 100%); color: #fff;">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="mb-2" style="font-size: .85rem; opacity: .92;">Symptom Profile</p>
                        <h1 class="mb-2" style="font-size: 2rem; font-weight: 800;">{{ $symptom->name }}</h1>
                        <p class="mb-0" style="opacity: 0.93;">Public symptom details and connected disease network.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('public.symptoms.index') }}" class="btn btn-light" style="border-radius: 12px; font-weight: 700; color: #92400e;">All Symptoms</a>
                        <a href="{{ route('public.diseases.index') }}" class="btn btn-outline-light" style="border-radius: 12px; font-weight: 700;">All Diseases</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-body p-4">
                <h2 class="h5 mb-3" style="font-weight: 800; color: #7c2d12;">Connected Diseases</h2>
                @if($symptom->diseases->isEmpty())
                    <p class="text-muted mb-0">No connected diseases found yet.</p>
                @else
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($symptom->diseases as $disease)
                            <a href="{{ route('public.disease.show', $disease) }}" class="badge rounded-pill text-bg-light border text-decoration-none px-3 py-2">
                                {{ $disease->disease_name }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
