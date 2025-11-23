@extends('layouts.board-dashboard')

@section('title', $lesson->title)

@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ $lesson->title }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('board.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('board.lessons.index') }}">Lessons</a></li>
                <li class="breadcrumb-item active" aria-current="page">View</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">{{ $lesson->title }}</h3>
                    <div>
                        <span class="badge bg-{{ $lesson->is_active ? 'success' : 'danger' }}">
                            {{ $lesson->is_active ? 'Active' : 'Draft' }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- YouTube Video Section -->
                    @if($lesson->youtube_video_id)
                        <div class="mb-4">
                            <div class="ratio ratio-16x9">
                                <iframe 
                                    src="https://www.youtube.com/embed/{{ $lesson->youtube_video_id }}?controls=0&rel=0&modestbranding=1"
                                    title="{{ $lesson->title }}"
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; encrypted-media;" 
                                    allowfullscreen>
                                </iframe>
                                <script>
                                    var iframe = document.querySelector('iframe');
                                    iframe.addEventListener('load', function() {
                                        const new_style_element = document.createElement("style");
                                        // disapear the youtube logo
                                        new_style_element.innerHTML = ".ytp-watermark { display: none !important; }";
                                        document.head.appendChild(new_style_element);
                                    });
                                </script>
                            </div>
                        </div>
                    @endif

                    <!-- Content Section -->
                    @if($lesson->content)
                        <div class="mb-4">
                            <div class="content-section">
                                {!! $lesson->content !!}
                            </div>
                        </div>
                    @endif

                    <!-- Tags Section -->
                    @if($lesson->tags && count($lesson->tags) > 0)
                        <div class="mb-4">
                            <h5>Links</h5>
                            <div>
                                @foreach($lesson->tags as $tag)
                                    <a href="{{ $tag }}" target="_blank" class="badge bg-info me-2 mb-2 text-decoration-none">
                                        <i class="fe fe-link me-1"></i>{{ Str::limit($tag, 50) }}
                                        <i class="fe fe-external-link ms-1"></i>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Metadata -->
                    <div class="border-top pt-3 mt-4">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fe fe-user me-1"></i>
                                    Created by: <strong>
                                        @if($lesson->highboard_id)
                                            {{ $lesson->highboard->name }} (Highboard)
                                        @elseif($lesson->board_id)
                                            {{ $lesson->board->name }}
                                        @else
                                            Unknown
                                        @endif
                                    </strong>
                                </small>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <small class="text-muted">
                                    <i class="fe fe-calendar me-1"></i>
                                    {{ $lesson->created_at->format('F d, Y \a\t h:i A') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            @if($lesson->board_id === auth('board')->id())
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <a href="{{ route('board.lessons.edit', $lesson) }}" class="btn btn-primary">
                                <i class="fe fe-edit me-2"></i>Edit Lesson
                            </a>
                            <form action="{{ route('board.lessons.destroy', $lesson) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete this lesson? All attached files will be deleted.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fe fe-trash-2 me-2"></i>Delete Lesson
                                </button>
                            </form>
                            <a href="{{ route('board.lessons.index') }}" class="btn btn-secondary">
                                <i class="fe fe-arrow-left me-2"></i>Back to Lessons
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Attachments -->
            @if($lesson->attachments->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Attachments ({{ $lesson->attachments->count() }})</h3>
                    </div>
                    <div class="card-body">
                        @foreach($lesson->attachments as $attachment)
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center">
                                        <i class="{{ $attachment->icon }} me-2 fs-4"></i>
                                        <div>
                                            <div class="fw-bold">{{ Str::limit($attachment->file_name, 30) }}</div>
                                            <small class="text-muted">{{ $attachment->formatted_size }}</small>
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ asset('storage/' . $attachment->file_path) }}" 
                                   download="{{ $attachment->file_name }}"
                                   class="btn btn-sm btn-primary">
                                    <i class="fe fe-download"></i>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Lesson Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Lesson Information</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="fw-bold d-block text-muted">Committee</label>
                        <span>{{ $lesson->committee->name }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold d-block text-muted">Status</label>
                        <span class="badge bg-{{ $lesson->is_active ? 'success' : 'danger' }}">
                            {{ $lesson->is_active ? 'Active' : 'Draft' }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold d-block text-muted">Created</label>
                        <span>{{ $lesson->created_at->diffForHumans() }}</span>
                    </div>
                    @if($lesson->updated_at != $lesson->created_at)
                        <div class="mb-3">
                            <label class="fw-bold d-block text-muted">Last Updated</label>
                            <span>{{ $lesson->updated_at->diffForHumans() }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
