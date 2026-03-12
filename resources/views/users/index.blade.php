@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Users Page Header -->
        <div class="text-center mb-5">
            <h1 class="display-4 text-primary mb-3">
                <i class="fas fa-users me-3"></i>Community Members
            </h1>
            <p class="lead text-muted">Connect with fellow health enthusiasts in our community</p>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-5">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm text-center community-stat-card">
                    <div class="card-body">
                        <div class="text-primary mb-2">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                        <div class="text-primary fw-bold h3 mb-1">{{ $totalUsers }}</div>
                        <div class="text-muted">Total Members</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm text-center community-stat-card">
                    <div class="card-body">
                        <div class="text-success mb-2">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                        <div class="text-success fw-bold h3 mb-1">{{ $memberCount }}</div>
                        <div class="text-muted">Active Members</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm text-center community-stat-card">
                    <div class="card-body">
                        <div class="text-warning mb-2">
                            <i class="fas fa-crown fa-2x"></i>
                        </div>
                        <div class="text-warning fw-bold h3 mb-1">{{ $adminCount }}</div>
                        <div class="text-muted">Community Leaders</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm text-center community-stat-card">
                    <div class="card-body">
                        <div class="text-info mb-2">
                            <i class="fas fa-user-plus fa-2x"></i>
                        </div>
                        <div class="text-info fw-bold h3 mb-1">{{ $recentUsers }}</div>
                        <div class="text-muted">New This Week</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control form-control-lg" id="searchInput"
                                placeholder="Search members by name..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-lg" id="roleFilter" onchange="applyFilters()">
                            <option value="">All Members</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Community Leaders
                            </option>
                            <option value="member" {{ request('role') == 'member' ? 'selected' : '' }}>Members</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-lg" id="sortBy" onchange="applyFilters()">
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Newest First</option>
                            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name A-Z</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Members Grid -->
        <div class="row">
            @forelse($users as $user)
                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                    <div class="card member-card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <!-- User Avatar -->
                            <div
                                class="avatar-lg bg-{{ $user->role === 'admin' ? 'warning' : 'primary' }} text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>

                            <!-- User Info -->
                            <h5 class="card-title mb-1">{{ $user->name }}</h5>

                            <!-- Role Badge -->
                            @if ($user->role === 'admin')
                                <span class="badge bg-warning mb-3">
                                    <i class="fas fa-crown me-1"></i>Community Leader
                                </span>
                            @else
                                <span class="badge bg-primary mb-3">
                                    <i class="fas fa-user me-1"></i>Member
                                </span>
                            @endif

                            <!-- Member Stats -->
                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    <div class="text-primary fw-bold">{{ $loop->iteration }}</div>
                                    <div class="small text-muted">Member #</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-success fw-bold">{{ $user->created_at->format('M Y') }}</div>
                                    <div class="small text-muted">Joined</div>
                                </div>
                            </div>

                            <!-- Email Status -->
                            <div class="mb-3">
                                @if ($user->email_verified_at)
                                    <span class="badge bg-success-soft text-success">
                                        <i class="fas fa-check-circle me-1"></i>Verified Member
                                    </span>
                                @else
                                    <span class="badge bg-warning-soft text-warning">
                                        <i class="fas fa-clock me-1"></i>Pending Verification
                                    </span>
                                @endif
                            </div>

                            <!-- Member Since -->
                            <div class="text-muted small">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Member since {{ $user->created_at->format('M d, Y') }}
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="card-footer bg-transparent border-0 pt-0">
                            <button class="btn btn-outline-primary btn-sm w-100"
                                onclick="connectWithUser('{{ $user->name }}')">
                                <i class="fas fa-handshake me-1"></i>Connect
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted">No members found</h4>
                        <p class="text-muted">Try adjusting your search criteria or filters</p>
                        <button class="btn btn-primary" onclick="clearFilters()">
                            <i class="fas fa-refresh me-1"></i>Clear Filters
                        </button>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($users->hasPages())
            <div class="d-flex justify-content-center mt-5">
                <nav aria-label="Members pagination">
                    <ul class="pagination pagination-lg">
                        {{-- Previous Page Link --}}
                        @if ($users->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->appends(request()->query())->previousPageUrl() }}">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            </li>
                        @endif

                        {{-- Pagination Elements --}}
                        @php
                            $start = max(1, $users->currentPage() - 2);
                            $end = min($users->lastPage(), $users->currentPage() + 2);
                        @endphp

                        @if ($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->appends(request()->query())->url(1) }}">1</a>
                            </li>
                            @if ($start > 2)
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            @endif
                        @endif

                        @for ($i = $start; $i <= $end; $i++)
                            @if ($i == $users->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $i }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link"
                                        href="{{ $users->appends(request()->query())->url($i) }}">{{ $i }}</a>
                                </li>
                            @endif
                        @endfor

                        @if ($end < $users->lastPage())
                            @if ($end < $users->lastPage() - 1)
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            @endif
                            <li class="page-item">
                                <a class="page-link"
                                    href="{{ $users->appends(request()->query())->url($users->lastPage()) }}">{{ $users->lastPage() }}</a>
                            </li>
                        @endif

                        {{-- Next Page Link --}}
                        @if ($users->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->appends(request()->query())->nextPageUrl() }}">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">
                                    Next <i class="fas fa-chevron-right"></i>
                                </span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>

            <!-- Pagination Info -->
            <div class="text-center mt-3">
                <div class="text-muted">
                    Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} members
                </div>
            </div>
        @endif
    </div>

@endsection

@push('styles')
    <style>
        .community-stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .community-stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
        }

        .member-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .member-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        }

        .avatar-lg {
            width: 80px;
            height: 80px;
            font-size: 28px;
            font-weight: bold;
        }

        .bg-success-soft {
            background-color: rgba(25, 135, 84, 0.1) !important;
        }

        .bg-warning-soft {
            background-color: rgba(255, 193, 7, 0.1) !important;
        }

        .pagination .page-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
            padding: 12px 18px;
            margin: 0 2px;
            border-radius: 8px;
        }

        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            color: white;
            box-shadow: 0 4px 8px rgba(13, 110, 253, 0.3);
        }

        .pagination .page-link:hover {
            color: #0d6efd;
            background-color: #e9ecef;
            transform: translateY(-2px);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        .display-4 {
            font-weight: 300;
        }

        .lead {
            font-size: 1.1rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        function applyFilters() {
            const search = document.getElementById('searchInput').value;
            const role = document.getElementById('roleFilter').value;
            const sort = document.getElementById('sortBy').value;
            const params = new URLSearchParams();
            if (search) params.set('search', search);
            if (role) params.set('role', role);
            if (sort) params.set('sort', sort);
            window.location.href = '{{ route('users.index') }}?' + params.toString();
        }
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') applyFilters();
                });
            }
        });

        function connectWithUser(userName) {
            alert(`Feature coming soon! Connect with ${userName} functionality will be available in future updates.`);
        }

        function clearFilters() {
            window.location.href = '{{ route('users.index') }}';
        }
    </script>
@endpush
