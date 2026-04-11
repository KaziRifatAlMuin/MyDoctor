@extends('layouts.app')

@section('content')
    <div
        style="background: linear-gradient(180deg, #eef4ff 0%, #f8fbff 55%, #ffffff 100%); min-height: 100vh; padding: 2rem 0 4rem;">
        <div class="container" style="max-width: 1180px;">
            <div class="card border-0 shadow-sm mb-4"
                style="border-radius: 24px; overflow: hidden; box-shadow: 0 20px 55px rgba(10,50,120,0.12) !important;">
                <div class="card-body p-4 p-md-5"
                    style="background: linear-gradient(135deg, #0b57d0 0%, #1a73e8 48%, #2b7de9 100%); color: #fff;">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <h1 class="mb-2" style="font-size: 2rem; font-weight: 800;">Community Diseases Hub</h1>
                            <p class="mb-0" style="opacity: .92;">Choose a disease card to view its dedicated feed.</p>
                        </div>
                        <a href="{{ route('community.posts.index') }}" class="btn btn-light"
                            style="border-radius: 12px; color: #0b57d0; font-weight: 800;">
                            Browse All Posts
                        </a>
                    </div>
                    <div class="d-flex gap-4 mt-3" style="font-weight: 700; font-size: .95rem; opacity: .95;">
                        <span><i class="fas fa-list-ul me-1"></i>{{ number_format($totalPosts) }} posts</span>
                        <span><i class="fas fa-heartbeat me-1"></i>{{ number_format($totalDiseases) }} diseases</span>
                    </div>
                </div>
            </div>

            <div class="row g-3 g-md-4">
                @forelse($diseases as $disease)
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 h-100"
                            style="border-radius: 16px; box-shadow: 0 10px 25px rgba(2,32,71,0.08); transition: transform .2s ease, box-shadow .2s ease;">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <h2 class="h6 mb-0" style="font-weight: 800; color: #0f2f6b;">
                                        <a href="{{ route('public.disease.show', $disease) }}" class="text-decoration-none"
                                            style="color: #0f2f6b;">
                                            {{ $disease->disease_name }}
                                        </a>
                                    </h2>
                                    <span class="badge text-bg-light border">{{ $disease->posts_count }}</span>
                                </div>
                                @auth
                                    @if(!auth()->user()->isAdmin())
                                        @php $starred = in_array($disease->id, $userStarredDiseaseIds ?? [], true); @endphp
                                        <div class="d-flex justify-content-end mt-2">
                                            <button type="button"
                                                class="btn btn-sm {{ $starred ? 'btn-warning' : 'btn-outline-secondary' }} rounded-pill disease-star-btn"
                                                data-disease-id="{{ $disease->id }}"
                                                onclick="toggleDiseaseStar(this)"
                                                style="font-weight:700;">
                                                <i class="{{ $starred ? 'fas' : 'far' }} fa-star me-1"></i>
                                                <span>{{ $starred ? 'Starred' : 'Star' }}</span>
                                            </button>
                                        </div>
                                    @endif
                                @endauth
                                <p class="text-muted mt-3 mb-3" style="font-size: .9rem;">Open disease-specific posts feed.
                                </p>
                                <div class="d-flex gap-2 mt-auto">
                                    <a href="{{ route('community.disease.posts', $disease) }}"
                                        class="btn btn-sm btn-primary" style="border-radius: 10px; font-weight: 700;">
                                        View Posts
                                    </a>
                                    <a href="{{ route('public.disease.show', $disease) }}"
                                        class="btn btn-sm btn-outline-primary"
                                        style="border-radius: 10px; font-weight: 700;">
                                        Disease View
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0" style="border-radius: 16px; box-shadow: 0 10px 25px rgba(2,32,71,0.08);">
                            <div class="card-body p-4 text-center text-muted">No diseases available yet.</div>
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
                            if (text) text.textContent = 'Starred';
                        } else {
                            button.classList.remove('btn-warning');
                            button.classList.add('btn-outline-secondary');
                            if (icon) {
                                icon.classList.remove('fas');
                                icon.classList.add('far');
                            }
                            if (text) text.textContent = 'Star';
                        }
                    } catch (error) {
                        console.error('Failed to toggle disease star', error);
                    }
                }
            </script>
        @endif
    @endauth
@endsection
