@extends('layouts.app')

@section('title', 'Admin Dashboard')

@php
    $activeTab = request('tab', 'users');
@endphp

@push('styles')
    <style>
        .dashboard-section {
            background: linear-gradient(180deg, #f0f2f8 0%, #e8ecf4 40%, #f5f7fb 100%);
            min-height: 100vh;
            padding: 2rem 0 4rem;
        }

        /* ── Animated Welcome Hero ── */
        .welcome-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 40%, #f093fb 80%, #667eea 100%);
            background-size: 300% 300%;
            animation: heroGradient 8s ease infinite;
            border-radius: 28px;
            padding: 2.5rem 2.5rem 2rem;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 2rem;
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.3);
        }

        @keyframes heroGradient {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        .welcome-hero::before {
            content: '';
            position: absolute;
            top: -60%;
            right: -15%;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            pointer-events: none;
            animation: float 6s ease-in-out infinite;
        }

        .welcome-hero::after {
            content: '';
            position: absolute;
            bottom: -40%;
            left: 10%;
            width: 350px;
            height: 350px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.04);
            pointer-events: none;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) scale(1);
            }

            50% {
                transform: translateY(-20px) scale(1.05);
            }
        }

        .hero-greeting {
            font-size: 1.7rem;
            font-weight: 800;
            margin-bottom: 0.25rem;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            position: relative;
            z-index: 2;
        }

        .hero-sub {
            font-size: 0.95rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        /* ── Hero Stats ── */
        .hero-stats {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
            position: relative;
            z-index: 2;
        }

        .hero-stat-pill {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 18px;
            padding: 0.7rem 1.1rem;
            min-width: 115px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: default;
        }

        .hero-stat-pill:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .hero-stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1;
        }

        .hero-stat-label {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            opacity: 0.8;
            margin-top: 4px;
        }

        /* ── Dashboard Cards ── */
        .dash-card {
            background: white;
            border-radius: 22px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            height: 100%;
            transition: all 0.3s ease;
            border: 1px solid rgba(102, 126, 234, 0.1);
        }

        .dash-card:hover {
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .dash-card-header {
            padding: 1.25rem 1.5rem 0.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #f0f0f0;
        }

        .dash-card-header h6 {
            font-weight: 800;
            color: #1a202c;
            margin: 0;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dash-card-header h6 i {
            color: #667eea;
        }

        .dash-card-body {
            padding: 1.25rem 1.5rem 1.5rem;
        }

        .dash-card-link {
            font-size: 0.78rem;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .dash-card-link:hover {
            color: #764ba2;
            gap: 8px;
        }

        /* ── Filter Toolbar ── */
        .filter-toolbar {
            border: 1px solid rgba(102, 126, 234, 0.14);
            background: #f8f9fb;
            border-radius: 20px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .filter-toolbar .form-control,
        .filter-toolbar .form-select {
            border-radius: 14px;
            min-height: 48px;
            border-color: rgba(102, 126, 234, 0.14);
        }

        .filter-toolbar .form-control:focus,
        .filter-toolbar .form-select:focus {
            border-color: rgba(102, 126, 234, 0.4);
            box-shadow: 0 0 0 0.22rem rgba(102, 126, 234, 0.12);
        }

        /* ── Table Card ── */
        .dashboard-table-card {
            border: 1px solid rgba(102, 126, 234, 0.14);
            border-radius: 20px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        }

        .dashboard-table {
            margin: 0;
        }

        .dashboard-table thead th {
            border: none;
            background: #f8f9fb;
            color: #6f7c96;
            font-size: 0.78rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 1.1rem;
            white-space: nowrap;
            font-weight: 700;
        }

        .dashboard-table tbody td {
            padding: 1.1rem;
            border-color: rgba(102, 126, 234, 0.08);
            vertical-align: middle;
            color: #1f2a44;
        }

        .dashboard-table tbody tr {
            transition: background 0.2s;
        }

        .dashboard-table tbody tr:hover {
            background: rgba(102, 126, 234, 0.04);
        }

        .user-cell {
            display: flex;
            align-items: center;
            gap: 0.95rem;
        }

        .user-avatar,
        .user-avatar-placeholder {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .user-avatar-placeholder {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            font-weight: 700;
        }

        .user-name {
            font-weight: 700;
            color: #1f2a44;
        }

        .user-subtext {
            color: #6f7c96;
            font-size: 0.92rem;
        }

        .status-badge,
        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .role-badge.role-admin {
            background: rgba(246, 196, 83, 0.22);
            color: #a26b00;
        }

        .role-badge.role-member {
            background: rgba(102, 126, 234, 0.14);
            color: #667eea;
        }

        .status-badge.status-verified {
            background: rgba(47, 158, 114, 0.14);
            color: #2f9e72;
        }

        .status-badge.status-pending {
            background: rgba(246, 196, 83, 0.18);
            color: #a26b00;
        }

        .table-actions {
            display: flex;
            gap: 0.5rem;
        }

        .table-actions .btn {
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .table-actions .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .pagination-shell {
            padding: 1rem 1.25rem 1.25rem;
        }

        .pagination-shell .pagination {
            margin-bottom: 0;
        }

        /* ── Action Panel ── */
        .dashboard-panel {
            border: 1px solid rgba(102, 126, 234, 0.14);
            background: white;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.05);
            border-radius: 22px;
            overflow: hidden;
        }

        .dashboard-panel-head {
            padding: 1.75rem 2rem 0;
        }

        .dashboard-panel-title {
            font-size: 1.35rem;
            font-weight: 800;
            color: #1f2a44;
            margin: 0;
        }

        .dashboard-panel-copy {
            color: #6f7c96;
            margin: 0.5rem 0 0;
            font-size: 0.95rem;
        }

        .dashboard-tabs {
            padding: 1.25rem 2rem 0;
            gap: 0.75rem;
            border-bottom: none;
        }

        .dashboard-tabs .nav-link {
            border: none;
            border-radius: 18px;
            color: #6f7c96;
            background: #f0f2f8;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .dashboard-tabs .nav-link:hover {
            background: #e8ecf4;
        }

        .dashboard-tabs .nav-link.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            box-shadow: 0 12px 24px rgba(102, 126, 234, 0.22);
        }

        .dashboard-tab-body {
            padding: 1.75rem 2rem;
        }

        /* ── Section Title ── */
        .section-title {
            font-weight: 800;
            color: #1a202c;
            font-size: 1.05rem;
            margin-bottom: 1rem;
            margin-top: 2rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: #667eea;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .welcome-hero {
                padding: 1.75rem 1.25rem 1.5rem;
                border-radius: 20px;
            }

            .hero-greeting {
                font-size: 1.3rem;
            }

            .hero-stats {
                gap: 0.5rem;
            }

            .hero-stat-pill {
                min-width: 100px;
                padding: 0.6rem 0.9rem;
            }

            .hero-stat-value {
                font-size: 1.2rem;
            }

            .dashboard-panel-head,
            .dashboard-tabs,
            .dashboard-tab-body {
                padding-inline: 1.25rem;
            }
        }

        /* ── Fade-in-up for cards ── */
        .fade-in-up {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s ease forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .delay-1 {
            animation-delay: 0.1s;
        }

        .delay-2 {
            animation-delay: 0.2s;
        }

        .delay-3 {
            animation-delay: 0.3s;
        }

        .delay-4 {
            animation-delay: 0.4s;
        }

        .delay-5 {
            animation-delay: 0.5s;
        }

        .delay-6 {
            animation-delay: 0.6s;
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-section">
        <div class="container" style="max-width: 1180px;">

            {{-- ══════════════════════════════════════════════════════
             ADMIN WELCOME HERO
        ══════════════════════════════════════════════════════ --}}
            <div class="welcome-hero fade-in-up">
                <div class="d-flex align-items-center justify-content-between position-relative hero-flex-wrap"
                    style="z-index:2;flex-wrap:wrap;gap:1.5rem;">
                    <div>
                        <div class="hero-greeting">
                            Welcome back, Admin! 👋
                        </div>
                        <div class="hero-sub">System status and operations overview</div>
                    </div>
                    <div class="hero-stats">
                        <div class="hero-stat-pill">
                            <div class="hero-stat-value">{{ number_format($stats['total_users'] ?? 0) }}</div>
                            <div class="hero-stat-label"><i class="fas fa-users me-1"></i>Users</div>
                        </div>
                        <div class="hero-stat-pill">
                            <div class="hero-stat-value">{{ number_format($adminCount ?? 0) }}</div>
                            <div class="hero-stat-label"><i class="fas fa-crown me-1"></i>Admins</div>
                        </div>
                        <div class="hero-stat-pill">
                            <div class="hero-stat-value">{{ number_format($stats['active_reminders'] ?? 0) }}</div>
                            <div class="hero-stat-label"><i class="fas fa-bell me-1"></i>Active</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════
             STATS ROW
        ══════════════════════════════════════════════════════ --}}
            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-lg-3 fade-in-up delay-1">
                    <div class="dash-card">
                        <div class="dash-card-body">
                            <div style="font-size: 2rem; font-weight: 800; color: #667eea; line-height: 1;">
                                {{ number_format($stats['total_users'] ?? 0) }}
                            </div>
                            <div style="color: #6f7c96; margin-top: 0.5rem;">Total Users</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 fade-in-up delay-2">
                    <div class="dash-card">
                        <div class="dash-card-body">
                            <div style="font-size: 2rem; font-weight: 800; color: #764ba2; line-height: 1;">
                                {{ number_format($adminCount ?? 0) }}
                            </div>
                            <div style="color: #6f7c96; margin-top: 0.5rem;">Administrators</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 fade-in-up delay-3">
                    <div class="dash-card">
                        <div class="dash-card-body">
                            <div style="font-size: 2rem; font-weight: 800; color: #2f9e72; line-height: 1;">
                                {{ number_format($memberCount ?? 0) }}
                            </div>
                            <div style="color: #6f7c96; margin-top: 0.5rem;">Members</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 fade-in-up delay-4">
                    <div class="dash-card">
                        <div class="dash-card-body">
                            <div style="font-size: 2rem; font-weight: 800; color: #dc5a6a; line-height: 1;">
                                {{ number_format($stats['recent_users'] ?? 0) }}
                            </div>
                            <div style="color: #6f7c96; margin-top: 0.5rem;">This Week</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════
             USERS MANAGEMENT PANEL
        ══════════════════════════════════════════════════════ --}}
            <section class="dashboard-panel fade-in-up delay-2">
                <div class="dashboard-panel-head">
                    <h2 class="dashboard-panel-title"><i class="fas fa-users me-2"></i>User Management</h2>
                    <p class="dashboard-panel-copy">View and manage all registered users on the platform.</p>
                </div>
                <ul class="nav nav-pills dashboard-tabs" id="adminDashboardTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link {{ $activeTab === 'users' ? 'active' : '' }}" id="users-tab"
                            data-bs-toggle="tab" data-bs-target="#users-pane" type="button" role="tab"
                            aria-controls="users-pane">
                            <i class="fas fa-users me-2"></i>All Users
                        </button>
                    </li>
                </ul>

                <div class="tab-content dashboard-tab-body">
                    <div class="tab-pane fade {{ $activeTab === 'users' ? 'show active' : '' }}" id="users-pane"
                        role="tabpanel">
                        <form method="GET" action="{{ route('admin.dashboard') }}" class="filter-toolbar">
                            <input type="hidden" name="tab" value="users">
                            <div class="row g-3 align-items-end">
                                <div class="col-lg-4">
                                    <label for="search" class="form-label fw-semibold text-secondary mb-2">Search</label>
                                    <input type="text" class="form-control" id="search" name="search"
                                        value="{{ request('search') }}" placeholder="Search by name or email">
                                </div>
                                <div class="col-lg-3">
                                    <label for="role" class="form-label fw-semibold text-secondary mb-2">Role</label>
                                    <select class="form-select" id="role" name="role">
                                        <option value="">All roles</option>
                                        <option value="admin" @selected(request('role') === 'admin')>Administrators</option>
                                        <option value="member" @selected(request('role') === 'member')>Members</option>
                                    </select>
                                </div>
                                <div class="col-lg-3">
                                    <label for="sort" class="form-label fw-semibold text-secondary mb-2">Sort by</label>
                                    <select class="form-select" id="sort" name="sort">
                                        <option value="latest" @selected(request('sort', 'latest') === 'latest')>Newest first</option>
                                        <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                                        <option value="name" @selected(request('sort') === 'name')>Name A-Z</option>
                                    </select>
                                </div>
                                <div class="col-lg-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-1"></i>Apply
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="dashboard-table-card">
                            <div class="table-responsive">
                                <table class="table dashboard-table">
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
                                                            <img src="{{ asset('storage/' . $user->picture) }}"
                                                                alt="{{ $user->name }}" class="user-avatar">
                                                        @else
                                                            <span
                                                                class="user-avatar-placeholder">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                                        @endif
                                                        <div>
                                                            <div class="user-name">{{ $user->name }}</div>
                                                            <div class="user-subtext">ID #{{ $user->id }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $user->email }}</td>
                                                <td>
                                                    <span class="role-badge role-{{ strtolower($user->role) }}">
                                                        <i
                                                            class="fas {{ $user->role === 'admin' ? 'fa-crown' : 'fa-user' }}"></i>
                                                        {{ ucfirst($user->role) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if ($user->email_verified_at)
                                                        <span class="status-badge status-verified">
                                                            <i class="fas fa-circle-check"></i>Verified
                                                        </span>
                                                    @else
                                                        <span class="status-badge status-pending">
                                                            <i class="fas fa-clock"></i>Pending
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>{{ optional($user->created_at)->format('M d, Y') }}</td>
                                                <td>
                                                    <div class="table-actions">
                                                        <a href="{{ route('users.show', $user) }}"
                                                            class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-arrow-up-right-from-square"></i> View
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5">
                                                    <div class="text-muted">
                                                        <i class="fas fa-users-slash fa-2x mb-3"></i>
                                                        <div class="fw-semibold">No users found</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if ($users->hasPages())
                                <div
                                    class="pagination-shell d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                                    <div class="text-muted small">
                                        Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of
                                        {{ $users->total() }} users
                                    </div>
                                    <div>{{ $users->appends(request()->query())->links() }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Intersection Observer for fade-in-up
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.style.animationPlayState = 'running';
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1
            });

            document.querySelectorAll('.fade-in-up').forEach(function(el) {
                el.style.animationPlayState = 'paused';
                observer.observe(el);
            });
        });
    </script>
@endpush
