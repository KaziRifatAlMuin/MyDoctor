@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 980px;">
    <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
        <div class="card-body p-4 p-md-5" style="background: linear-gradient(135deg, #fff6eb 0%, #fffbf7 100%);">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h1 class="h3 mb-0" style="color: #b45309;">{{ $symptom->name }}</h1>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">Back</a>
            </div>

            <h2 class="h5 mb-3">Connected Diseases</h2>
            @if($symptom->diseases->isEmpty())
                <p class="text-muted mb-0">No connected diseases found yet.</p>
            @else
                <div class="d-flex flex-wrap gap-2">
                    @foreach($symptom->diseases as $disease)
                        <a href="{{ route('public.diseases.show', $disease) }}" class="badge rounded-pill text-bg-light border text-decoration-none px-3 py-2">
                            {{ $disease->disease_name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
