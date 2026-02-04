@extends('layouts.user-dashboard')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1 class="page-title">Evaluate Instructor</h1>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $googleSession->title }}</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($existingEvaluation)
                        <div class="alert alert-info border-0 bg-info-transparent">
                            <i class="fe fe-check-circle me-2"></i>
                            You have already evaluated this session.
                        </div>
                    @endif

                    <form action="{{ route('user.sessions.evaluate.store', $googleSession->id) }}" method="POST">
                        @csrf
                        
                        <div class="mb-4 text-center">
                            <label class="form-label d-block">Rate the Instructor</label>
                            <div class="rating-stars">
                                <input type="hidden" name="rating" id="ratingInput" value="{{ $existingEvaluation ? $existingEvaluation->rating : 0 }}">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fe fe-star fs-30 {{ $existingEvaluation ? '' : 'cursor-pointer' }} star-icon text-muted" data-value="{{ $i }}"></i>
                                @endfor
                            </div>
                            @error('rating')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="message" class="form-label">Message (Optional)</label>
                            <textarea class="form-control" id="message" name="message" rows="4" placeholder="Write your feedback here..." {{ $existingEvaluation ? 'readonly disabled' : '' }}>{{ $existingEvaluation ? $existingEvaluation->message : '' }}</textarea>
                            @error('message')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        @if(!$existingEvaluation)
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Submit Evaluation</button>
                        </div>
                        @else
                        <div class="d-grid">
                            <button type="button" class="btn btn-secondary" disabled>Evaluation Submitted</button>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const stars = document.querySelectorAll('.star-icon');
        const ratingInput = document.getElementById('ratingInput');
        let currentRating = parseInt(ratingInput.value) || 0;

        function updateStars(value) {
            stars.forEach(star => {
                const starValue = parseInt(star.getAttribute('data-value'));
                if (starValue <= value) {
                    star.classList.add('text-warning');
                    star.classList.remove('text-muted');
                    // Fill star class if available in theme, for now text color is enough
                } else {
                    star.classList.remove('text-warning');
                    star.classList.add('text-muted');
                }
            });
        }

        const isReadOnly = {{ $existingEvaluation ? 'true' : 'false' }};

        updateStars(currentRating);

        if (!isReadOnly) {
            stars.forEach(star => {
                star.addEventListener('mouseover', function() {
                    updateStars(parseInt(this.getAttribute('data-value')));
                });

                star.addEventListener('mouseout', function() {
                    updateStars(currentRating);
                });

                star.addEventListener('click', function() {
                    currentRating = parseInt(this.getAttribute('data-value'));
                    ratingInput.value = currentRating;
                    updateStars(currentRating);
                });
            });
        }
    });
</script>
<style>
    .cursor-pointer { cursor: pointer; }
    .text-warning { color: #f1c40f !important; }
    .fs-30 { font-size: 30px; }
</style>
@endsection
