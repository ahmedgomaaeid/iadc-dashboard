@extends('layouts.highboard-dashboard')

@section('title', 'Lessons')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Lessons</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('highboard.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Lessons</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Field Lessons</h3>
                    <a href="{{ route('highboard.lessons.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>Create Lesson
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($lessons->count() > 0)
                        <div class="row">
                            @foreach($lessons as $lesson)
                                <div class="col-md-6 col-xl-4">
                                    <div class="card border-primary mb-3">
                                        @if($lesson->youtube_video_id)
                                            <div class="card-img-top bg-primary-transparent text-center py-4">
                                                <i class="fe fe-video display-4 text-danger"></i>
                                            </div>
                                        @else
                                            <div class="card-img-top bg-secondary-transparent text-center py-4">
                                                <i class="fe fe-book-open display-4 text-primary"></i>
                                            </div>
                                        @endif
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="card-title mb-0">
                                                    <a href="{{ route('highboard.lessons.show', $lesson) }}" class="text-dark">
                                                        {{ $lesson->title }}
                                                    </a>
                                                </h5>
                                                @if($lesson->is_active)
                                                    <span class="badge bg-success-transparent rounded-pill">Active</span>
                                                @else
                                                    <span class="badge bg-danger-transparent rounded-pill">Inactive</span>
                                                @endif
                                            </div>
                                            
                                            <div class="mt-3">
                                                @if($lesson->tags && count($lesson->tags) > 0)
                                                    <div class="mb-2">
                                                        @foreach(array_slice($lesson->tags, 0, 3) as $tag)
                                                            <span class="badge bg-info-transparent rounded-pill me-1">{{ Str::limit($tag, 20) }}</span>
                                                        @endforeach
                                                        @if(count($lesson->tags) > 3)
                                                            <span class="badge bg-light text-dark rounded-pill">+{{ count($lesson->tags) - 3 }}</span>
                                                        @endif
                                                    </div>
                                                @endif
                                                
                                                <div class="d-flex justify-content-between align-items-center mt-3">
                                                    <div class="text-muted small">
                                                        <i class="fe fe-paperclip me-1"></i>{{ $lesson->attachments_count }} Attachments
                                                    </div>
                                                    <div class="text-muted small">
                                                        <i class="fe fe-users me-1"></i>{{ $lesson->committee->name }}
                                                    </div>
                                                </div>
                                                <div class="mt-2 text-muted small">
                                                    <i class="fe fe-user me-1"></i>
                                                    @if($lesson->highboard_id)
                                                        {{ $lesson->highboard->name }} (Highboard)
                                                    @elseif($lesson->board_id)
                                                        {{ $lesson->board->name }} (Board)
                                                    @else
                                                        Unknown
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer d-flex justify-content-between">
                                            <a href="{{ route('highboard.lessons.show', $lesson) }}" class="btn btn-sm btn-info">
                                                <i class="fe fe-eye me-1"></i>View
                                            </a>
                                            @if($lesson->highboard_id === Auth::guard('highboard')->id() || $lesson->board_id)
                                                <div>
                                                    <a href="{{ route('highboard.lessons.edit', $lesson) }}" class="btn btn-sm btn-warning me-1">
                                                        <i class="fe fe-edit-2"></i>
                                                    </a>
                                                    <form action="{{ route('highboard.lessons.destroy', $lesson) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this lesson?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fe fe-trash-2"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            {{ $lessons->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fe fe-book-open display-4 text-muted"></i>
                            </div>
                            <h4 class="text-muted">No lessons found</h4>
                            <p class="text-muted">Get started by creating a new lesson for a committee.</p>
                            <a href="{{ route('highboard.lessons.create') }}" class="btn btn-primary mt-3">
                                <i class="fe fe-plus me-2"></i>Create Lesson
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
