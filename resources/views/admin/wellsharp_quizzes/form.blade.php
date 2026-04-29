@extends('layouts.admin-dashboard')

@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ isset($quiz) ? 'Edit WellSharp Quiz' : 'Create WellSharp Quiz' }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.wellsharp_quizzes.index') }}">WellSharp Quizzes</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ isset($quiz) ? 'Edit' : 'Create' }}</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">{{ isset($quiz) ? 'Edit WellSharp Quiz' : 'Create WellSharp Quiz' }}</h5>
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

                    <form action="{{ isset($quiz) ? route('admin.wellsharp_quizzes.update', $quiz) : route('admin.wellsharp_quizzes.store') }}" method="POST" class="row g-3">
                        @csrf
                        @if (isset($quiz))
                            @method('PUT')
                        @endif

                        <div class="col-12">
                            <label for="name" class="form-label">Quiz Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ isset($quiz) ? $quiz->name : old('name') }}" required>
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ isset($quiz) ? 'Update Quiz' : 'Create Quiz' }}</button>
                            <a href="{{ route('admin.wellsharp_quizzes.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
