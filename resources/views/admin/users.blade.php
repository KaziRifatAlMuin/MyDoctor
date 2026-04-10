@extends('layouts.app')

@section('title', 'Admin Users')

@push('styles')
<style>
    .admin-users-surface {
        min-height: 100vh;
        padding: 2rem 0 4rem;
        background:
            radial-gradient(circle at 12% 12%, rgba(37, 99, 235, 0.2), transparent 36%),
            radial-gradient(circle at 82% 18%, rgba(14, 165, 233, 0.18), transparent 30%),
            linear-gradient(160deg, #eef4ff 0%, #f8fbff 45%, #edf3ff 100%);
    }

    .users-hero {
        border-radius: 26px;
        padding: 1.9rem;
        color: #fff;
        margin-bottom: 1.4rem;
        background: linear-gradient(135deg, #123fbb 0%, #1f5fd1 52%, #0ea5e9 100%);
        box-shadow: 0 20px 54px rgba(18, 63, 187, 0.25);
    }

    .users-hero h1 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 800;
    }

    .users-hero p {
        margin-top: 0.45rem;
        opacity: 0.92;
    }

    .hero-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.7rem;
        margin-top: 1rem;
    }

    .hero-stat {
        border: 1px solid rgba(255, 255, 255, 0.24);
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.12);
        padding: 0.65rem 0.85rem;
    }

    .hero-stat strong {
        font-size: 1.2rem;
        line-height: 1.1;
        display: block;
    }

    .users-panel {
        background: #fff;
        border-radius: 22px;
        border: 1px solid rgba(30, 64, 175, 0.11);
        box-shadow: 0 12px 32px rgba(15, 33, 87, 0.08);
        overflow: hidden;
    }

    .users-panel-head {
        padding: 1.2rem;
        border-bottom: 1px solid rgba(30, 64, 175, 0.1);
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }

    .filter-form {
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: 0.65rem;
    }

    .filter-form input {
        min-height: 46px;
        border-radius: 12px;
        border: 1px solid rgba(30, 64, 175, 0.22);
        padding: 0.65rem 0.9rem;
    }

    .btn-main,
    .btn-ghost {
        min-height: 46px;
        border: 0;
        border-radius: 12px;
        font-weight: 700;
        padding: 0.6rem 1rem;
    }

    .btn-main {
        color: #fff;
        background: linear-gradient(135deg, #1d4ed8, #0ea5e9);
    }

    .btn-ghost {
        color: #17356c;
        background: #eaf1ff;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .table-shell {
        padding: 1rem;
    }

    .table-users {
        width: 100%;
        border-collapse: collapse;
    }

    .table-users th {
        background: #f3f7ff;
        color: #5c6f95;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-size: 0.74rem;
        padding: 0.8rem;
        border-bottom: 1px solid rgba(30, 64, 175, 0.12);
        white-space: nowrap;
    }

    .table-users td {
        padding: 0.85rem;
        border-bottom: 1px solid rgba(30, 64, 175, 0.08);
        vertical-align: middle;
    }

    .table-users tr:hover td {
        background: #f9fbff;
    }

    .user-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .user-avatar,
    .user-avatar-placeholder {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        object-fit: cover;
        flex-shrink: 0;
    }

    .user-avatar-placeholder {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 800;
        background: linear-gradient(135deg, #1d4ed8, #0ea5e9);
    }

    .user-name {
        color: #193664;
        font-weight: 700;
    }

    .user-sub {
        color: #7487ab;
        font-size: 0.83rem;
    }

    .role-badge,
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.38rem 0.72rem;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .role-admin { background: rgba(180, 83, 9, 0.14); color: #b45309; }
    .role-member { background: rgba(29, 78, 216, 0.14); color: #1d4ed8; }
    .status-ok { background: rgba(15, 159, 95, 0.14); color: #0f9f5f; }
    .status-pending { background: rgba(217, 119, 6, 0.18); color: #b45309; }

    .action-link {
        border-radius: 10px;
        border: 1px solid rgba(30, 64, 175, 0.25);
        padding: 0.36rem 0.64rem;
        color: #1d4ed8;
        text-decoration: none;
        font-size: 0.82rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .pagination-wrap {
        padding: 0.8rem 1rem 1.2rem;
    }

    @media (max-width: 991px) {
        .hero-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .filter-form {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575px) {
        .hero-stats {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="admin-users-surface">
    <div class="container" style="max-width: 1240px;">
        <div class="users-hero">
            <h1><i class="fas fa-users me-2"></i>Admin Users Directory</h1>
            <p>All user accounts are now listed here. Dashboard is reserved for cards and system-wide statistics.</p>
            <div class="hero-stats">
                <div class="hero-stat"><small>Total Users</small><strong>{{ number_format($users->total()) }}</strong></div>
                <div class="hero-stat"><small>Admins</small><strong>{{ number_format($adminCount) }}</strong></div>
                <div class="hero-stat"><small>Members</small><strong>{{ number_format($memberCount) }}</strong></div>
                <div class="hero-stat"><small>Verified</small><strong>{{ number_format($verifiedCount) }}</strong></div>
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

        <section class="users-panel">
            <div class="users-panel-head">
                <form class="filter-form" method="GET" action="{{ route('admin.users.index') }}">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Search by name or email...">
                    <button class="btn-main" type="submit"><i class="fas fa-magnifying-glass me-1"></i>Search</button>
                    <a href="{{ route('admin.dashboard') }}" class="btn-ghost"><i class="fas fa-arrow-left me-1"></i>Dashboard</a>
                </form>
            </div>

            <div class="table-shell table-responsive">
                <table class="table-users" id="usersTable">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        @if ($user->picture)
                                            <img src="{{ asset('storage/' . $user->picture) }}" alt="{{ $user->name }}" class="user-avatar">
                                        @else
                                            <span class="user-avatar-placeholder">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                        @endif
                                        <div>
                                            <div class="user-name">{{ $user->name }}</div>
                                            <div class="user-sub">ID #{{ $user->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="role-badge role-{{ strtolower($user->role) }}">
                                        <i class="fas {{ $user->role === 'admin' ? 'fa-crown' : 'fa-user' }}"></i>
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($user->email_verified_at)
                                        <span class="status-badge status-ok"><i class="fas fa-circle-check"></i>Verified</span>
                                    @else
                                        <span class="status-badge status-pending"><i class="fas fa-clock"></i>Pending</span>
                                    @endif
                                </td>
                                <td>{{ optional($user->created_at)->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.users.show', $user) }}" class="action-link">
                                        <i class="fas fa-arrow-up-right-from-square"></i>View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="pagination-wrap d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2">
                    <div class="small text-muted">
                        Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} users
                    </div>
                    <div>{{ $users->links() }}</div>
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
