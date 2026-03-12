@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Admin Dashboard Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 text-primary">
                    <i class="fas fa-shield-alt me-2"></i>Admin Dashboard
                </h1>
                <p class="text-muted mb-0">Complete system overview and management</p>
            </div>
            <div class="badge bg-danger fs-6 px-3 py-2">
                <i class="fas fa-crown me-1"></i>Administrator
            </div>
        </div>

        <!-- Statistics Overview Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm admin-stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-primary fw-bold h4 mb-1">{{ $stats['total_users'] ?? 0 }}</div>
                                <div class="text-muted small">Total Users</div>
                                <div class="text-success small mt-1">
                                    <i class="fas fa-arrow-up"></i> {{ $stats['new_users_today'] ?? 0 }} today
                                </div>
                            </div>
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-users text-primary fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm admin-stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-success fw-bold h4 mb-1">{{ $stats['total_posts'] ?? 0 }}</div>
                                <div class="text-muted small">Community Posts</div>
                                <div class="text-info small mt-1">
                                    <i class="fas fa-comments"></i> {{ $stats['total_comments'] ?? 0 }} comments
                                </div>
                            </div>
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-newspaper text-success fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm admin-stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-warning fw-bold h4 mb-1">{{ $stats['total_medicines'] ?? 0 }}</div>
                                <div class="text-muted small">Medicine Records</div>
                                <div class="text-primary small mt-1">
                                    <i class="fas fa-bell"></i> {{ $stats['active_reminders'] ?? 0 }} reminders
                                </div>
                            </div>
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-pills text-warning fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm admin-stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-info fw-bold h4 mb-1">{{ $stats['total_health_metrics'] ?? 0 }}</div>
                                <div class="text-muted small">Health Records</div>
                                <div class="text-warning small mt-1">
                                    <i class="fas fa-heartbeat"></i> {{ $stats['recent_metrics'] ?? 0 }} recent
                                </div>
                            </div>
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-chart-line text-info fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Management Sections -->
        <div class="row">
            <!-- User Management -->
            <div class="col-lg-8 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-users-cog me-2 text-primary"></i>User Management
                            </h5>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshUsers()">
                                    <i class="fas fa-sync-alt"></i> Refresh
                                </button>
                                <a href="#" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#addUserModal">
                                    <i class="fas fa-plus"></i> Add User
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- User Search and Filter -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="userSearch"
                                        placeholder="Search users by name or email...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="roleFilter">
                                    <option value="">All Roles</option>
                                    <option value="admin">Administrators</option>
                                    <option value="member">Members</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <!-- Recent Users Table -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Role</th>
                                        <th>Joined</th>
                                        <th>Last Active</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTableBody">
                                    @forelse($recent_users ?? [] as $user)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div
                                                        class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">{{ $user->name }}</div>
                                                        <div class="text-muted small">{{ $user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge {{ $user->role === 'admin' ? 'bg-danger' : 'bg-primary' }}">
                                                    <i
                                                        class="fas {{ $user->role === 'admin' ? 'fa-crown' : 'fa-user' }} me-1"></i>
                                                    {{ ucfirst($user->role) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $user->created_at->format('M d, Y') }}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $user->updated_at->diffForHumans() }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">Active</span>
                                            </td>
                                            <td>
                                                @if ($user->role !== 'admin')
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                                            onclick="viewUser({{ $user->id }})" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-warning btn-sm"
                                                            onclick="editUser({{ $user->id }})" title="Edit User">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                            onclick="deleteUser({{ $user->id }})"
                                                            title="Delete User">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="text-muted small">Protected</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-users fa-2x mb-2"></i>
                                                <div>No users found</div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- View All Users Link -->
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-list me-1"></i>View All Users
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & System Info -->
            <div class="col-lg-4 mb-4">
                <!-- Quick Actions -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt me-2 text-warning"></i>Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-users-cog me-2"></i>Manage All Users
                            </a>
                            <a href="{{ route('admin.medical.index') }}" class="btn btn-outline-success">
                                <i class="fas fa-stethoscope me-2"></i>Medical Data Overview
                            </a>
                            <a href="{{ route('admin.analytics') }}" class="btn btn-outline-info">
                                <i class="fas fa-chart-bar me-2"></i>Analytics Dashboard
                            </a>
                            <a href="{{ route('admin.settings') }}" class="btn btn-outline-warning">
                                <i class="fas fa-cogs me-2"></i>System Settings
                            </a>
                            <button type="button" class="btn btn-outline-danger" onclick="exportData()">
                                <i class="fas fa-download me-2"></i>Export Data
                            </button>
                        </div>
                    </div>
                </div>

                <!-- System Health -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-heartbeat me-2 text-danger"></i>System Health
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-muted">Database</span>
                                <span class="badge bg-success">Online</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: 95%"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-muted">Storage</span>
                                <span class="small text-muted">{{ $stats['storage_usage'] ?? '45' }}% used</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-info" style="width: {{ $stats['storage_usage'] ?? '45' }}%">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-muted">Queue Jobs</span>
                                <span class="badge bg-warning">{{ $stats['pending_jobs'] ?? 3 }} pending</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-warning" style="width: 20%"></div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                onclick="refreshSystemHealth()">
                                <i class="fas fa-sync-alt me-1"></i>Refresh Status
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2 text-primary"></i>Recent Activity
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="activity-timeline">
                            @forelse($recent_activities ?? [] as $activity)
                                <div class="d-flex align-items-start mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="activity-icon bg-primary text-white rounded-circle">
                                            <i class="fas fa-user-plus"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="small">{{ $activity['message'] ?? 'System activity' }}</div>
                                        <div class="text-muted small">{{ $activity['time'] ?? 'Few minutes ago' }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-history fa-2x mb-2"></i>
                                    <div class="small">No recent activity</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Medical Data Overview -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-stethoscope me-2 text-success"></i>Medical Data Overview
                            </h5>
                            <a href="{{ route('admin.medical.index') }}" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-external-link-alt me-1"></i>View All
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="text-primary h4 mb-1">{{ $stats['total_medicines'] ?? 0 }}</div>
                                    <div class="text-muted small">Medicine Records</div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="text-success h4 mb-1">{{ $stats['total_health_metrics'] ?? 0 }}</div>
                                    <div class="text-muted small">Health Metrics</div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="text-warning h4 mb-1">{{ $stats['total_symptoms'] ?? 0 }}</div>
                                    <div class="text-muted small">Symptom Reports</div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="text-info h4 mb-1">{{ $stats['total_diseases'] ?? 0 }}</div>
                                    <div class="text-muted small">Disease Records</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .admin-stat-card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .admin-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
        }

        .avatar-sm {
            width: 36px;
            height: 36px;
            font-size: 14px;
            font-weight: 600;
        }

        .activity-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }

        .activity-timeline {
            max-height: 300px;
            overflow-y: auto;
        }

        .table th {
            font-weight: 600;
            font-size: 0.875rem;
            border-bottom: 2px solid #dee2e6;
        }

        .progress {
            background-color: #e9ecef;
        }
    </style>

    <!-- JavaScript Functions -->
    <script>
        function refreshUsers() {
            // Add refresh functionality
            location.reload();
        }

        function viewUser(userId) {
            // Implement user view modal or page
            console.log('View user:', userId);
        }

        function editUser(userId) {
            // Implement user edit functionality
            console.log('Edit user:', userId);
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                // Implement user deletion
                console.log('Delete user:', userId);
            }
        }

        function exportData() {
            // Implement data export functionality
            console.log('Export data');
        }

        function refreshSystemHealth() {
            // Implement system health refresh
            console.log('Refresh system health');
        }
    </script>
@endsection
