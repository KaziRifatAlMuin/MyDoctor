@extends('layouts.app')

@section('title', __('ui.auto.Register'))

@section('content')
<div class="container-fluid px-0">
    <div class="row g-0 min-vh-100">
        <!-- Left Side - Form -->
        <div class="col-lg-7 d-flex align-items-center justify-content-center p-5" style="background: #ffffff;">
            <div class="w-100" style="max-width: 700px;">
                <div class="text-center mb-4">
                    <img src="{{ asset('images/logos/applogo.png') }}" alt="My Doctor" height="60" class="mb-3">
                    <h2 class="fw-bold text-primary">{{ __('ui.auth.register_title') }}</h2>
                    <p class="text-muted">{{ __('ui.auth.register_subtitle') }}</p>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('ui.actions.close') }}"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>{{ __('ui.auth.fix_errors') }}</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('ui.actions.close') }}"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('register', [], false) }}" id="registerForm">
                    @csrf
                    <input type="hidden" name="redirect" value="{{ request('redirect') }}">

                    <div class="row">
                        <!-- Name Field -->
                        <div class="col-md-6 mb-3">
                            <label for="Name" class="form-label fw-semibold">{{ __('ui.profile.full_name') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-user text-primary"></i>
                                </span>
                                <input type="text" 
                                       class="form-control border-start-0 @error('Name') is-invalid @enderror" 
                                       id="Name" 
                                       name="Name" 
                                       value="{{ old('Name') }}" 
                                       placeholder="{{ __('ui.profile.full_name_placeholder') }}"
                                       required>
                            </div>
                            @error('Name')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Email Field -->
                        <div class="col-md-6 mb-3">
                            <label for="Email" class="form-label fw-semibold">{{ __('ui.auth.email') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-envelope text-primary"></i>
                                </span>
                                <input type="email" 
                                       class="form-control border-start-0 @error('Email') is-invalid @enderror" 
                                       id="Email" 
                                       name="Email" 
                                       value="{{ old('Email') }}" 
                                       placeholder="{{ __('ui.auth.email_placeholder') }}"
                                       required>
                            </div>
                            @error('Email')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                            <small class="text-muted">{{ __('ui.auth.verification_email_note') }}</small>
                        </div>

                        <!-- Phone Field -->
                        <div class="col-md-6 mb-3">
                            <label for="Phone" class="form-label fw-semibold">{{ __('ui.profile.phone_number') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-phone text-primary"></i>
                                </span>
                                <input type="tel" 
                                       class="form-control border-start-0 @error('Phone') is-invalid @enderror" 
                                       id="Phone" 
                                       name="Phone" 
                                       value="{{ old('Phone') }}" 
                                       placeholder="{{ __('ui.profile.phone_placeholder') }}">
                            </div>
                            @error('Phone')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Date of Birth Field -->
                        <div class="col-md-6 mb-3">
                            <label for="DateOfBirth" class="form-label fw-semibold">{{ __('ui.profile.date_of_birth') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-calendar text-primary"></i>
                                </span>
                                <input type="date" 
                                       class="form-control border-start-0 @error('DateOfBirth') is-invalid @enderror" 
                                       id="DateOfBirth" 
                                       name="DateOfBirth" 
                                       value="{{ old('DateOfBirth') }}">
                            </div>
                            @error('DateOfBirth')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Occupation Field -->
                        <div class="col-md-6 mb-3">
                            <label for="Occupation" class="form-label fw-semibold">{{ __('ui.profile.occupation') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-briefcase text-primary"></i>
                                </span>
                                <input type="text" 
                                       class="form-control border-start-0 @error('Occupation') is-invalid @enderror" 
                                       id="Occupation" 
                                       name="Occupation" 
                                       value="{{ old('Occupation') }}" 
                                       placeholder="{{ __('ui.profile.occupation_placeholder') }}">
                            </div>
                            @error('Occupation')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Blood Group Field -->
                        <div class="col-md-6 mb-3">
                            <label for="BloodGroup" class="form-label fw-semibold">{{ __('ui.profile.blood_group') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-tint text-primary"></i>
                                </span>
                                <select class="form-control border-start-0 @error('BloodGroup') is-invalid @enderror" 
                                        id="BloodGroup" 
                                        name="BloodGroup">
                                    <option value="">{{ __('ui.form.select_option') }}</option>
                                    <option value="A+" {{ old('BloodGroup') == 'A+' ? 'selected' : '' }}>A+</option>
                                    <option value="A-" {{ old('BloodGroup') == 'A-' ? 'selected' : '' }}>A-</option>
                                    <option value="B+" {{ old('BloodGroup') == 'B+' ? 'selected' : '' }}>B+</option>
                                    <option value="B-" {{ old('BloodGroup') == 'B-' ? 'selected' : '' }}>B-</option>
                                    <option value="AB+" {{ old('BloodGroup') == 'AB+' ? 'selected' : '' }}>AB+</option>
                                    <option value="AB-" {{ old('BloodGroup') == 'AB-' ? 'selected' : '' }}>AB-</option>
                                    <option value="O+" {{ old('BloodGroup') == 'O+' ? 'selected' : '' }}>O+</option>
                                    <option value="O-" {{ old('BloodGroup') == 'O-' ? 'selected' : '' }}>O-</option>
                                </select>
                            </div>
                            @error('BloodGroup')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Gender Field -->
                        <div class="col-md-6 mb-3">
                            <label for="Gender" class="form-label fw-semibold">{{ __('ui.profile.gender') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-venus-mars text-primary"></i>
                                </span>
                                <select class="form-control border-start-0 @error('Gender') is-invalid @enderror" id="Gender" name="Gender" required>
                                    <option value="">{{ __('ui.form.select_option') }}</option>
                                    <option value="male" {{ old('Gender') == 'male' ? 'selected' : '' }}>{{ __('ui.profile.male') }}</option>
                                    <option value="female" {{ old('Gender') == 'female' ? 'selected' : '' }}>{{ __('ui.profile.female') }}</option>
                                    <option value="other" {{ old('Gender') == 'other' ? 'selected' : '' }}>{{ __('ui.profile.other') }}</option>
                                </select>
                            </div>
                            @error('Gender')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Division Field -->
                        <div class="col-md-6 mb-3">
                            @php
                                $isBnLocale = str_starts_with((string) app()->getLocale(), 'bn');
                            @endphp
                            <label for="Division" class="form-label fw-semibold">{{ __('ui.profile.division') }} <span class="text-danger">*</span></label>
                            <select id="Division" class="form-control @error('Division') is-invalid @enderror" 
                                data-current-division-id="{{ old('DivisionId') }}" 
                                data-current-district-id="{{ old('DistrictId') }}" 
                                data-current-upazila-id="{{ old('UpazilaId') }}" 
                                data-current-district="{{ old('District') }}" 
                                data-current-upazila="{{ old('Upazila') }}" 
                                required>
                                @if (old('Division'))
                                    <option value="{{ old('Division') }}" data-id="{{ old('DivisionId') }}" data-bn="{{ old('DivisionBn') }}" selected>{{ $isBnLocale ? (old('DivisionBn') ?: old('Division')) : old('Division') }}</option>
                                @else
                                    <option value="">{{ $isBnLocale ? 'বিভাগ নির্বাচন করুন' : 'Select Division' }}</option>
                                @endif
                            </select>
                            <input type="hidden" id="DivisionId" name="DivisionId" value="{{ old('DivisionId') }}">
                            <input type="hidden" id="DivisionEn" name="Division" value="{{ old('Division') }}">
                            <input type="hidden" id="DivisionBn" name="DivisionBn" value="{{ old('DivisionBn') }}">
                            @error('Division')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                            @error('DivisionId')
                                <span class="text-danger small d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- District Field -->
                        <div class="col-md-6 mb-3">
                            <label for="District" class="form-label fw-semibold">{{ __('ui.profile.district') }} <span class="text-danger">*</span></label>
                            <select id="District" name="District" class="form-control @error('District') is-invalid @enderror" required>
                                <option value="{{ old('District') }}">{{ old('District') ? ($isBnLocale ? (old('DistrictBn') ?: old('District')) : old('District')) : ($isBnLocale ? 'জেলা নির্বাচন করুন' : 'Select District') }}</option>
                            </select>
                            <input type="hidden" id="DistrictId" name="DistrictId" value="{{ old('DistrictId') }}">
                            <input type="hidden" id="DistrictBn" name="DistrictBn" value="{{ old('DistrictBn') }}">
                            @error('District')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Upazila Field -->
                        <div class="col-md-6 mb-3">
                            <label for="Upazila" class="form-label fw-semibold">{{ __('ui.profile.upazila') }} <span class="text-danger">*</span></label>
                            <select id="Upazila" name="Upazila" class="form-control @error('Upazila') is-invalid @enderror" required>
                                <option value="{{ old('Upazila') }}">{{ old('Upazila') ? ($isBnLocale ? (old('UpazilaBn') ?: old('Upazila')) : old('Upazila')) : ($isBnLocale ? 'উপজেলা নির্বাচন করুন' : 'Select Upazila') }}</option>
                            </select>
                            <input type="hidden" id="UpazilaId" name="UpazilaId" value="{{ old('UpazilaId') }}">
                            <input type="hidden" id="UpazilaBn" name="UpazilaBn" value="{{ old('UpazilaBn') }}">
                            @error('Upazila')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Street Field -->
                        <div class="col-md-6 mb-3">
                            <label for="Street" class="form-label fw-semibold">{{ __('ui.profile.street') }}</label>
                            <input type="text" 
                                   class="form-control @error('Street') is-invalid @enderror" 
                                   id="Street" 
                                   name="Street" 
                                   value="{{ old('Street') }}" 
                                   placeholder="{{ __('ui.profile.street_placeholder') }}">
                            @error('Street')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- House Field -->
                        <div class="col-md-6 mb-3">
                            <label for="House" class="form-label fw-semibold">{{ __('ui.profile.house') }}</label>
                            <input type="text" 
                                   class="form-control @error('House') is-invalid @enderror" 
                                   id="House" 
                                   name="House" 
                                   value="{{ old('House') }}" 
                                   placeholder="{{ __('ui.profile.house_placeholder') }}">
                            @error('House')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Password Field -->
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label fw-semibold">{{ __('ui.auth.password') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-lock text-primary"></i>
                                </span>
                                <input type="password" 
                                       class="form-control border-start-0 @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="{{ __('ui.auth.password_placeholder') }} ({{ __('ui.auth.min_chars', ['min' => 8]) }})"
                                       required>
                                <button class="btn btn-outline-secondary border-start-0 bg-white" 
                                        type="button" 
                                        onclick="togglePassword('password')">
                                    <i class="fas fa-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                            <div class="password-strength mt-2" id="passwordStrength">
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar" id="passwordStrengthBar" role="progressbar" style="width: 0%;"></div>
                                </div>
                                <small class="text-muted" id="passwordStrengthText">{{ __('ui.auth.password_strength') }}</small>
                            </div>
                        </div>

                        <!-- Confirm Password Field -->
                        <div class="col-md-6 mb-3">
                            <label for="password-confirm" class="form-label fw-semibold">{{ __('ui.auth.confirm_password') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-lock text-primary"></i>
                                </span>
                                <input type="password" 
                                       class="form-control border-start-0" 
                                       id="password-confirm" 
                                       name="password_confirmation" 
                                       placeholder="{{ __('ui.auth.confirm_password_placeholder') }}"
                                       required>
                                <button class="btn btn-outline-secondary border-start-0 bg-white" 
                                        type="button" 
                                        onclick="togglePassword('password-confirm')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="passwordMatchWarning" class="text-danger small mt-1" style="display: none;">
                                <i class="fas fa-times-circle"></i> {{ __('ui.auth.password_mismatch') }}
                            </div>
                            <div id="passwordMatchSuccess" class="text-success small mt-1" style="display: none;">
                                <i class="fas fa-check-circle"></i> {{ __('ui.auth.password_match') }}
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                            <label class="form-check-label text-muted" for="terms">
                                {{ __('ui.auth.terms_agreement') }}
                            </label>
                        </div>
                    </div>

                    <!-- Register Button -->
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary btn-lg fw-semibold py-3" 
                                id="registerBtn"
                                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                            <i class="fas fa-user-plus me-2"></i>{{ __('ui.auth.sign_up') }}
                        </button>
                    </div>

                    <!-- Login Link -->
                    <div class="text-center">
                        <p class="text-muted mb-0">
                            {{ __('ui.auth.have_account') }} 
                            <a href="{{ route('login', [], false) }}" class="text-primary fw-bold text-decoration-none">
                                {{ __('ui.auth.sign_in') }} <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Side - Image/Info -->
        <div class="col-lg-5 d-none d-lg-block" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="h-100 d-flex flex-column align-items-center justify-content-center text-white p-5">
                <i class="fas fa-notes-medical fa-6x mb-4"></i>
                <h2 class="fw-bold mb-3">{{ __('ui.auth.right_side_register_title') }}</h2>
                <ul class="list-unstyled" style="max-width: 400px;">
                    <li class="mb-3 d-flex align-items-center">
                        <i class="fas fa-check-circle me-3 fa-lg"></i>
                        <span>{{ __('ui.auth.feature_health_monitoring') }}</span>
                    </li>
                    <li class="mb-3 d-flex align-items-center">
                        <i class="fas fa-check-circle me-3 fa-lg"></i>
                        <span>{{ __('ui.auth.feature_medicine_reminder') }}</span>
                    </li>
                    <li class="mb-3 d-flex align-items-center">
                        <i class="fas fa-check-circle me-3 fa-lg"></i>
                        <span>{{ __('ui.auth.feature_secure_records') }}</span>
                    </li>
                    <li class="mb-3 d-flex align-items-center">
                        <i class="fas fa-check-circle me-3 fa-lg"></i>
                        <span>{{ __('ui.auth.feature_real_time_suggestions') }}</span>
                    </li>
                    <li class="mb-3 d-flex align-items-center">
                        <i class="fas fa-check-circle me-3 fa-lg"></i>
                        <span>{{ __('ui.auth.feature_community_support') }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Toggle password visibility
    function togglePassword(fieldId) {
        const passwordInput = document.getElementById(fieldId);
        const icon = passwordInput.parentElement.querySelector('button i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Password strength checker
    function checkPasswordStrength(password) {
        let strength = 0;
        let message = '';
        let color = '';
        
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]+/)) strength++;
        if (password.match(/[A-Z]+/)) strength++;
        if (password.match(/[0-9]+/)) strength++;
        if (password.match(/[$@#&!]+/)) strength++;
        
        switch(strength) {
            case 0:
            case 1:
                message = '{{ __("ui.auth.password_very_weak") }}';
                color = '#dc3545';
                break;
            case 2:
                message = '{{ __("ui.auth.password_weak") }}';
                color = '#ffc107';
                break;
            case 3:
                message = '{{ __("ui.auth.password_fair") }}';
                color = '#17a2b8';
                break;
            case 4:
                message = '{{ __("ui.auth.password_good") }}';
                color = '#28a745';
                break;
            case 5:
                message = '{{ __("ui.auth.password_strong") }}';
                color = '#28a745';
                break;
        }
        
        const percentage = (strength / 5) * 100;
        const strengthBar = document.getElementById('passwordStrengthBar');
        const strengthText = document.getElementById('passwordStrengthText');
        
        if (strengthBar) {
            strengthBar.style.width = percentage + '%';
            strengthBar.style.backgroundColor = color;
        }
        if (strengthText) {
            strengthText.innerHTML = message;
            strengthText.style.color = color;
        }
    }

    // Check password match
    function checkPasswordMatch() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('password-confirm').value;
        const warning = document.getElementById('passwordMatchWarning');
        const success = document.getElementById('passwordMatchSuccess');
        const registerBtn = document.getElementById('registerBtn');
        
        if (confirmPassword.length > 0) {
            if (password === confirmPassword) {
                warning.style.display = 'none';
                success.style.display = 'block';
                registerBtn.disabled = false;
            } else {
                warning.style.display = 'block';
                success.style.display = 'none';
                registerBtn.disabled = true;
            }
        } else {
            warning.style.display = 'none';
            success.style.display = 'none';
            registerBtn.disabled = false;
        }
    }

    // Form validation before submit
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('password-confirm').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('{{ __("ui.auth.password_mismatch_alert") }}');
            return false;
        }
        
        if (password.length < 8) {
            e.preventDefault();
            alert('{{ __("ui.auth.password_min_length_alert", ["min" => 8]) }}');
            return false;
        }
        
        const terms = document.getElementById('terms');
        if (!terms.checked) {
            e.preventDefault();
            alert('{{ __("ui.auth.terms_required") }}');
            return false;
        }
        
        // Disable button to prevent double submission
        document.getElementById('registerBtn').disabled = true;
        document.getElementById('registerBtn').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>{{ __("ui.actions.loading") }}';
    });

    // Event listeners for password fields
    document.getElementById('password').addEventListener('input', function() {
        checkPasswordStrength(this.value);
        checkPasswordMatch();
    });
    
    document.getElementById('password-confirm').addEventListener('input', checkPasswordMatch);

    // BD Address API Integration
    document.addEventListener('DOMContentLoaded', async function() {
        const divisionSelect = document.getElementById('Division');
        const districtSelect = document.getElementById('District');
        const upazilaSelect = document.getElementById('Upazila');
        const divisionIdInput = document.getElementById('DivisionId');
        const divisionEnInput = document.getElementById('DivisionEn');
        const divisionBnInput = document.getElementById('DivisionBn');
        const districtIdInput = document.getElementById('DistrictId');
        const districtBnInput = document.getElementById('DistrictBn');
        const upazilaIdInput = document.getElementById('UpazilaId');
        const upazilaBnInput = document.getElementById('UpazilaBn');

        if (!divisionSelect || !districtSelect || !upazilaSelect) {
            return;
        }

        const locale = document.documentElement.lang?.startsWith('bn') ? 'bn' : 'en';
        const apiBase = '/geo/v2.0';
        const divisionPlaceholder = locale === 'bn' ? 'বিভাগ নির্বাচন করুন' : 'Select Division';
        const districtPlaceholder = locale === 'bn' ? 'জেলা নির্বাচন করুন' : 'Select District';
        const upazilaPlaceholder = locale === 'bn' ? 'উপজেলা নির্বাচন করুন' : 'Select Upazila';
        const currentDistrict = divisionSelect.dataset.currentDistrict || '';
        const currentUpazila = divisionSelect.dataset.currentUpazila || '';
        const currentDivisionId = divisionSelect.dataset.currentDivisionId || '';
        const currentDistrictId = divisionSelect.dataset.currentDistrictId || '';
        const currentUpazilaId = divisionSelect.dataset.currentUpazilaId || '';

        const localDivisions = [
            { id: 1, name: 'Chattogram', bn_name: 'চট্টগ্রাম' },
            { id: 2, name: 'Rajshahi', bn_name: 'রাজশাহী' },
            { id: 3, name: 'Khulna', bn_name: 'খুলনা' },
            { id: 4, name: 'Barishal', bn_name: 'বরিশাল' },
            { id: 5, name: 'Sylhet', bn_name: 'সিলেট' },
            { id: 6, name: 'Dhaka', bn_name: 'ঢাকা' },
            { id: 7, name: 'Rangpur', bn_name: 'রংপুর' },
            { id: 8, name: 'Mymensingh', bn_name: 'ময়মনসিংহ' },
        ];

        const localDistricts = [
            { id: 26, division_id: 6, name: 'Dhaka', bn_name: 'ঢাকা' },
            { id: 30, division_id: 6, name: 'Faridpur', bn_name: 'ফরিদপুর' },
            { id: 15, division_id: 1, name: 'Chattogram', bn_name: 'চট্টগ্রাম' },
            { id: 22, division_id: 1, name: 'Cumilla', bn_name: 'কুমিল্লা' },
            { id: 69, division_id: 2, name: 'Rajshahi', bn_name: 'রাজশাহী' },
            { id: 76, division_id: 2, name: 'Pabna', bn_name: 'পাবনা' },
            { id: 47, division_id: 3, name: 'Khulna', bn_name: 'খুলনা' },
            { id: 10, division_id: 3, name: 'Bagerhat', bn_name: 'বাগেরহাট' },
            { id: 6, division_id: 4, name: 'Barishal', bn_name: 'বরিশাল' },
            { id: 9, division_id: 4, name: 'Bhola', bn_name: 'ভোলা' },
            { id: 60, division_id: 5, name: 'Sylhet', bn_name: 'সিলেট' },
            { id: 64, division_id: 5, name: 'Sunamganj', bn_name: 'সুনামগঞ্জ' },
            { id: 85, division_id: 7, name: 'Rangpur', bn_name: 'রংপুর' },
            { id: 77, division_id: 7, name: 'Panchagarh', bn_name: 'পঞ্চগড়' },
            { id: 61, division_id: 8, name: 'Mymensingh', bn_name: 'ময়মনসিংহ' },
            { id: 39, division_id: 8, name: 'Jamalpur', bn_name: 'জামালপুর' },
        ];

        const localUpazilas = [
            { id: 8, district_id: 26, name: 'Dhanmondi', bn_name: 'ধানমন্ডি' },
            { id: 10, district_id: 26, name: 'Mirpur', bn_name: 'মিরপুর' },
            { id: 401, district_id: 30, name: 'Faridpur Sadar', bn_name: 'ফরিদপুর সদর' },
            { id: 91, district_id: 22, name: 'Kotwali', bn_name: 'কোতোয়ালি' },
            { id: 194, district_id: 15, name: 'Pahartali', bn_name: 'পাহাড়তলী' },
            { id: 501, district_id: 69, name: 'Rajshahi Sadar', bn_name: 'রাজশাহী সদর' },
            { id: 511, district_id: 76, name: 'Pabna Sadar', bn_name: 'পাবনা সদর' },
            { id: 521, district_id: 47, name: 'Khalishpur', bn_name: 'খালিশপুর' },
            { id: 531, district_id: 10, name: 'Bagerhat Sadar', bn_name: 'বাগেরহাট সদর' },
            { id: 541, district_id: 6, name: 'Barishal Sadar', bn_name: 'বরিশাল সদর' },
            { id: 551, district_id: 9, name: 'Bhola Sadar', bn_name: 'ভোলা সদর' },
            { id: 561, district_id: 64, name: 'Sunamganj Sadar', bn_name: 'সুনামগঞ্জ সদর' },
            { id: 571, district_id: 60, name: 'Sylhet Sadar', bn_name: 'সিলেট সদর' },
            { id: 581, district_id: 85, name: 'Rangpur Sadar', bn_name: 'রংপুর সদর' },
            { id: 591, district_id: 77, name: 'Panchagarh Sadar', bn_name: 'পঞ্চগড় সদর' },
            { id: 601, district_id: 61, name: 'Mymensingh Sadar', bn_name: 'ময়মনসিংহ সদর' },
            { id: 611, district_id: 39, name: 'Jamalpur Sadar', bn_name: 'জামালপুর সদর' },
        ];

        const toList = (payload) => Array.isArray(payload) ? payload : (payload?.data || payload?.result || []);
        const getId = (item) => item?.id ?? item?.division_id ?? item?.district_id ?? item?.upazila_id ?? null;
        const getEn = (item) => item?.name ?? item?.division ?? item?.district ?? item?.upazila ?? item?.upazilla ?? item?.name_en ?? '';
        const getBn = (item) => item?.bn_name ?? item?.bn ?? item?.name_bn ?? item?.bangla ?? '';
        const labelOf = (item) => locale === 'bn' ? (getBn(item) || getEn(item)) : getEn(item);
        const byDivision = (divisionId) => localDistricts.filter((d) => String(d.division_id) === String(divisionId));
        const byDistrict = (districtId) => localUpazilas.filter((u) => String(u.district_id) === String(districtId));

        const fetchRows = async (path, fallbackRows = []) => {
            try {
                const response = await fetch(`${apiBase}/${path}`, { headers: { 'Accept': 'application/json' } });
                if (!response.ok) {
                    return fallbackRows;
                }
                const payload = await response.json();
                const rows = toList(payload);
                return rows.length ? rows : fallbackRows;
            } catch (e) {
                return fallbackRows;
            }
        };

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
            const rows = await fetchRows(`upazilas/${districtId}`, byDistrict(districtId));
            renderUpazilas(rows, selectedUpazilaName, selectedUpazilaId);
        };

        try {
            const divisions = await fetchRows('divisions', localDivisions);

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
                const districtRows = await fetchRows(`districts/${selectedDivisionId}`, byDivision(selectedDivisionId));
                renderDistricts(districtRows, currentDistrict, currentDistrictId, currentUpazila, currentUpazilaId);
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

                const districts = await fetchRows(`districts/${divisionId}`, byDivision(divisionId));
                renderDistricts(districts, '', '', '', '');
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
            console.error('Failed to load BD address API for registration form', error);
        }
    });
</script>

<style>
    .password-strength {
        font-size: 0.75rem;
    }
    .progress {
        background-color: #e9ecef;
        border-radius: 2px;
    }
    .progress-bar {
        transition: width 0.3s ease;
    }
    input:focus {
        box-shadow: none !important;
        border-color: #667eea !important;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
</style>
@endpush
@endsection