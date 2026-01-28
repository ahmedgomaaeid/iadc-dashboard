@extends('layouts.global-form')

@section('title', $form->title)
@section('subtitle', $form->subtitle ?? 'Fill out the form')
@if($form->form_image)
    @section('form-img')
        <img src="{{ asset('storage/' . $form->form_image) }}" alt="Form Image" class="w-100">
    @endsection
@endif

@section('content')
    <form action="{{ route('form.submit', $form->subdomain) }}" method="POST" id="guestForm">
        @csrf
        
        @foreach($orderedFields as $fieldName => $fieldConfig)
            <div class="mb-4">
                <label for="{{ $fieldName }}" class="form-label">
                    {{ $fieldConfig['label'] }}
                    @if($fieldConfig['required'])
                        <span class="text-danger">*</span>
                    @endif
                </label>
                
                <div class="input-group">
                    <span class="input-icon">
                        <i class="fas {{ $fieldConfig['icon'] }}"></i>
                    </span>
                    
                    @if($fieldConfig['type'] === 'textarea')
                        <textarea 
                            class="form-control with-icon @error($fieldName) is-invalid @enderror" 
                            id="{{ $fieldName }}" 
                            name="{{ $fieldName }}" 
                            rows="4"
                            placeholder="{{ $fieldConfig['placeholder'] ?? '' }}"
                            {{ $fieldConfig['required'] ? 'required' : '' }}
                        >{{ old($fieldName) }}</textarea>
                    @elseif($fieldConfig['type'] === 'select')
                        <select 
                            class="form-control with-icon @error($fieldName) is-invalid @enderror" 
                            id="{{ $fieldName }}" 
                            name="{{ $fieldName }}"
                            {{ $fieldConfig['required'] ? 'required' : '' }}
                        >
                            @foreach($fieldConfig['options'] ?? [] as $option)
                                <option value="{{ $option }}" {{ old($fieldName) === $option ? 'selected' : '' }}>
                                    {{ $option }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input 
                            type="{{ $fieldConfig['type'] }}" 
                            class="form-control with-icon @error($fieldName) is-invalid @enderror" 
                            id="{{ $fieldName }}" 
                            name="{{ $fieldName }}" 
                            value="{{ old($fieldName) }}"
                            placeholder="{{ $fieldConfig['placeholder'] ?? '' }}"
                            {{ $fieldConfig['required'] ? 'required' : '' }}
                        >
                    @endif
                    
                    @error($fieldName)
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        @endforeach

        <button type="submit" class="btn btn-register w-100">
            <i class="fas fa-paper-plane me-2"></i>Submit
        </button>
    </form>
@endsection
