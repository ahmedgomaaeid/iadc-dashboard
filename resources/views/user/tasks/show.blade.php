@extends('layouts.user-dashboard')

@section('title', $task->title)

@section('content')
    <!-- PAGE-HEADER -->
    <div class="page-header">
        <h1 class="page-title">{{ $task->title }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}"><i class="fe fe-home"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}"><i class="fe fe-check-square"></i> Tasks</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $task->title }}</li>
            </ol>
        </div>
    </div>
    <!-- PAGE-HEADER END -->

    <!-- ROW-1 -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="mb-4">
                        <h4 class="card-title">Description</h4>
                        {!! $task->content !!}
                    </div>

                    @if($task->attachments->count() > 0)
                        <div class="mt-5">
                            <h4 class="card-title"><i class="fe fe-paperclip me-2"></i> Attachments</h4>
                            <div class="list-group">
                                @foreach($task->attachments as $attachment)
                                    <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" class="list-group-item list-group-item-action d-flex align-items-center">
                                        <span class="avatar avatar-sm bg-success-transparent brround me-3">
                                            <i class="fe fe-file text-success"></i>
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
                        <a href="{{ route('tasks.index') }}" class="btn btn-light"><i class="fe fe-arrow-left me-1"></i> Back to Tasks</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fe fe-upload-cloud me-2"></i> Submission</h3>
                </div>
                <div class="card-body">
                    @if($submission)
                        <div class="alert alert-{{ $submission->status == 'accepted' ? 'success' : ($submission->status == 'rejected' ? 'danger' : 'warning') }} mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fe fe-{{ $submission->status == 'accepted' ? 'check-circle' : ($submission->status == 'rejected' ? 'x-circle' : 'alert-circle') }} me-2 fs-18"></i>
                                <div>
                                    <h5 class="alert-heading mb-0">Status: {{ ucfirst($submission->status) }}</h5>
                                </div>
                            </div>
                        </div>
                        
                        <div class="list-group mb-4">
                            <div class="list-group-item d-flex align-items-center">
                                <i class="fe fe-calendar me-3 text-muted"></i>
                                <div>
                                    <small class="text-muted d-block">Submitted on</small>
                                    <span class="fw-semibold">{{ $submission->created_at->format('M d, Y H:i') }}</span>
                                </div>
                            </div>
                        </div>

                        @if($submission->text_content)
                            <div class="mb-3">
                                <h6 class="mb-2"><i class="fe fe-file-text me-1"></i> Text Submission</h6>
                                <div class="p-3 bg-light rounded">
                                    {!! nl2br(e($submission->text_content)) !!}
                                </div>
                            </div>
                        @endif

                        @if($submission->file)
                            <a href="{{ asset('storage/' . $submission->file) }}" class="btn btn-info btn-block w-100 mb-3" target="_blank">
                                <i class="fe fe-eye me-1"></i> View File Submission
                            </a>
                        @endif
                        
                        @if($submission->status == 'rejected')
                             <div class="alert alert-danger mt-3">
                                <i class="fe fe-alert-triangle me-1"></i> Your submission was rejected. Please upload again.
                             </div>
                             <form action="{{ route('tasks.submit', $task) }}" method="POST" enctype="multipart/form-data" class="mt-3">
                                @csrf
                                <div class="form-group mb-3">
                                    <label class="form-label">Text Content (Optional)</label>
                                    <textarea class="form-control" name="text_content" rows="4" placeholder="Enter your answer here..."></textarea>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label">Upload File (Optional)</label>
                                    <input type="file" class="form-control" name="file">
                                    <small class="text-muted">Provide either text content or a file, or both.</small>
                                </div>
                                @error('submission')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                                <button type="submit" class="btn btn-primary btn-block w-100"><i class="fe fe-refresh-cw me-1"></i> Resubmit</button>
                            </form>
                        @endif
                    @else
                        <div class="text-center mb-4">
                            <span class="bg-light brround p-3 d-inline-block">
                                <i class="fe fe-upload-cloud fs-30 text-primary"></i>
                            </span>
                            <h5 class="mt-3">Upload your solution</h5>
                            <p class="text-muted text-small">Submit your answer as text or file.</p>
                        </div>
                        <form action="{{ route('tasks.submit', $task) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group mb-4">
                                <label class="form-label">Text Content (Optional)</label>
                                <textarea class="form-control" name="text_content" rows="5" placeholder="Type your answer here..."></textarea>
                            </div>
                            <div class="form-group mb-4">
                                <label class="form-label">Upload File (Optional)</label>
                                <input type="file" class="form-control" name="file">
                                <small class="text-muted">Provide either text content or a file, or both.</small>
                            </div>
                            @error('submission')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                            <button type="submit" class="btn btn-primary btn-block w-100"><i class="fe fe-send me-1"></i> Submit</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- ROW-1 END -->
@endsection
