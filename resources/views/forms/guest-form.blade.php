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
        
        @php
            $isWizard = count($orderedSections) > 1;
        @endphp

        @if($isWizard)
            <!-- Step Indicators -->
            <div class="wizard-steps mb-4">
                @foreach($orderedSections as $index => $section)
                    <div class="wizard-step {{ $index === 0 ? 'active' : '' }}" data-step="{{ $index }}">
                        <div class="step-number">{{ $index + 1 }}</div>
                        <div class="step-label d-none d-md-block">{{ $section['name'] }}</div>
                    </div>
                @endforeach
            </div>
        @endif

        @foreach($orderedSections as $index => $section)
            <div class="form-section {{ $isWizard && $index !== 0 ? 'd-none' : '' }}" data-section="{{ $index }}">
                @if($isWizard)
                    <h4 class="mb-4 text-primary" style="color: var(--primary-color) !important;">{{ $section['name'] }}</h4>
                @endif

                @foreach($section['fields'] as $fieldName => $fieldConfig)
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
                                    @if(!empty($fieldConfig['placeholder']))
                                        <option value="" disabled selected>{{ $fieldConfig['placeholder'] }}</option>
                                    @endif
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
                            
                            <!-- Client-side validation feedback -->
                            <div class="invalid-feedback custom-feedback d-none">This field is required.</div>
                        </div>
                    </div>
                @endforeach

                @if($isWizard)
                    <div class="d-flex justify-content-between mt-4">
                        @if($index > 0)
                            <button type="button" class="btn btn-outline-secondary btn-prev px-4 py-2" style="border-radius: 10px;">
                                <i class="fas fa-arrow-left me-2"></i>Previous
                            </button>
                        @else
                            <div></div>
                        @endif

                        @if($index < count($orderedSections) - 1)
                            <button type="button" class="btn btn-primary btn-next px-4 py-2" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); border: none; border-radius: 10px;">
                                Next<i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        @else
                            <button type="submit" class="btn btn-register px-4 py-2 m-0" style="width: auto;">
                                <i class="fas fa-paper-plane me-2"></i>Submit
                            </button>
                        @endif
                    </div>
                @else
                    <button type="submit" class="btn btn-register w-100 mt-4">
                        <i class="fas fa-paper-plane me-2"></i>Submit
                    </button>
                @endif
            </div>
        @endforeach
    </form>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isWizard = {{ isset($isWizard) && $isWizard ? 'true' : 'false' }};
        if (!isWizard) return;

        let currentSection = 0;
        const sections = document.querySelectorAll('.form-section');
        const steps = document.querySelectorAll('.wizard-step');

        function showSection(n) {
            sections.forEach((sec, idx) => {
                if (idx === n) {
                    sec.classList.remove('d-none');
                } else {
                    sec.classList.add('d-none');
                }
            });

            steps.forEach((step, idx) => {
                if (idx === n) {
                    step.classList.add('active');
                    step.classList.remove('completed');
                } else if (idx < n) {
                    step.classList.remove('active');
                    step.classList.add('completed');
                } else {
                    step.classList.remove('active', 'completed');
                }
            });
        }

        function validateSection(n) {
            let valid = true;
            const section = sections[n];
            const requiredInputs = section.querySelectorAll('input[required], select[required], textarea[required]');
            
            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    valid = false;
                    input.classList.add('is-invalid');
                    const customFeedback = input.closest('.input-group').querySelector('.custom-feedback');
                    if (customFeedback) customFeedback.classList.remove('d-none');
                    
                    input.addEventListener('input', function() {
                        if(this.value.trim()) {
                            this.classList.remove('is-invalid');
                            if (customFeedback) customFeedback.classList.add('d-none');
                        }
                    }, { once: true });
                }
            });

            return valid;
        }

        document.querySelectorAll('.btn-next').forEach(btn => {
            btn.addEventListener('click', function() {
                if (validateSection(currentSection)) {
                    currentSection++;
                    showSection(currentSection);
                    document.getElementById('guestForm').scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

        document.querySelectorAll('.btn-prev').forEach(btn => {
            btn.addEventListener('click', function() {
                if (currentSection > 0) {
                    currentSection--;
                    showSection(currentSection);
                    document.getElementById('guestForm').scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    });
</script>
@endsection
