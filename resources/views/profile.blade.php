@extends('layouts.app')

@section('title', 'My Profile')

@push('styles')
    <style>
        .profile-avatar-ring {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.35);
            object-fit: cover;
        }

        .profile-avatar-initials {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: 4px solid white;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            font-weight: 700;
            color: white;
        }

        .camera-btn {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: 2px solid white;
            color: white;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .camera-btn:hover {
            transform: scale(1.15);
        }

        .profile-header-card {
            background: white;
            border-radius: 16px;
            border: none;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
        }

        .info-card {
            background: white;
            border-radius: 16px;
            border: none;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            height: 100%;
        }

        .info-card .card-header-custom {
            padding: 1.25rem 1.5rem 0.5rem;
            border-bottom: 1px solid #f0f0f0;
            margin-bottom: 0.5rem;
        }

        .info-card .card-header-custom h5 {
            font-weight: 700;
            color: #667eea;
            margin: 0;
            font-size: 1.05rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 0.85rem 1.5rem;
            border-bottom: 1px solid #f8f9fa;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .info-icon.purple {
            background: rgba(102, 126, 234, 0.12);
            color: #667eea;
        }

        .info-icon.red {
            background: rgba(245, 101, 101, 0.12);
            color: #e53e3e;
        }

        .info-icon.green {
            background: rgba(72, 187, 120, 0.12);
            color: #38a169;
        }

        .info-icon.gray {
            background: rgba(113, 128, 150, 0.12);
            color: #718096;
        }

        .info-icon.orange {
            background: rgba(237, 137, 54, 0.12);
            color: #dd6b20;
        }

        .info-label {
            font-size: 0.75rem;
            color: #a0aec0;
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .info-value {
            font-weight: 600;
            color: #2d3748;
            font-size: 0.95rem;
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.9rem 1.25rem;
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            background: white;
            color: #4a5568;
            text-decoration: none;
            transition: all 0.25s ease;
            cursor: pointer;
            width: 100%;
            font-size: 0.95rem;
        }

        .action-btn:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
            color: #667eea;
            transform: translateX(3px);
        }

        .action-btn.danger {
            border-color: #fed7d7;
            color: #e53e3e;
        }

        .action-btn.danger:hover {
            background: rgba(229, 62, 62, 0.05);
            border-color: #e53e3e;
            color: #e53e3e;
        }

        .occupation-badge {
            background: rgba(102, 126, 234, 0.12);
            color: #667eea;
            border-radius: 20px;
            padding: 0.3rem 0.85rem;
            font-size: 0.82rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .blood-badge {
            background: rgba(229, 62, 62, 0.12);
            color: #e53e3e;
            border-radius: 20px;
            padding: 0.3rem 0.85rem;
            font-size: 0.82rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .verified-badge {
            background: rgba(72, 187, 120, 0.12);
            color: #38a169;
            border-radius: 20px;
            padding: 0.25rem 0.75rem;
            font-size: 0.78rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .unverified-badge {
            background: rgba(237, 137, 54, 0.12);
            color: #dd6b20;
            border-radius: 20px;
            padding: 0.25rem 0.75rem;
            font-size: 0.78rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .gradient-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            transition: opacity 0.2s, transform 0.2s;
        }

        .gradient-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            color: white;
        }

        .section-bg {
            background: #f8f9fa;
            min-height: calc(100vh - 400px);
            padding: 2.5rem 0 3rem;
        }
    </style>
@endpush

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show m-3 rounded-3 border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show m-3 rounded-3 border-0 shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="section-bg">
        <div class="container" style="max-width: 960px;">

            {{-- ── Profile Header ── --}}
            <div class="profile-header-card p-4 mb-4">
                <div class="row align-items-center g-3">

                    {{-- Avatar --}}
                    <div class="col-auto">
                        <div class="position-relative" style="width:110px;">
                            @if (auth()->user()->Picture)
                                <img src="{{ asset('storage/' . auth()->user()->Picture) }}" alt="Profile Picture"
                                    class="profile-avatar-ring">
                            @else
                                <div class="profile-avatar-initials">
                                    {{ strtoupper(substr(auth()->user()->Name, 0, 1)) }}
                                </div>
                            @endif
                            <button class="camera-btn" data-bs-toggle="modal" data-bs-target="#changePictureModal"
                                title="Change picture">
                                <i class="fas fa-camera"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Name & details --}}
                    <div class="col">
                        <h4 class="fw-bold mb-1" style="color:#2d3748;">
                            {{ auth()->user()->Name }}
                        </h4>
                        <p class="text-muted mb-2" style="font-size:0.9rem;">
                            <i class="fas fa-envelope me-1" style="color:#667eea;"></i>
                            {{ auth()->user()->Email }}
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            @if (auth()->user()->Occupation)
                                <span class="occupation-badge">
                                    <i class="fas fa-briefcase"></i>
                                    {{ auth()->user()->Occupation }}
                                </span>
                            @endif
                            @if (auth()->user()->BloodGroup)
                                <span class="blood-badge">
                                    <i class="fas fa-tint"></i>
                                    {{ auth()->user()->BloodGroup }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Edit button --}}
                    <div class="col-auto">
                        <button class="gradient-btn btn" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="fas fa-user-edit me-2"></i>Edit Profile
                        </button>
                    </div>

                </div>
            </div>

            <div class="row g-4">

                {{-- ── Personal Information ── --}}
                <div class="col-md-6">
                    <div class="info-card">
                        <div class="card-header-custom">
                            <h5><i class="fas fa-id-card me-2"></i>Personal Information</h5>
                        </div>

                        <div class="info-item">
                            <div class="info-icon purple"><i class="fas fa-calendar-alt"></i></div>
                            <div>
                                <div class="info-label">Date of Birth</div>
                                <div class="info-value">
                                    {{ auth()->user()->DateOfBirth ? auth()->user()->DateOfBirth->format('d M, Y') : 'Not provided' }}
                                </div>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon purple"><i class="fas fa-phone-alt"></i></div>
                            <div>
                                <div class="info-label">Phone Number</div>
                                <div class="info-value">
                                    {{ auth()->user()->Phone ?? 'Not provided' }}
                                </div>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon orange"><i class="fas fa-briefcase"></i></div>
                            <div>
                                <div class="info-label">Occupation</div>
                                <div class="info-value">
                                    {{ auth()->user()->Occupation ?? 'Not provided' }}
                                </div>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon red"><i class="fas fa-tint"></i></div>
                            <div>
                                <div class="info-label">Blood Group</div>
                                <div class="info-value">
                                    {{ auth()->user()->BloodGroup ?? 'Not provided' }}
                                </div>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon green"><i class="fas fa-shield-alt"></i></div>
                            <div>
                                <div class="info-label">Email Verification</div>
                                <div class="info-value mt-1">
                                    @if (auth()->user()->email_verified_at)
                                        <span class="verified-badge">
                                            <i class="fas fa-check-circle"></i> Verified
                                        </span>
                                    @else
                                        <span class="unverified-badge">
                                            <i class="fas fa-exclamation-circle"></i> Not Verified
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon gray"><i class="fas fa-clock"></i></div>
                            <div>
                                <div class="info-label">Member Since</div>
                                <div class="info-value">
                                    {{ auth()->user()->CreatedAt ? auth()->user()->CreatedAt->format('d M, Y') : 'N/A' }}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ── Account Security ── --}}
                <div class="col-md-6">
                    <div class="info-card">
                        <div class="card-header-custom">
                            <h5><i class="fas fa-lock me-2"></i>Account &amp; Security</h5>
                        </div>

                        <div class="p-3 d-flex flex-column gap-2">

                            <p class="text-muted small px-2 mb-1">
                                Manage your account settings and keep your information secure.
                            </p>

                            <button class="action-btn text-start" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                <span><i class="fas fa-user me-2" style="color:#667eea;"></i>Update Personal Info</span>
                                <i class="fas fa-chevron-right text-muted" style="font-size:0.8rem;"></i>
                            </button>

                            <button class="action-btn text-start" data-bs-toggle="modal"
                                data-bs-target="#changePasswordModal">
                                <span><i class="fas fa-key me-2" style="color:#667eea;"></i>Change Password</span>
                                <i class="fas fa-chevron-right text-muted" style="font-size:0.8rem;"></i>
                            </button>

                            <button class="action-btn text-start" data-bs-toggle="modal"
                                data-bs-target="#changePictureModal">
                                <span><i class="fas fa-camera me-2" style="color:#667eea;"></i>Change Profile
                                    Picture</span>
                                <i class="fas fa-chevron-right text-muted" style="font-size:0.8rem;"></i>
                            </button>

                            {{-- Email verification UI removed (handled elsewhere) --}}

                            <div class="mt-2">
                                <button class="action-btn danger text-start" data-bs-toggle="modal"
                                    data-bs-target="#deleteAccountModal">
                                    <span><i class="fas fa-user-times me-2"></i>Delete Account</span>
                                    <i class="fas fa-chevron-right" style="font-size:0.8rem;"></i>
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>


    {{-- ════════════════════════════════════════════════════════ --}}
    {{-- Modal: Edit Profile                                       --}}
    {{-- ════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow">

                <div class="modal-header border-0 px-4 pt-4">
                    <h5 class="modal-title fw-bold" id="editProfileLabel">
                        <i class="fas fa-user-edit me-2 text-primary" style="color:#667eea!important;"></i>
                        Edit Personal Information
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')

                    <div class="modal-body px-4">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-user text-primary" style="color:#667eea!important;"></i>
                                    </span>
                                    <input type="text" name="Name" class="form-control border-start-0"
                                        value="{{ old('Name', auth()->user()->Name) }}" placeholder="Your full name"
                                        required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Date of Birth</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-calendar text-primary" style="color:#667eea!important;"></i>
                                    </span>
                                    <input type="date" name="DateOfBirth" class="form-control border-start-0"
                                        value="{{ old('DateOfBirth', auth()->user()->DateOfBirth?->format('Y-m-d')) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-phone text-primary" style="color:#667eea!important;"></i>
                                    </span>
                                    <input type="text" name="Phone" class="form-control border-start-0"
                                        value="{{ old('Phone', auth()->user()->Phone) }}"
                                        placeholder="e.g. +880 1234 567890">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Occupation</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-briefcase text-primary" style="color:#667eea!important;"></i>
                                    </span>
                                    <input type="text" name="Occupation" class="form-control border-start-0"
                                        value="{{ old('Occupation', auth()->user()->Occupation) }}"
                                        placeholder="e.g. Software Engineer">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Blood Group</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-tint" style="color:#e53e3e;"></i>
                                    </span>
                                    <select name="BloodGroup" class="form-select border-start-0">
                                        <option value="">-- Select Blood Group --</option>
                                        @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                            <option value="{{ $bg }}"
                                                {{ old('BloodGroup', auth()->user()->BloodGroup) === $bg ? 'selected' : '' }}>
                                                {{ $bg }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="gradient-btn btn px-4">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>


    {{-- ════════════════════════════════════════════════════════ --}}
    {{-- Modal: Change Profile Picture                             --}}
    {{-- ════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="changePictureModal" tabindex="-1" aria-labelledby="changePictureLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">

                <div class="modal-header border-0 px-4 pt-4">
                    <h5 class="modal-title fw-bold" id="changePictureLabel">
                        <i class="fas fa-camera me-2" style="color:#667eea;"></i>
                        Change Profile Picture
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" action="{{ route('profile.picture') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="modal-body px-4">
                        {{-- Preview --}}
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                @if (auth()->user()->Picture)
                                    <img src="{{ asset('storage/' . auth()->user()->Picture) }}" id="picturePreview"
                                        class="rounded-circle border border-3"
                                        style="width:100px;height:100px;object-fit:cover;border-color:#667eea!important;">
                                @else
                                    <div id="picturePreviewInitials"
                                        class="rounded-circle d-flex align-items-center justify-content-center"
                                        style="width:100px;height:100px;background:linear-gradient(135deg,#667eea,#764ba2);font-size:2rem;font-weight:700;color:white;">
                                        {{ strtoupper(substr(auth()->user()->Name, 0, 1)) }}
                                    </div>
                                    <img id="picturePreview" src="#" alt="Preview"
                                        class="rounded-circle border border-3 d-none"
                                        style="width:100px;height:100px;object-fit:cover;border-color:#667eea!important;">
                                @endif
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-semibold small">Choose New Picture</label>
                            <input type="file" name="Picture" id="pictureInput" class="form-control rounded-3"
                                accept="image/png, image/jpeg, image/jpg, image/webp" required>
                            <div class="form-text">Allowed: JPG, PNG, WEBP &mdash; Max 2 MB</div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="gradient-btn btn px-4">
                            <i class="fas fa-cloud-upload-alt me-2"></i>Upload
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>


    {{-- ════════════════════════════════════════════════════════ --}}
    {{-- Modal: Change Password                                    --}}
    {{-- ════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">

                <div class="modal-header border-0 px-4 pt-4">
                    <h5 class="modal-title fw-bold" id="changePasswordLabel">
                        <i class="fas fa-key me-2" style="color:#667eea;"></i>
                        Change Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf
                    @method('PATCH')

                    <div class="modal-body px-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Current Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-lock" style="color:#667eea;"></i>
                                </span>
                                <input type="password" name="current_password" class="form-control border-start-0"
                                    placeholder="Enter current password" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-lock" style="color:#667eea;"></i>
                                </span>
                                <input type="password" name="password" class="form-control border-start-0"
                                    placeholder="At least 8 characters" required>
                            </div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label fw-semibold small">Confirm New Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-lock" style="color:#667eea;"></i>
                                </span>
                                <input type="password" name="password_confirmation" class="form-control border-start-0"
                                    placeholder="Repeat new password" required>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="gradient-btn btn px-4">
                            <i class="fas fa-save me-2"></i>Update Password
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>


    {{-- ════════════════════════════════════════════════════════ --}}
    {{-- Modal: Delete Account                                     --}}
    {{-- ════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">

                <div class="modal-header border-0 px-4 pt-4">
                    <h5 class="modal-title fw-bold text-danger" id="deleteAccountLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Delete Account
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body px-4">
                    <div class="alert border-0 rounded-3 mb-3" style="background:rgba(229,62,62,0.08); color:#c53030;">
                        <i class="fas fa-info-circle me-2"></i>
                        This action is <strong>permanent and irreversible</strong>. All your data will be lost.
                    </div>
                    <form method="POST" action="{{ route('profile.destroy') }}" id="deleteAccountForm">
                        @csrf
                        @method('DELETE')
                        <div>
                            <label class="form-label fw-semibold small">
                                Confirm your password to proceed
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-lock text-danger"></i>
                                </span>
                                <input type="password" name="password" class="form-control border-start-0"
                                    placeholder="Enter your password" required>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="deleteAccountForm" class="btn btn-danger rounded-pill px-4 fw-semibold">
                        <i class="fas fa-trash-alt me-2"></i>Delete Account
                    </button>
                </div>

            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // Live picture preview in modal
        document.getElementById('pictureInput')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(ev) {
                const preview = document.getElementById('picturePreview');
                const initials = document.getElementById('picturePreviewInitials');
                preview.src = ev.target.result;
                preview.classList.remove('d-none');
                if (initials) initials.classList.add('d-none');
            };
            reader.readAsDataURL(file);
        });

        // Re-open the correct modal after validation errors (via session flash)
        @if ($errors->hasAny(['Name', 'Phone', 'DateOfBirth', 'Occupation', 'BloodGroup']))
            var modal = new bootstrap.Modal(document.getElementById('editProfileModal'));
            modal.show();
        @endif

        @if ($errors->hasAny(['current_password', 'password']))
            var modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
            modal.show();
        @endif
    </script>
@endpush
