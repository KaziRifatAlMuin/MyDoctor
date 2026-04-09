@extends('layouts.app')

@section('main_content_class', 'main-content main-content--wide users-main-content')

@section('content')
<div style="background: linear-gradient(180deg, #fff8ef 0%, #fffdf7 60%, #ffffff 100%); min-height: 100vh; padding: 2rem 0 4rem;">
    <div class="container" style="max-width: 1140px;">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 22px; overflow: hidden; box-shadow: 0 20px 55px rgba(90,45,6,0.12) !important;">
            <div class="card-body p-4 p-md-5" style="background: linear-gradient(135deg, #b45309 0%, #d97706 52%, #f59e0b 100%); color: #fff;">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h1 class="mb-2" style="font-size: 2rem; font-weight: 800;">Symptoms Directory</h1>
                        <p class="mb-0" style="opacity: 0.93;">Browse all symptoms and open details with connected diseases.</p>
                    </div>
                    <a href="{{ route('public.diseases.index') }}" class="btn btn-light" style="border-radius: 12px; font-weight: 700; color: #92400e;">
                        Explore Diseases
                    </a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-body p-3 p-md-4">
                <form method="GET" action="{{ route('public.symptoms.index') }}" class="d-flex flex-wrap gap-2">
                    <input type="text" name="q" value="{{ $query }}" class="form-control" placeholder="Search symptom by name..." style="flex: 1 1 300px; border-radius: 10px;">
                    <button class="btn btn-warning" style="border-radius: 10px; min-width: 120px; color: #1f2937; font-weight: 700;">Search</button>
                    @if($query !== '')
                        <a href="{{ route('public.symptoms.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px;">Reset</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="row g-3 g-md-4">
            @forelse($symptoms as $symptom)
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('public.symptoms.show', $symptom) }}" class="text-decoration-none">
                        <div class="card border-0 h-100" style="border-radius: 16px; box-shadow: 0 10px 25px rgba(90,45,6,0.08); transition: transform .2s ease, box-shadow .2s ease;">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <h2 class="h6 mb-0" style="font-weight: 800; color: #7c2d12;">{{ $symptom->name }}</h2>
                                    <span class="badge text-bg-light border">{{ $symptom->diseases_count }} diseases</span>
                                </div>
                                <p class="mb-0 mt-3 text-muted" style="font-size: .9rem;">Open details to inspect linked diseases and navigate quickly.</p>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0" style="border-radius: 16px; box-shadow: 0 10px 25px rgba(90,45,6,0.08);">
                        <div class="card-body p-4 text-center text-muted">
                            No symptoms found for this search.
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $symptoms->links() }}
        </div>
    </div>
</div>
@endsection
