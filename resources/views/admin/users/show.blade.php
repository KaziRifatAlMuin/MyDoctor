@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- User Profile Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 text-primary">
                    <i class="fas fa-user-circle me-2"></i>User Profile
                </h1>
                <p class="text-muted mb-0">Complete user information and activity</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Users
                </a>
                @if($user->role !== 'admin' || $user->id !== auth()->id())
                    <button type="button" class="btn btn-warning" onclick="editUser({{ $user->id }})">
                        <i class="fas fa-edit me-1"></i>Edit User
                    </button>
                    @if($user->id !== auth()->id())
                        <button type="button" class="btn btn-danger" onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')">
                            <i class="fas fa-trash me-1"></i>Delete User
                        </button>
                    @endif
                @endif
            </div>
        </div>

        <div class="row">
            <!-- User Information Card -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <!-- User Avatar -->
                        <div class="avatar-xl bg-{{ $user->role === 'admin' ? 'danger' : 'primary' }} text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        
                        <!-- User Basic Info -->
                        <h4 class="mb-1">{{ $user->name }}</h4>
                        <p class="text-muted mb-3">{{ $user->email }}</p>
                        
                        <!-- Role Badge -->
                        <div class="mb-4">
                            <span class="badge {{ $user->role === 'admin' ? 'bg-danger' : 'bg-primary' }} fs-6 px-3 py-2">
                                <i class="fas {{ $user->role === 'admin' ? 'fa-crown' : 'fa-user' }} me-1"></i>
                                {{ ucfirst($user->role) }}
                            </span>
                        </div>

                        <!-- Account Status -->
                        <div class="row text-center mb-4">
                            <div class="col-6">
                                <div class="border-end">
                                    @if($user->email_verified_at)
                                        <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                        <div class="small text-muted">Email Verified</div>
                                    @else
                                        <i class="fas fa-clock text-warning fa-2x mb-2"></i>
                                        <div class="small text-muted">Email Pending</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6">
                                <i class="fas fa-calendar-check text-info fa-2x mb-2"></i>
                                <div class="small text-muted">Member Since</div>
                                <div class="fw-semibold">{{ $user->created_at->format('M Y') }}</div>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="text-primary h5 mb-0">{{ $userStats['posts'] ?? 0 }}</div>
                                <div class="small text-muted">Posts</div>
                            </div>
                            <div class="col-4">
                                <div class="text-success h5 mb-0">{{ $userStats['medicines'] ?? 0 }}</div>
                                <div class="small text-muted">Medicines</div>
                            </div>
                            <div class="col-4">
                                <div class="text-info h5 mb-0">{{ $userStats['health_metrics'] ?? 0 }}</div>
                                <div class="small text-muted">Records</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Details and Activity -->
            <div class="col-lg-8">
                <div class="row">
                    <!-- Account Details -->
                    <div class="col-12 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="mb-0">
                                    <i class="fas fa-info-circle me-2 text-primary"></i>Account Details
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small">Full Name</label>
                                        <div class="fw-semibold">{{ $user->name }}</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small">Email Address</label>
                                        <div class="fw-semibold">{{ $user->email }}</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small">User ID</label>
                                        <div class="fw-semibold">#{{ $user->id }}</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small">Account Role</label>
                                        <div>
                                            <span class="badge {{ $user->role === 'admin' ? 'bg-danger' : 'bg-primary' }}">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small">Registration Date</label>
                                        <div class="fw-semibold">{{ $user->created_at->format('F d, Y') }}</div>
                                        <div class="small text-muted">{{ $user->created_at->diffForHumans() }}</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small">Last Updated</label>
                                        <div class="fw-semibold">{{ $user->updated_at->format('F d, Y') }}</div>
                                        <div class="small text-muted">{{ $user->updated_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Medical Information -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="mb-0">
                                    <i class="fas fa-heart me-2 text-danger"></i>Health Overview
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Medicines</span>
                                        <span class="badge bg-primary">{{ $userStats['medicines'] ?? 0 }}</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-primary" style="width: {{ min(100, ($userStats['medicines'] ?? 0) * 10) }}%"></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Health Records</span>
                                        <span class="badge bg-info">{{ $userStats['health_metrics'] ?? 0 }}</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-info" style="width: {{ min(100, ($userStats['health_metrics'] ?? 0) * 5) }}%"></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Active Reminders</span>
                                        <span class="badge bg-warning">{{ $userStats['active_reminders'] ?? 0 }}</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-warning" style="width: {{ min(100, ($userStats['active_reminders'] ?? 0) * 20) }}%"></div>
                                    </div>
                                </div>

                                <div class="text-center mt-4">
                                    <a href="#" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-chart-line me-1"></i>View Full Report
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Community Activity -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="mb-0">
                                    <i class="fas fa-comments me-2 text-success"></i>Community Activity
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Posts Created</span>
                                        <span class="badge bg-success">{{ $userStats['posts'] ?? 0 }}</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: {{ min(100, ($userStats['posts'] ?? 0) * 20) }}%"></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Comments</span>
                                        <span class="badge bg-info">{{ $userStats['comments'] ?? 0 }}</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-info" style="width: {{ min(100, ($userStats['comments'] ?? 0) * 10) }}%"></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Likes Received</span>
                                        <span class="badge bg-warning">{{ $userStats['likes'] ?? 0 }}</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-warning" style="width: {{ min(100, ($userStats['likes'] ?? 0) * 5) }}%"></div>
                                    </div>
                                </div>

                                <div class="text-center mt-4">
                                    <a href="#" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-eye me-1"></i>View Posts
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Timeline -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2 text-primary"></i>Recent Activity
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(isset($recentActivities) && $recentActivities->count() > 0)
                            <div class="timeline">
                                @foreach($recentActivities as $activity)
                                    <div class="timeline-item d-flex mb-3">
                                        <div class="timeline-marker">
                                            <div class="bg-{{ $activity['color'] ?? 'primary' }} text-white rounded-circle activity-icon">
                                                <i class="fas {{ $activity['icon'] ?? 'fa-circle' }}"></i>
                                            </div>
                                        </div>
                                        <div class="timeline-content ms-3">
                                            <div class="fw-semibold">{{ $activity['title'] ?? 'Activity' }}</div>
                                            <div class="text-muted small">{{ $activity['description'] ?? 'No description' }}</div>
                                            <div class="text-muted small">{{ $activity['time'] ?? 'Unknown time' }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-clock fa-2x text-muted mb-2"></i>
                                <div class="text-muted">No recent activity</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .avatar-xl {
            width: 80px;
            height: 80px;
            font-size: 28px;
            font-weight: 600;
        }

        .activity-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .timeline-item {
            position: relative;
        }

        .timeline-item:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 15px;
            top: 32px;
            bottom: -12px;
            width: 2px;
            background-color: #dee2e6;
        }

        .progress {
            background-color: #e9ecef;
        }

        .card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1) !important;
        }

        .border-end {
            border-right: 1px solid #dee2e6;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
    </style>

    <!-- JavaScript Functions -->
    <script>
        function editUser(userId) {
            // Implement user editing
            console.log('Edit user:', userId);
        }

        function deleteUser(userId, userName) {
            if (confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`)) {
                // Implement user deletion
                console.log('Delete user:', userId);
            }
        }
    </script>
@endsection