@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                <div>
                    <h1 class="h3 mb-2 text-gray-800">
                        <i class="fas fa-database text-primary me-2"></i>Database Backups
                    </h1>
                    <p class="text-muted mb-3 mb-md-0">Manage your database backups</p>
                </div>
                <div>
                    <form method="POST" action="{{ route('admin.backups.store') }}" class="d-inline-block">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm" id="manualBackupBtn">
                            <i class="fas fa-plus-circle me-2"></i>Create Manual Backup
                        </button>
                    </form>
                </div>
            </div>

            <!-- Alert Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="card bg-gradient-primary text-white shadow h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-1">Total Backups</h6>
                                    <h2 class="text-white mb-0">{{ number_format($stats['total_backups'] ?? 0) }}</h2>
                                </div>
                                <div class="rounded-circle bg-white bg-opacity-25 p-3">
                                    <i class="fas fa-database fa-2x text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-sm-6 col-lg-3">
                    <div class="card bg-gradient-success text-white shadow h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-1">Total Size</h6>
                                    <h2 class="text-white mb-0">{{ number_format($stats['total_size_mb'] ?? 0, 2) }} MB</h2>
                                </div>
                                <div class="rounded-circle bg-white bg-opacity-25 p-3">
                                    <i class="fas fa-hdd fa-2x text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-sm-6 col-lg-3">
                    <div class="card bg-gradient-warning text-white shadow h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-1">Average Size</h6>
                                    <h2 class="text-white mb-0">{{ number_format($stats['average_size_mb'] ?? 0, 2) }} MB</h2>
                                </div>
                                <div class="rounded-circle bg-white bg-opacity-25 p-3">
                                    <i class="fas fa-chart-line fa-2x text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-sm-6 col-lg-3">
                    <div class="card bg-gradient-danger text-white shadow h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-1">Schedule</h6>
                                    <h2 class="text-white mb-0">Every 12h</h2>
                                    <small class="text-white-50">Auto cleanup: 30 days</small>
                                </div>
                                <div class="rounded-circle bg-white bg-opacity-25 p-3">
                                    <i class="fas fa-clock fa-2x text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Backups Table -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center">
                        <h5 class="mb-2 mb-sm-0">
                            <i class="fas fa-archive text-primary me-2"></i>Backup Files
                        </h5>
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <span class="input-group-text bg-white">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" id="searchBackup" placeholder="Search backups...">
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="60" class="align-middle">#</th>
                                    <th class="align-middle">Filename</th>
                                    <th width="100" class="align-middle">Size</th>
                                    <th width="180" class="align-middle">Created At</th>
                                    <th width="200" class="align-middle text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($backups ?? [] as $index => $backup)
                                <tr class="backup-row" data-filename="{{ strtolower($backup['name']) }}">
                                    <td class="align-middle text-center">{{ $loop->iteration }}</td>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file-archive fa-lg text-secondary me-2"></i>
                                            <code class="small">{{ $backup['name'] }}</code>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge bg-info">
                                            <i class="fas fa-database me-1"></i>{{ $backup['size_mb'] }} MB
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex flex-column">
                                            <small class="fw-bold">{{ \Carbon\Carbon::parse($backup['created_at'])->format('M d, Y') }}</small>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($backup['created_at'])->format('h:i:s A') }}</small>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.backups.download', ['file' => $backup['name']]) }}" 
                                               class="btn btn-success btn-action" 
                                               title="Download Backup">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <form method="POST" 
                                                  action="{{ route('admin.backups.destroy', ['file' => $backup['name']]) }}" 
                                                  style="display: inline-block;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this backup?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-action" title="Delete Backup">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                        <h5 class="text-muted">No backups found</h5>
                                        <p class="text-muted small">Click "Create Manual Backup" to create your first database backup.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card-footer bg-white">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center">
                        <div class="small text-muted mb-2 mb-sm-0">
                            <i class="fas fa-info-circle me-1"></i>
                            Showing {{ count($backups ?? []) }} backup(s)
                        </div>
                        <div class="text-muted small">
                            <i class="fas fa-clock me-1"></i>
                            Last updated: {{ now()->format('Y-m-d H:i:s') }}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Info Alert -->
            <div class="alert alert-info shadow-sm mt-4 border-start border-info border-4">
                <div class="d-flex">
                    <i class="fas fa-info-circle fa-2x text-info me-3"></i>
                    <div>
                        <h6 class="alert-heading mb-2">About Automatic Backups</h6>
                        <p class="mb-0 small">
                            <i class="fas fa-check-circle text-success me-1"></i> <strong>Schedule:</strong> Backups are created every 12 hours (12:00 AM & 12:00 PM)<br>
                            <i class="fas fa-trash-alt text-warning me-1"></i> <strong>Retention:</strong> Old backups are deleted after 30 days<br>
                            <i class="fas fa-database text-primary me-1"></i> <strong>Storage:</strong>Automatic Scheduled Backups stored in <code>storage/app/backups/</code>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Gradient Backgrounds */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .bg-gradient-success {
        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    }
    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
    }
    .bg-gradient-danger {
        background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
    }
    
    /* Card Hover Effects */
    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: none;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    /* Table Row Hover */
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s ease;
    }
    
    /* Action Buttons */
    .btn-action {
        transition: all 0.2s ease;
        border-radius: 4px;
    }
    
    .btn-action:hover {
        transform: translateY(-1px);
    }
    
    /* Badge Styling */
    .badge {
        padding: 6px 10px;
        font-weight: 500;
    }
    
    /* Search Input */
    .input-group-text {
        border-right: none;
    }
    
    #searchBackup:focus {
        box-shadow: none;
        border-color: #ced4da;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .container-fluid {
            padding: 1rem !important;
        }
        
        .table-responsive {
            font-size: 0.85rem;
        }
        
        .btn-group .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        
        .stat-card h2 {
            font-size: 1.5rem;
        }
        
        .stat-card .rounded-circle {
            padding: 0.5rem !important;
        }
        
        .stat-card .rounded-circle i {
            font-size: 1.25rem !important;
        }
        
        .btn-lg {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
        
        h1.h3 {
            font-size: 1.5rem;
        }
    }
    
    @media (max-width: 576px) {
        .stat-card .rounded-circle {
            display: none;
        }
        
        .table td, .table th {
            padding: 0.5rem;
        }
        
        code.small {
            font-size: 0.7rem;
        }
    }
    
    /* Loading Animation for Backup Button */
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
    
    .btn-loading {
        animation: pulse 1.5s ease-in-out infinite;
        pointer-events: none;
    }
    
    /* Custom Scrollbar for Table */
    .table-responsive::-webkit-scrollbar {
        height: 6px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* Alert Styling */
    .alert {
        border-radius: 8px;
    }
    
    .btn-close:focus {
        box-shadow: none;
    }
</style>

<script>
    // Search functionality
    const searchInput = document.getElementById('searchBackup');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('.backup-row');
            
            rows.forEach(row => {
                const filename = row.getAttribute('data-filename');
                if (filename && filename.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // Loading state for manual backup button
    const manualBackupBtn = document.getElementById('manualBackupBtn');
    if (manualBackupBtn) {
        manualBackupBtn.addEventListener('click', function(e) {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating...';
            this.classList.add('btn-loading');
            
            // Reset button after 30 seconds (in case form doesn't redirect)
            setTimeout(() => {
                if (this.classList.contains('btn-loading')) {
                    this.innerHTML = originalText;
                    this.classList.remove('btn-loading');
                }
            }, 30000);
        });
    }
</script>
@endsection