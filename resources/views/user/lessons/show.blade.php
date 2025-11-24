@extends('layouts.user-dashboard')

@section('title', $lesson->title)

@section('content')
    <!-- PAGE-HEADER -->
    <div class="page-header">
        <h1 class="page-title">{{ $lesson->title }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}"><i class="fe fe-home"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('lessons.index') }}"><i class="fe fe-book-open"></i> Lessons</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $lesson->title }}</li>
            </ol>
        </div>
    </div>
    <!-- PAGE-HEADER END -->

    <!-- ROW-1 -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    @if($lesson->youtube_video_id)
                        <div class="ratio ratio-16x9 mb-4 border rounded">
                            <iframe src="https://www.youtube.com/embed/{{ $lesson->youtube_video_id }}" title="{{ $lesson->title }}" allowfullscreen></iframe>
                        </div>
                    @endif

                    <div class="mt-4 mb-4">
                        <h4 class="card-title">Description</h4>
                        {!! $lesson->content !!}
                    </div>

                    @if($lesson->attachments->count() > 0)
                        <div class="mt-5">
                            <h4 class="card-title"><i class="fe fe-paperclip me-2"></i> Attachments</h4>
                            <div class="list-group">
                                @foreach($lesson->attachments as $attachment)
                                    <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" class="list-group-item list-group-item-action d-flex align-items-center">
                                        <span class="avatar avatar-sm bg-primary-transparent brround me-3">
                                            <i class="fe fe-file text-primary"></i>
                                        </span>
                                        <div>
                                            <h6 class="mb-0">{{ $attachment->file_name }}</h6>
                                            <small class="text-muted">Click to download</small>
                                        </div>
                                        <div class="ms-auto">
                                            <i class="fe fe-download fs-16 text-muted"></i>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <div class="mt-5">
                        <a href="{{ route('lessons.index') }}" class="btn btn-light"><i class="fe fe-arrow-left me-1"></i> Back to Lessons</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ROW-1 END -->
@endsection
