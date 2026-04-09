@extends('layouts.global-form')

@section('title', $form->title)
@section('subtitle', $form->subtitle ?? 'Fill out the form')
@if($form->form_image)
    @section('form-img')
        <img src="{{ asset('storage/' . $form->form_image) }}" alt="Form Image" class="w-100">
    @endsection
@endif

@section('content')
    @if(!session('is_pulse'))
    <form action="{{ route('form.submit', $form->subdomain) }}" method="POST" id="guestForm" enctype="multipart/form-data">
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
                    @php
                        $hasCondition = !empty($fieldConfig['depends_on']);
                        $depOn = $hasCondition ? $fieldConfig['depends_on'] : '';
                        $depVal = $hasCondition ? ($fieldConfig['depends_value'] ?? '') : '';
                        
                        // Check validation state in case of redirect back with errors
                        $wasSubmittedAndFailed = $errors->any();
                        if ($wasSubmittedAndFailed && $hasCondition && (string)$depVal !== '' && old($depOn) == $depVal) {
                            $hasConditionClass = 'conditional-field'; // show it because old value matched
                        } else {
                            $hasConditionClass = $hasCondition ? 'conditional-field d-none' : '';
                        }
                    @endphp
                    <div class="mb-4 field-wrapper {{ $hasConditionClass }}" 
                         data-field-name="{{ $fieldName }}"
                         {!! $hasCondition ? 'data-depends-on="'.$depOn.'" data-depends-value="'.$depVal.'"' : '' !!}>
                        <label for="{{ $fieldName }}" class="form-label">
                            {{ $fieldConfig['label'] }}
                            @if($fieldConfig['required'])
                                <span class="text-danger required-asterisk">*</span>
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
                            @elseif($fieldConfig['type'] === 'file')
                                <input 
                                    type="file" 
                                    class="form-control with-icon @error($fieldName) is-invalid @enderror" 
                                    id="{{ $fieldName }}" 
                                    name="{{ $fieldName }}" 
                                    accept="image/*"
                                    {{ $fieldConfig['required'] ? 'required' : '' }}
                                >
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
    @endif

    @if(session('is_pulse'))
    {{-- === PULSE EVENT — CUSTOM SUCCESS SCREEN === --}}
    <style>
        #pulseSuccessScreen {
            padding: 40px 30px;
            text-align: center;
            animation: fadeInUp 0.6s ease;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .pulse-success-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
        }
        .pulse-image-wrapper {
            position: relative;
            display: inline-block;
            margin: 20px auto;
        }
        .pulse-image-wrapper img {
            max-width: 100%;
            max-height: 420px;
            width: auto;
            height: auto;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            border: 5px solid white;
            display: block;
            margin: 0 auto;
        }
        .pulse-headline {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 6px;
            margin-top: 24px;
        }
        .pulse-sub {
            color: #6b7280;
            font-size: 0.95rem;
            margin-bottom: 28px;
        }
        .pulse-actions {
            display: flex;
            gap: 14px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-linkedin {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #0a66c2;
            color: white !important;
            border: none;
            border-radius: 12px;
            padding: 14px 28px;
            font-size: 1.05rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(10, 102, 194, 0.35);
        }
        .btn-linkedin:hover {
            background: #004182;
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(10, 102, 194, 0.5);
            color: white !important;
        }
        .btn-download {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: white;
            color: #374151 !important;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px 28px;
            font-size: 1.05rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn-download:hover {
            border-color: #9ca3af;
            background: #f9fafb;
            transform: translateY(-2px);
            color: #111827 !important;
        }
        .pulse-note {
            margin-top: 18px;
            font-size: 0.82rem;
            color: #9ca3af;
            line-height: 1.5;
        }
    </style>

    <div id="pulseSuccessScreen">
        @php
            $pulseImage = session('pulse_image');
            $linkedInText = 'hi i will attend pulse event in 21 april #pulse #iadcsuez';
            $linkedInUrl = 'https://www.linkedin.com/feed/?shareActive=true&text=' . rawurlencode($linkedInText);
            $imageUrl = $pulseImage ? asset('storage/' . $pulseImage) : null;
        @endphp

        <div class="pulse-success-badge">
            <i class="fas fa-check-circle"></i>
            Registration Complete!
        </div>

        <div class="pulse-headline">You're In! 🎉</div>
        <p class="pulse-sub">Share your excitement with your network on LinkedIn!</p>

        @if($imageUrl)
            <div class="pulse-image-wrapper">
                <img src="{{ $imageUrl }}" alt="Your registration photo" id="pulsePhoto">
            </div>
        @endif

        <div class="pulse-actions">
            <a href="{{ $linkedInUrl }}"
               target="_blank"
               rel="noopener noreferrer"
               class="btn-linkedin"
               id="linkedinShareBtn">
                <i class="fab fa-linkedin"></i>
                Share on LinkedIn
            </a>

            @if($imageUrl)
            <a href="{{ $imageUrl }}"
               download
               class="btn-download"
               id="downloadBtn">
                <i class="fas fa-download"></i>
                Download Photo
            </a>
            @endif
        </div>

        <p class="pulse-note">
            💡 <strong>Tip:</strong> Download your photo first, then click "Share on LinkedIn".<br>
            Paste the photo into the LinkedIn post to share it along with your message!
        </p>
    </div>

    @endif

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isWizard = {{ isset($isWizard) && $isWizard ? 'true' : 'false' }};

        // --- Conditional Fields Logic (runs for ALL forms, wizard or not) ---
        function runDependencyChecks(triggerSelect) {
            if (!triggerSelect) return;
            const $trigger = $(triggerSelect);
            const fieldName = $trigger.attr('name') || $trigger.attr('id');
            if (!fieldName) return;
            
            const rawFieldValue = $trigger.val() || '';
            const fieldValue = String(rawFieldValue).trim().toLowerCase();

            // Find all wrappers dependent on this trigger field
            $(`.conditional-field[data-depends-on="${fieldName}"]`).each(function() {
                const $wrapper = $(this);
                const rawTargetValue = $wrapper.attr('data-depends-value') || '';
                const targetValue = String(rawTargetValue).trim().toLowerCase();
                
                const isMatch = (targetValue.length > 0 && fieldValue === targetValue);
                
                if (isMatch) {
                    $wrapper.removeClass('d-none').show();
                    // Reactivate required attributes
                    $wrapper.find('input, select, textarea').each(function() {
                        if ($wrapper.find('.required-asterisk').length > 0) {
                            $(this).prop('required', true).attr('required', 'required');
                        }
                    });
                } else {
                    $wrapper.addClass('d-none').hide();
                    // Strip required to prevent browser form blocking on hidden fields
                    $wrapper.find('input, select, textarea').each(function() {
                        $(this).prop('required', false).removeAttr('required').removeClass('is-invalid');
                    });
                    $wrapper.find('.custom-feedback').addClass('d-none');
                }
            });
        }

        // Listen for any changes on the form
        $(document).on('change input', 'select, input, textarea', function() {
            runDependencyChecks(this);
        });

        // Run on page load to set initial state
        $('select, input, textarea').each(function() {
            runDependencyChecks(this);
        });

        // --- Wizard Logic (only runs for multi-section forms) ---
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

            if (steps.length > 0) {
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
        }

        function validateSection(n) {
            let valid = true;
            const section = sections[n];
            // Only validate visible inputs (ignore conditional ones that are hidden)
            const requiredInputs = section.querySelectorAll('.field-wrapper:not(.d-none) input[required], .field-wrapper:not(.d-none) select[required], .field-wrapper:not(.d-none) textarea[required]');
            
            requiredInputs.forEach(input => {
                const wrapper = input.closest('.field-wrapper');
                if (wrapper && wrapper.classList.contains('d-none')) {
                    return;
                }

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
