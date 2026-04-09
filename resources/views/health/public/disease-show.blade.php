@extends('layouts.app')

@section('content')
<div style="background: linear-gradient(180deg, #eef4ff 0%, #f8fbff 60%, #ffffff 100%); min-height: 100vh; padding: 2rem 0 4rem;">
    <div class="container" style="max-width: 1080px;">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 22px; overflow: hidden; box-shadow: 0 20px 55px rgba(10,50,120,0.12) !important;">
            <div class="card-body p-4 p-md-5" style="background: linear-gradient(135deg, #0b57d0 0%, #1a73e8 50%, #2b7de9 100%); color: #fff;">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="mb-2" style="font-size: .85rem; opacity: .9;">Disease Profile</p>
                        <h1 class="mb-2" style="font-size: 2rem; font-weight: 800;">{{ $disease->disease_name }}</h1>
                        <p class="mb-0" style="opacity: 0.92;">{{ $disease->description ?: 'Public disease details and related symptom network.' }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('public.diseases.index') }}" class="btn btn-light" style="border-radius: 12px; font-weight: 700; color: #0b57d0;">All Diseases</a>
                        <a href="{{ route('public.symptoms.index') }}" class="btn btn-outline-light" style="border-radius: 12px; font-weight: 700;">All Symptoms</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3" style="color: #0f2f6b; font-weight: 800;">Connected Symptoms</h2>
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

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-body p-4">
                        <h3 class="h6 mb-3" style="font-weight: 800; color: #0f2f6b;">Community Posts</h3>
                        @if($disease->posts->isEmpty())
                            <p class="text-muted mb-0" style="font-size: .92rem;">No public posts tagged with this disease yet.</p>
                        @else
                            @foreach($disease->posts->take(5) as $post)
                                <div class="border rounded-3 p-2 mb-2">
                                    <a href="{{ route('community.post.show', $post) }}" class="text-decoration-none fw-semibold" style="color:#0b57d0; font-size:.9rem;">
                                        {{ \Illuminate\Support\Str::limit($post->description, 80) }}
                                    </a>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
