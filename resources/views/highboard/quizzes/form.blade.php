@extends('layouts.highboard-dashboard')

@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ isset($quiz) ? 'Edit Quiz' : 'Create Quiz' }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('highboard.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ isset($quiz) ? 'Edit Quiz' : 'Create Quiz' }}</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">{{ isset($quiz) ? 'Edit Quiz' : 'Create Quiz' }}</h5>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>There were some problems with your input.</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ isset($quiz) ? route('highboard.quizzes.update', $quiz) : route('highboard.quizzes.store') }}" method="POST" class="row g-3">
                        @csrf
                        @if (isset($quiz))
                            @method('PUT')
                        @endif

                        <div class="col-12">
                            <label for="name" class="form-label">Quiz Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ isset($quiz) ? $quiz->name : old('name') }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Visibility</label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input type="radio" name="visibility" id="visibility_global" class="form-check-input" value="global" 
                                        {{ (!isset($quiz) || $quiz->visibility === 'global') ? 'checked' : '' }} required>
                                    <label for="visibility_global" class="form-check-label">
                                        <i class="fe fe-globe me-1"></i> Global (Everyone)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" name="visibility" id="visibility_private" class="form-check-input" value="private" 
                                        {{ (isset($quiz) && $quiz->visibility === 'private') ? 'checked' : '' }} required>
                                    <label for="visibility_private" class="form-check-label">
                                        <i class="fe fe-lock me-1"></i> Private (Committee Members Only)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12" id="committee_section" style="display: {{ (isset($quiz) && $quiz->visibility === 'private') ? 'block' : 'none' }};">
                            <label for="committee_id" class="form-label">Committee <span class="text-danger">*</span></label>
                            <select name="committee_id" id="committee_id" class="form-control form-select">
                                <option value="">Select Committee</option>
                                @foreach($committees as $committee)
                                    <option value="{{ $committee->id }}" {{ (isset($quiz) && $quiz->committee_id == $committee->id) ? 'selected' : '' }}>
                                        {{ $committee->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Only members of this committee will be able to access this quiz.</small>
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ isset($quiz) ? 'Update Quiz' : 'Create Quiz' }}</button>
                            <a href="{{ route('highboard.quizzes.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle committee dropdown based on visibility selection
        document.querySelectorAll('input[name="visibility"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const committeeSection = document.getElementById('committee_section');
                const committeeSelect = document.getElementById('committee_id');
                
                if (this.value === 'private') {
                    committeeSection.style.display = 'block';
                    committeeSelect.required = true;
                } else {
                    committeeSection.style.display = 'none';
                    committeeSelect.required = false;
                    committeeSelect.value = '';
                }
            });
        });
    </script>
@endsection
