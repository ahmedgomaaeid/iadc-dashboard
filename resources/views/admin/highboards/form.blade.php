@extends('layouts.admin-dashboard')

@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ isset($highboard) ? 'Edit Highboard Member' : 'Create Highboard Member' }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.highboards.index') }}">Highboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    {{ isset($highboard) ? 'Edit' : 'Create' }}
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ isset($highboard) ? 'Edit Highboard Member' : 'New Highboard Member' }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ isset($highboard) ? route('admin.highboards.update', $highboard) : route('admin.highboards.store') }}" 
                          method="POST">
                        @csrf
                        @if(isset($highboard))
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $highboard->name ?? '') }}" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $highboard->email ?? '') }}" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone', $highboard->phone ?? '') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="field_id" class="form-label">Field <span class="text-danger">*</span></label>
                                <select class="form-select @error('field_id') is-invalid @enderror" 
                                        id="field_id" 
                                        name="field_id" 
                                        required>
                                    <option value="">Select a field</option>
                                    @foreach($fields as $field)
                                        <option value="{{ $field->id }}" 
                                                {{ old('field_id', $highboard->field_id ?? '') == $field->id ? 'selected' : '' }}>
                                            {{ $field->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('field_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                Password 
                                @if(!isset($highboard))
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password"
                                   {{ !isset($highboard) ? 'required' : '' }}>
                            @if(isset($highboard))
                                <small class="text-muted">Leave blank to keep current password</small>
                            @else
                                <small class="text-muted">Minimum 8 characters</small>
                            @endif
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $highboard->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                            <small class="text-muted">Inactive members will not be able to log in</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fe fe-save me-2"></i>{{ isset($highboard) ? 'Update' : 'Create' }} Highboard Member
                            </button>
                            <a href="{{ route('admin.highboards.index') }}" class="btn btn-secondary">
                                <i class="fe fe-x me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
