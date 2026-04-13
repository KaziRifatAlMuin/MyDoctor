@extends('layouts.app')

@section('title', __('ui.auth.verify_email_title'))

@section('content')
<style>
    .verify-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }
    
    .verify-card {
        max-width: 700px !important;
        width: 100% !important;
        min-width: 500px !important;
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        border: none;
    }
    
    .verify-card-body {
        padding: 3rem !important;
    }
    
    .verify-icon {
        width: 100px;
        height: 100px;
        background: rgba(255, 193, 7, 0.1);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
    }
    
    .verify-icon i {
        font-size: 48px;
        color: #ffc107;
    }
    
    .verify-title {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 1rem;
        color: #1a1a1a;
    }
    
    .verify-message {
        font-size: 16px;
        color: #6c757d;
        margin-bottom: 1rem;
        line-height: 1.6;
    }
    
    .verify-email {
        font-weight: 700;
        color: #667eea;
        background: rgba(102, 126, 234, 0.1);
        padding: 4px 12px;
        border-radius: 20px;
        display: inline-block;
    }
    
    .spam-warning {
        color: #dc3545;
        font-size: 14px;
        margin-top: 0.5rem;
    }
    
    .btn-resend {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 14px 30px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 10px;
        width: 100%;
        transition: all 0.3s ease;
    }
    
    .btn-resend:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .btn-logout {
        color: #6c757d;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s ease;
        background: none;
        border: none;
        cursor: pointer;
    }
    
    .btn-logout:hover {
        color: #667eea;
        text-decoration: underline;
    }
    
    .alert-custom {
        border-radius: 10px;
        padding: 12px 20px;
        margin-bottom: 1.5rem;
    }
    
    @media (max-width: 768px) {
        .verify-card {
            min-width: auto !important;
            max-width: 95% !important;
        }
        
        .verify-card-body {
            padding: 2rem !important;
        }
        
        .verify-title {
            font-size: 24px;
        }
    }
</style>

<div class="verify-container">
    <div class="verify-card">
        <div class="verify-card-body text-center">
            <div class="verify-icon">
                <i class="fas fa-envelope"></i>
            </div>

            <h4 class="verify-title">{{ __('ui.auth.verify_email_heading') }}</h4>
            
            <p class="verify-message">
                {{ __('ui.auth.verification_link_sent_first_part') }}
                <br>
                <span class="verify-email">{{ auth()->user()->email }}</span>
            </p>
            
            <p class="verify-message">
                {{ __('ui.auth.verification_link_sent_second_part') }}
            </p>
            
            <p class="spam-warning">
                <i class="fas fa-exclamation-triangle me-1"></i>
                {{ __('ui.auth.check_spam') }}
            </p>

            @if (session('status') || session('success'))
                <div class="alert alert-success alert-custom" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('status') ?? session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('verification.resend') }}">
                @csrf
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-resend">
                        <i class="fas fa-redo-alt me-2"></i>{{ __('ui.auth.resend_verification') }}
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="fas fa-sign-out-alt me-1"></i>{{ __('ui.auth.logout') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection