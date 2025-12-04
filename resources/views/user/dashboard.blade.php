@extends('layouts.user-dashboard')

@section('title', 'User Dashboard')

@section('content')
    <!-- PAGE-HEADER -->
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </div>
    </div>
    <!-- PAGE-HEADER END -->

    <!-- ROW-1 -->
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xl-12">
            <div class="card bg-success-gradient text-white">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h3 class="card-title text-white"><i class="fe fe-filter me-2"></i> Filter Content</h3>
                            <p class="mb-0">Select a committee to see specific lessons and quizzes.</p>
                        </div>
                        <div class="col-md-4">
                            <form action="{{ route('user.dashboard') }}" method="GET">
                                <select name="committee_id" class="form-control form-select" onchange="this.form.submit()" style="background-color: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3);">
                                    <option value="" class="text-dark">All Committees</option>
                                    @foreach($committees as $committee)
                                        <option value="{{ $committee->id }}" {{ $selectedCommitteeId == $committee->id ? 'selected' : '' }} class="text-dark">
                                            {{ $committee->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ROW-1 END -->

    <!-- ROW-2 -->
    <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-6">
            <div class="card">
                <div class="card-header border-bottom-0">
                    <h3 class="card-title"><i class="fe fe-book-open text-primary me-2"></i> New Lessons</h3>
                    <div class="card-options">
                        <a href="{{ route('lessons.index') }}" class="btn btn-sm btn-light">View All <i class="fe fe-arrow-right ms-1"></i></a>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 text-nowrap border-bottom">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0">Title</th>
                                    <th class="border-bottom-0">Committee</th>
                                    <th class="border-bottom-0">Date</th>
                                    <th class="border-bottom-0">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLessons as $lesson)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="bg-primary-transparent brround p-2 me-2">
                                                    <i class="fe fe-video text-primary"></i>
                                                </span>
                                                <a href="{{ route('lessons.show', $lesson) }}">
                                                    {{ Str::limit($lesson->title, 20) }}
                                                </a>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light text-dark">{{ $lesson->committee->name }}</span></td>
                                        <td>{{ $lesson->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('lessons.show', $lesson) }}" class="btn btn-primary btn-sm btn-icon"><i class="fe fe-play"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">
                                            <i class="fe fe-slash fs-30 mb-2 d-block"></i>
                                            No recent lessons found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-6">
            <div class="card">
                <div class="card-header border-bottom-0">
                    <h3 class="card-title"><i class="fe fe-award text-primary me-2"></i> New Quizzes</h3>
                    <div class="card-options">
                        <a href="{{ route('user.quizzes.index') }}" class="btn btn-sm btn-light">View All <i class="fe fe-arrow-right ms-1"></i></a>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 text-nowrap border-bottom">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0">Title</th>
                                    <th class="border-bottom-0">Committee</th>
                                    <th class="border-bottom-0">Date</th>
                                    <th class="border-bottom-0">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentQuizzes as $quiz)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="bg-primary-transparent brround p-2 me-2">
                                                    <i class="fe fe-award text-primary"></i>
                                                </span>
                                                <a href="{{ route('quiz.show', $quiz->id) }}">
                                                    {{ Str::limit($quiz->name, 20) }}
                                                </a>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light text-dark">{{ $quiz->committee->name }}</span></td>
                                        <td>{{ $quiz->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('quiz.show', $quiz->id) }}" class="btn btn-primary btn-sm btn-icon"><i class="fe fe-play"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">
                                            <i class="fe fe-slash fs-30 mb-2 d-block"></i>
                                            No recent quizzes found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ROW-2 END -->
@endsection
