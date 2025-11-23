@extends('layouts.board-dashboard')

@section('title', 'Tasks')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Tasks</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('board.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tasks</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Committee Tasks</h3>
                    <a href="{{ route('board.tasks.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>Create Task
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($tasks->count() > 0)
                        <div class="row">
                            @foreach($tasks as $task)
                                <div class="col-md-6 col-xl-4">
                                    <div class="card border-primary mb-3">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="card-title mb-0">
                                                    <a href="{{ route('board.tasks.show', $task) }}" class="text-dark">
                                                        {{ $task->title }}
                                                    </a>
                                                </h5>
                                                @if($task->is_active)
                                                    <span class="badge bg-success-transparent rounded-pill">Active</span>
                                                @else
                                                    <span class="badge bg-danger-transparent rounded-pill">Inactive</span>
                                                @endif
                                            </div>
                                            
                                            <div class="mt-3">
                                                @if($task->tags && count($task->tags) > 0)
                                                    <div class="mb-2">
                                                        @foreach(array_slice($task->tags, 0, 3) as $tag)
                                                            <span class="badge bg-info-transparent rounded-pill me-1">{{ Str::limit($tag, 20) }}</span>
                                                        @endforeach
                                                        @if(count($task->tags) > 3)
                                                            <span class="badge bg-light text-dark rounded-pill">+{{ count($task->tags) - 3 }}</span>
                                                        @endif
                                                    </div>
                                                @endif
                                                
                                                <div class="d-flex justify-content-between align-items-center mt-3">
                                                    <div class="text-muted small">
                                                        <i class="fe fe-paperclip me-1"></i>{{ $task->attachments_count }} Attachments
                                                    </div>
                                                    <div class="text-muted small">
                                                        <i class="fe fe-clock me-1"></i>{{ $task->created_at->diffForHumans() }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer d-flex justify-content-between">
                                            <a href="{{ route('board.tasks.show', $task) }}" class="btn btn-sm btn-info">
                                                <i class="fe fe-eye me-1"></i>View
                                            </a>
                                            @if($task->board_id === Auth::guard('board')->id())
                                                <div>
                                                    <a href="{{ route('board.tasks.edit', $task) }}" class="btn btn-sm btn-warning me-1">
                                                        <i class="fe fe-edit-2"></i>
                                                    </a>
                                                    <form action="{{ route('board.tasks.destroy', $task) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this task?');">
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
                            {{ $tasks->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fe fe-check-square display-4 text-muted"></i>
                            </div>
                            <h4 class="text-muted">No tasks found</h4>
                            <p class="text-muted">Get started by creating a new task for your committee.</p>
                            <a href="{{ route('board.tasks.create') }}" class="btn btn-primary mt-3">
                                <i class="fe fe-plus me-2"></i>Create Task
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
