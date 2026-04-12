{{-- resources/views/auth/forgot-password.blade.php --}}
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
            max-width: 600px;  /* Increased from default 400px to 600px */
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
        
        .back-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .back-link:hover {
            color: #764ba2;
            transform: translateX(-3px);
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
    </style>
</head>
<body>
    <div class="wide-container">
        <div class="wide-card">
            <div class="card-body-custom">
                <div class="text-center mb-4">
                    <img src="{{ asset('images/logos/applogo.png') }}" alt="{{ config('app.name') }}" class="logo">
                    <h4>Reset Password</h4>
                    <p class="text-muted-custom">Enter your email to receive a password reset link</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        @foreach ($errors->all() as $error)
                            {{ $error }}
                        @endforeach
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm" novalidate>
                    @csrf

                    <div class="mb-4">
                        <label for="email">Email Address</label>
                        <div class="input-group input-group-custom">
                            <span class="input-group-text">
                                <i class="fas fa-envelope text-primary"></i>
                            </span>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   placeholder="Enter your registered email address"
                                   required 
                                   autofocus>
                        </div>
                        @error('email')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-gradient" id="sendResetLinkBtn">
                            <i class="fas fa-paper-plane me-2"></i>Send Password Reset Link
                        </button>
                    </div>

                    <div class="text-center">
                        <a href="{{ route('login') }}" class="back-link">
                            <i class="fas fa-arrow-left me-1"></i>Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('forgotPasswordForm');
            const submitBtn = document.getElementById('sendResetLinkBtn');
            const emailInput = document.getElementById('email');
            
            form.addEventListener('submit', function(e) {
                console.log('Form submit event triggered');
                
                const email = emailInput.value.trim();
                
                // Validate email
                if (!email) {
                    e.preventDefault();
                    alert('Please enter your email address.');
                    emailInput.focus();
                    return false;
                }
                
                // Basic email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    alert('Please enter a valid email address.');
                    emailInput.focus();
                    return false;
                }
                
                console.log('Validation passed, showing loading state');
                
                // Show loading state
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending reset link...';
                submitBtn.disabled = true;
                
                // Re-enable button after 10 seconds as fallback
                setTimeout(function() {
                    console.log('Fallback: re-enabling button');
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send Password Reset Link';
                    submitBtn.disabled = false;
                }, 10000);
                
                // Don't prevent default - let form submit normally
                console.log('Form submitting...');
            });
        });
        
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