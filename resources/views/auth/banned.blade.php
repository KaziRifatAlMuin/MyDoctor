@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center">
                        <h2 class="text-danger mb-3">You Are Banned</h2>
                        <p>Your account has been deactivated. Please contact support if you believe this is a mistake.</p>
                        <div class="mt-4">
                            <a href="{{ route('home') }}" class="btn btn-secondary">Return Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
