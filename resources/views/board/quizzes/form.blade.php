@extends('layouts.board-dashboard')

@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ isset($quiz) ? 'Edit Quiz' : 'Create Quiz' }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('board.dashboard') }}">Home</a></li>
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

                    <form action="{{ isset($quiz) ? route('board.quizzes.update', $quiz) : route('board.quizzes.store') }}" method="POST" class="row g-3">
                        @csrf
                        @if (isset($quiz))
                            @method('PUT')
                        @endif

                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fe fe-info me-2"></i>
                                <strong>Committee:</strong> {{ $board->committee->name }}<br>
                                <small>This quiz will be created as <strong>Private</strong> and accessible only to members of your committee.</small>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="name" class="form-label">Quiz Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ isset($quiz) ? $quiz->name : old('name') }}" required>
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ isset($quiz) ? 'Update Quiz' : 'Create Quiz' }}</button>
                            <a href="{{ route('board.quizzes.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
