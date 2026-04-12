{{-- resources/views/auth/verify-email.blade.php --}}
@extends('layouts.app')

@section('title', 'Verify Email')

@section('content')
<div class="container">
    <div class="row justify-content-center min-vh-100 align-items-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow border-0 rounded-4">
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-4 d-inline-block">
                            <i class="fas fa-envelope fa-4x text-warning"></i>
                        </div>
                    </div>

                    <h4 class="fw-bold mb-3">Verify Your Email Address</h4>
                    
                    <p class="text-muted mb-4">
                        A verification link was sent to <strong>{{ auth()->user()->email }}</strong>.
                        Please open that email and click the verification link before logging in.<br>
                        <span class="text-danger">Check the spam folder if you don't see it in your inbox.</span>
                    </p>

                    @if (session('status') || session('success'))
                        <div class="alert alert-success mb-4" role="alert">
                            {{ session('status') ?? session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg py-3">
                                <i class="fas fa-redo-alt me-2"></i>Resend Verification Email
                            </button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-link text-decoration-none">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection