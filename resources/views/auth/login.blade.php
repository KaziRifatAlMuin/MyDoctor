@extends('layouts.app')

@section('title', __('ui.auto.Login'))

@section('content')
<div class="container-fluid px-0">
    <div class="row g-0 min-vh-100">
        <!-- Left Side - Form -->
        <div class="col-lg-6 d-flex align-items-center justify-content-center p-5" style="background: #ffffff;">
            <div class="w-100" style="max-width: 500px;">
                <div class="text-center mb-5">
                    <img src="{{ asset('images/logos/applogo.png') }}" alt="My Doctor" height="60" class="mb-3">
                    <h2 class="fw-bold text-primary">{{ __('ui.auth.login_title') }}</h2>
                    <p class="text-muted">{{ __('ui.auth.login_subtitle') }}</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('ui.actions.close') }}"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        @foreach ($errors->all() as $error)
                            {{ $error }}
                        @endforeach
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('ui.actions.close') }}"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('login', [], false) }}" id="loginForm">
                    @csrf
                    <input type="hidden" name="redirect" value="{{ request('redirect') }}">

                    <!-- Email Field -->
                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold">{{ __('ui.auth.email') }}</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-envelope text-primary"></i>
                            </span>
                            <input type="email" 
                                   class="form-control border-start-0 @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   placeholder="{{ __('ui.auth.email') }}"
                                   required 
                                   autofocus>
                        </div>
                        @error('email')
                            <span class="text-danger small" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">{{ __('ui.auth.password') }}</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-lock text-primary"></i>
                            </span>
                            <input type="password" 
                                   class="form-control border-start-0 @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="{{ __('ui.auth.password') }}"
                                   required>
                            <button class="btn btn-outline-secondary border-start-0 bg-white" 
                                    type="button" 
                                    onclick="togglePassword()"
                                    title="{{ __('ui.actions.show') }}">
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
                                {{ __('ui.auth.remember_me') }}
                            </label>
                        </div>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request', [], false) }}" class="text-decoration-none text-primary">
                                {{ __('ui.auth.forgot_password') }}
                            </a>
                        @endif
                    </div>

                    <!-- Login Button -->
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary btn-lg fw-semibold py-3" 
                                id="loginBtn"
                                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                            <i class="fas fa-sign-in-alt me-2"></i>{{ __('ui.auth.sign_in') }}
                        </button>
                    </div>

                    <!-- Register Link -->
                    <div class="text-center">
                        <p class="text-muted mb-0">
                            {{ __('ui.auth.no_account') }}
                            <a href="{{ route('register', [], false) }}" class="text-primary fw-bold text-decoration-none">
                                {{ __('ui.auth.sign_up') }} <i class="fas fa-arrow-right ms-1"></i>
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
                <h2 class="fw-bold mb-3">{{ __('ui.auth.right_side_title') }}</h2><br>
                <h2 class="fw-bold mb-3">{{ __('ui.auth.right_side_title2') }}</h2>
                <p class="text-center mb-4" style="max-width: 500px; opacity: 0.9;">
                    {{ __('ui.auth.right_side_subtitle') }}
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

    // Add loading state to prevent double submission
    document.getElementById('loginForm')?.addEventListener('submit', function() {
        const btn = document.getElementById('loginBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>{{ __("ui.actions.loading") }}';
    });
</script>
@endpush