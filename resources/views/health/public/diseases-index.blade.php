@extends('layouts.app')

@section('main_content_class', 'main-content main-content--wide users-main-content')

@section('content')
<div style="background: linear-gradient(180deg, #eef4ff 0%, #f8fbff 60%, #ffffff 100%); min-height: 100vh; padding: 2rem 0 4rem;">
    <div class="container" style="max-width: 1140px;">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 22px; overflow: hidden; box-shadow: 0 20px 55px rgba(10,50,120,0.12) !important;">
            <div class="card-body p-4 p-md-5" style="background: linear-gradient(135deg, #0b57d0 0%, #1a73e8 50%, #2b7de9 100%); color: #fff;">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h1 class="mb-2" style="font-size: 2rem; font-weight: 800;">{{ __('ui.diseases.diseases_directory') }}</h1>
                        <p class="mb-0" style="opacity: 0.92;">{{ __('ui.diseases.browse_all_diseases') }}</p>
                    </div>
                    <a href="{{ route('public.symptoms.index') }}" class="btn btn-light" style="border-radius: 12px; font-weight: 700; color: #0b57d0;">
                        {{ __('ui.diseases.explore_symptoms') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-body p-3 p-md-4">
                <form method="GET" action="{{ route('public.diseases.index') }}" class="d-flex flex-wrap gap-2">
                    <input type="text" name="q" value="{{ $query }}" class="form-control" placeholder="{{ __('ui.diseases.search_disease_by_name') }}" style="flex: 1 1 300px; border-radius: 10px;">
                    <button class="btn btn-primary" style="border-radius: 10px; min-width: 120px;">{{ __('ui.diseases.search') }}</button>
                    @if($query !== '')
                        <a href="{{ route('public.diseases.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px;">{{ __('ui.diseases.reset') }}</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="row g-3 g-md-4">
            @forelse($diseases as $disease)
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('public.disease.show', $disease) }}" class="text-decoration-none">
                        <div class="card border-0 h-100" style="border-radius: 16px; box-shadow: 0 10px 25px rgba(2,32,71,0.08); transition: transform .2s ease, box-shadow .2s ease;">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <h2 class="h6 mb-0" style="font-weight: 800; color: #0f2f6b;">{{ $disease->display_name }}</h2>
                                    <span class="badge text-bg-light border">{{ $disease->symptoms_count }} {{ __('ui.diseases.symptoms') }}</span>
                                </div>
                                <p class="mb-0 mt-3 text-muted" style="font-size: .9rem;">{{ \Illuminate\Support\Str::limit($disease->description ?: __('ui.diseases.open_details_to_view'), 95) }}</p>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0" style="border-radius: 16px; box-shadow: 0 10px 25px rgba(2,32,71,0.08);">
                        <div class="card-body p-4 text-center text-muted">
                            {{ __('ui.diseases.no_diseases_found') }}
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $diseases->links() }}
        </div>
    </div>
</div>
@endsection