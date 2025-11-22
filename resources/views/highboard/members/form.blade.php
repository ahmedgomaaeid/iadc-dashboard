@extends('layouts.highboard-dashboard')

@section('title', isset($member) ? 'Edit Member' : 'Create Member')

@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ isset($member) ? 'Edit Member' : 'Create Member' }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('highboard.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('highboard.members.index') }}">Members</a></li>
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
                    <form action="{{ isset($member) ? route('highboard.members.update', $member) : route('highboard.members.store') }}" 
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
                                <label for="committees" class="form-label">Committees <span class="text-danger">*</span></label>
                                <select class="form-select @error('committees') is-invalid @enderror" 
                                        id="committees" 
                                        name="committees[]" 
                                        multiple
                                        required>
                                    @foreach($committees as $committee)
                                        <option value="{{ $committee->id }}" 
                                                {{ in_array($committee->id, old('committees', isset($member) ? $member->committees->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                                            {{ $committee->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('committees')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Hold Ctrl (Windows) or Command (Mac) to select multiple committees</small>
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

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fe fe-save me-2"></i>{{ isset($member) ? 'Update' : 'Create' }} Member
                            </button>
                            <a href="{{ route('highboard.members.index') }}" class="btn btn-secondary">
                                <i class="fe fe-x me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
