@extends('layouts.app')

@section('title', 'Register - My Doctor')

@section('content')
<div class="container-fluid px-0">
    <div class="row g-0 min-vh-100">
        <!-- Left Side - Form -->
        <div class="col-lg-7 d-flex align-items-center justify-content-center p-5" style="background: #ffffff;">
            <div class="w-100" style="max-width: 700px;">
                <div class="text-center mb-4">
                    <img src="{{ asset('images/logos/applogo.jpg') }}" alt="My Doctor" height="60" class="mb-3">
                    <h2 class="fw-bold text-primary">Create Account</h2>
                    <p class="text-muted">Join My Doctor today and take control of your health</p>
                </div>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

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
                                I agree to the <a href="#" class="text-primary text-decoration-none">Terms of Service</a> and 
                                <a href="#" class="text-primary text-decoration-none">Privacy Policy</a>
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
                            <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-none">
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
</script>
@endpush