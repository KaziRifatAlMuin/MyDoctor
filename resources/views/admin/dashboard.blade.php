@extends('layouts.app')

@section('title', 'Admin Dashboard - System Management')

@push('styles')
<style>
    .admin-wrapper {
        background: linear-gradient(135deg, #f5f7fa 0%, #f0f2f5 100%);
        min-height: 100vh;
        padding: 20px 0;
    }

    .admin-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px 0;
        margin-bottom: 40px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .admin-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .admin-subtitle {
        font-size: 1rem;
        opacity: 0.9;
        font-weight: 300;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border-left: 4px solid #667eea;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .stat-card.blue { border-left-color: #667eea; }
    .stat-card.red { border-left-color: #e53e3e; }
    .stat-card.green { border-left-color: #38a169; }
    .stat-card.orange { border-left-color: #dd6b20; }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 15px;
    }

    .stat-card.blue .stat-icon { background: rgba(102, 126, 234, 0.12); color: #667eea; }
    .stat-card.red .stat-icon { background: rgba(229, 62, 62, 0.12); color: #e53e3e; }
    .stat-card.green .stat-icon { background: rgba(56, 161, 105, 0.12); color: #38a169; }
    .stat-card.orange .stat-icon { background: rgba(237, 137, 54, 0.12); color: #dd6b20; }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #a0aec0;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Main Content */
    .admin-tabs {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .admin-tabs .nav-tabs {
        border-bottom: 2px solid #e2e8f0;
        padding: 0;
        background: #f7fafc;
    }

    .admin-tabs .nav-link {
        color: #718096;
        border: none;
        padding: 15px 25px;
        font-weight: 500;
        border-bottom: 2px solid transparent;
        transition: all 0.3s ease;
    }

    .admin-tabs .nav-link:hover {
        color: #667eea;
    }

    .admin-tabs .nav-link.active {
        color: #667eea;
        border-bottom-color: #667eea;
        background: white;
    }

    .tab-content {
        padding: 30px;
    }

    /* Users Table */
    .users-table {
        width: 100%;
        border-collapse: collapse;
    }

    .users-table thead {
        background: #f7fafc;
        border-bottom: 2px solid #e2e8f0;
    }

    .users-table th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #2d3748;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .users-table td {
        padding: 15px;
        border-bottom: 1px solid #e2e8f0;
        color: #4a5568;
    }

    .users-table tbody tr:hover {
        background: #f7fafc;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e2e8f0;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-name {
        font-weight: 500;
        color: #2d3748;
    }

    .user-email {
        font-size: 0.85rem;
        color: #a0aec0;
    }

    .role-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .role-badge.admin {
        background: rgba(245, 101, 101, 0.12);
        color: #e53e3e;
    }

    .role-badge.member {
        background: rgba(102, 126, 234, 0.12);
        color: #667eea;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .action-btn {
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .action-btn.edit {
        background: rgba(102, 126, 234, 0.12);
        color: #667eea;
    }

    .action-btn.edit:hover {
        background: rgba(102, 126, 234, 0.25);
    }

    .action-btn.view {
        background: rgba(72, 187, 120, 0.12);
        color: #38a169;
    }

    .action-btn.view:hover {
        background: rgba(72, 187, 120, 0.25);
    }

    /* Filters */
    .filters-bar {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        align-items: center;
    }

    .filter-group {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }

    .filter-input {
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 0.9rem;
        width: auto;
    }

    .filter-select {
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 0.9rem;
        background: white;
        cursor: pointer;
    }

    .filter-btn {
        padding: 8px 16px;
        background: #667eea;
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .filter-btn:hover {
        background: #5568d3;
    }

    /* Medical Data Management */
    .medical-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .medical-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .medical-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e2e8f0;
    }

    .medical-title {
        font-weight: 600;
        color: #2d3748;
        font-size: 1.1rem;
    }

    .medical-actions {
        display: flex;
        gap: 8px;
    }

    .medical-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-bottom: 15px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
    }

    .info-label {
        font-size: 0.75rem;
        color: #a0aec0;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 5px;
    }

    .info-value {
        font-weight: 500;
        color: #2d3748;
    }

    /* Modal Enhancements */
    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
    }

    .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }

    .form-label {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .form-control, .form-select {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 8px 12px;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    /* Pagination */
    .pagination-wrapper {
        margin-top: 20px;
        display: flex;
        justify-content: center;
    }

    .page-link {
        color: #667eea;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
    }

    .page-link:hover {
        color: white;
        background: #667eea;
        border-color: #667eea;
    }

    .page-item.active .page-link {
        background: #667eea;
        border-color: #667eea;
    }

    /* Animation */
    .fade-in {
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .search-highlight {
        background: rgba(102, 126, 234, 0.2);
        border-radius: 4px;
        padding: 2px 4px;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state-icon {
        font-size: 3rem;
        color: #cbd5e0;
        margin-bottom: 15px;
    }

    .empty-state-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 8px;
    }

    .empty-state-text {
        color: #a0aec0;
        font-size: 0.95rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .filters-bar {
            flex-direction: column;
        }

        .filter-input, .filter-select, .filter-btn {
            width: 100%;
        }

        .users-table {
            font-size: 0.85rem;
        }

        .users-table th, .users-table td {
            padding: 10px;
        }

        .admin-title {
            font-size: 1.8rem;
        }
    }

    @media (max-width: 576px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .admin-tabs .nav-link {
            padding: 12px 15px;
            font-size: 0.85rem;
        }

        .medical-info {
            grid-template-columns: 1fr;
        }

        .action-buttons {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')

<div class="admin-wrapper">
    <!-- Admin Header -->
    <div class="admin-header">
        <div class="container">
            <h1 class="admin-title">
                <i class="fas fa-shield-alt me-2"></i>Admin Dashboard
            </h1>
            <p class="admin-subtitle">Manage users, medical information, and system settings</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Stats Grid -->
        <div class="stats-grid fade-in">
            <div class="stat-card blue">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-value">{{ $stats['total_users'] ?? 0 }}</div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card orange">
                <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
                <div class="stat-value">{{ $stats['admin_count'] ?? 0 }}</div>
                <div class="stat-label">Administrators</div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon"><i class="fas fa-user-check"></i></div>
                <div class="stat-value">{{ $stats['member_count'] ?? 0 }}</div>
                <div class="stat-label">Members</div>
            </div>
            <div class="stat-card red">
                <div class="stat-icon"><i class="fas fa-fire"></i></div>
                <div class="stat-value">{{ $stats['recent_users'] ?? 0 }}</div>
                <div class="stat-label">New This Week</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-icon"><i class="fas fa-pills"></i></div>
                <div class="stat-value">{{ $stats['total_medicines'] ?? 0 }}</div>
                <div class="stat-label">Total Medicines</div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon"><i class="fas fa-heartbeat"></i></div>
                <div class="stat-value">{{ $stats['total_health_metrics'] ?? 0 }}</div>
                <div class="stat-label">Health Metrics</div>
            </div>
        </div>

        <!-- Tabs Section -->
        <div class="admin-tabs fade-in" style="animation-delay: 0.1s;">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users-content" 
                            type="button" role="tab">
                        <i class="fas fa-users me-2"></i>Users Management
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="medical-tab" data-bs-toggle="tab" data-bs-target="#medical-content"
                            type="button" role="tab">
                        <i class="fas fa-hospital-user me-2"></i>Medical Information
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity-content"
                            type="button" role="tab">
                        <i class="fas fa-history me-2"></i>Activity Log
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Users Management Tab -->
                <div class="tab-pane fade show active" id="users-content" role="tabpanel">
                    <div class="filters-bar">
                        <div class="filter-group">
                            <input type="text" class="filter-input" id="userSearch" placeholder="Search by name or email..." style="min-width: 250px;">
                            <select class="filter-select" id="roleFilter">
                                <option value="">All Roles</option>
                                <option value="admin">Administrators</option>
                                <option value="member">Members</option>
                            </select>
                            <button class="filter-btn" onclick="filterUsers()">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                        </div>
                    </div>

                    @if($users && count($users) > 0)
                        <div class="table-responsive">
                            <table class="users-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Registration Date</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr class="user-row" data-role="{{ $user->role }}">
                                            <td>
                                                <div class="user-info">
                                                    @if($user->picture)
                                                        <img src="{{ asset('storage/' . $user->picture) }}" alt="{{ $user->name }}" class="user-avatar">
                                                    @else
                                                        <div class="user-avatar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="user-name">{{ $user->name }}</div>
                                                        @if($user->phone)
                                                            <div class="user-email">{{ $user->phone }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                <span class="role-badge {{ strtolower($user->role) }}">
                                                    {{ ucfirst($user->role) }}
                                                </span>
                                            </td>
                                            <td>{{ $user->created_at ? $user->created_at->format('d M, Y') : 'Unknown' }}</td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="action-btn edit" onclick="editUser({{ $user->id }})" title="Edit User">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="action-btn view" onclick="viewUserMedical({{ $user->id }})" title="View Medical Info">
                                                        <i class="fas fa-heartbeat"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($users instanceof \Illuminate\Pagination\Paginator)
                            <div class="pagination-wrapper">
                                {{ $users->links() }}
                            </div>
                        @endif
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="fas fa-users"></i></div>
                            <div class="empty-state-title">No Users Found</div>
                            <div class="empty-state-text">No users match your search criteria</div>
                        </div>
                    @endif
                </div>

                <!-- Medical Information Tab -->
                <div class="tab-pane fade" id="medical-content" role="tabpanel">
                    <div class="filters-bar">
                        <div class="filter-group">
                            <input type="text" class="filter-input" id="medicalSearch" placeholder="Search by user name..." style="min-width: 250px;">
                            <select class="filter-select" id="medicalTypeFilter">
                                <option value="">All Types</option>
                                <option value="disease">Diseases</option>
                                <option value="medicine">Medicines</option>
                                <option value="metric">Health Metrics</option>
                            </select>
                            <button class="filter-btn" onclick="filterMedical()">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                        </div>
                    </div>

                    <!-- Diseases Section -->
                    <div style="margin-bottom: 30px;">
                        <h5 style="margin-bottom: 20px; color: #2d3748; font-weight: 600;">
                            <i class="fas fa-virus me-2" style="color: #e53e3e;"></i>User Diseases
                        </h5>
                        
                        @php
                            $allDiseases = \App\Models\User::whereNotNull('id')
                                ->with('userDiseases.disease')
                                ->get()
                                ->flatMap(fn($user) => $user->userDiseases->map(fn($ud) => [
                                    'user' => $user,
                                    'disease' => $ud->disease,
                                    'diagnosed_at' => $ud->diagnosed_at,
                                    'user_disease_id' => $ud->id
                                ]))
                                ->take(10);
                        @endphp

                        @if(count($allDiseases) > 0)
                            @foreach($allDiseases as $record)
                                <div class="medical-card medical-disease">
                                    <div class="medical-header">
                                        <div>
                                            <div class="medical-title">{{ $record['disease']->name ?? 'Unknown Disease' }}</div>
                                            <small style="color: #a0aec0;">User: <strong>{{ $record['user']->name }}</strong></small>
                                        </div>
                                        <div class="medical-actions">
                                            <button class="action-btn edit" onclick="editDisease({{ $record['user_disease_id'] }}, {{ $record['user']->id }})">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </button>
                                            <button class="action-btn" style="background: rgba(229, 62, 62, 0.12); color: #e53e3e;" onclick="deleteDisease({{ $record['user_disease_id'] }}, {{ $record['user']->id }})">
                                                <i class="fas fa-trash me-1"></i>Delete
                                            </button>
                                        </div>
                                    </div>
                                    <div class="medical-info">
                                        <div class="info-item">
                                            <label class="info-label">Diagnosed Date</label>
                                            <span class="info-value">{{ $record['diagnosed_at'] ? \Carbon\Carbon::parse($record['diagnosed_at'])->format('d M, Y') : 'Not specified' }}</span>
                                        </div>
                                        <div class="info-item">
                                            <label class="info-label">Status</label>
                                            <span class="info-value" style="color: #38a169;"><i class="fas fa-check-circle me-1"></i>Active</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="empty-state" style="padding: 40px;">
                                <div class="empty-state-icon"><i class="fas fa-virus"></i></div>
                                <div class="empty-state-title">No Disease Records</div>
                            </div>
                        @endif
                    </div>

                    <!-- Medicines Section -->
                    <div style="margin-bottom: 30px;">
                        <h5 style="margin-bottom: 20px; color: #2d3748; font-weight: 600;">
                            <i class="fas fa-pills me-2" style="color: #667eea;"></i>User Medicines
                        </h5>

                        @php
                            $allMedicines = \App\Models\Medicine::with('user')
                                ->latest()
                                ->take(10)
                                ->get();
                        @endphp

                        @if(count($allMedicines) > 0)
                            @foreach($allMedicines as $medicine)
                                <div class="medical-card medical-medicine">
                                    <div class="medical-header">
                                        <div>
                                            <div class="medical-title">{{ $medicine->name }}</div>
                                            <small style="color: #a0aec0;">User: <strong>{{ $medicine->user->name }}</strong></small>
                                        </div>
                                        <div class="medical-actions">
                                            <button class="action-btn edit" onclick="editMedicine({{ $medicine->id }}, {{ $medicine->user_id }})">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </button>
                                            <button class="action-btn" style="background: rgba(229, 62, 62, 0.12); color: #e53e3e;" onclick="deleteMedicine({{ $medicine->id }}, {{ $medicine->user_id }})">
                                                <i class="fas fa-trash me-1"></i>Delete
                                            </button>
                                        </div>
                                    </div>
                                    <div class="medical-info">
                                        <div class="info-item">
                                            <label class="info-label">Dosage</label>
                                            <span class="info-value">{{ $medicine->dosage ?? 'Not specified' }}</span>
                                        </div>
                                        <div class="info-item">
                                            <label class="info-label">Frequency</label>
                                            <span class="info-value">{{ $medicine->frequency ?? 'Not specified' }}</span>
                                        </div>
                                        <div class="info-item">
                                            <label class="info-label">Start Date</label>
                                            <span class="info-value">{{ $medicine->start_date ? \Carbon\Carbon::parse($medicine->start_date)->format('d M, Y') : 'Not specified' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="empty-state" style="padding: 40px;">
                                <div class="empty-state-icon"><i class="fas fa-pills"></i></div>
                                <div class="empty-state-title">No Medicine Records</div>
                            </div>
                        @endif
                    </div>

                    <!-- Health Metrics Section -->
                    <div>
                        <h5 style="margin-bottom: 20px; color: #2d3748; font-weight: 600;">
                            <i class="fas fa-chart-line me-2" style="color: #38a169;"></i>Health Metrics
                        </h5>

                        @php
                            $allMetrics = \App\Models\HealthMetric::with('user')
                                ->latest()
                                ->take(10)
                                ->get();
                        @endphp

                        @if(count($allMetrics) > 0)
                            @foreach($allMetrics as $metric)
                                <div class="medical-card medical-metric">
                                    <div class="medical-header">
                                        <div>
                                            <div class="medical-title">{{ $metric->metric_type }}</div>
                                            <small style="color: #a0aec0;">User: <strong>{{ $metric->user->name ?? 'Unknown' }}</strong> | Date: {{ $metric->recorded_at ? $metric->recorded_at->format('d M, Y') : 'Not specified' }}</small>
                                        </div>
                                        <div class="medical-actions">
                                            <button class="action-btn edit" onclick="editMetric({{ $metric->id }}, {{ $metric->user_id }})">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </button>
                                            <button class="action-btn" style="background: rgba(229, 62, 62, 0.12); color: #e53e3e;" onclick="deleteMetric({{ $metric->id }}, {{ $metric->user_id }})">
                                                <i class="fas fa-trash me-1"></i>Delete
                                            </button>
                                        </div>
                                    </div>
                                    <div class="medical-info">
                                        <div class="info-item">
                                            <label class="info-label">Value</label>
                                            @php
                                                $metricValue = $metric->value;
                                                if (is_array($metricValue)) {
                                                    $metricValue = collect($metricValue)->map(function($v) {
                                                        return is_array($v) ? json_encode($v) : (string) $v;
                                                    })->join(', ');
                                                } elseif (is_null($metricValue) || $metricValue === '') {
                                                    $metricValue = 'N/A';
                                                }
                                            @endphp
                                            <span class="info-value">{{ $metricValue }}</span>
                                        </div>
                                        <div class="info-item">
                                            <label class="info-label">Unit</label>
                                            <span class="info-value">{{ $metric->unit ?? 'N/A' }}</span>
                                        </div>
                                        <div class="info-item">
                                            <label class="info-label">Notes</label>
                                            <span class="info-value">{{ $metric->notes ?? 'None' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="empty-state" style="padding: 40px;">
                                <div class="empty-state-icon"><i class="fas fa-chart-line"></i></div>
                                <div class="empty-state-title">No Health Metrics</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Activity Log Tab -->
                <div class="tab-pane fade" id="activity-content" role="tabpanel">
                    <div style="max-width: 800px;">
                        @if($recent_activities && count($recent_activities) > 0)
                            <div class="timeline" style="margin: 0; padding: 0;">
                                @foreach($recent_activities as $activity)
                                    <div style="display: flex; gap: 20px; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0;">
                                        <div style="flex-shrink: 0;">
                                            <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(102, 126, 234, 0.12); display: flex; align-items: center; justify-content: center; color: #667eea;">
                                                <i class="fas {{ $activity['icon'] }}"></i>
                                            </div>
                                        </div>
                                        <div style="flex: 1;">
                                            <div style="font-weight: 600; color: #2d3748; margin-bottom: 5px;">
                                                {{ $activity['message'] }}
                                            </div>
                                            <div style="color: #a0aec0; font-size: 0.85rem;">
                                                <i class="fas fa-clock me-1"></i>{{ $activity['time'] }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-history"></i></div>
                                <div class="empty-state-title">No Recent Activities</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUserForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="editUserName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="editUserEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" id="editUserPhone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Occupation</label>
                        <input type="text" class="form-control" id="editUserOccupation" name="occupation">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Blood Group</label>
                        <select class="form-select" id="editUserBloodGroup" name="blood_group">
                            <option value="">Select Blood Group</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="editUserDOB" name="date_of_birth">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View User Medical Info Modal -->
<div class="modal fade" id="userMedicalModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Medical Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="userMedicalContent">
                <div class="text-center py-5">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #667eea;"></i>
                    <p class="mt-3 text-muted">Loading medical information...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let currentUserId = null;

    function editUser(userId) {
        currentUserId = userId;
        
        fetch(`/api/users/${userId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('editUserName').value = data.name;
                document.getElementById('editUserEmail').value = data.email;
                document.getElementById('editUserPhone').value = data.phone || '';
                document.getElementById('editUserOccupation').value = data.occupation || '';
                document.getElementById('editUserBloodGroup').value = data.blood_group || '';
                document.getElementById('editUserDOB').value = data.date_of_birth || '';
                
                document.getElementById('editUserForm').action = `/profile/update`;
                new bootstrap.Modal(document.getElementById('editUserModal')).show();
            })
            .catch(error => {
                alert('Error loading user data: ' + error);
            });
    }

    function viewUserMedical(userId) {
        fetch(`/api/users/${userId}/medical`)
            .then(response => response.json())
            .then(data => {
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-pills me-2"></i>Medicines
                            </h6>
                            ${data.medicines.length > 0 ? 
                                data.medicines.map(m => `
                                    <div class="mb-3 p-3" style="background: #f7fafc; border-radius: 8px;">
                                        <strong>${m.name}</strong><br>
                                        <small class="text-muted">Dosage: ${m.dosage || 'N/A'}</small><br>
                                        <small class="text-muted">Frequency: ${m.frequency || 'N/A'}</small>
                                    </div>
                                `).join('')
                                : '<p class="text-muted">No medicines recorded</p>'
                            }
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-danger mb-3">
                                <i class="fas fa-virus me-2"></i>Diseases
                            </h6>
                            ${data.diseases.length > 0 ? 
                                data.diseases.map(d => `
                                    <div class="mb-3 p-3" style="background: #f7fafc; border-radius: 8px;">
                                        <strong>${d.name}</strong><br>
                                        <small class="text-muted">Diagnosed: ${d.diagnosed_at || 'N/A'}</small>
                                    </div>
                                `).join('')
                                : '<p class="text-muted">No diseases recorded</p>'
                            }
                        </div>
                    </div>
                `;
                document.getElementById('userMedicalContent').innerHTML = html;
            })
            .catch(error => {
                document.getElementById('userMedicalContent').innerHTML = `<p class="text-danger">Error loading medical information</p>`;
            });

        new bootstrap.Modal(document.getElementById('userMedicalModal')).show();
    }

    function editDisease(diseaseId, userId) {
        alert('Edit disease feature - Implementation pending');
    }

    function deleteDisease(diseaseId, userId) {
        if (confirm('Are you sure you want to delete this disease record?')) {
            fetch(`/health/disease/${diseaseId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => {
                if (response.ok) {
                    alert('Disease deleted successfully');
                    location.reload();
                } else {
                    alert('Error deleting disease');
                }
            });
        }
    }

    function editMedicine(medicineId, userId) {
        alert('Edit medicine feature - Implementation pending');
    }

    function deleteMedicine(medicineId, userId) {
        if (confirm('Are you sure you want to delete this medicine record?')) {
            // Implement delete
            alert('Delete medicine feature - Implementation pending');
        }
    }

    function editMetric(metricId, userId) {
        alert('Edit metric feature - Implementation pending');
    }

    function deleteMetric(metricId, userId) {
        if (confirm('Are you sure you want to delete this metric?')) {
            fetch(`/health/metric/${metricId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => {
                if (response.ok) {
                    alert('Metric deleted successfully');
                    location.reload();
                } else {
                    alert('Error deleting metric');
                }
            });
        }
    }

    function filterUsers() {
        const search = document.getElementById('userSearch').value.toLowerCase();
        const roleFilter = document.getElementById('roleFilter').value;
        const rows = document.querySelectorAll('.user-row');

        rows.forEach(row => {
            const name = row.textContent.toLowerCase();
            const role = row.getAttribute('data-role');
            
            const matchesSearch = name.includes(search);
            const matchesRole = !roleFilter || role === roleFilter;
            
            row.style.display = (matchesSearch && matchesRole) ? '' : 'none';
        });
    }

    function filterMedical() {
        const search = document.getElementById('medicalSearch').value.toLowerCase();
        const typeFilter = document.getElementById('medicalTypeFilter').value;
        
        // Filter logic for medical data
        console.log('Filtering medical data:', search, typeFilter);
    }

    // Search functionality
    document.getElementById('userSearch')?.addEventListener('keyup', filterUsers);
</script>
@endpush
