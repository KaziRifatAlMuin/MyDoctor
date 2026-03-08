@extends('layouts.app')

@section('title', 'My Prescriptions - My Doctor')

@section('content')
    <div class="container py-5">
        <div class="text-center mb-4">
            <h1 class="display-6">Prescriptions</h1>
            <p class="text-secondary">Manage your uploaded prescriptions and view details here.</p>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <p class="mb-0">You have no prescriptions yet.</p>
                <div class="mt-3">
                    <a href="{{ route('medicine') }}" class="btn btn-primary">Back to Medicine</a>
                    <a href="{{ route('health.upload') }}" class="btn btn-outline-secondary">Upload Prescription</a>
                </div>
            </div>
        </div>
    </div>
@endsection
