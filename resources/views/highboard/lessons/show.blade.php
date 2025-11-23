@extends('layouts.highboard-dashboard')

@section('title', $lesson->title)

@section('content')
    <div class="page-header">
        <h1 class="page-title">Lesson Details</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('highboard.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('highboard.lessons.index') }}">Lessons</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($lesson->title, 20) }}</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="card-title mb-0">{{ $lesson->title }}</h2>
                        @if($lesson->is_active)
                            <span class="badge bg-success-transparent rounded-pill">Active</span>
                        @else
                            <span class="badge bg-danger-transparent rounded-pill">Inactive</span>
                        @endif
                    </div>

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

                    @if($lesson->content)
                        <div class="mb-4">
                            <div class="content-section">
                                {!! $lesson->content !!}
                            </div>
                        </div>
                    @endif

                    @if($lesson->tags && count($lesson->tags) > 0)
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="fe fe-link me-2"></i>Links
                            </h5>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($lesson->tags as $tag)
                                    <a href="{{ $tag }}" target="_blank" class="btn btn-outline-info btn-sm rounded-pill">
                                        <i class="fe fe-external-link me-1"></i>{{ Str::limit($tag, 50) }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($lesson->attachments->count() > 0)
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="fe fe-paperclip me-2"></i>Attachments
                            </h5>
                            <div class="row">
                                @foreach($lesson->attachments as $attachment)
                                    <div class="col-md-6 mb-3">
                                        <div class="card border mb-0">
                                            <div class="card-body p-3 d-flex align-items-center">
                                                <div class="me-3">
                                                    <span class="avatar avatar-md bg-primary-transparent text-primary rounded">
                                                        <i class="{{ $attachment->icon }} fs-20"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <h6 class="mb-1 text-truncate" title="{{ $attachment->file_name }}">
                                                        {{ $attachment->file_name }}
                                                    </h6>
                                                    <small class="text-muted">{{ $attachment->formatted_size }}</small>
                                                </div>
                                                <div class="ms-2">
                                                    <a href="{{ Storage::url($attachment->file_path) }}" 
                                                       class="btn btn-sm btn-icon btn-primary-transparent rounded-pill" 
                                                       download="{{ $attachment->file_name }}"
                                                       title="Download">
                                                        <i class="fe fe-download"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('highboard.lessons.index') }}" class="btn btn-secondary">
                            <i class="fe fe-arrow-left me-2"></i>Back to Lessons
                        </a>
                        @if($lesson->highboard_id === Auth::guard('highboard')->id() || $lesson->board_id)
                            <div class="d-flex gap-2">
                                <a href="{{ route('highboard.lessons.edit', $lesson) }}" class="btn btn-warning">
                                    <i class="fe fe-edit-2 me-2"></i>Edit
                                </a>
                                <form action="{{ route('highboard.lessons.destroy', $lesson) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lesson?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fe fe-trash-2 me-2"></i>Delete
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Lesson Info</h3>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Created By</span>
                            <span class="fw-medium">
                                @if($lesson->highboard_id)
                                    {{ $lesson->highboard->name }} (Highboard)
                                @elseif($lesson->board_id)
                                    {{ $lesson->board->name }} (Board)
                                @else
                                    Unknown
                                @endif
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Committee</span>
                            <span class="fw-medium">{{ $lesson->committee->name }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Created At</span>
                            <span class="fw-medium">{{ $lesson->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Last Updated</span>
                            <span class="fw-medium">{{ $lesson->updated_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
