@extends('layouts.global-form')

@section('title', 'IADC Suez')
@section('subtitle', 'Account Registration')
@section('content')
@section('styles')
<style>
    /* Select2 Custom Styling */
    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--multiple {
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 4px 15px 4px 45px; /* Left padding for icon */
        min-height: 48px;
        transition: all 0.3s ease;
        background-color: #fff;
    }

    /* Focus State */
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: var(--primary-color, #2563eb);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        outline: none;
    }

    /* Chips (Selected Items) */
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: linear-gradient(135deg, var(--primary-color, #2563eb) 0%, var(--secondary-color, #1d4ed8) 100%) !important;
        color: white !important;
        border: none !important;
        border-radius: 15px !important;
        padding: 3px 10px !important;
        font-size: 0.8rem !important;
        margin: 2px 4px 2px 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        flex-direction: row-reverse !important;
        vertical-align: middle !important;
        white-space: nowrap !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: rgba(255, 255, 255, 0.9) !important;
        margin-right: 0 !important;
        margin-left: 6px !important;
        border: none !important;
        background: transparent !important;
        padding: 0 !important;
        font-size: 14px !important;
        font-weight: bold !important;
        line-height: 1 !important;
        display: inline-block !important;
        position: static !important;
        float: none !important;
        vertical-align: middle !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        background-color: transparent !important;
        color: #fff !important;
    }

    /* Dropdown */
    .select2-dropdown {
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        overflow: hidden;
        z-index: 9999;
        margin-top: 5px;
    }

    .select2-results__option {
        padding: 10px 15px;
        font-size: 0.95rem;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background: linear-gradient(135deg, var(--primary-color, #2563eb) 0%, var(--secondary-color, #1d4ed8) 100%);
        color: white;
    }

    /* Placeholder */
    .select2-search__field::placeholder {
        color: #9ca3af;
    }
</style>
@endsection
    <form action="{{ route('register.post') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">
                    <i class="fas fa-user"></i> Full Name
                </label>
                <div class="input-group">
                    <span class="input-icon"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control with-icon" id="name" name="name"
                        value="{{ old('name') }}" placeholder="Enter your full name" required>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">
                    <i class="fas fa-envelope"></i> Email Address
                </label>
                <div class="input-group">
                    <span class="input-icon"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control with-icon" id="email" name="email"
                        value="{{ old('email') }}" placeholder="your.email@example.com" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="phone" class="form-label">
                    <i class="fas fa-phone"></i> Phone Number
                </label>
                <div class="input-group">
                    <span class="input-icon"><i class="fas fa-phone"></i></span>
                    <input type="text" class="form-control with-icon" id="phone" name="phone"
                        value="{{ old('phone') }}" placeholder="01XXXXXXXXXX" required>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <label for="committees" class="form-label">
                    <i class="fas fa-globe"></i> Committees
                </label>
                <div class="input-group">
                    <select class="form-control @error('committees') is-invalid @enderror" data-placeholder="Select Committees" name="committees[]" id="committees" multiple>
                        @foreach($committees as $committee)
                            <option value="{{ $committee->id }}"
                                    {{ in_array($committee->id, old('committees', isset($member) ? $member->committees->pluck('id')->toArray() : [])) ? 'selected' : '' }}>{{ $committee->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="university" class="form-label">
                    <i class="fas fa-university"></i> University
                </label>
                <div class="input-group">
                    <span class="input-icon"><i class="fas fa-university"></i></span>
                    <input type="text" class="form-control with-icon" id="university"
                            name="university" value="{{ old('university') }}" placeholder="Enter your university" required>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="faculty" class="form-label">
                    <i class="fas fa-calendar"></i> Faculty
                </label>
                <div class="input-group">
                    <span class="input-icon"><i class="fas fa-calendar"></i></span>
                    <input type="text" class="form-control with-icon" id="faculty" name="faculty"
                        value="{{ old('faculty') }}" placeholder="e.g., Petroleum Engineering" required>
                </div>
            </div>
        </div>


        <div class="mb-3">
            <label for="collage" class="form-label">
                <i class="fas fa-building"></i> Academic Year
            </label>
            <div class="input-group">
                <span class="input-icon"><i class="fas fa-building"></i></span>
                <select name="academic_year" id="academic_year" class="form-control with-icon" required>
                    <option value="preparation">Preparation</option>
                    <option value="first_year">First Year</option>
                    <option value="second_year">Second Year</option>
                    <option value="third_year">Third Year</option>
                    <option value="fourth_year">Fourth Year</option>
                    <option value="graduated">Graduated</option>
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">
                <i class="fas fa-lock"></i> Password
            </label>
            <div class="input-group">
                <span class="input-icon"><i class="fas fa-lock"></i></span>
                <input type="password" class="form-control with-icon" id="password" name="password"
                    value="{{ old('password') }}" placeholder="Enter your password" required>
            </div>
        </div>

        <button type="submit" class="btn btn-register w-100">
            <i class="fas fa-paper-plane"></i> Submit Registration
        </button>
    </form>
@endsection
@section('scripts')
    <script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#committees').select2({
                placeholder: "Select Committees (Max 2)",
                allowClear: true,
                width: '100%',
                closeOnSelect: false, // Keep dropdown open for multiple selection
                minimumResultsForSearch: 0 // Ensure search is enabled
            });

            // Handle selection to maintain max 2 items
            $('#committees').on('select2:select', function(e) {
                var selectedValues = $(this).val();
                
                // If more than 2 items are selected, remove the first one
                if (selectedValues && selectedValues.length > 2) {
                    // Remove the first selected item
                    selectedValues.shift();
                    // Update the select with the new values
                    $(this).val(selectedValues).trigger('change');
                }
            });
        });
    </script>
@endsection