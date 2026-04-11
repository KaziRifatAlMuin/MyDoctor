@extends('layouts.app')

@section('title', 'Admin Diseases')

@push('styles')
<style>
    .admin-catalog-surface {
        min-height: 100vh;
        padding: 2rem 0 4rem;
        background:
            radial-gradient(circle at 85% 15%, rgba(15, 118, 110, 0.2), transparent 34%),
            radial-gradient(circle at 8% 82%, rgba(20, 184, 166, 0.2), transparent 36%),
            linear-gradient(155deg, #eefcf8 0%, #f7fffd 44%, #f0fbf7 100%);
    }

    .catalog-hero {
        border-radius: 26px;
        padding: 1.9rem;
        margin-bottom: 1.4rem;
        color: #fff;
        background: linear-gradient(135deg, #0f766e 0%, #0d9488 52%, #14b8a6 100%);
        box-shadow: 0 20px 54px rgba(15, 118, 110, 0.24);
    }

    .catalog-hero h1 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 800;
    }

    .catalog-hero p {
        margin: 0.4rem 0 0;
        opacity: 0.92;
    }

    .catalog-toolbar,
    .catalog-panel,
    .item-card {
        background: #fff;
        border: 1px solid rgba(15, 118, 110, 0.12);
        border-radius: 20px;
        box-shadow: 0 12px 32px rgba(10, 52, 49, 0.08);
    }

    .catalog-toolbar {
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .toolbar-grid {
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: 0.65rem;
    }

    .toolbar-grid input,
    .form-input,
    .form-textarea {
        min-height: 44px;
        border: 1px solid rgba(15, 118, 110, 0.22);
        border-radius: 11px;
        padding: 0.6rem 0.84rem;
        width: 100%;
    }

    .form-textarea {
        min-height: 84px;
        resize: vertical;
    }

    .btn-main,
    .btn-ghost,
    .btn-edit,
    .btn-delete,
    .btn-open {
        min-height: 44px;
        border: 0;
        border-radius: 11px;
        padding: 0.55rem 0.9rem;
        font-size: 0.86rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
    }

    .btn-main { color: #fff; background: linear-gradient(135deg, #0f766e, #14b8a6); }
    .btn-ghost { color: #0f4e49; background: #e9f8f5; }
    .btn-open { color: #0f766e; background: rgba(15, 118, 110, 0.12); }
    .btn-edit { color: #fff; background: #0d9488; }
    .btn-delete { color: #fff; background: #d14343; }

    .catalog-panel {
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .create-grid {
        display: grid;
        grid-template-columns: 1fr 1.4fr auto;
        gap: 0.65rem;
    }

    .cards-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.9rem;
    }

    .item-card {
        padding: 1rem;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .item-title {
        margin: 0;
        font-size: 1.02rem;
        font-weight: 800;
        color: #0d4d48;
    }

    .item-badges {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-top: 0.55rem;
    }

    .item-badge {
        border-radius: 999px;
        padding: 0.25rem 0.62rem;
        background: #ecfdf9;
        color: #0f766e;
        border: 1px solid rgba(15, 118, 110, 0.2);
        font-size: 0.75rem;
        font-weight: 700;
    }

    .item-copy {
        margin-top: 0.65rem;
        color: #4f6d69;
        font-size: 0.88rem;
        flex-grow: 1;
    }

    .item-actions {
        display: flex;
        gap: 0.45rem;
        flex-wrap: wrap;
        margin-top: 0.85rem;
    }

    .edit-box {
        border-top: 1px dashed rgba(15, 118, 110, 0.2);
        margin-top: 0.9rem;
        padding-top: 0.9rem;
        display: none;
    }

    .edit-box.is-open {
        display: block;
    }

    @media (max-width: 1199px) {
        .cards-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991px) {
        .toolbar-grid,
        .create-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .cards-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="admin-catalog-surface">
    <div class="container" style="max-width: 1240px;">
        <div class="catalog-hero">
            <h1><i class="fas fa-virus me-2"></i>Admin Diseases Catalog</h1>
            <p>Same card-based look and size as symptoms page. Open each item to public view with extra admin controls.</p>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="catalog-toolbar">
            <form class="toolbar-grid" method="GET" action="{{ route('admin.diseases.index') }}">
                <input type="text" name="q" value="{{ $search }}" placeholder="Search diseases by name...">
                <button class="btn-main" type="submit"><i class="fas fa-magnifying-glass"></i>Search</button>
                <a href="{{ route('admin.dashboard') }}" class="btn-ghost"><i class="fas fa-arrow-left"></i>Back</a>
            </form>
        </section>

        <section class="catalog-panel">
            <form class="create-grid" method="POST" action="{{ route('admin.diseases.store') }}">
                @csrf
                <input class="form-input" type="text" name="disease_name" placeholder="New disease name" required>
                <textarea class="form-textarea" name="description" placeholder="Description (optional)"></textarea>
                <button class="btn-main" type="submit"><i class="fas fa-plus"></i>Create</button>
            </form>
        </section>

        <div class="cards-grid">
            @forelse ($diseases as $disease)
                <article class="item-card">
                    <h2 class="item-title">{{ $disease->disease_name }}</h2>
                    <div class="item-badges">
                        <span class="item-badge">{{ number_format($disease->user_diseases_count) }} users</span>
                        <span class="item-badge">{{ number_format($disease->symptoms_count) }} symptoms</span>
                    </div>
                    <p class="item-copy">{{ \Illuminate\Support\Str::limit($disease->description ?: 'Open this disease in public view to inspect linked content and navigation.', 120) }}</p>
                    <div class="item-actions">
                        <a href="{{ route('public.disease.show', $disease) }}" class="btn-open"><i class="fas fa-arrow-up-right-from-square"></i>Public View</a>
                        <button type="button" class="btn-edit js-toggle-edit" data-target="disease-edit-{{ $disease->id }}"><i class="fas fa-pen"></i>Edit</button>
                        <form method="POST" action="{{ route('admin.diseases.destroy', $disease) }}" onsubmit="return confirm('Delete this disease?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn-delete" type="submit"><i class="fas fa-trash"></i>Delete</button>
                        </form>
                    </div>

                    <div id="disease-edit-{{ $disease->id }}" class="edit-box">
                        <form method="POST" action="{{ route('admin.diseases.update', $disease) }}">
                            @csrf
                            @method('PATCH')
                            <div class="mb-2">
                                <input class="form-input" type="text" name="disease_name" value="{{ $disease->disease_name }}" required>
                            </div>
                            <div class="mb-2">
                                <textarea class="form-textarea" name="description">{{ $disease->description }}</textarea>
                            </div>
                            <button class="btn-main" type="submit"><i class="fas fa-floppy-disk"></i>Save</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="item-card" style="grid-column: 1 / -1; text-align: center;">
                    <p class="mb-0 text-muted">No diseases found for this search.</p>
                </div>
            @endforelse
        </div>

        @if ($diseases->hasPages())
            <div class="mt-3">{{ $diseases->links() }}</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.js-toggle-edit').forEach(function (button) {
            button.addEventListener('click', function () {
                const target = document.getElementById(button.getAttribute('data-target'));
                if (target) {
                    target.classList.toggle('is-open');
                }
            });
        });
    });
</script>
@endpush
