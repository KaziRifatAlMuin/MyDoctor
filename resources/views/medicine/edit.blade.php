@extends('layouts.app')

@section('title', 'Edit Medicine - My Doctor')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('medicine.index') }}" class="text-decoration-none">Medicine</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('medicine.my-medicines') }}" class="text-decoration-none">My Medicines</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Medicine</li>
                </ol>
            </nav>

            <!-- Form Card -->
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Medicine</h4>
                </div>
                
                <div class="card-body p-4">
                    <form action="{{ route('medicine.update', $medicine->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Medicine Name -->
                        <div class="mb-4">
                            <label for="medicine_name" class="form-label fw-bold">
                                <i class="fas fa-capsules me-2 text-primary"></i>Medicine Name
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('medicine_name') is-invalid @enderror" 
                                   id="medicine_name" 
                                   name="medicine_name" 
                                   value="{{ old('medicine_name', $medicine->medicine_name) }}" 
                                   required>
                            @error('medicine_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <!-- Type -->
                            <div class="col-md-6 mb-4">
                                <label for="type" class="form-label fw-bold">
                                    <i class="fas fa-tag me-2 text-primary"></i>Medicine Type
                                </label>
                                <select class="form-select form-select-lg @error('type') is-invalid @enderror" 
                                        id="type" name="type">
                                    <option value="">Select Type</option>
                                    <option value="tablet" {{ (old('type', $medicine->type) == 'tablet') ? 'selected' : '' }}>Tablet</option>
                                    <option value="capsule" {{ (old('type', $medicine->type) == 'capsule') ? 'selected' : '' }}>Capsule</option>
                                    <option value="syrup" {{ (old('type', $medicine->type) == 'syrup') ? 'selected' : '' }}>Syrup</option>
                                    <option value="injection" {{ (old('type', $medicine->type) == 'injection') ? 'selected' : '' }}>Injection</option>
                                    <option value="drops" {{ (old('type', $medicine->type) == 'drops') ? 'selected' : '' }}>Drops</option>
                                    <option value="cream" {{ (old('type', $medicine->type) == 'cream') ? 'selected' : '' }}>Cream</option>
                                    <option value="inhaler" {{ (old('type', $medicine->type) == 'inhaler') ? 'selected' : '' }}>Inhaler</option>
                                    <option value="other" {{ (old('type', $medicine->type) == 'other') ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Rule -->
                            <div class="col-md-6 mb-4">
                                <label for="rule" class="form-label fw-bold">
                                    <i class="fas fa-clock me-2 text-primary"></i>When to Take
                                </label>
                                <select class="form-select form-select-lg @error('rule') is-invalid @enderror" 
                                        id="rule" name="rule">
                                    <option value="">Select Rule</option>
                                    <option value="before_food" {{ (old('rule', $medicine->rule) == 'before_food') ? 'selected' : '' }}>Before Food</option>
                                    <option value="after_food" {{ (old('rule', $medicine->rule) == 'after_food') ? 'selected' : '' }}>After Food</option>
                                    <option value="with_food" {{ (old('rule', $medicine->rule) == 'with_food') ? 'selected' : '' }}>With Food</option>
                                    <option value="before_sleep" {{ (old('rule', $medicine->rule) == 'before_sleep') ? 'selected' : '' }}>Before Sleep</option>
                                    <option value="anytime" {{ (old('rule', $medicine->rule) == 'anytime') ? 'selected' : '' }}>Anytime</option>
                                </select>
                                @error('rule')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Value Per Dose -->
                            <div class="col-md-4 mb-4">
                                <label for="value_per_dose" class="form-label fw-bold">
                                    <i class="fas fa-weight me-2 text-primary"></i>Value Per Dose
                                </label>
                                <input type="number" step="0.01" min="0" 
                                       class="form-control form-control-lg @error('value_per_dose') is-invalid @enderror" 
                                       id="value_per_dose" name="value_per_dose" 
                                       value="{{ old('value_per_dose', $medicine->value_per_dose) }}" 
                                       required>
                                @error('value_per_dose')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Unit -->
                            <div class="col-md-4 mb-4">
                                <label for="unit" class="form-label fw-bold">
                                    <i class="fas fa-flask me-2 text-primary"></i>Unit
                                </label>
                                <select class="form-select form-select-lg @error('unit') is-invalid @enderror" 
                                        id="unit" name="unit" required>
                                    <option value="">Select Unit</option>
                                    <option value="mg" {{ (old('unit', $medicine->unit) == 'mg') ? 'selected' : '' }}>mg</option>
                                    <option value="ml" {{ (old('unit', $medicine->unit) == 'ml') ? 'selected' : '' }}>ml</option>
                                    <option value="mcg" {{ (old('unit', $medicine->unit) == 'mcg') ? 'selected' : '' }}>mcg</option>
                                    <option value="g" {{ (old('unit', $medicine->unit) == 'g') ? 'selected' : '' }}>g</option>
                                    <option value="IU" {{ (old('unit', $medicine->unit) == 'IU') ? 'selected' : '' }}>IU</option>
                                    <option value="tablet" {{ (old('unit', $medicine->unit) == 'tablet') ? 'selected' : '' }}>Tablet</option>
                                    <option value="capsule" {{ (old('unit', $medicine->unit) == 'capsule') ? 'selected' : '' }}>Capsule</option>
                                    <option value="drop" {{ (old('unit', $medicine->unit) == 'drop') ? 'selected' : '' }}>Drop</option>
                                    <option value="puff" {{ (old('unit', $medicine->unit) == 'puff') ? 'selected' : '' }}>Puff</option>
                                </select>
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Dose Limit -->
                            <div class="col-md-4 mb-4">
                                <label for="dose_limit" class="form-label fw-bold">
                                    <i class="fas fa-ban me-2 text-primary"></i>Daily Dose Limit
                                </label>
                                <input type="number" class="form-control form-control-lg @error('dose_limit') is-invalid @enderror" 
                                    id="dose_limit" name="dose_limit" 
                                    value="{{ old('dose_limit', $medicine->dose_limit) }}" 
                                    placeholder="Optional" min="1">
                                <small class="text-muted">Leave empty for no limit</small>
                                @error('dose_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-3">
                            <a href="{{ route('medicine.my-medicines') }}" class="btn btn-outline-secondary btn-lg flex-grow-1">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                                <i class="fas fa-save me-2"></i>Update Medicine
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection