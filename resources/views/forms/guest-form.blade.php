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
            $isPeaks = strtolower($form->subdomain) === 'peaks';
            $isWizard = count($orderedSections) > 1 || $isPeaks;
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

                @if($isPeaks)
                    @php 
                        $pkgIndex = count($orderedSections); 
                        $payIndex = count($orderedSections) + 1; 
                    @endphp
                    <div class="wizard-step {{ $pkgIndex === 0 ? 'active' : '' }}" data-step="{{ $pkgIndex }}">
                        <div class="step-number">{{ $pkgIndex + 1 }}</div>
                        <div class="step-label d-none d-md-block">Package</div>
                    </div>
                    <div class="wizard-step {{ $payIndex === 0 ? 'active' : '' }}" data-step="{{ $payIndex }}">
                        <div class="step-number">{{ $payIndex + 1 }}</div>
                        <div class="step-label d-none d-md-block">Payment</div>
                    </div>
                @endif
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

                        @php
                            $isLastDynamicSection = $index === (count($orderedSections) - 1);
                            $showSubmit = $isLastDynamicSection && !$isPeaks;
                        @endphp

                        @if(!$showSubmit)
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
    @php
        $isPeaksForm = session('pulse_subdomain') === 'peaks';
    @endphp

    {{-- === PACKAGE SELECTION SCREEN (PEAKS ONLY) === --}}
    @if($isPeaksForm)
    <style>
        /* ─── Package Selection Screen ─── */
        #peaksPackageScreen {
            padding: 30px 20px;
            text-align: center;
            animation: fadeInUp 0.6s ease;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes shimmer {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 20px rgba(99, 102, 241, 0.15); }
            50% { box-shadow: 0 0 35px rgba(99, 102, 241, 0.35); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
        }
        @keyframes checkBounce {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.3); }
            100% { transform: scale(1); opacity: 1; }
        }
        .peaks-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            padding: 10px 26px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
        }
        .peaks-title {
            font-size: 1.45rem;
            font-weight: 800;
            color: #1e1b4b;
            margin: 12px 0 4px;
            letter-spacing: -0.3px;
        }
        .peaks-subtitle {
            color: #6b7280;
            font-size: 0.92rem;
            margin-bottom: 28px;
            line-height: 1.5;
        }
        .packages-grid {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 28px;
        }
        .package-card {
            position: relative;
            background: #ffffff;
            border: 2px solid #e5e7eb;
            border-radius: 20px;
            padding: 0;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            display: flex;
            align-items: stretch;
            min-height: 130px;
        }
        .package-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(99,102,241,0.04) 0%, rgba(139,92,246,0.04) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
            z-index: 0;
        }
        .package-card:hover {
            border-color: #a5b4fc;
            transform: translateY(-3px);
            box-shadow: 0 12px 40px rgba(99, 102, 241, 0.15);
        }
        .package-card:hover::before { opacity: 1; }
        .package-card.selected {
            border-color: #6366f1;
            box-shadow: 0 8px 35px rgba(99, 102, 241, 0.25);
            animation: pulseGlow 2s ease-in-out infinite;
        }
        .package-card.selected::before { opacity: 1; }
        .package-card .card-img-section {
            width: 130px;
            min-width: 130px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            position: relative;
            z-index: 1;
        }
        .package-card .card-img-section img {
            width: 100px;
            height: 100px;
            object-fit: contain;
            border-radius: 14px;
            transition: transform 0.4s ease;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.1));
        }
        .package-card:hover .card-img-section img,
        .package-card.selected .card-img-section img {
            transform: scale(1.08);
        }
        .package-card.selected .card-img-section img {
            animation: float 3s ease-in-out infinite;
        }
        .package-card .card-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 18px 18px 18px 0;
            text-align: left;
            position: relative;
            z-index: 1;
        }
        .package-card .card-info .pkg-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #8b5cf6;
            margin-bottom: 3px;
        }
        .package-card .card-info .pkg-name {
            font-size: 12px;
            color: #1e1b4b;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        .package-card .card-info .pkg-price {
            display: inline-flex;
            align-items: baseline;
            gap: 3px;
        }
        .package-card .card-info .pkg-price .amount {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
        }
        .package-card .card-info .pkg-price .currency {
            font-size: 0.85rem;
            font-weight: 600;
            color: #6b7280;
        }
        .package-card .card-check {
            position: absolute;
            top: 12px;
            right: 14px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2px solid #d1d5db;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            background: white;
            z-index: 2;
        }
        .package-card .card-check i {
            display: none;
            font-size: 0.8rem;
            color: white;
        }
        .package-card.selected .card-check {
            border-color: #6366f1;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            box-shadow: 0 2px 10px rgba(99, 102, 241, 0.4);
        }
        .package-card.selected .card-check i {
            display: block;
            animation: checkBounce 0.4s ease;
        }
        /* Most popular tag */
        .package-card .popular-tag {
            position: absolute;
            top: 0;
            left: 0;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 4px 16px 4px 12px;
            border-radius: 0 0 14px 0;
            z-index: 3;
        }
        .btn-confirm-package {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 16px 32px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a78bfa 100%);
            background-size: 200% auto;
            color: white;
            font-size: 1.1rem;
            font-weight: 700;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.4s ease;
            box-shadow: 0 6px 25px rgba(99, 102, 241, 0.4);
            opacity: 0.5;
            pointer-events: none;
        }
        .btn-confirm-package.active {
            opacity: 1;
            pointer-events: auto;
        }
        .btn-confirm-package.active:hover {
            background-position: right center;
            transform: translateY(-2px);
            box-shadow: 0 10px 35px rgba(99, 102, 241, 0.5);
        }
        .btn-confirm-package .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .btn-confirm-package.loading .spinner { display: inline-block; }
        .btn-confirm-package.loading .btn-text { display: none; }
        .peaks-note {
            margin-top: 14px;
            font-size: 0.8rem;
            color: #9ca3af;
            line-height: 1.5;
        }
        @media (max-width: 480px) {
            .package-card .card-img-section {
                width: 100px;
                min-width: 100px;
                padding: 12px;
            }
            .package-card .card-img-section img {
                width: 76px;
                height: 76px;
            }
            .package-card .card-info .pkg-name { font-size: 0.95rem; }
            .package-card .card-info .pkg-price .amount { font-size: 1.3rem; }
            .peaks-title { font-size: 1.25rem; }
        }
        
        /* Bundle Toggle & Fields */
        .reg-mode-toggle {
            display: inline-flex;
            background: #f3f4f6;
            padding: 4px;
            border-radius: 12px;
            margin-bottom: 24px;
        }
        .reg-mode-btn {
            padding: 8px 24px;
            font-size: 0.95rem;
            font-weight: 600;
            color: #6b7280;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            background: transparent;
        }
        .reg-mode-btn.active {
            background: white;
            color: #6366f1;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        #bundleFields {
            display: none;
            background: #f9fafb;
            border: 2px dashed #e5e7eb;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 24px;
            text-align: left;
            animation: fadeInUp 0.4s ease;
        }
        #bundleFields.active {
            display: block;
        }
        .bundle-field-group {
            margin-bottom: 12px;
        }
        .bundle-field-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        .bundle-input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        .bundle-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        .bundle-input.is-invalid {
            border-color: #ef4444;
            background-color: #fef2f2;
        }
    </style>

    <div id="peaksPackageScreen">
        <div class="peaks-badge">
            <i class="fas fa-check-circle"></i>
            Registration Complete!
        </div>

        <h2 class="peaks-title">Choose Your Package</h2>
        <p class="peaks-subtitle">Select the experience that suits you best</p>

        <div class="reg-mode-toggle">
            <button class="reg-mode-btn active" onclick="setRegMode('alone')" id="btnModeAlone">Alone</button>
            <button class="reg-mode-btn" onclick="setRegMode('bundle')" id="btnModeBundle">Bundle</button>
        </div>

        <div class="packages-grid">
            {{-- Package 1 --}}
            <div class="package-card" data-package="1" onclick="selectPackage(this)">
                <div class="popular-tag"><i class="fas fa-star" style="margin-right:4px"></i> Best Value</div>
                <div class="card-img-section">
                    <img src="{{ asset('images/22.png') }}" alt="Package 1">
                </div>
                <div class="card-info">
                    <span class="pkg-label">Package 1</span>
                    <span class="pkg-name">Attending, Transportation, Lunch & Breakfast, and Certificates</span>
                    <div class="pkg-price">
                        <span class="amount" id="pkg1PriceAmount">175</span>
                        <span class="currency">L.E</span>
                    </div>
                </div>
                <div class="card-check"><i class="fas fa-check"></i></div>
            </div>

            {{-- Package 2 --}}
            <div class="package-card" data-package="2" onclick="selectPackage(this)">
                <div class="card-img-section">
                    <img src="{{ asset('images/44.png') }}" alt="Package 2">
                </div>
                <div class="card-info">
                    <span class="pkg-label">Package 2</span>
                    <span class="pkg-name">Attending, Lunch & Breakfast, and Certificates</span>
                    <div class="pkg-price">
                        <span class="amount">100</span>
                        <span class="currency">L.E</span>
                    </div>
                </div>
                <div class="card-check"><i class="fas fa-check"></i></div>
            </div>

            {{-- Package 3 --}}
            <div class="package-card" data-package="3" onclick="selectPackage(this)">
                <div class="card-img-section">
                    <img src="{{ asset('images/11.png') }}" alt="Package 3">
                </div>
                <div class="card-info">
                    <span class="pkg-label">Package 3</span>
                    <span class="pkg-name">Attending, and Certificates</span>
                    <div class="pkg-price">
                        <span class="amount">60</span>
                        <span class="currency">L.E</span>
                    </div>
                </div>
                <div class="card-check"><i class="fas fa-check"></i></div>
            </div>
        </div>

        <div id="bundleFields">
            <p style="font-size: 0.9rem; color: #6b7280; margin-bottom: 16px;">
                <i class="fas fa-info-circle"></i> Please enter the names of your 4 group members as they registered in the form.
            </p>
            <div class="bundle-field-group">
                <label>Person 1 Name</label>
                <input type="text" class="bundle-input" id="bundlePerson1" placeholder="Name as he registered">
            </div>
            <div class="bundle-field-group">
                <label>Person 2 Name</label>
                <input type="text" class="bundle-input" id="bundlePerson2" placeholder="Name as he registered">
            </div>
            <div class="bundle-field-group">
                <label>Person 3 Name</label>
                <input type="text" class="bundle-input" id="bundlePerson3" placeholder="Name as he registered">
            </div>
            <div class="bundle-field-group mb-0">
                <label>Person 4 Name</label>
                <input type="text" class="bundle-input" id="bundlePerson4" placeholder="Name as he registered">
            </div>
        </div>

        <button class="btn-confirm-package" id="confirmPackageBtn" onclick="confirmPackage()">
            <span class="btn-text"><i class="fas fa-arrow-right"></i> Continue</span>
            <span class="spinner"></span>
        </button>

        <p class="peaks-note">
            💡 Your package choice will be recorded with your registration.
        </p>
    </div>

    <div id="peaksPaymentScreen" style="display: none; padding: 30px 20px; text-align: center;">
        <h2 class="peaks-title">Choose Payment Method</h2>
        <p class="peaks-subtitle">How would you like to pay?</p>

        <div class="packages-grid">
            {{-- Payment 1 --}}
            <div class="package-card" data-payment="vodafone" onclick="selectPayment(this)">
                <div class="card-img-section">
                    <img src="{{ asset('images/vodafone.png') }}" alt="Vodafone Cash">
                </div>
                <div class="card-info">
                    <span class="pkg-name">Vodafone Cash</span>
                </div>
                <div class="card-check"><i class="fas fa-check"></i></div>
            </div>

            {{-- Payment 2 --}}
            <div class="package-card" data-payment="instapay" onclick="selectPayment(this)">
                <div class="card-img-section">
                    <img src="{{ asset('images/instapay.png') }}" alt="Instapay">
                </div>
                <div class="card-info">
                    <span class="pkg-name">Instapay</span>
                </div>
                <div class="card-check"><i class="fas fa-check"></i></div>
            </div>

            {{-- Payment 3 --}}
            <div class="package-card" data-payment="cash" onclick="selectPayment(this)">
                <div class="card-img-section">
                    <img src="{{ asset('images/cash.png') }}" alt="Cash">
                </div>
                <div class="card-info">
                    <span class="pkg-name">Cash</span>
                </div>
                <div class="card-check"><i class="fas fa-check"></i></div>
            </div>
        </div>

        <button class="btn-confirm-package" id="confirmPaymentBtn" onclick="confirmPayment()">
            <span class="btn-text"><i class="fas fa-check"></i> Submit</span>
            <span class="spinner"></span>
        </button>
    </div>
    @endif

    {{-- === PULSE / PEAKS — CUSTOM SUCCESS SCREEN === --}}
    <style>
        #pulseSuccessScreen {
            padding: 40px 30px;
            text-align: center;
            animation: fadeInUp 0.6s ease;
        }
        @if(!$isPeaksForm)
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @endif
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

    <div id="pulseSuccessScreen" @if($isPeaksForm) style="display:none;" @endif>
        @php
            $pulseImage = session('pulse_image');
            $pulseSubmissionId = session('pulse_submission_id');
            // OAuth route — will upload image and create post with native image attachment
            $linkedInUrl = $pulseSubmissionId
                ? 'https://iadcsuez.org/linkedin/share?submission_id=' . $pulseSubmissionId
                : '#';
            $imageUrl = $pulseImage ? asset('storage/' . $pulseImage) : null;
        @endphp

        <div class="pulse-success-badge">
            <i class="fas fa-check-circle"></i>
            Registration Complete!
        </div>

        <p class="pulse-sub">Share your excitement with your network on LinkedIn!</p>

        @if($imageUrl)
            <div class="pulse-image-wrapper">
                <img src="{{ $imageUrl }}" alt="Your registration photo" id="pulsePhoto">
            </div>
        @endif

        <div class="pulse-actions">
            <a href="{{ $linkedInUrl }}"
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
            🔗 Clicking <strong>Share on LinkedIn</strong> will ask you to authorize once,
            then automatically create a post with your photo attached — ready to publish!
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

    // ─── Peaks Package Selection Logic ───
    let selectedPackage = null;
    let registrationMode = 'alone';

    function setRegMode(mode) {
        registrationMode = mode;
        if (mode === 'alone') {
            document.getElementById('btnModeAlone').classList.add('active');
            document.getElementById('btnModeBundle').classList.remove('active');
            document.getElementById('bundleFields').classList.remove('active');
            document.getElementById('pkg1PriceAmount').innerText = '175';
        } else {
            document.getElementById('btnModeAlone').classList.remove('active');
            document.getElementById('btnModeBundle').classList.add('active');
            document.getElementById('bundleFields').classList.add('active');
            document.getElementById('pkg1PriceAmount').innerText = '150';
            
            // Auto-select package 1 if nothing is selected or if they switch to bundle
            // Optional: you can force package 1 here, but let's just make it visually 150.
        }
    }

    function selectPackage(card) {
        // Remove selection from all cards
        document.querySelectorAll('.package-card').forEach(c => c.classList.remove('selected'));
        // Select this card
        card.classList.add('selected');
        selectedPackage = card.getAttribute('data-package');
        // Enable the continue button
        document.getElementById('confirmPackageBtn').classList.add('active');
    }

    function confirmPackage() {
        if (!selectedPackage) return;
        
        let bundleData = null;
        if (registrationMode === 'bundle') {
            const p1 = document.getElementById('bundlePerson1');
            const p2 = document.getElementById('bundlePerson2');
            const p3 = document.getElementById('bundlePerson3');
            const p4 = document.getElementById('bundlePerson4');
            
            let valid = true;
            [p1, p2, p3, p4].forEach(el => {
                if (!el.value.trim()) {
                    valid = false;
                    el.classList.add('is-invalid');
                } else {
                    el.classList.remove('is-invalid');
                }
                
                el.addEventListener('input', () => el.classList.remove('is-invalid'), {once: true});
            });
            
            if (!valid) return; // Stop if bundle fields are empty
            
            bundleData = {
                p1: p1.value.trim(),
                p2: p2.value.trim(),
                p3: p3.value.trim(),
                p4: p4.value.trim()
            };
        }

        // Transition to Payment Screen
        const pkgScreen = document.getElementById('peaksPackageScreen');
        pkgScreen.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        pkgScreen.style.opacity = '0';
        pkgScreen.style.transform = 'translateY(-20px)';

        setTimeout(() => {
            pkgScreen.style.display = 'none';
            // Show the Payment Selection screen
            const payScreen = document.getElementById('peaksPaymentScreen');
            payScreen.style.display = 'block';
            payScreen.style.opacity = '0';
            payScreen.style.transform = 'translateY(20px)';
            payScreen.style.transition = 'opacity 0.5s ease, transform 0.5s ease';

            requestAnimationFrame(() => {
                payScreen.style.opacity = '1';
                payScreen.style.transform = 'translateY(0)';
            });

            payScreen.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 400);
    }
    
    let selectedPayment = null;

    function selectPayment(card) {
        // Remove selection from all cards within payment screen
        document.querySelectorAll('#peaksPaymentScreen .package-card').forEach(c => c.classList.remove('selected'));
        // Select this card
        card.classList.add('selected');
        selectedPayment = card.getAttribute('data-payment');
        // Enable the submit button
        document.getElementById('confirmPaymentBtn').classList.add('active');
    }

    function confirmPayment() {
        if (!selectedPackage || !selectedPayment) return;

        const btn = document.getElementById('confirmPaymentBtn');
        btn.classList.add('loading');
        btn.disabled = true;

        const submissionId = '{{ session("pulse_submission_id") }}';
        const csrfToken = '{{ csrf_token() }}';
        
        let bundleNames = [];
        if (registrationMode === 'bundle') {
            bundleNames = [
                document.getElementById('bundlePerson1').value.trim(),
                document.getElementById('bundlePerson2').value.trim(),
                document.getElementById('bundlePerson3').value.trim(),
                document.getElementById('bundlePerson4').value.trim(),
            ];
        }

        fetch('/save-package', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                submission_id: submissionId,
                package: selectedPackage,
                payment_method: selectedPayment,
                registration_mode: registrationMode,
                bundle_names: bundleNames
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Smoothly hide payment screen
                const payScreen = document.getElementById('peaksPaymentScreen');
                payScreen.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                payScreen.style.opacity = '0';
                payScreen.style.transform = 'translateY(-20px)';

                setTimeout(() => {
                    payScreen.style.display = 'none';
                    // Show the LinkedIn share screen
                    const successScreen = document.getElementById('pulseSuccessScreen');
                    successScreen.style.display = 'block';
                    successScreen.style.opacity = '0';
                    successScreen.style.transform = 'translateY(20px)';
                    successScreen.style.transition = 'opacity 0.5s ease, transform 0.5s ease';

                    requestAnimationFrame(() => {
                        successScreen.style.opacity = '1';
                        successScreen.style.transform = 'translateY(0)';
                    });

                    // Scroll to the success screen
                    successScreen.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 400);
            } else {
                btn.classList.remove('loading');
                btn.disabled = false;
                alert('Something went wrong. Please try again.');
            }
        })
        .catch(err => {
            console.error(err);
            btn.classList.remove('loading');
            btn.disabled = false;
            alert('Network error. Please try again.');
        });
    }
</script>
@endsection
