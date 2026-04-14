@extends('layouts.app')

@section('content')
    @php
        $isOwner = auth()->check() && auth()->id() === $post->user_id;
        $isAdmin = auth()->check() && auth()->user()->isAdmin();
        $isAdminCommunity = $isAdminCommunity ?? false;
        $feedRoute = $isAdminCommunity ? route('admin.community.posts.index') : route('community.posts.index');
        $pendingRoute = $isAdminCommunity ? route('admin.community.posts.pending') : route('community.posts.pending');
    @endphp
    <div style="background: linear-gradient(180deg, #eef4ff 0%, #f8fbff 60%, #ffffff 100%); min-height: 100vh; padding: 2rem 0 4rem;">
        <div class="container" style="max-width: 980px;">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 22px; overflow: hidden; box-shadow: 0 20px 55px rgba(10,50,120,0.12) !important;">
                <div class="card-body p-4 p-md-5" style="background: linear-gradient(135deg, #0b57d0 0%, #1a73e8 48%, #2b7de9 100%); color: #fff;">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <p class="mb-2" style="font-size:.85rem; opacity:.92;">{{ $isAdminCommunity ? __('ui.community.admin_community') : __('ui.community.community_post') }}</p>
                            <h1 class="mb-0" style="font-size:1.7rem; font-weight:800;">{{ __('ui.community.post') }} #{{ $post->id }}</h1>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ url()->previous() }}" class="btn btn-outline-light" style="border-radius: 12px; font-weight:700;">{{ __('ui.community.back') }}</a>
                            <a href="{{ $feedRoute }}" class="btn btn-light" style="border-radius: 12px; color:#0b57d0; font-weight:700;">{{ __('ui.community.all_posts') }}</a>
                            @if($isAdminCommunity)
                                <a href="{{ $pendingRoute }}" class="btn btn-outline-light" style="border-radius: 12px; font-weight:700;">{{ __('ui.community.pending_queue') }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @include('community.partials.post', ['post' => $post, 'adminReadOnlyCommunity' => $isAdminCommunity])
        </div>
    </div>

    @push('scripts')
    <script>
        // Comments are always shown on detail page, no toggle needed
    </script>
    @endpush
@endsection