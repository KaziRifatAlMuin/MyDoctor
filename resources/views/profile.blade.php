@extends('layouts.app')

@section('title', __('ui.profile.my_profile_title'))

@push('styles')
    <style>
        /* All existing styles remain exactly the same */
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
            border-bottom: 1px solid #e2e8f0;
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

        .profile-disease-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(11, 87, 208, 0.12);
            color: #0b57d0;
            border: 1px solid rgba(11, 87, 208, 0.2);
            border-radius: 20px;
            padding: 0.28rem 0.72rem;
            font-size: 0.78rem;
            font-weight: 600;
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
            background: #ffffff;
            min-height: calc(100vh - 400px);
            padding: 2.5rem 0 3rem;
        }
    </style>
@endpush

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show m-3 rounded-3 border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('ui.actions.close') }}"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show m-3 rounded-3 border-0 shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('ui.actions.close') }}"></button>
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
                            @if (auth()->user()->picture)
                                <img src="{{ asset('storage/' . auth()->user()->picture) }}" alt="{{ __('ui.profile.profile_picture') }}"
                                    class="profile-avatar-ring">
                            @else
                                <div class="profile-avatar-initials">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            @endif
                            <button class="camera-btn" data-bs-toggle="modal" data-bs-target="#changePictureModal"
                                title="{{ __('ui.profile.change_picture') }}">
                                <i class="fas fa-camera"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Name & details --}}
                    <div class="col">
                        @php
                            $profileDiseases = auth()->user()->userDiseases()
                                ->with('disease')
                                ->latest()
                                ->get()
                                ->pluck('disease')
                                ->filter()
                                ->unique('id')
                                ->values();
                        @endphp

                        <h4 class="fw-bold mb-1" style="color:#2d3748;">
                            {{ auth()->user()->name }}
                        </h4>
                        <p class="text-muted mb-2" style="font-size:0.9rem;">
                            <i class="fas fa-envelope me-1" style="color:#667eea;"></i>
                            {{ auth()->user()->email }}
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            @if (auth()->user()->occupation)
                                <span class="occupation-badge">
                                    <i class="fas fa-briefcase"></i>
                                    {{ auth()->user()->occupation }}
                                </span>
                            @endif
                            @if (auth()->user()->blood_group)
                                <span class="blood-badge">
                                    <i class="fas fa-tint"></i>
                                    {{ auth()->user()->blood_group }}
                                </span>
                            @endif
                            @if (auth()->user()->role)
                                <span class="occupation-badge" style="background: {{ auth()->user()->role === 'admin' ? 'rgba(245,101,101,0.12)' : 'rgba(102,126,234,0.12)' }}; color: {{ auth()->user()->role === 'admin' ? '#e53e3e' : '#667eea' }};">
                                    <i class="fas fa-user-shield"></i>
                                    {{ ucfirst(auth()->user()->role) }}
                                </span>
                            @endif
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-2">
                            @forelse($profileDiseases as $disease)
                                <a href="{{ route('public.disease.show', $disease) }}" class="profile-disease-tag text-decoration-none">
                                    {{ $disease->disease_name }}
                                </a>
                            @empty
                                <span class="profile-disease-tag" style="background: #f1f5f9; color: #64748b; border-color: #e2e8f0;">
                                    {{ __('ui.profile.no_disease_tags') }}
                                </span>
                            @endforelse
                        </div>
                    </div>

                    {{-- Edit button --}}
                    <div class="col-auto">
                        <button class="gradient-btn btn" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="fas fa-user-edit me-2"></i>{{ __('ui.profile.edit_profile') }}
                        </button>
                    </div>

                </div>
            </div>

            <div class="row g-4">

                {{-- ── Personal Information ── --}}
                <div class="col-md-6">
                    <div class="info-card">
                        <div class="card-header-custom">
                            <h5><i class="fas fa-id-card me-2"></i>{{ __('ui.profile.personal_information') }}</h5>
                        </div>

                        <div class="info-item">
                            <div class="info-icon purple"><i class="fas fa-calendar-alt"></i></div>
                            <div>
                                <div class="info-label">{{ __('ui.profile.date_of_birth') }}</div>
                                <div class="info-value">
                                    {{ auth()->user()->date_of_birth ? auth()->user()->date_of_birth->format('d M, Y') : __('ui.profile.not_provided') }}
                                </div>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon purple"><i class="fas fa-phone-alt"></i></div>
                            <div>
                                <div class="info-label">{{ __('ui.profile.phone_number') }}</div>
                                <div class="info-value">
                                    {{ auth()->user()->phone ?? __('ui.profile.not_provided') }}
                                </div>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon orange"><i class="fas fa-briefcase"></i></div>
                            <div>
                                <div class="info-label">{{ __('ui.profile.occupation') }}</div>
                                <div class="info-value">
                                    {{ auth()->user()->occupation ?? __('ui.profile.not_provided') }}
                                </div>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon red"><i class="fas fa-tint"></i></div>
                            <div>
                                <div class="info-label">{{ __('ui.profile.blood_group') }}</div>
                                <div class="info-value">
                                    {{ auth()->user()->blood_group ?? __('ui.profile.not_provided') }}
                                </div>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon gray"><i class="fas fa-person"></i></div>
                            <div>
                                <div class="info-label">{{ __('ui.profile.gender') }}</div>
                                <div class="info-value">
                                    {{ auth()->user()->gender ? ucfirst(auth()->user()->gender) : __('ui.profile.not_provided') }}
                                </div>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon green"><i class="fas fa-location-dot"></i></div>
                            <div>
                                <div class="info-label">{{ __('ui.profile.address') }}</div>
                                <div class="info-value">
                                    {{ auth()->user()->address?->display_address ?? __('ui.profile.not_set') }}
                                </div>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon green"><i class="fas fa-shield-alt"></i></div>
                            <div>
                                <div class="info-label">{{ __('ui.profile.email_verification') }}</div>
                                <div class="info-value mt-1">
                                    @if (auth()->user()->email_verified_at)
                                        <span class="verified-badge">
                                            <i class="fas fa-check-circle"></i> {{ __('ui.profile.verified') }}
                                        </span>
                                    @else
                                        <span class="unverified-badge">
                                            <i class="fas fa-exclamation-circle"></i> {{ __('ui.profile.not_verified') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon gray"><i class="fas fa-clock"></i></div>
                            <div>
                                <div class="info-label">{{ __('ui.profile.member_since') }}</div>
                                <div class="info-value">
                                    {{ auth()->user()->created_at ? auth()->user()->created_at->format('d M, Y') : 'N/A' }}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ── Account Security ── --}}
                <div class="col-md-6">
                    <div class="info-card">
                        <div class="card-header-custom">
                            <h5><i class="fas fa-lock me-2"></i>{{ __('ui.profile.account_security') }}</h5>
                        </div>

                        <div class="p-3 d-flex flex-column gap-2">

                            <p class="text-muted small px-2 mb-1">
                                {{ __('ui.profile.account_security_desc') }}
                            </p>

                            <button class="action-btn text-start" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                <span><i class="fas fa-user me-2" style="color:#667eea;"></i>{{ __('ui.profile.update_personal_info') }}</span>
                                <i class="fas fa-chevron-right text-muted" style="font-size:0.8rem;"></i>
                            </button>

                            <button class="action-btn text-start" data-bs-toggle="modal"
                                data-bs-target="#changePasswordModal">
                                <span><i class="fas fa-key me-2" style="color:#667eea;"></i>{{ __('ui.profile.change_password') }}</span>
                                <i class="fas fa-chevron-right text-muted" style="font-size:0.8rem;"></i>
                            </button>

                            <button class="action-btn text-start" data-bs-toggle="modal"
                                data-bs-target="#changePictureModal">
                                <span><i class="fas fa-camera me-2" style="color:#667eea;"></i>{{ __('ui.profile.change_profile_picture') }}</span>
                                <i class="fas fa-chevron-right text-muted" style="font-size:0.8rem;"></i>
                            </button>

                            <a href="{{ route('profile.setting') }}" class="action-btn text-start">
                                <span><i class="fas fa-cog me-2" style="color:#667eea;"></i>{{ __('ui.profile.settings') }}</span>
                                <i class="fas fa-chevron-right text-muted" style="font-size:0.8rem;"></i>
                            </a>

                            <button type="button" class="action-btn danger text-start" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                                <span><i class="fas fa-trash-alt me-2"></i>{{ __('ui.profile.delete_account') }}</span>
                                <i class="fas fa-chevron-right" style="font-size:0.8rem;"></i>
                            </button>

                            <div class="mt-2">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="action-btn danger text-start w-100">
                                        <span><i class="fas fa-sign-out-alt me-2"></i>{{ __('ui.profile.sign_out') }}</span>
                                        <i class="fas fa-chevron-right" style="font-size:0.8rem;"></i>
                                    </button>
                                </form>
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
                        {{ __('ui.profile.edit_personal_information') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('ui.actions.close') }}"></button>
                </div>

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')

                    <div class="modal-body px-4">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">{{ __('ui.profile.full_name') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-user text-primary" style="color:#667eea!important;"></i>
                                    </span>
                                    <input type="text" name="name" class="form-control border-start-0"
                                        value="{{ old('name', auth()->user()->name) }}" placeholder="{{ __('ui.profile.full_name_placeholder') }}"
                                        required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">{{ __('ui.profile.date_of_birth') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-calendar text-primary" style="color:#667eea!important;"></i>
                                    </span>
                                    <input type="date" name="date_of_birth" class="form-control border-start-0"
                                        value="{{ old('date_of_birth', auth()->user()->date_of_birth?->format('Y-m-d')) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">{{ __('ui.profile.phone_number') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-phone text-primary" style="color:#667eea!important;"></i>
                                    </span>
                                    <input type="text" name="phone" class="form-control border-start-0"
                                        value="{{ old('phone', auth()->user()->phone) }}"
                                        placeholder="{{ __('ui.profile.phone_placeholder') }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">{{ __('ui.profile.occupation') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-briefcase text-primary" style="color:#667eea!important;"></i>
                                    </span>
                                    <input type="text" name="occupation" class="form-control border-start-0"
                                        value="{{ old('occupation', auth()->user()->occupation) }}"
                                        placeholder="{{ __('ui.profile.occupation_placeholder') }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">{{ __('ui.profile.blood_group') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-tint" style="color:#e53e3e;"></i>
                                    </span>
                                    <select name="blood_group" class="form-select border-start-0">
                                        <option value="">-- {{ __('ui.profile.select_blood_group') }} --</option>
                                        @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                            <option value="{{ $bg }}"
                                                {{ old('blood_group', auth()->user()->blood_group) === $bg ? 'selected' : '' }}>
                                                {{ $bg }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">{{ __('ui.profile.gender') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-person text-primary" style="color:#667eea!important;"></i>
                                    </span>
                                    <select name="gender" class="form-select border-start-0">
                                        <option value="">-- {{ __('ui.profile.select_gender') }} --</option>
                                        <option value="male" {{ old('gender', auth()->user()->gender) === 'male' ? 'selected' : '' }}>{{ __('ui.profile.male') }}</option>
                                        <option value="female" {{ old('gender', auth()->user()->gender) === 'female' ? 'selected' : '' }}>{{ __('ui.profile.female') }}</option>
                                        <option value="other" {{ old('gender', auth()->user()->gender) === 'other' ? 'selected' : '' }}>{{ __('ui.profile.other') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                @php
                                    $isBnLocale = str_starts_with((string) app()->getLocale(), 'bn');
                                @endphp
                                <label class="form-label fw-semibold small">{{ __('ui.profile.division') }}</label>
                                <select id="profileDivision" class="form-select" data-current-id="{{ old('division_id', auth()->user()->address?->division_id) }}">
                                    @if (old('division', auth()->user()->address?->division))
                                        <option value="{{ old('division', auth()->user()->address?->division) }}" data-id="{{ old('division_id', auth()->user()->address?->division_id) }}" data-bn="{{ old('division_bn', auth()->user()->address?->division_bn) }}" selected>{{ $isBnLocale ? (old('division_bn', auth()->user()->address?->division_bn) ?: old('division', auth()->user()->address?->division)) : old('division', auth()->user()->address?->division) }}</option>
                                    @else
                                        <option value="">{{ $isBnLocale ? __('ui.profile.select_division') : __('ui.profile.select_division') }}</option>
                                    @endif
                                </select>
                                <input type="hidden" name="division_id" id="profileDivisionId" value="{{ old('division_id', auth()->user()->address?->division_id) }}">
                                <input type="hidden" name="division" id="profileDivisionEn" value="{{ old('division', auth()->user()->address?->division) }}">
                                <input type="hidden" name="division_bn" id="profileDivisionBn" value="{{ old('division_bn', auth()->user()->address?->division_bn) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">{{ __('ui.profile.district') }} <span class="text-danger">*</span></label>
                                <select name="district" id="profileDistrict" class="form-select" required data-current="{{ old('district', auth()->user()->address?->district) }}" data-current-id="{{ old('district_id', auth()->user()->address?->district_id) }}">
                                    <option value="{{ old('district', auth()->user()->address?->district) }}" selected>{{ old('district', auth()->user()->address?->district) ? ($isBnLocale ? (old('district_bn', auth()->user()->address?->district_bn) ?: old('district', auth()->user()->address?->district)) : old('district', auth()->user()->address?->district)) : ($isBnLocale ? __('ui.profile.select_district') : __('ui.profile.select_district')) }}</option>
                                </select>
                                <input type="hidden" name="district_id" id="profileDistrictId" value="{{ old('district_id', auth()->user()->address?->district_id) }}">
                                <input type="hidden" name="district_bn" id="profileDistrictBn" value="{{ old('district_bn', auth()->user()->address?->district_bn) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">{{ __('ui.profile.upazila') }} <span class="text-danger">*</span></label>
                                <select name="upazila" id="profileUpazila" class="form-select" required data-current="{{ old('upazila', auth()->user()->address?->upazila) }}" data-current-id="{{ old('upazila_id', auth()->user()->address?->upazila_id) }}">
                                    <option value="{{ old('upazila', auth()->user()->address?->upazila) }}" selected>{{ old('upazila', auth()->user()->address?->upazila) ? ($isBnLocale ? (old('upazila_bn', auth()->user()->address?->upazila_bn) ?: old('upazila', auth()->user()->address?->upazila)) : old('upazila', auth()->user()->address?->upazila)) : ($isBnLocale ? __('ui.profile.select_upazila') : __('ui.profile.select_upazila')) }}</option>
                                </select>
                                <input type="hidden" name="upazila_id" id="profileUpazilaId" value="{{ old('upazila_id', auth()->user()->address?->upazila_id) }}">
                                <input type="hidden" name="upazila_bn" id="profileUpazilaBn" value="{{ old('upazila_bn', auth()->user()->address?->upazila_bn) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">{{ __('ui.profile.street') }}</label>
                                <input type="text" name="street" class="form-control" value="{{ old('street', auth()->user()->address?->street) }}" placeholder="{{ __('ui.profile.street_placeholder') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">{{ __('ui.profile.house') }}</label>
                                <input type="text" name="house" class="form-control" value="{{ old('house', auth()->user()->address?->house) }}" placeholder="{{ __('ui.profile.house_placeholder') }}">
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">{{ __('ui.profile.cancel') }}</button>
                        <button type="submit" class="gradient-btn btn px-4">
                            <i class="fas fa-save me-2"></i>{{ __('ui.profile.save_changes') }}
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
                        {{ __('ui.profile.change_profile_picture') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('ui.actions.close') }}"></button>
                </div>

                <form method="POST" action="{{ route('profile.picture') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="modal-body px-4">
                        {{-- Preview --}}
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                @if (auth()->user()->picture)
                                    <img src="{{ asset('storage/' . auth()->user()->picture) }}" id="picturePreview"
                                        class="rounded-circle border border-3"
                                        style="width:100px;height:100px;object-fit:cover;border-color:#667eea!important;">
                                @else
                                    <div id="picturePreviewInitials"
                                        class="rounded-circle d-flex align-items-center justify-content-center"
                                        style="width:100px;height:100px;background:linear-gradient(135deg,#667eea,#764ba2);font-size:2rem;font-weight:700;color:white;">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                    <img id="picturePreview" src="#" alt="Preview"
                                        class="rounded-circle border border-3 d-none"
                                        style="width:100px;height:100px;object-fit:cover;border-color:#667eea!important;">
                                @endif
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-semibold small">{{ __('ui.profile.choose_new_picture') }}</label>
                            <input type="file" name="picture" id="pictureInput" class="form-control rounded-3"
                                accept="image/png, image/jpeg, image/jpg, image/webp" required>
                            <div class="form-text">{{ __('ui.profile.picture_allowed_formats') }}</div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">{{ __('ui.profile.cancel') }}</button>
                        <button type="submit" class="gradient-btn btn px-4">
                            <i class="fas fa-cloud-upload-alt me-2"></i>{{ __('ui.profile.upload') }}
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
                        {{ __('ui.profile.change_password') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('ui.actions.close') }}"></button>
                </div>

                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf
                    @method('PATCH')

                    <div class="modal-body px-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">{{ __('ui.profile.current_password') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-lock" style="color:#667eea;"></i>
                                </span>
                                <input type="password" name="current_password" class="form-control border-start-0"
                                    placeholder="{{ __('ui.profile.current_password_placeholder') }}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">{{ __('ui.profile.new_password') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-lock" style="color:#667eea;"></i>
                                </span>
                                <input type="password" name="password" class="form-control border-start-0"
                                    placeholder="{{ __('ui.profile.new_password_placeholder') }}" required>
                            </div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label fw-semibold small">{{ __('ui.profile.confirm_new_password') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-lock" style="color:#667eea;"></i>
                                </span>
                                <input type="password" name="password_confirmation" class="form-control border-start-0"
                                    placeholder="{{ __('ui.profile.confirm_password_placeholder') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">{{ __('ui.profile.cancel') }}</button>
                        <button type="submit" class="gradient-btn btn px-4">
                            <i class="fas fa-save me-2"></i>{{ __('ui.profile.update_password') }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════ --}}
    {{-- Modal: Delete Account                                   --}}
    {{-- ════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">

                <div class="modal-header border-0 px-4 pt-4">
                    <h5 class="modal-title fw-bold text-danger" id="deleteAccountLabel">
                        <i class="fas fa-trash-alt me-2"></i>
                        {{ __('ui.profile.delete_account') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('ui.actions.close') }}"></button>
                </div>

                <form method="POST" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('DELETE')

                    <div class="modal-body px-4">
                        <p class="text-danger fw-semibold">
                            {{ __('ui.profile.delete_account_warning') }}
                        </p>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">{{ __('ui.profile.confirm_password') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-lock text-danger"></i>
                                </span>
                                <input type="password" name="delete_password" class="form-control border-start-0"
                                    placeholder="{{ __('ui.profile.enter_password') }}" required>
                            </div>
                            @error('delete_password')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-warning mb-0" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ __('ui.profile.delete_account_logout_warning') }}
                        </div>
                    </div>

                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">{{ __('ui.profile.cancel') }}</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4">
                            <i class="fas fa-trash-alt me-2"></i>{{ __('ui.profile.delete_my_account') }}
                        </button>
                    </div>
                </form>

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
        @if ($errors->hasAny(['name', 'phone', 'date_of_birth', 'occupation', 'blood_group', 'gender', 'district', 'upazila', 'street', 'house']))
            var modal = new bootstrap.Modal(document.getElementById('editProfileModal'));
            modal.show();
        @endif

        @if ($errors->hasAny(['current_password', 'password']))
            var modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
            modal.show();
        @endif

        @if ($errors->has('delete_password'))
            var modal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
            modal.show();
        @endif

        initializeBdAddressSelector();

        async function initializeBdAddressSelector() {
            // ... keep existing address API code exactly as is ...
            const divisionSelect = document.getElementById('profileDivision');
            const districtSelect = document.getElementById('profileDistrict');
            const upazilaSelect = document.getElementById('profileUpazila');
            const divisionIdInput = document.getElementById('profileDivisionId');
            const divisionEnInput = document.getElementById('profileDivisionEn');
            const divisionBnInput = document.getElementById('profileDivisionBn');
            const districtIdInput = document.getElementById('profileDistrictId');
            const districtBnInput = document.getElementById('profileDistrictBn');
            const upazilaIdInput = document.getElementById('profileUpazilaId');
            const upazilaBnInput = document.getElementById('profileUpazilaBn');

            if (!divisionSelect || !districtSelect || !upazilaSelect) {
                return;
            }

            const locale = document.documentElement.lang?.startsWith('bn') ? 'bn' : 'en';
            const apiBase = '{{ url('/geo/v2.0') }}';
            const divisionPlaceholder = locale === 'bn' ? 'বিভাগ নির্বাচন করুন' : 'Select Division';
            const districtPlaceholder = locale === 'bn' ? 'জেলা নির্বাচন করুন' : 'Select District';
            const upazilaPlaceholder = locale === 'bn' ? 'উপজেলা নির্বাচন করুন' : 'Select Upazila';
            const currentDistrict = districtSelect.dataset.current || '';
            const currentUpazila = upazilaSelect.dataset.current || '';
            const currentDivisionId = divisionSelect.dataset.currentId || '';
            const currentDistrictId = districtSelect.dataset.currentId || '';
            const currentUpazilaId = upazilaSelect.dataset.currentId || '';

            const toList = (payload) => Array.isArray(payload) ? payload : (payload?.data || payload?.result || []);
            const getId = (item) => item?.id ?? item?.division_id ?? item?.district_id ?? item?.upazila_id ?? null;
            const getEn = (item) => item?.name ?? item?.division ?? item?.district ?? item?.upazila ?? item?.upazilla ?? item?.name_en ?? '';
            const getBn = (item) => item?.bn_name ?? item?.bn ?? item?.name_bn ?? item?.bangla ?? '';
            const labelOf = (item) => locale === 'bn' ? (getBn(item) || getEn(item)) : getEn(item);

            const setDivisionMeta = (item) => {
                divisionIdInput.value = getId(item) ?? '';
                divisionEnInput.value = getEn(item) || '';
                divisionBnInput.value = getBn(item) || '';
            };
            const setDistrictMeta = (item) => {
                districtIdInput.value = getId(item) ?? '';
                districtBnInput.value = getBn(item) || '';
            };
            const setUpazilaMeta = (item) => {
                upazilaIdInput.value = getId(item) ?? '';
                upazilaBnInput.value = getBn(item) || '';
            };

            const renderUpazilas = (upazilas, selectedName, selectedId) => {
                upazilaSelect.innerHTML = `<option value="">${upazilaPlaceholder}</option>`;
                (upazilas || []).forEach((item) => {
                    const option = document.createElement('option');
                    option.value = getEn(item);
                    option.textContent = labelOf(item);
                    option.dataset.id = String(getId(item) ?? '');
                    option.dataset.bn = getBn(item) || '';
                    if ((selectedId && option.dataset.id === String(selectedId)) || (selectedName && selectedName === option.value)) {
                        option.selected = true;
                        setUpazilaMeta(item);
                    }
                    upazilaSelect.appendChild(option);
                });
            };

            const renderDistricts = (districtRows, selectedDistrictName, selectedDistrictId, selectedUpazilaName, selectedUpazilaId) => {
                districtSelect.innerHTML = `<option value="">${districtPlaceholder}</option>`;
                districtRows.forEach((districtRow) => {
                    const option = document.createElement('option');
                    option.value = getEn(districtRow);
                    option.textContent = labelOf(districtRow);
                    option.dataset.id = String(getId(districtRow) ?? '');
                    option.dataset.bn = getBn(districtRow) || '';
                    if ((selectedDistrictId && option.dataset.id === String(selectedDistrictId)) || (selectedDistrictName && selectedDistrictName === option.value)) {
                        option.selected = true;
                        setDistrictMeta(districtRow);
                    }
                    districtSelect.appendChild(option);
                });

                const selectedRow = districtRows.find((item) => String(getId(item) ?? '') === districtSelect.selectedOptions[0]?.dataset.id) || districtRows.find((item) => getEn(item) === districtSelect.value);
                renderUpazilas([], '', '');
                if (selectedRow && getId(selectedRow)) {
                    loadUpazilas(getId(selectedRow), selectedUpazilaName, selectedUpazilaId);
                }
            };

            const loadUpazilas = async (districtId, selectedUpazilaName, selectedUpazilaId) => {
                const response = await fetch(`${apiBase}/upazilas/${districtId}`);
                const payload = await response.json();
                renderUpazilas(toList(payload), selectedUpazilaName, selectedUpazilaId);
            };

            try {
                const divisionsResponse = await fetch(`${apiBase}/divisions`);
                const divisionsPayload = await divisionsResponse.json();
                const divisions = toList(divisionsPayload);

                divisionSelect.innerHTML = `<option value="">${divisionPlaceholder}</option>`;

                divisions.forEach((division) => {
                    const option = document.createElement('option');
                    option.value = getEn(division);
                    option.textContent = labelOf(division);
                    option.dataset.id = String(getId(division) ?? '');
                    option.dataset.bn = getBn(division) || '';
                    if ((currentDivisionId && option.dataset.id === String(currentDivisionId)) || (!currentDivisionId && divisionEnInput.value && option.value === divisionEnInput.value)) {
                        option.selected = true;
                        setDivisionMeta(division);
                    }
                    divisionSelect.appendChild(option);
                });

                const selectedDivisionId = divisionSelect.selectedOptions[0]?.dataset.id;
                if (selectedDivisionId) {
                    const districtsResponse = await fetch(`${apiBase}/districts/${selectedDivisionId}`);
                    const districtsPayload = await districtsResponse.json();
                    renderDistricts(toList(districtsPayload), currentDistrict, currentDistrictId, currentUpazila, currentUpazilaId);
                }

                divisionSelect.addEventListener('change', async function() {
                    const selected = this.selectedOptions[0];
                    const divisionId = selected?.dataset.id || '';
                    setDivisionMeta({ id: divisionId, name: selected?.value || '', bn_name: selected?.dataset.bn || '' });
                    districtSelect.innerHTML = `<option value="">${districtPlaceholder}</option>`;
                    upazilaSelect.innerHTML = `<option value="">${upazilaPlaceholder}</option>`;
                    setDistrictMeta({});
                    setUpazilaMeta({});

                    if (!divisionId) {
                        return;
                    }

                    const districtsResponse = await fetch(`${apiBase}/districts/${divisionId}`);
                    const districtsPayload = await districtsResponse.json();
                    renderDistricts(toList(districtsPayload), '', '', '', '');
                });

                districtSelect.addEventListener('change', async function() {
                    const selected = this.selectedOptions[0];
                    const districtId = selected?.dataset.id || '';
                    setDistrictMeta({ id: districtId, bn_name: selected?.dataset.bn || '' });
                    upazilaSelect.innerHTML = `<option value="">${upazilaPlaceholder}</option>`;
                    setUpazilaMeta({});

                    if (!districtId) {
                        return;
                    }

                    await loadUpazilas(districtId, '', '');
                });

                upazilaSelect.addEventListener('change', function() {
                    const selected = this.selectedOptions[0];
                    setUpazilaMeta({ id: selected?.dataset.id || '', bn_name: selected?.dataset.bn || '' });
                });
            } catch (error) {
                console.error('Failed to load BD address API for profile form', error);
            }
        }
    </script>
@endpush