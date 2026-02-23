@extends('layouts.app')

@section('title', 'Login - My Doctor')

@section('content')
<div class="container-fluid px-0">
    <div class="row g-0 min-vh-100">
        <!-- Left Side - Form -->
        <div class="col-lg-6 d-flex align-items-center justify-content-center p-5" style="background: #ffffff;">
            <div class="w-100" style="max-width: 500px;">
                <div class="text-center mb-5">
                    <img src="{{ asset('images/logos/applogo.jpg') }}" alt="My Doctor" height="60" class="mb-3">
                    <h2 class="fw-bold text-primary">Welcome Back</h2>
                    <p class="text-muted">Please login to your account</p>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Field -->
                    <div class="mb-4">
                        <label for="Email" class="form-label fw-semibold">Email Address</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-envelope text-primary"></i>
                            </span>
                            <input type="email" 
                                   class="form-control border-start-0 @error('Email') is-invalid @enderror" 
                                   id="Email" 
                                   name="Email" 
                                   value="{{ old('Email') }}" 
                                   placeholder="Enter your email"
                                   required 
                                   autofocus>
                        </div>
                        @error('Email')
                            <span class="text-danger small" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-lock text-primary"></i>
                            </span>
                            <input type="password" 
                                   class="form-control border-start-0 @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter your password"
                                   required>
                            <button class="btn btn-outline-secondary border-start-0 bg-white" 
                                    type="button" 
                                    onclick="togglePassword()">
                                <i class="fas fa-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <span class="text-danger small" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" 
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label text-muted" for="remember">
                                Remember Me
                            </label>
                        </div>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-decoration-none text-primary">
                                Forgot Password?
                            </a>
                        @endif
                    </div>

                    <!-- Login Button -->
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary btn-lg fw-semibold py-3" 
                                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </div>

                    <!-- Social Login -->
                    <div class="text-center mb-4">
                        <p class="text-muted mb-3">Or continue with</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="#" class="btn btn-outline-danger btn-lg rounded-circle p-3" style="width: 60px; height: 60px;">
                                <i class="fab fa-google fa-lg"></i>
                            </a>
                            <a href="#" class="btn btn-outline-primary btn-lg rounded-circle p-3" style="width: 60px; height: 60px;">
                                <i class="fab fa-facebook-f fa-lg"></i>
                            </a>
                            <a href="#" class="btn btn-outline-info btn-lg rounded-circle p-3" style="width: 60px; height: 60px;">
                                <i class="fab fa-twitter fa-lg"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Register Link -->
                    <div class="text-center">
                        <p class="text-muted mb-0">
                            Don't have an account? 
                            <a href="{{ route('register') }}" class="text-primary fw-bold text-decoration-none">
                                Create Account <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Side - Image/Info -->
        <div class="col-lg-6 d-none d-lg-block" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="h-100 d-flex flex-column align-items-center justify-content-center text-white p-5">
                <i class="fas fa-heartbeat fa-6x mb-4"></i>
                <h2 class="fw-bold mb-3">Your Health, Our Priority</h2>
                <p class="text-center mb-4" style="max-width: 500px; opacity: 0.9;">
                    Access quality healthcare services from the comfort of your home. 
                    
                </p>
                
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');
        
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