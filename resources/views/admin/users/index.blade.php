@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Users Management Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 text-primary">
                    <i class="fas fa-users-cog me-2"></i>User Management
                </h1>
                <p class="text-muted mb-0">Manage all users and their permissions</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-user-plus me-1"></i>Add New User
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-primary fw-bold h4 mb-1">{{ $users->total() }}</div>
                                <div class="text-muted small">Total Users</div>
                            </div>
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-users text-primary fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-danger fw-bold h4 mb-1">{{ $adminCount }}</div>
                                <div class="text-muted small">Administrators</div>
                            </div>
                            <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-crown text-danger fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-success fw-bold h4 mb-1">{{ $memberCount }}</div>
                                <div class="text-muted small">Members</div>
                            </div>
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-user text-success fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-info fw-bold h4 mb-1">{{ $recentUsers }}</div>
                                <div class="text-muted small">New This Week</div>
                            </div>
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-user-plus text-info fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>All Users
                    </h5>
                </div>
            </div>
            <div class="card-body">
                <!-- Search and Filter Section -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="searchInput" placeholder="Search users..." 
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="roleFilter" onchange="applyFilters()">
                            <option value="">All Roles</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Administrators</option>
                            <option value="member" {{ request('role') == 'member' ? 'selected' : '' }}>Members</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="sortBy" onchange="applyFilters()">
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest First</option>
                            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name A-Z</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-primary w-100" onclick="applyFilters()">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Email Verified</th>
                                <th>Joined</th>
                                <th>Last Active</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-md bg-{{ $user->role === 'admin' ? 'danger' : 'primary' }} text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $user->name }}</div>
                                                <div class="text-muted small">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $user->role === 'admin' ? 'bg-danger' : 'bg-primary' }} fs-6">
                                            <i class="fas {{ $user->role === 'admin' ? 'fa-crown' : 'fa-user' }} me-1"></i>
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($user->email_verified_at)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>Verified
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock me-1"></i>Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $user->created_at->format('M d, Y') }}</span>
                                        <br>
                                        <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $user->updated_at->diffForHumans() }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.users.show', $user) }}" 
                                               class="btn btn-outline-primary btn-sm" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($user->role !== 'admin' || $user->id !== auth()->id())
                                                <button type="button" class="btn btn-outline-warning btn-sm" 
                                                        onclick="editUser({{ $user->id }})" title="Edit User">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                @if($user->id !== auth()->id())
                                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                                            onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')" title="Delete User">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            @else
                                                <span class="btn btn-outline-secondary btn-sm disabled">
                                                    <i class="fas fa-lock"></i>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No users found</h5>
                                        <p class="text-muted">Try adjusting your search criteria</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($users->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} users
                        </div>
                        <div>
                            {{ $users->appends(request()->query())->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>Add New User
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="addUserForm" onsubmit="addUser(event)">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="userName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="userName" required>
                        </div>
                        <div class="mb-3">
                            <label for="userEmail" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="userEmail" required>
                        </div>
                        <div class="mb-3">
                            <label for="userPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="userPassword" required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label for="userRole" class="form-label">Role</label>
                            <select class="form-select" id="userRole" required>
                                <option value="member">Member</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Add User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .stat-card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1) !important;
        }

        .avatar-md {
            width: 48px;
            height: 48px;
            font-size: 16px;
            font-weight: 600;
        }

        .table th {
            font-weight: 600;
            font-size: 0.875rem;
            border-bottom: 2px solid #dee2e6;
            background-color: #f8f9fa !important;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0,123,255,0.05);
        }

        .btn-group .btn {
            border-radius: 0.375rem !important;
            margin-right: 2px;
        }

        .modal-content {
            border-radius: 1rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
    </style>

    <!-- JavaScript Functions -->
    <script>
        function applyFilters() {
            const search = document.getElementById('searchInput').value;
            const role = document.getElementById('roleFilter').value;
            const sort = document.getElementById('sortBy').value;
            
            const params = new URLSearchParams();
            if (search) params.set('search', search);
            if (role) params.set('role', role);
            if (sort) params.set('sort', sort);
            
            window.location.href = '{{ route("admin.users.index") }}?' + params.toString();
        }

        // Search on Enter key
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
        });

        function editUser(userId) {
            // Implement edit functionality
            console.log('Edit user:', userId);
            // You can open a modal or redirect to edit page
        }

        function deleteUser(userId, userName) {
            if (confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`)) {
                // Implement delete functionality
                console.log('Delete user:', userId);
                // Send AJAX request to delete user
            }
        }

        function addUser(event) {
            event.preventDefault();
            // Implement add user functionality
            console.log('Add new user');
            // Send AJAX request to create user
        }
    </script>
@endsection