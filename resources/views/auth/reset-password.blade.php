{{-- resources/views/auth/reset-password.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password - {{ config('app.name', 'My Doctor') }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        .wide-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .wide-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .wide-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 50px rgba(0, 0, 0, 0.15);
        }
        
        .card-body-custom {
            padding: 3rem;
        }
        
        @media (max-width: 768px) {
            .wide-container {
                padding: 1rem;
            }
            .card-body-custom {
                padding: 2rem 1.5rem;
            }
            .wide-card {
                max-width: 100%;
            }
        }
        
        .logo {
            max-height: 70px;
            width: auto;
            margin-bottom: 1.5rem;
        }
        
        .input-group-custom {
            margin-bottom: 1.5rem;
        }
        
        .input-group-custom .input-group-text {
            background: white;
            border-right: none;
            padding: 0.75rem 1rem;
        }
        
        .input-group-custom .form-control {
            border-left: none;
            padding: 0.75rem 1rem;
            font-size: 1rem;
        }
        
        .input-group-custom .form-control:focus {
            border-color: #667eea;
            box-shadow: none;
        }
        
        .input-group-custom:focus-within {
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.25);
            border-radius: 8px;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.875rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            background: linear-gradient(135deg, #5a6fd6 0%, #6a4190 100%);
        }
        
        .btn-gradient:disabled {
            opacity: 0.7;
            transform: none;
        }
        
        .alert-custom {
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .password-match-success {
            color: #28a745;
            font-size: 0.75rem;
            margin-top: 0.25rem;
            display: none;
        }
        
        .password-match-error {
            color: #dc3545;
            font-size: 0.75rem;
            margin-top: 0.25rem;
            display: none;
        }
        
        h4 {
            font-size: 1.75rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        
        .text-muted-custom {
            color: #6c757d;
            font-size: 0.95rem;
        }
        
        label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .btn-show-password {
            border-left: none;
            background: white;
            border-color: #ced4da;
        }
        
        .btn-show-password:hover {
            background: #f8f9fa;
        }
        
        .btn-show-password:focus {
            box-shadow: none;
        }
    </style>
</head>
<body>
    <div class="wide-container">
        <div class="wide-card">
            <div class="card-body-custom">
                <div class="text-center mb-4">
                    <img src="{{ asset('images/logos/applogo.png') }}" alt="{{ config('app.name') }}" class="logo">
                    <h4>Create New Password</h4>
                    <p class="text-muted-custom">Please enter your new password below</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <ul class="mb-0 mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" id="resetPasswordForm">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-3">
                        <label for="email">Email Address</label>
                        <div class="input-group input-group-custom">
                            <span class="input-group-text">
                                <i class="fas fa-envelope text-primary"></i>
                            </span>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ $email ?? old('email') }}" 
                                   placeholder="Enter your email address"
                                   readonly
                                   required>
                        </div>
                        @error('email')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password">New Password</label>
                        <div class="input-group input-group-custom">
                            <span class="input-group-text">
                                <i class="fas fa-lock text-primary"></i>
                            </span>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter new password"
                                   required>
                            <button class="btn btn-outline-secondary btn-show-password" 
                                    type="button" 
                                    onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation">Confirm Password</label>
                        <div class="input-group input-group-custom">
                            <span class="input-group-text">
                                <i class="fas fa-lock text-primary"></i>
                            </span>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   placeholder="Confirm your new password"
                                   required>
                            <button class="btn btn-outline-secondary btn-show-password" 
                                    type="button" 
                                    onclick="togglePassword('password_confirmation')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="passwordMatchSuccess" class="password-match-success">
                            <i class="fas fa-check-circle"></i> Passwords match!
                        </div>
                        <div id="passwordMatchError" class="password-match-error">
                            <i class="fas fa-times-circle"></i> Passwords do not match
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-gradient" id="resetPasswordBtn">
                            <i class="fas fa-key me-2"></i>Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const icon = passwordInput.parentElement.querySelector('.btn-show-password i');
            
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
        
        // Check password match only (no complexity)
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            const successMsg = document.getElementById('passwordMatchSuccess');
            const errorMsg = document.getElementById('passwordMatchError');
            const resetBtn = document.getElementById('resetPasswordBtn');
            
            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    successMsg.style.display = 'block';
                    errorMsg.style.display = 'none';
                    resetBtn.disabled = false;
                } else {
                    successMsg.style.display = 'none';
                    errorMsg.style.display = 'block';
                    resetBtn.disabled = true;
                }
            } else {
                successMsg.style.display = 'none';
                errorMsg.style.display = 'none';
                resetBtn.disabled = false;
            }
        }
        
        // Form validation before submit
        document.getElementById('resetPasswordForm')?.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match. Please check your password confirmation.');
                return false;
            }
            
            const resetBtn = document.getElementById('resetPasswordBtn');
            resetBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Resetting Password...';
            resetBtn.disabled = true;
        });
        
        // Event listener for password match check
        document.getElementById('password')?.addEventListener('input', checkPasswordMatch);
        document.getElementById('password_confirmation')?.addEventListener('input', checkPasswordMatch);
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                setTimeout(function() {
                    bsAlert.close();
                }, 5000);
            });
        }, 1000);
    </script>
</body>
</html>