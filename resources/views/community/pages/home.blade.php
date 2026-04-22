@extends('layouts.app')

@section('content')
    <div
        style="background: linear-gradient(180deg, #eef4ff 0%, #f8fbff 55%, #ffffff 100%); min-height: 100vh; padding: 1rem 0.5rem; padding-bottom: 4rem;">
        <div class="container-fluid" style="max-width: 1180px; padding: 0 1rem;">
            <div class="card border-0 shadow-sm mb-3 mb-md-4"
                style="border-radius: 16px; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 55px rgba(10,50,120,0.12) !important;">
                <div class="card-body p-3 p-sm-4 p-md-5"
                    style="background: linear-gradient(135deg, #0b57d0 0%, #1a73e8 48%, #2b7de9 100%); color: #fff;">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 gap-md-3">
                        <div>
                            <h1 class="mb-1 mb-md-2" style="font-size: clamp(1.25rem, 5vw, 2rem); font-weight: 800;">{{ __('ui.community.community_diseases_hub') }}</h1>
                            <p class="mb-0" style="opacity: .92; font-size: clamp(0.875rem, 3vw, 1rem);">{{ __('ui.community.choose_disease_card') }}</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2 justify-content-end">
                            @auth
                                @if(!auth()->user()->isAdmin())
                                    <a href="{{ route('community.diseases.starred.history') }}" class="btn btn-outline-light"
                                        style="border-radius: 12px; font-weight: 800; white-space: nowrap; font-size: clamp(0.75rem, 2vw, 0.95rem);">
                                        {{ __('ui.community.starred_diseases_history') }}
                                    </a>
                                @endif
                            @endauth
                            <a href="{{ route('community.posts.index') }}" class="btn btn-light"
                                style="border-radius: 12px; color: #0b57d0; font-weight: 800; white-space: nowrap; font-size: clamp(0.75rem, 2vw, 0.95rem);">
                                {{ __('ui.community.browse_all_posts') }}
                            </a>
                        </div>
                    </div>
                    <div class="d-flex gap-2 gap-md-4 mt-2 mt-md-3 flex-wrap" style="font-weight: 700; font-size: clamp(0.75rem, 2.5vw, 0.95rem); opacity: .95;">
                        <span><i class="fas fa-list-ul me-1"></i>{{ number_format($totalPosts) }} {{ __('ui.community.posts') }}</span>
                        <span><i class="fas fa-heartbeat me-1"></i>{{ number_format($totalDiseases) }} {{ __('ui.community.diseases') }}</span>
                    </div>
                </div>
            </div>

            <div class="row g-2 g-md-3 g-lg-4">
                @forelse($diseases as $disease)
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="card border-0 h-100"
                            style="border-radius: 16px; box-shadow: 0 10px 25px rgba(2,32,71,0.08); transition: transform .2s ease, box-shadow .2s ease;">
                            <div class="card-body p-3 p-md-4 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <h2 class="h6 mb-0" style="font-weight: 800; color: #0f2f6b; font-size: clamp(0.875rem, 2vw, 1rem);">
                                        <a href="{{ route('community.disease.posts', $disease) }}" class="text-decoration-none"
                                            style="color: #0f2f6b;">
                                            {{ $disease->display_name }}
                                        </a>
                                    </h2>
                                    <span class="badge text-bg-light border" style="font-size: clamp(0.65rem, 1.5vw, 0.8rem);">{{ $disease->posts_count }}</span>
                                </div>
                                @auth
                                    @if(!auth()->user()->isAdmin())
                                        @php $starred = in_array($disease->id, $userStarredDiseaseIds ?? [], true); @endphp
                                        <div class="d-flex justify-content-end mt-2">
                                            <button type="button"
                                                class="btn btn-sm {{ $starred ? 'btn-warning' : 'btn-outline-secondary' }} rounded-pill disease-star-btn"
                                                data-disease-id="{{ $disease->id }}"
                                                onclick="toggleDiseaseStar(this)"
                                                style="font-weight:700; font-size: clamp(0.65rem, 1.5vw, 0.8rem);">
                                                <i class="{{ $starred ? 'fas' : 'far' }} fa-star me-1"></i>
                                                <span>{{ $starred ? __('ui.community.starred') : __('ui.community.star') }}</span>
                                            </button>
                                        </div>
                                    @endif
                                @endauth
                                <p class="text-muted mt-2 mt-md-3 mb-2 mb-md-3" style="font-size: clamp(0.8rem, 2vw, 0.9rem);">{{ __('ui.community.choose_disease_card') }}
                                </p>
                                <div class="d-flex gap-2 mt-auto flex-wrap">
                                    <a href="{{ route('community.disease.posts', $disease) }}"
                                        class="btn btn-sm btn-primary" style="border-radius: 10px; font-weight: 700; font-size: clamp(0.7rem, 1.5vw, 0.85rem); padding: 0.4rem 0.8rem;">
                                        {{ __('ui.community.view_posts') }}
                                    </a>
                                    <a href="{{ route('public.disease.show', $disease) }}"
                                        class="btn btn-sm btn-outline-primary"
                                        style="border-radius: 10px; font-weight: 700; font-size: clamp(0.7rem, 1.5vw, 0.85rem); padding: 0.4rem 0.8rem;">
                                        {{ __('ui.community.disease_view') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0" style="border-radius: 16px; box-shadow: 0 10px 25px rgba(2,32,71,0.08);">
                            <div class="card-body p-3 p-md-4 text-center text-muted">{{ __('ui.community.no_diseases_available') }}</div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @auth
        @if(!auth()->user()->isAdmin())
            <script>
                async function toggleDiseaseStar(button) {
                    const diseaseId = button.dataset.diseaseId;
                    if (!diseaseId) return;

                    try {
                        const response = await fetch(`/community/diseases/${diseaseId}/star`, {
                            method: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        });

                        const data = await response.json();
                        if (!data.success) {
                            return;
                        }

                        const icon = button.querySelector('i');
                        const text = button.querySelector('span');
                        if (data.starred) {
                            button.classList.remove('btn-outline-secondary');
                            button.classList.add('btn-warning');
                            if (icon) {
                                icon.classList.remove('far');
                                icon.classList.add('fas');
                            }
                            if (text) text.textContent = '{{ __("ui.community.starred") }}';
                        } else {
                            button.classList.remove('btn-warning');
                            button.classList.add('btn-outline-secondary');
                            if (icon) {
                                icon.classList.remove('fas');
                                icon.classList.add('far');
                            }
                            if (text) text.textContent = '{{ __("ui.community.star") }}';
                        }
                    } catch (error) {
                        console.error('Failed to toggle disease star', error);
                    }
                }
            </script>
        @endif
    @endauth
@endsection

@push('styles')
<style>
    /* Responsive Design for Mobile and Tablets */
    @media (max-width: 768px) {
        div[style*="min-height: 100vh"] {
            padding: 1rem 0 2rem;
        }

        .card-body.p-4.p-md-5 {
            padding: 1.5rem !important;
        }

        h1[style*="font-size: 2rem"] {
            font-size: 1.5rem !important;
        }

        .d-flex.flex-wrap.justify-content-between.align-items-center.gap-3 > div > p {
            font-size: 0.9rem;
        }

        .d-flex.gap-4 {
            flex-direction: column;
            gap: 0.5rem !important;
            font-size: 0.85rem !important;
        }

        .row.g-3.g-md-4 {
            --bs-gutter-x: 1rem;
            --bs-gutter-y: 1rem;
        }

        .card-body.p-4.d-flex.flex-column {
            padding: 1.25rem !important;
        }

        .d-flex.justify-content-between.align-items-start.gap-2 h2 {
            font-size: 1rem !important;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.8rem;
        }

        .d-flex.gap-2.mt-auto {
            flex-direction: column;
            gap: 0.5rem !important;
        }

        .d-flex.gap-2.mt-auto .btn {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        div[style*="min-height: 100vh"] {
            padding: 0.75rem 0 1.5rem;
        }

        .card-body.p-4.p-md-5 {
            padding: 1.25rem !important;
        }

        h1[style*="font-size: 2rem"] {
            font-size: 1.25rem !important;
        }

        .btn {
            font-size: 0.85rem;
        }

        .d-flex.gap-4 {
            font-size: 0.8rem !important;
        }

        .card-body.p-4.d-flex.flex-column {
            padding: 1rem !important;
        }

        .text-muted {
            font-size: 0.85rem;
        }
    }

    @media (min-width: 769px) and (max-width: 992px) {
        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }

        .col-lg-4 {
            flex: 0 0 33.333%;
            max-width: 33.333%;
        }
    }
</style>
@endpush