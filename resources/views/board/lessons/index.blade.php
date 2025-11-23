@extends('layouts.board-dashboard')

@section('title', 'Lessons Management')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Lessons</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('board.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Lessons</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-12 text-end">
            <a href="{{ route('board.lessons.create') }}" class="btn btn-primary">
                <i class="fe fe-plus me-2"></i>Add New Lesson
            </a>
        </div>
    </div>

    <div class="row">
        @forelse($lessons as $lesson)
            <div class="col-lg-4 col-md-6 col-12">
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
                    
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="{{ route('board.lessons.show', $lesson) }}" class="text-dark">
                                {{ $lesson->title }}
                            </a>
                        </h5>
                        

                        <div class="mt-3">
                            @if($lesson->tags && count($lesson->tags) > 0)
                                <div class="mb-2">
                                    @foreach(array_slice($lesson->tags, 0, 2) as $tag)
                                        <span class="badge bg-info me-1">
                                            <i class="fe fe-link me-1"></i>Link
                                        </span>
                                    @endforeach
                                    @if(count($lesson->tags) > 2)
                                        <span class="badge bg-secondary">+{{ count($lesson->tags) - 2 }}</span>
                                    @endif
                                </div>
                            @endif

                            @if($lesson->attachments_count > 0)
                                <span class="badge bg-success me-2">
                                    <i class="fe fe-paperclip me-1"></i>{{ $lesson->attachments_count }} {{ Str::plural('file', $lesson->attachments_count) }}
                                </span>
                            @endif

                            <span class="badge bg-{{ $lesson->is_active ? 'success' : 'danger' }}">
                                {{ $lesson->is_active ? 'Active' : 'Draft' }}
                            </span>
                        </div>

                        <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fe fe-calendar me-1"></i>{{ $lesson->created_at->format('M d, Y') }}
                            </small>
                            
                            <div class="btn-group" role="group">
                                <a href="{{ route('board.lessons.show', $lesson) }}" 
                                   class="btn btn-sm btn-info" title="View">
                                    <i class="fe fe-eye"></i>
                                </a>
                                <a href="{{ route('board.lessons.edit', $lesson) }}" 
                                   class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>
                                <form action="{{ route('board.lessons.destroy', $lesson) }}" 
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this lesson? All attached files will be deleted.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="fe fe-trash-2"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fe fe-book-open display-1 text-muted mb-3"></i>
                        <h4>No Lessons Found</h4>
                        <p class="text-muted">Start creating lessons for your committee members.</p>
                        <a href="{{ route('board.lessons.create') }}" class="btn btn-primary mt-3">
                            <i class="fe fe-plus me-2"></i>Create Your First Lesson
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if($lessons->hasPages())
        <div class="row mt-4">
            <div class="col-12">
                {{ $lessons->links() }}
            </div>
        </div>
    @endif
@endsection
