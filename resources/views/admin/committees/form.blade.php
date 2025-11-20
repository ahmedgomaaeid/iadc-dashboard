@extends('layouts.admin-dashboard')

@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ isset($committee) ? 'Edit Committee' : 'Create Committee' }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.committees.index') }}">Committees</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    {{ isset($committee) ? 'Edit' : 'Create' }}
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ isset($committee) ? 'Edit Committee' : 'New Committee' }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ isset($committee) ? route('admin.committees.update', $committee) : route('admin.committees.store') }}" 
                          method="POST">
                        @csrf
                        @if(isset($committee))
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="name" class="form-label">Committee Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $committee->name ?? '') }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="field_id" class="form-label">Field <span class="text-danger">*</span></label>
                            <select class="form-select @error('field_id') is-invalid @enderror" 
                                    id="field_id" 
                                    name="field_id" 
                                    required>
                                <option value="">Select a field</option>
                                @foreach($fields as $field)
                                    <option value="{{ $field->id }}" 
                                            {{ old('field_id', $committee->field_id ?? '') == $field->id ? 'selected' : '' }}>
                                        {{ $field->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('field_id')
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
                                       {{ old('is_active', $committee->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                            <small class="text-muted">Inactive committees will not be visible to users</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fe fe-save me-2"></i>{{ isset($committee) ? 'Update' : 'Create' }} Committee
                            </button>
                            <a href="{{ route('admin.committees.index') }}" class="btn btn-secondary">
                                <i class="fe fe-x me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
