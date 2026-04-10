@extends('layouts.app')

@section('title', 'Admin Diseases')

@push('styles')
<style>
    .admin-surface {
        min-height: 100vh;
        padding: 2rem 0 4rem;
        background: radial-gradient(circle at 85% 15%, rgba(240, 147, 251, 0.28), transparent 35%),
                    radial-gradient(circle at 10% 85%, rgba(102, 126, 234, 0.25), transparent 38%),
                    linear-gradient(145deg, #eff4ff 0%, #f7f9ff 45%, #eef2ff 100%);
    }

    .hero-card {
        border-radius: 26px;
        padding: 1.8rem;
        color: #fff;
        margin-bottom: 1.5rem;
        background: linear-gradient(135deg, #1f6fba 0%, #2f80ed 45%, #4f9ff9 100%);
        box-shadow: 0 20px 50px rgba(31, 111, 186, 0.26);
    }

    .panel-card {
        background: #fff;
        border-radius: 22px;
        border: 1px solid rgba(31, 111, 186, 0.12);
        box-shadow: 0 12px 32px rgba(18, 32, 86, 0.08);
        overflow: hidden;
    }

    .panel-head {
        padding: 1.2rem;
        border-bottom: 1px solid rgba(31, 111, 186, 0.1);
        background: linear-gradient(180deg, #fdfdff 0%, #f8faff 100%);
    }

    .search-form {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 0.75rem;
    }

    .search-form input,
    .mini-input,
    .mini-textarea {
        width: 100%;
        min-height: 42px;
        border-radius: 10px;
        border: 1px solid rgba(31, 111, 186, 0.2);
        padding: 0.45rem 0.65rem;
    }

    .mini-textarea {
        min-height: 62px;
        resize: vertical;
    }

    .action-btn {
        border: 0;
        border-radius: 12px;
        min-height: 42px;
        padding: 0.6rem 1rem;
        font-weight: 700;
        color: #fff;
        background: linear-gradient(135deg, #1f6fba, #2f80ed);
    }

    .table-wrap {
        padding: 1rem;
    }

    .table-admin {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-admin th {
        background: #f4f8ff;
        color: #4e678d;
        text-transform: uppercase;
        font-size: 0.74rem;
        letter-spacing: 0.08em;
        padding: 0.85rem;
        border-bottom: 1px solid rgba(31, 111, 186, 0.12);
    }

    .table-admin td {
        padding: 0.85rem;
        border-bottom: 1px solid rgba(31, 111, 186, 0.08);
        vertical-align: middle;
    }

    .table-admin tr:hover td {
        background: #f9fbff;
    }

    .btn-save,
    .btn-delete {
        border: 0;
        border-radius: 8px;
        color: #fff;
        font-size: 0.78rem;
        padding: 0.44rem 0.62rem;
    }

    .btn-save { background: #0f9f5f; }
    .btn-delete { background: #d44c4c; }

    .create-grid {
        display: grid;
        grid-template-columns: 1fr 1.4fr auto;
        gap: 0.6rem;
        margin-top: 1rem;
    }

    @media (max-width: 991px) {
        .create-grid,
        .search-form {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="admin-surface">
    <div class="container" style="max-width: 1240px;">
        <div class="hero-card">
            <h1 style="margin:0;font-size:1.7rem;font-weight:800;"><i class="fas fa-virus me-2"></i>Admin Diseases</h1>
            <p style="margin-top:0.35rem;opacity:0.92;">Row-by-row disease management with full CRUD controls.</p>
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

        <div class="panel-card">
            <div class="panel-head">
                <form class="search-form" method="GET" action="{{ route('admin.diseases.index') }}">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Search diseases by name">
                    <button class="action-btn" type="submit"><i class="fas fa-magnifying-glass me-1"></i>Search</button>
                </form>

                <form class="create-grid" method="POST" action="{{ route('admin.diseases.store') }}">
                    @csrf
                    <input class="mini-input" type="text" name="disease_name" placeholder="Disease name" required>
                    <textarea class="mini-textarea" name="description" placeholder="Description (optional)"></textarea>
                    <button class="action-btn" type="submit"><i class="fas fa-plus me-1"></i>Create</button>
                </form>
            </div>

            <div class="table-wrap table-responsive">
                <table class="table-admin">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th style="width: 95px;">Users</th>
                            <th style="width: 95px;">Symptoms</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($diseases as $disease)
                        <tr>
                            <td>#{{ $disease->id }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.diseases.update', $disease) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input class="mini-input" type="text" name="disease_name" value="{{ $disease->disease_name }}" required>
                            </td>
                            <td>
                                    <textarea class="mini-textarea" name="description">{{ $disease->description }}</textarea>
                            </td>
                            <td>{{ number_format($disease->user_diseases_count) }}</td>
                            <td>{{ number_format($disease->symptoms_count) }}</td>
                            <td>
                                <div style="display:flex; gap:0.4rem; align-items:center;">
                                    <button class="btn-save" type="submit"><i class="fas fa-floppy-disk"></i></button>
                                </form>
                                    <form method="POST" action="{{ route('admin.diseases.destroy', $disease) }}" onsubmit="return confirm('Delete this disease?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-delete" type="submit"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #7a84a8; padding: 1.8rem;">No diseases found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                @if ($diseases->hasPages())
                    <div class="mt-3">{{ $diseases->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
