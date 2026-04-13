{{-- resources/views/medicine/add.blade.php --}}
@extends('layouts.app')

@section('title', 'Add Medicine')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('medicine.index') }}" class="text-decoration-none">Medicine</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('medicine.my-medicines') }}" class="text-decoration-none">My Medicines</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add New Medicine</li>
                </ol>
            </nav>

            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add New Medicine</h4>
                </div>
                
                <div class="card-body p-4">
                    <form action="{{ route('medicine.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="medicine_name" class="form-label fw-bold">
                                <i class="fas fa-capsules me-2 text-primary"></i>Medicine Name
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('medicine_name') is-invalid @enderror" 
                                   id="medicine_name" 
                                   name="medicine_name" 
                                   value="{{ old('medicine_name') }}" 
                                   placeholder="e.g., Napa Extra, Metformin, etc."
                                   required>
                            @error('medicine_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="type" class="form-label fw-bold">
                                    <i class="fas fa-tag me-2 text-primary"></i>Medicine Type
                                </label>
                                <select class="form-select form-select-lg @error('type') is-invalid @enderror" 
                                        id="type" name="type">
                                    <option value="">Select Type (Optional)</option>
                                    <option value="tablet" {{ old('type') == 'tablet' ? 'selected' : '' }}>Tablet</option>
                                    <option value="capsule" {{ old('type') == 'capsule' ? 'selected' : '' }}>Capsule</option>
                                    <option value="syrup" {{ old('type') == 'syrup' ? 'selected' : '' }}>Syrup</option>
                                    <option value="injection" {{ old('type') == 'injection' ? 'selected' : '' }}>Injection</option>
                                    <option value="drops" {{ old('type') == 'drops' ? 'selected' : '' }}>Drops</option>
                                    <option value="cream" {{ old('type') == 'cream' ? 'selected' : '' }}>Cream</option>
                                    <option value="inhaler" {{ old('type') == 'inhaler' ? 'selected' : '' }}>Inhaler</option>
                                    <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="rule" class="form-label fw-bold">
                                    <i class="fas fa-clock me-2 text-primary"></i>When to Take
                                </label>
                                <select class="form-select form-select-lg @error('rule') is-invalid @enderror" 
                                        id="rule" name="rule">
                                    <option value="">Select Rule (Optional)</option>
                                    <option value="before_food" {{ old('rule') == 'before_food' ? 'selected' : '' }}>Before Food</option>
                                    <option value="after_food" {{ old('rule') == 'after_food' ? 'selected' : '' }}>After Food</option>
                                    <option value="with_food" {{ old('rule') == 'with_food' ? 'selected' : '' }}>With Food</option>
                                    <option value="before_sleep" {{ old('rule') == 'before_sleep' ? 'selected' : '' }}>Before Sleep</option>
                                    <option value="anytime" {{ old('rule') == 'anytime' ? 'selected' : '' }}>Anytime</option>
                                </select>
                                @error('rule')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <label for="value_per_dose" class="form-label fw-bold">
                                    <i class="fas fa-weight me-2 text-primary"></i>Value Per Dose
                                </label>
                                <input type="number" step="0.01" min="0" 
                                       class="form-control form-control-lg @error('value_per_dose') is-invalid @enderror" 
                                       id="value_per_dose" name="value_per_dose" 
                                       value="{{ old('value_per_dose') }}" 
                                       placeholder="e.g., 500">
                                @error('value_per_dose')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-4">
                                <label for="unit" class="form-label fw-bold">
                                    <i class="fas fa-flask me-2 text-primary"></i>Unit
                                </label>
                                <select class="form-select form-select-lg @error('unit') is-invalid @enderror" 
                                        id="unit" name="unit">
                                    <option value="">Select Unit (Optional)</option>
                                    <option value="mg" {{ old('unit') == 'mg' ? 'selected' : '' }}>mg (Milligram)</option>
                                    <option value="ml" {{ old('unit') == 'ml' ? 'selected' : '' }}>ml (Milliliter)</option>
                                    <option value="mcg" {{ old('unit') == 'mcg' ? 'selected' : '' }}>mcg (Microgram)</option>
                                    <option value="g" {{ old('unit') == 'g' ? 'selected' : '' }}>g (Gram)</option>
                                    <option value="IU" {{ old('unit') == 'IU' ? 'selected' : '' }}>IU (International Unit)</option>
                                    <option value="tablet" {{ old('unit') == 'tablet' ? 'selected' : '' }}>Tablet(s)</option>
                                    <option value="capsule" {{ old('unit') == 'capsule' ? 'selected' : '' }}>Capsule(s)</option>
                                    <option value="drop" {{ old('unit') == 'drop' ? 'selected' : '' }}>Drop(s)</option>
                                    <option value="puff" {{ old('unit') == 'puff' ? 'selected' : '' }}>Puff(s)</option>
                                </select>
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-4">
                                <label for="dose_limit" class="form-label fw-bold">
                                    <i class="fas fa-ban me-2 text-primary"></i>Daily Dose Limit
                                </label>
                                <input type="number" class="form-control form-control-lg @error('dose_limit') is-invalid @enderror" 
                                    id="dose_limit" name="dose_limit" value="{{ old('dose_limit') }}" 
                                    placeholder="Optional - max per day" min="1">
                                <small class="text-muted">Leave empty for no limit</small>
                                @error('dose_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="alert alert-info bg-light border-0 rounded-3 p-3 mb-4">
                            <div class="d-flex">
                                <i class="fas fa-info-circle fa-2x text-info me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Next Steps</h6>
                                    <p class="mb-0 small">After adding the medicine, you can set up a schedule with reminder times. This will help you never miss a dose!</p>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-3">
                            <a href="{{ route('medicine.my-medicines') }}" class="btn btn-outline-secondary btn-lg flex-grow-1">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                                <i class="fas fa-save me-2"></i>Save Medicine
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection