@extends('layouts.app')

@section('title', __('ui.admin_symptoms.title'))

@push('styles')
<style>
    .admin-catalog-surface {
        min-height: 100vh;
        padding: 2rem 0 4rem;
        background:
            radial-gradient(circle at 85% 15%, rgba(180, 83, 9, 0.22), transparent 34%),
            radial-gradient(circle at 8% 82%, rgba(249, 115, 22, 0.2), transparent 36%),
            linear-gradient(155deg, #fff8ef 0%, #fffdf8 44%, #fff5ea 100%);
    }

    .catalog-hero {
        border-radius: 26px;
        padding: 1.9rem;
        margin-bottom: 1.4rem;
        color: #fff;
        background: linear-gradient(135deg, #b45309 0%, #d97706 52%, #f59e0b 100%);
        box-shadow: 0 20px 54px rgba(180, 83, 9, 0.25);
    }

    .catalog-hero h1 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 800;
    }

    .catalog-hero p {
        margin: 0.4rem 0 0;
        opacity: 0.93;
    }

    .catalog-toolbar,
    .catalog-panel,
    .item-card {
        background: #fff;
        border: 1px solid rgba(180, 83, 9, 0.12);
        border-radius: 20px;
        box-shadow: 0 12px 32px rgba(90, 45, 6, 0.08);
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
    .form-input {
        min-height: 44px;
        border: 1px solid rgba(180, 83, 9, 0.22);
        border-radius: 11px;
        padding: 0.6rem 0.84rem;
        width: 100%;
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

    .btn-main { color: #fff; background: linear-gradient(135deg, #b45309, #f59e0b); }
    .btn-ghost { color: #7c2d12; background: #fff2df; }
    .btn-open { color: #92400e; background: rgba(217, 119, 6, 0.13); }
    .btn-edit { color: #fff; background: #d97706; }
    .btn-delete { color: #fff; background: #d14343; }

    .catalog-panel {
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .create-grid {
        display: grid;
        grid-template-columns: 1fr auto;
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
        color: #7c2d12;
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
        background: #fff7ed;
        color: #b45309;
        border: 1px solid rgba(217, 119, 6, 0.25);
        font-size: 0.75rem;
        font-weight: 700;
    }

    .item-copy {
        margin-top: 0.65rem;
        color: #8a4f2d;
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
        border-top: 1px dashed rgba(180, 83, 9, 0.24);
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
            <h1><i class="fas fa-stethoscope me-2"></i>{{ __('ui.admin_symptoms.title') }}</h1>
            <p>{{ __('ui.admin_symptoms.subtitle') }}</p>
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
            <form class="toolbar-grid" method="GET" action="{{ route('admin.symptoms.index') }}">
                <input type="text" name="q" value="{{ $search }}" placeholder="{{ __('ui.admin_symptoms.search_placeholder') }}">
                <button class="btn-main" type="submit"><i class="fas fa-magnifying-glass"></i>{{ __('ui.admin_symptoms.search') }}</button>
                <a href="{{ route('admin.dashboard') }}" class="btn-ghost"><i class="fas fa-arrow-left"></i>{{ __('ui.admin_symptoms.back') }}</a>
            </form>
        </section>

        <section class="catalog-panel">
            <form class="create-grid" method="POST" action="{{ route('admin.symptoms.store') }}">
                @csrf
                <input class="form-input" type="text" name="name" placeholder="{{ __('ui.admin_symptoms.new_symptom_name') }}" required>
                <input class="form-input" type="text" name="name_bn" placeholder="Bangla name (বাংলা)">
                <button class="btn-main" type="submit"><i class="fas fa-plus"></i>{{ __('ui.admin_symptoms.create') }}</button>
            </form>
        </section>

        <div class="cards-grid">
            @forelse ($symptoms as $symptom)
                <article class="item-card">
                    <h2 class="item-title">{{ $symptom->display_name }}</h2>
                    <div class="item-badges">
                        <span class="item-badge">{{ number_format($symptom->user_symptoms_count) }} {{ __('ui.admin_symptoms.user_logs') }}</span>
                        <span class="item-badge">{{ number_format($symptom->diseases_count) }} {{ __('ui.admin_symptoms.diseases') }}</span>
                    </div>
                    <p class="item-copy">{{ __('ui.admin_symptoms.default_description') }}</p>
                    <div class="item-actions">
                        <a href="{{ route('public.symptoms.show', $symptom) }}" class="btn-open"><i class="fas fa-arrow-up-right-from-square"></i>{{ __('ui.admin_symptoms.public_view') }}</a>
                        <button type="button" class="btn-edit js-toggle-edit" data-target="symptom-edit-{{ $symptom->id }}"><i class="fas fa-pen"></i>{{ __('ui.admin_symptoms.edit') }}</button>
                        <form method="POST" action="{{ route('admin.symptoms.destroy', $symptom) }}" onsubmit="return confirm('{{ __('ui.admin_symptoms.delete_confirm') }}');">
                            @csrf
                            @method('DELETE')
                            <button class="btn-delete" type="submit"><i class="fas fa-trash"></i>{{ __('ui.admin_symptoms.delete') }}</button>
                        </form>
                    </div>

                    <div id="symptom-edit-{{ $symptom->id }}" class="edit-box">
                        <form method="POST" action="{{ route('admin.symptoms.update', $symptom) }}">
                            @csrf
                            @method('PATCH')
                            <div class="mb-2">
                                <input class="form-input" type="text" name="name" value="{{ $symptom->name }}" required>
                            </div>
                            <div class="mb-2">
                                <input class="form-input" type="text" name="name_bn" value="{{ $symptom->bangla_name }}" placeholder="Bangla name (বাংলা)">
                            </div>
                            <button class="btn-main" type="submit"><i class="fas fa-floppy-disk"></i>{{ __('ui.admin_symptoms.save') }}</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="item-card" style="grid-column: 1 / -1; text-align: center;">
                    <p class="mb-0 text-muted">{{ __('ui.admin_symptoms.no_symptoms_found') }}</p>
                </div>
            @endforelse
        </div>

        @if ($symptoms->hasPages())
            <div class="mt-3">{{ $symptoms->links() }}</div>
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