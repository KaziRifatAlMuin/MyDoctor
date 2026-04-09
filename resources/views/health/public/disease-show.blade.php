@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 980px;">
    <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
        <div class="card-body p-4 p-md-5" style="background: linear-gradient(135deg, #eaf3ff 0%, #f7fbff 100%);">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h1 class="h3 mb-0 text-primary">{{ $disease->disease_name }}</h1>
                <a href="{{ url()->previous() }}" class="btn btn-outline-primary btn-sm">Back</a>
            </div>

            @if(!empty($disease->description))
                <p class="text-muted mb-4">{{ $disease->description }}</p>
            @endif

            <h2 class="h5 mb-3">Connected Symptoms</h2>
            @if($disease->symptoms->isEmpty())
                <p class="text-muted mb-0">No connected symptoms found yet.</p>
            @else
                <div class="d-flex flex-wrap gap-2">
                    @foreach($disease->symptoms as $symptom)
                        <a href="{{ route('public.symptoms.show', $symptom) }}" class="badge rounded-pill text-bg-light border text-decoration-none px-3 py-2">
                            {{ $symptom->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
