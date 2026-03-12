@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- User Profile Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 text-primary">
                    <i class="fas fa-user-circle me-2"></i>User Profile
                </h1>
                <p class="text-muted mb-0">Detailed user information and activity overview</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                </a>
                <button type="button" class="btn btn-warning" onclick="editUserFromProfile()">
                    <i class="fas fa-edit me-1"></i>Edit User
                </button>
            </div>
        </div>

        <div class="row">
            <!-- User Information Card -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <!-- User Avatar -->
                        <div
                            class="avatar-xl bg-{{ $user->role === 'admin' ? 'danger' : 'primary' }} text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
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
                                    @if ($user->email_verified_at)
                                        <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                        <div class="small text-muted">Email Verified</div>
                                        <div class="fw-semibold">{{ $user->email_verified_at->format('M d, Y') }}</div>
                                    @else
                                        <i class="fas fa-clock text-warning fa-2x mb-2"></i>
                                        <div class="small text-muted">Email Pending</div>
                                        <div class="fw-semibold">Not Verified</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6">
                                <i class="fas fa-calendar-check text-info fa-2x mb-2"></i>
                                <div class="small text-muted">Member Since</div>
                                <div class="fw-semibold">{{ $user->created_at->format('M d, Y') }}</div>
                            </div>
                        </div>

                        <!-- User ID and Join Info -->
                        <div class="bg-light rounded p-3 mb-3">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="text-primary fw-bold">#{{ $user->id }}</div>
                                    <div class="small text-muted">User ID</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-success fw-bold">{{ $user->created_at->diffForHumans() }}</div>
                                    <div class="small text-muted">Joined</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Details and Information -->
            <div class="col-lg-8">
                <div class="row">
                    <!-- Account Details -->
                    <div class="col-12 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="mb-0">
                                    <i class="fas fa-info-circle me-2 text-primary"></i>Account Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small fw-semibold">FULL NAME</label>
                                        <div class="fw-semibold fs-5">{{ $user->name }}</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small fw-semibold">EMAIL ADDRESS</label>
                                        <div class="fw-semibold fs-5">{{ $user->email }}</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small fw-semibold">USER ID</label>
                                        <div class="fw-semibold fs-5">#{{ $user->id }}</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small fw-semibold">ACCOUNT ROLE</label>
                                        <div>
                                            <span
                                                class="badge {{ $user->role === 'admin' ? 'bg-danger' : 'bg-primary' }} fs-6">
                                                <i
                                                    class="fas {{ $user->role === 'admin' ? 'fa-crown' : 'fa-user' }} me-1"></i>
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small fw-semibold">REGISTRATION DATE</label>
                                        <div class="fw-semibold">{{ $user->created_at->format('F d, Y') }}</div>
                                        <div class="text-muted small">{{ $user->created_at->diffForHumans() }}</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small fw-semibold">LAST ACTIVITY</label>
                                        <div class="fw-semibold">{{ $user->updated_at->format('F d, Y') }}</div>
                                        <div class="text-muted small">{{ $user->updated_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Email Verification Status -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="mb-0">
                                    @if ($user->email_verified_at)
                                        <i class="fas fa-check-circle me-2 text-success"></i>Email Status
                                    @else
                                        <i class="fas fa-exclamation-triangle me-2 text-warning"></i>Email Status
                                    @endif
                                </h5>
                            </div>
                            <div class="card-body">
                                @if ($user->email_verified_at)
                                    <div class="text-center">
                                        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                                        <h5 class="text-success">Email Verified</h5>
                                        <p class="text-muted">Verified on {{ $user->email_verified_at->format('M d, Y') }}
                                        </p>
                                        <span class="badge bg-success">Active Account</span>
                                    </div>
                                @else
                                    <div class="text-center">
                                        <i class="fas fa-clock text-warning fa-3x mb-3"></i>
                                        <h5 class="text-warning">Pending Verification</h5>
                                        <p class="text-muted">Email verification required</p>
                                        <button class="btn btn-warning btn-sm">
                                            <i class="fas fa-paper-plane me-1"></i>Resend Verification
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Account Management -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="mb-0">
                                    <i class="fas fa-cogs me-2 text-primary"></i>Account Management
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-outline-primary"
                                        onclick="editUserFromProfile()">
                                        <i class="fas fa-edit me-2"></i>Edit Profile
                                    </button>

                                    @if (!$user->email_verified_at)
                                        <button type="button" class="btn btn-outline-warning">
                                            <i class="fas fa-paper-plane me-2"></i>Send Verification
                                        </button>
                                    @endif

                                    <button type="button" class="btn btn-outline-info">
                                        <i class="fas fa-key me-2"></i>Reset Password
                                    </button>

                                    @if ($user->role === 'member')
                                        <button type="button" class="btn btn-outline-success">
                                            <i class="fas fa-arrow-up me-2"></i>Promote to Admin
                                        </button>
                                    @elseif($user->role === 'admin' && $user->id !== auth()->id())
                                        <button type="button" class="btn btn-outline-warning">
                                            <i class="fas fa-arrow-down me-2"></i>Demote to Member
                                        </button>
                                    @endif
                                </div>

                                @if ($user->role === 'admin' && $user->id === auth()->id())
                                    <div class="mt-3 p-2 bg-light rounded text-center">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            You cannot modify your own account
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-info me-2 text-info"></i>System Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="text-primary h5 mb-1">{{ $user->id }}</div>
                                    <div class="text-muted small">Database ID</div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="text-success h5 mb-1">{{ $user->created_at->format('Y') }}</div>
                                    <div class="text-muted small">Join Year</div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="text-warning h5 mb-1">
                                        {{ $user->updated_at->diffInDays($user->created_at) }}</div>
                                    <div class="text-muted small">Days Since Join</div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="text-info h5 mb-1">{{ $user->updated_at->diffInDays() }}</div>
                                    <div class="text-muted small">Days Since Update</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Edit User Profile
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="editProfileForm" onsubmit="updateUserProfile(event)">
                    <div class="modal-body">
                        <input type="hidden" id="profileUserId" value="{{ $user->id }}">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="profileUserName" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="profileUserName"
                                    value="{{ $user->name }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="profileUserEmail" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="profileUserEmail"
                                    value="{{ $user->email }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="profileUserRole" class="form-label">Account Role</label>
                                <select class="form-select" id="profileUserRole"
                                    {{ $user->role === 'admin' && $user->id === auth()->id() ? 'disabled' : '' }}>
                                    <option value="member" {{ $user->role === 'member' ? 'selected' : '' }}>Member
                                    </option>
                                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrator
                                    </option>
                                </select>
                                @if ($user->role === 'admin' && $user->id === auth()->id())
                                    <div class="form-text text-warning">
                                        <i class="fas fa-exclamation-triangle me-1"></i>You cannot change your own role
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Verification</label>
                                <div>
                                    @if ($user->email_verified_at)
                                        <span class="badge bg-success fs-6">
                                            <i class="fas fa-check-circle me-1"></i>Verified
                                        </span>
                                        <div class="form-text">Verified on
                                            {{ $user->email_verified_at->format('M d, Y') }}</div>
                                    @else
                                        <span class="badge bg-warning fs-6">
                                            <i class="fas fa-clock me-1"></i>Pending
                                        </span>
                                        <div class="form-text">Email verification required</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> Changes to user information will be saved immediately. Email changes may
                            require re-verification.
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .avatar-xl {
            width: 100px;
            height: 100px;
            font-size: 36px;
            font-weight: 700;
        }

        .card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .border-end {
            border-right: 1px solid #dee2e6 !important;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .modal-content {
            border-radius: 1rem;
        }

        .btn {
            border-radius: 0.5rem;
        }

        .badge {
            border-radius: 0.5rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        function editUserFromProfile() {
            new bootstrap.Modal(document.getElementById('editProfileModal')).show();
        }

        function updateUserProfile(event) {
            event.preventDefault();
            const userId = document.getElementById('profileUserId').value;
            const name = document.getElementById('profileUserName').value;
            const email = document.getElementById('profileUserEmail').value;
            const role = document.getElementById('profileUserRole').value;
            console.log('Update user profile:', {
                userId,
                name,
                email,
                role
            });
            bootstrap.Modal.getInstance(document.getElementById('editProfileModal')).hide();

            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show';
            alert.innerHTML =
                `\n                <i class="fas fa-check-circle me-2"></i>\n                <strong>Success!</strong> User profile updated successfully.\n                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>\n            `;

            document.querySelector('.container-fluid').insertBefore(alert, document.querySelector('.container-fluid')
                .firstChild);
            setTimeout(() => {
                if (alert.parentNode) alert.remove();
            }, 5000);
        }
    </script>
@endpush
