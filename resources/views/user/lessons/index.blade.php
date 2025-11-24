@extends('layouts.user-dashboard')

@section('title', 'Lessons')

@section('content')
    <!-- PAGE-HEADER -->
    <div class="page-header">
        <h1 class="page-title">Lessons</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Lessons</li>
            </ol>
        </div>
    </div>
    <!-- PAGE-HEADER END -->

    <!-- ROW-1 -->
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xl-12">
            <div class="card bg-primary-gradient text-white">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h3 class="card-title text-white"><i class="fe fe-filter me-2"></i> Filter Lessons</h3>
                            <p class="mb-0">Select a committee to filter lessons.</p>
                        </div>
                        <div class="col-md-4">
                            <form action="{{ route('lessons.index') }}" method="GET">
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
        @forelse($lessons as $lesson)
            <div class="col-md-6 col-xl-4">
                <div class="card">
                    @if($lesson->youtube_video_id)
                        <div class="card-img-top bg-primary-transparent text-center py-4">
                            <i class="fe fe-video display-4 text-danger"></i>
                        </div>
                    @else
                        <div class="card-img-top bg-secondary-transparent text-center py-4">
                            <i class="fe fe-file-text display-4 text-secondary"></i>
                        </div>
                    @endif
                    <div class="card-body d-flex flex-column">
                        <h4 class="card-title">
                            <a href="{{ route('lessons.show', $lesson) }}">{{ $lesson->title }}</a>
                            <span class="badge bg-primary-transparent text-primary float-end fs-12">{{ $lesson->committee->name }}</span>
                        </h4>
                        <div class="text-muted mb-3">{{ Str::limit(strip_tags($lesson->content), 100) }}</div>
                        <div class="d-flex align-items-center pt-2 mt-auto">
                            <div class="ms-auto">
                                <a href="{{ route('lessons.show', $lesson) }}" class="btn btn-primary"><i class="fe fe-play me-1"></i> Watch Lesson</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fe fe-slash fs-50 text-muted mb-3"></i>
                        <h3 class="text-muted">No lessons found.</h3>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
    <div class="d-flex justify-content-center">
        {{ $lessons->links() }}
    </div>
    <!-- ROW-2 END -->
@endsection
