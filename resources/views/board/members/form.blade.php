@extends('layouts.board-dashboard')

@section('title', isset($member) ? 'Edit Member' : 'Create Member')

@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ isset($member) ? 'Edit Member' : 'Create Member' }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('board.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('board.members.index') }}">Members</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    {{ isset($member) ? 'Edit' : 'Create' }}
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ isset($member) ? 'Edit Member' : 'New Member' }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ isset($member) ? route('board.members.update', $member) : route('board.members.store') }}" 
                          method="POST">
                        @csrf
                        @if(isset($member))
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $member->name ?? '') }}" 
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
                                       value="{{ old('email', $member->email ?? '') }}" 
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
                                       value="{{ old('phone', $member->phone ?? '') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="university" class="form-label">University</label>
                                <input type="text" 
                                       class="form-control @error('university') is-invalid @enderror" 
                                       id="university" 
                                       name="university" 
                                       value="{{ old('university', $member->university ?? '') }}">
                                @error('university')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="faculty" class="form-label">Faculty</label>
                                <input type="text" 
                                       class="form-control @error('faculty') is-invalid @enderror" 
                                       id="faculty" 
                                       name="faculty" 
                                       value="{{ old('faculty', $member->faculty ?? '') }}">
                                @error('faculty')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="academic_year" class="form-label">Academic Year</label>
                                <input type="text" 
                                       class="form-control @error('academic_year') is-invalid @enderror" 
                                       id="academic_year" 
                                       name="academic_year" 
                                       value="{{ old('academic_year', $member->academic_year ?? '') }}">
                                @error('academic_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                Password 
                                @if(!isset($member))
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password"
                                   {{ !isset($member) ? 'required' : '' }}>
                            @if(isset($member))
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
                                       {{ old('is_active', $member->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                            <small class="text-muted">Inactive members will not be able to log in</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fe fe-info me-2"></i>
                            This member will be added to: <strong>{{ $board->committee->name ?? 'your committee' }}</strong>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fe fe-save me-2"></i>{{ isset($member) ? 'Update' : 'Create' }} Member
                            </button>
                            <a href="{{ route('board.members.index') }}" class="btn btn-secondary">
                                <i class="fe fe-x me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

