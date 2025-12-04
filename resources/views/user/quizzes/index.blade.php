@extends('layouts.user-dashboard')

@section('title', 'My Quizzes')

@section('content')
    <!-- PAGE-HEADER -->
    <div class="page-header">
        <h1 class="page-title">My Committee Quizzes</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Quizzes</li>
            </ol>
        </div>
    </div>
    <!-- PAGE-HEADER END -->

    <!-- Private Quizzes Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success-gradient">
                    <h3 class="card-title text-white"><i class="fe fe-award me-2"></i>Available Quizzes</h3>
                </div>
                <div class="card-body">
                    @if($quizzes->count() > 0)
                        <div class="row">
                            @foreach($quizzes as $quiz)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card border border-primary">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-3">
                                                <span class="bg-primary-transparent brround p-3 me-3">
                                                    <i class="fe fe-award text-primary fs-20"></i>
                                                </span>
                                                <div>
                                                    <h5 class="mb-0">{{ $quiz->name }}</h5>
                                                    <span class="badge bg-primary mt-1">
                                                        <i class="fe fe-users me-1"></i> {{ $quiz->committee->name }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    <i class="fe fe-info me-1"></i>
                                                    Only {{ $quiz->committee->name }} members can join
                                                </small>
                                            </div>
                                            <a href="{{ route('quiz.show', $quiz->id) }}" class="btn btn-primary btn-block w-100">
                                                <i class="fe fe-play me-2"></i>Join Quiz
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="fe fe-slash fs-30 mb-2 d-block"></i>
                            <p class="fs-16">No quizzes available from your committees at the moment.</p>
                            <small class="text-muted">Check back later or contact your committee board members.</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
