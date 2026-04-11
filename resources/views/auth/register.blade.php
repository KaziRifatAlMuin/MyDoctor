@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="container-fluid px-0">
    <div class="row g-0 min-vh-100">
        <!-- Left Side - Form -->
        <div class="col-lg-7 d-flex align-items-center justify-content-center p-5" style="background: #ffffff;">
            <div class="w-100" style="max-width: 700px;">
                <div class="text-center mb-4">
                    <img src="{{ asset('images/logos/applogo.png') }}" alt="My Doctor" height="60" class="mb-3">
                    <h2 class="fw-bold text-primary">Create Account</h2>
                    <p class="text-muted">Join My Doctor today and take control of your health</p>
                </div>

                <form method="POST" action="{{ route('register', [], false) }}">
                    @csrf
                    <input type="hidden" name="redirect" value="{{ request('redirect') }}">

                    <div class="row">
                        <!-- Name Field -->
                        <div class="col-md-6 mb-3">
                            <label for="Name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-user text-primary"></i>
                                </span>
                                <input type="text" 
                                       class="form-control border-start-0 @error('Name') is-invalid @enderror" 
                                       id="Name" 
                                       name="Name" 
                                       value="{{ old('Name') }}" 
                                       placeholder="Enter your full name"
                                       required>
                            </div>
                            @error('Name')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Email Field -->
                        <div class="col-md-6 mb-3">
                            <label for="Email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-envelope text-primary"></i>
                                </span>
                                <input type="email" 
                                       class="form-control border-start-0 @error('Email') is-invalid @enderror" 
                                       id="Email" 
                                       name="Email" 
                                       value="{{ old('Email') }}" 
                                       placeholder="Enter your email"
                                       required>
                            </div>
                            @error('Email')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Phone Field -->
                        <div class="col-md-6 mb-3">
                            <label for="Phone" class="form-label fw-semibold">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-phone text-primary"></i>
                                </span>
                                <input type="text" 
                                       class="form-control border-start-0 @error('Phone') is-invalid @enderror" 
                                       id="Phone" 
                                       name="Phone" 
                                       value="{{ old('Phone') }}" 
                                       placeholder="Enter your phone number">
                            </div>
                            @error('Phone')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Date of Birth Field -->
                        <div class="col-md-6 mb-3">
                            <label for="DateOfBirth" class="form-label fw-semibold">Date of Birth</label>
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
                            <label for="Occupation" class="form-label fw-semibold">Occupation</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-briefcase text-primary"></i>
                                </span>
                                <input type="text" 
                                       class="form-control border-start-0 @error('Occupation') is-invalid @enderror" 
                                       id="Occupation" 
                                       name="Occupation" 
                                       value="{{ old('Occupation') }}" 
                                       placeholder="Enter your occupation">
                            </div>
                            @error('Occupation')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Blood Group Field -->
                        <div class="col-md-6 mb-3">
                            <label for="BloodGroup" class="form-label fw-semibold">Blood Group</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-tint text-primary"></i>
                                </span>
                                <select class="form-control border-start-0 @error('BloodGroup') is-invalid @enderror" 
                                        id="BloodGroup" 
                                        name="BloodGroup">
                                    <option value="">Select Blood Group</option>
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

                        <div class="col-md-6 mb-3">
                            <label for="Gender" class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-person text-primary"></i>
                                </span>
                                <select class="form-control border-start-0 @error('Gender') is-invalid @enderror" id="Gender" name="Gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('Gender') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('Gender') == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('Gender') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            @error('Gender')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            @php
                                $isBnLocale = str_starts_with((string) app()->getLocale(), 'bn');
                            @endphp
                            <label for="Division" class="form-label fw-semibold">Division <span class="text-danger">*</span></label>
                            <select id="Division" class="form-control @error('Division') is-invalid @enderror" data-current-division-id="{{ old('DivisionId') }}" data-current-district-id="{{ old('DistrictId') }}" data-current-upazila-id="{{ old('UpazilaId') }}" data-current-district="{{ old('District') }}" data-current-upazila="{{ old('Upazila') }}" required>
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

                        <div class="col-md-6 mb-3">
                            <label for="District" class="form-label fw-semibold">District <span class="text-danger">*</span></label>
                            <select id="District" name="District" class="form-control @error('District') is-invalid @enderror" required>
                                <option value="{{ old('District') }}">{{ old('District') ? ($isBnLocale ? (old('DistrictBn') ?: old('District')) : old('District')) : ($isBnLocale ? 'জেলা নির্বাচন করুন' : 'Select District') }}</option>
                            </select>
                            <input type="hidden" id="DistrictId" name="DistrictId" value="{{ old('DistrictId') }}">
                            <input type="hidden" id="DistrictBn" name="DistrictBn" value="{{ old('DistrictBn') }}">
                            @error('District')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="Upazila" class="form-label fw-semibold">Upazila <span class="text-danger">*</span></label>
                            <select id="Upazila" name="Upazila" class="form-control @error('Upazila') is-invalid @enderror" required>
                                <option value="{{ old('Upazila') }}">{{ old('Upazila') ? ($isBnLocale ? (old('UpazilaBn') ?: old('Upazila')) : old('Upazila')) : ($isBnLocale ? 'উপজেলা নির্বাচন করুন' : 'Select Upazila') }}</option>
                            </select>
                            <input type="hidden" id="UpazilaId" name="UpazilaId" value="{{ old('UpazilaId') }}">
                            <input type="hidden" id="UpazilaBn" name="UpazilaBn" value="{{ old('UpazilaBn') }}">
                            @error('Upazila')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="Street" class="form-label fw-semibold">Street</label>
                            <input type="text" class="form-control @error('Street') is-invalid @enderror" id="Street" name="Street" value="{{ old('Street') }}" placeholder="Street / Road">
                            @error('Street')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="House" class="form-label fw-semibold">House</label>
                            <input type="text" class="form-control @error('House') is-invalid @enderror" id="House" name="House" value="{{ old('House') }}" placeholder="House / Building">
                            @error('House')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Password Field -->
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-lock text-primary"></i>
                                </span>
                                <input type="password" 
                                       class="form-control border-start-0 @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Enter password"
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
                        </div>

                        <!-- Confirm Password Field -->
                        <div class="col-md-6 mb-3">
                            <label for="password-confirm" class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-lock text-primary"></i>
                                </span>
                                <input type="password" 
                                       class="form-control border-start-0" 
                                       id="password-confirm" 
                                       name="password_confirmation" 
                                       placeholder="Confirm password"
                                       required>
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                            <label class="form-check-label text-muted" for="terms">
                                I agree to the <a href="{{ route('terms.service', [], false) }}" target="_blank" rel="noopener" class="text-primary text-decoration-none">Terms of Service</a> and 
                                <a href="{{ route('privacy.policy', [], false) }}" target="_blank" rel="noopener" class="text-primary text-decoration-none">Privacy Policy</a>
                            </label>
                        </div>
                    </div>

                    <!-- Register Button -->
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary btn-lg fw-semibold py-3" 
                                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </button>
                    </div>

                    <!-- Login Link -->
                    <div class="text-center">
                        <p class="text-muted mb-0">
                            Already have an account? 
                            <a href="{{ route('login', [], false) }}" class="text-primary fw-bold text-decoration-none">
                                Login here <i class="fas fa-arrow-right ms-1"></i>
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
                <h2 class="fw-bold mb-3">Why Choose My Doctor?</h2>
                <ul class="list-unstyled" style="max-width: 400px;">
                    <li class="mb-3 d-flex align-items-center">
                        <i class="fas fa-check-circle me-3 fa-lg"></i>
                        <span>Health Monitoring </span>
                    </li>
                    <li class="mb-3 d-flex align-items-center">
                        <i class="fas fa-check-circle me-3 fa-lg"></i>
                        <span>Medicine Remainder</span>
                    </li>
                    <li class="mb-3 d-flex align-items-center">
                        <i class="fas fa-check-circle me-3 fa-lg"></i>
                        <span>Secure health records storage</span>
                    </li>
                    <li class="mb-3 d-flex align-items-center">
                        <i class="fas fa-check-circle me-3 fa-lg"></i>
                        <span>Real Time Health Suggestions</span>
                    </li>
                   
                </ul>
                
                
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword(fieldId) {
        const passwordInput = document.getElementById(fieldId);
        const toggleIcon = passwordInput.parentElement.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }

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
@endpush