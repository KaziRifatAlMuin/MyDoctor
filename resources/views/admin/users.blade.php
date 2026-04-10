@extends('layouts.app')

@section('title', 'Admin Users')

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
        background: linear-gradient(135deg, #2446d8 0%, #5f63f2 45%, #ca5dc8 100%);
        box-shadow: 0 20px 50px rgba(36, 70, 216, 0.28);
    }

    .hero-title {
        margin: 0;
        font-size: 1.7rem;
        font-weight: 800;
    }

    .hero-sub {
        margin-top: 0.35rem;
        opacity: 0.92;
    }

    .hero-pills {
        margin-top: 1rem;
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .hero-pill {
        border: 1px solid rgba(255, 255, 255, 0.22);
        background: rgba(255, 255, 255, 0.14);
        border-radius: 14px;
        padding: 0.55rem 0.85rem;
        min-width: 120px;
    }

    .hero-pill strong {
        display: block;
        line-height: 1.1;
        font-size: 1.2rem;
    }

    .panel-card {
        background: #fff;
        border-radius: 22px;
        border: 1px solid rgba(36, 70, 216, 0.12);
        box-shadow: 0 12px 32px rgba(18, 32, 86, 0.08);
        overflow: hidden;
    }

    .panel-head {
        padding: 1.2rem;
        border-bottom: 1px solid rgba(36, 70, 216, 0.1);
        background: linear-gradient(180deg, #fdfdff 0%, #f8faff 100%);
    }

    .search-form {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 0.75rem;
    }

    .search-form input {
        min-height: 46px;
        border-radius: 12px;
        border: 1px solid rgba(36, 70, 216, 0.2);
        padding: 0.6rem 0.9rem;
    }

    .search-form button,
    .action-btn {
        border: 0;
        border-radius: 12px;
        min-height: 46px;
        padding: 0.6rem 1rem;
        font-weight: 700;
    }

    .action-btn {
        background: linear-gradient(135deg, #2446d8, #5f63f2);
        color: #fff;
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
        background: #f4f7ff;
        color: #51608a;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.08em;
        padding: 0.85rem;
        border-bottom: 1px solid rgba(36, 70, 216, 0.12);
    }

    .table-admin td {
        padding: 0.85rem;
        border-bottom: 1px solid rgba(36, 70, 216, 0.08);
        vertical-align: middle;
    }

    .table-admin tr:hover td {
        background: #f9fbff;
    }

    .mini-input,
    .mini-select {
        width: 100%;
        min-height: 36px;
        border-radius: 9px;
        border: 1px solid rgba(36, 70, 216, 0.2);
        padding: 0.35rem 0.55rem;
        font-size: 0.86rem;
    }

    .row-actions {
        display: flex;
        gap: 0.45rem;
        align-items: center;
    }

    .btn-save {
        border: 0;
        border-radius: 8px;
        background: #0f9f5f;
        color: #fff;
        font-size: 0.78rem;
        padding: 0.44rem 0.62rem;
    }

    .btn-delete {
        border: 0;
        border-radius: 8px;
        background: #d44c4c;
        color: #fff;
        font-size: 0.78rem;
        padding: 0.44rem 0.62rem;
    }

    .create-grid {
        display: grid;
        grid-template-columns: 1.3fr 1.3fr 1fr 1fr 1fr auto;
        gap: 0.6rem;
        margin-top: 1rem;
    }

    @media (max-width: 991px) {
        .create-grid {
            grid-template-columns: 1fr;
        }

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
            <h1 class="hero-title"><i class="fas fa-users-cog me-2"></i>Admin Users</h1>
            <p class="hero-sub">Create, update, and remove user accounts from one table-based control center.</p>
            <div class="hero-pills">
                <div class="hero-pill">
                    <small>Total</small>
                    <strong>{{ number_format($users->total()) }}</strong>
                </div>
                <div class="hero-pill">
                    <small>Admins</small>
                    <strong>{{ number_format($adminCount) }}</strong>
                </div>
                <div class="hero-pill">
                    <small>Members</small>
                    <strong>{{ number_format($memberCount) }}</strong>
                </div>
            </div>
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
                <form class="search-form" method="GET" action="{{ route('admin.users.index') }}">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Search users by name or email">
                    <button class="action-btn" type="submit"><i class="fas fa-magnifying-glass me-1"></i>Search</button>
                </form>

                <form class="create-grid" method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    <input class="mini-input" type="text" name="name" placeholder="New user name" required>
                    <input class="mini-input" type="email" name="email" placeholder="Email" required>
                    <input class="mini-input" type="password" name="password" placeholder="Password" required>
                    <input class="mini-input" type="text" name="phone" placeholder="Phone">
                    <select class="mini-select" name="role" required>
                        <option value="member">member</option>
                        <option value="admin">admin</option>
                    </select>
                    <button class="action-btn" type="submit"><i class="fas fa-plus me-1"></i>Create</button>
                </form>
            </div>

            <div class="table-wrap table-responsive">
                <table class="table-admin">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th style="width: 140px;">Role</th>
                            <th style="width: 150px;">Phone</th>
                            <th style="width: 190px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>#{{ $user->id }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="row-actions">
                                    @csrf
                                    @method('PATCH')
                                    <input class="mini-input" type="text" name="name" value="{{ $user->name }}" required>
                            </td>
                            <td>
                                    <input class="mini-input" type="email" name="email" value="{{ $user->email }}" required>
                            </td>
                            <td>
                                    <select class="mini-select" name="role" required>
                                        <option value="member" {{ $user->role === 'member' ? 'selected' : '' }}>member</option>
                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>admin</option>
                                    </select>
                            </td>
                            <td>
                                    <input class="mini-input" type="text" name="phone" value="{{ $user->phone }}">
                            </td>
                            <td>
                                <div class="row-actions">
                                    <button class="btn-save" type="submit"><i class="fas fa-floppy-disk"></i></button>
                                </form>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-delete" type="submit"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #7a84a8; padding: 1.8rem;">No users found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                @if ($users->hasPages())
                    <div class="mt-3">{{ $users->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
