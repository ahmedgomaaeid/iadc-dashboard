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
                            <div class="form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ isset($quiz) && $quiz->is_active ? 'checked' : '' }}>
                                <label for="is_active" class="form-check-label">Active</label>
                            </div>
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
@endsection
