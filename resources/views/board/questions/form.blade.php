@extends('layouts.highboard-dashboard')

@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ isset($question) ? 'Edit Question' : 'Create Question' }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('board.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ isset($question) ? 'Edit Question' : 'Create Question' }}</li>
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
                    <h5 class="mb-0">{{ isset($question) ? 'Edit Question' : 'Create Question' }}</h5>
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

                    <form action="{{ isset($question) ? route('board.questions.update', $question) : route('board.quizzes.questions.store', $quiz) }}" method="POST" class="row g-3">
                        @csrf
                        @if (isset($question))
                            @method('PUT')
                        @endif

                        <div class="col-12">
                            <label for="question" class="form-label">Question</label>
                            <textarea name="question" id="question" class="form-control" rows="3" required>{{ isset($question) ? $question->question : old('question') }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="option_a" class="form-label">Option A</label>
                            <input type="text" name="option_a" id="option_a" class="form-control" value="{{ isset($question) ? $question->option_a : old('option_a') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="option_b" class="form-label">Option B</label>
                            <input type="text" name="option_b" id="option_b" class="form-control" value="{{ isset($question) ? $question->option_b : old('option_b') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="option_c" class="form-label">Option C</label>
                            <input type="text" name="option_c" id="option_c" class="form-control" value="{{ isset($question) ? $question->option_c : old('option_c') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="option_d" class="form-label">Option D</label>
                            <input type="text" name="option_d" id="option_d" class="form-control" value="{{ isset($question) ? $question->option_d : old('option_d') }}" required>
                        </div>

                        <div class="col-12">
                            <label for="correct_option" class="form-label">Correct Option</label>
                            <select name="correct_option" id="correct_option" class="form-select" required>
                                <option value="a" {{ isset($question) && $question->correct_option == 'a' ? 'selected' : '' }}>A</option>
                                <option value="b" {{ isset($question) && $question->correct_option == 'b' ? 'selected' : '' }}>B</option>
                                <option value="c" {{ isset($question) && $question->correct_option == 'c' ? 'selected' : '' }}>C</option>
                                <option value="d" {{ isset($question) && $question->correct_option == 'd' ? 'selected' : '' }}>D</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="time_limit" class="form-label">Time Limit (seconds)</label>
                            <input type="number" name="time_limit" id="time_limit" class="form-control"
                                   value="{{ isset($question) ? $question->time_limit : old('time_limit', 20) }}"
                                   min="5" max="300" required>
                            <div class="form-text">How many seconds participants have to answer this question (5-300 seconds)</div>
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ isset($question) ? 'Update Question' : 'Add Question' }}</button>
                            <a href="{{ isset($question) ? route('board.quizzes.show', $question->quiz) : route('board.quizzes.show', $quiz) }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
