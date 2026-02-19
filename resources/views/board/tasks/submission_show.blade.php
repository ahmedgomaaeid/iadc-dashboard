@extends('layouts.board-dashboard')

@section('title', 'Submission Details')

@section('content')
    <!-- PAGE-HEADER -->
    <div class="page-header">
        <h1 class="page-title">Submission Details</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('board.dashboard') }}"><i class="fe fe-home"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('board.tasks.index') }}"><i class="fe fe-check-square"></i> Tasks</a></li>
                <li class="breadcrumb-item"><a href="{{ route('board.tasks.submissions') }}">Submissions</a></li>
                <li class="breadcrumb-item active" aria-current="page">Details</li>
            </ol>
        </div>
    </div>
    <!-- PAGE-HEADER END -->

    <!-- ROW-1 -->
    <div class="row">
        <div class="col-xl-8 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Submission Content</h3>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label">Task</label>
                        <h4><a href="{{ route('board.tasks.show', $submission->task) }}">{{ $submission->task->title }}</a></h4>
                    </div>

                    @if($submission->text_content)
                        <div class="mb-4">
                            <label class="form-label">Text Submission</label>
                            <div class="p-4 bg-light br-5 border">
                                {!! nl2br(e($submission->text_content)) !!}
                            </div>
                        </div>
                    @endif

                    @if($submission->files && count($submission->files) > 0)
                        <div class="mb-4">
                            <label class="form-label">Attachments</label>
                            <div class="row">
                                @foreach($submission->files as $file)
                                    @php
                                        $extension = pathinfo($file, PATHINFO_EXTENSION);
                                        $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                    @endphp
                                    <div class="col-md-6 mb-3">
                                        @if($isImage)
                                            <div class="card border">
                                                <a href="{{ asset('storage/' . $file) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $file) }}" class="card-img-top" alt="Attachment" style="height: 200px; object-fit: cover;">
                                                </a>
                                                <div class="card-body p-2 text-center">
                                                     <a href="{{ asset('storage/' . $file) }}" target="_blank" class="btn btn-primary btn-sm"><i class="fe fe-eye"></i> View</a>
                                                     <a href="{{ asset('storage/' . $file) }}" download class="btn btn-secondary btn-sm"><i class="fe fe-download"></i> Download</a>
                                                </div>
                                            </div>
                                        @else
                                            <div class="p-3 bg-light border br-5 d-flex align-items-center">
                                                <span class="avatar avatar-md brround bg-primary me-3">
                                                    <i class="fe fe-file fs-18 text-white"></i>
                                                </span>
                                                <div class="overflow-hidden w-100">
                                                    <h6 class="mb-1 text-truncate" title="{{ basename($file) }}">{{ basename($file) }}</h6>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ asset('storage/' . $file) }}" target="_blank" class="text-primary fs-12"><i class="fe fe-eye me-1"></i> View</a>
                                                        <a href="{{ asset('storage/' . $file) }}" download class="text-secondary fs-12"><i class="fe fe-download me-1"></i> Download</a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @elseif($submission->file)
                        <div class="mb-4">
                            <label class="form-label">Attachment</label>
                            @php
                                $extension = pathinfo($submission->file, PATHINFO_EXTENSION);
                                $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            @endphp

                            @if($isImage)
                                <div class="text-center p-3 bg-light border br-5">
                                    <img src="{{ asset('storage/' . $submission->file) }}" alt="Submission Attachment" class="img-fluid rounded" style="max-height: 500px;">
                                    <div class="mt-2">
                                        <a href="{{ asset('storage/' . $submission->file) }}" target="_blank" class="btn btn-primary btn-sm">
                                            <i class="fe fe-maximize"></i> View Full Size
                                        </a>
                                        <a href="{{ asset('storage/' . $submission->file) }}" download class="btn btn-secondary btn-sm">
                                            <i class="fe fe-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="p-3 bg-light border br-5 d-flex align-items-center">
                                    <span class="avatar avatar-lg brround bg-primary me-3">
                                        <i class="fe fe-file fs-20 text-white"></i>
                                    </span>
                                    <div>
                                        <h5 class="mb-1">{{ basename($submission->file) }}</h5>
                                        <a href="{{ asset('storage/' . $submission->file) }}" target="_blank" class="text-primary me-3">
                                            <i class="fe fe-eye"></i> View
                                        </a>
                                        <a href="{{ asset('storage/' . $submission->file) }}" download class="text-secondary">
                                            <i class="fe fe-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="card-footer text-end">
                    @if($submission->status == 'pending')
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#acceptModal">
                            <i class="fe fe-check"></i> Accept & Evaluate
                        </button>
                        <form action="{{ route('board.tasks.submissions.reject', $submission) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Reject this submission?')">
                                <i class="fe fe-x"></i> Reject
                            </button>
                        </form>
                    @else
                        <span class="badge bg-{{ $submission->status == 'accepted' ? 'success' : 'danger' }} fs-14">
                            {{ ucfirst($submission->status) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Student Details</h3>
                </div>
                <div class="card-body text-center">
                    <span class="avatar avatar-xxl brround cover-image mb-4" 
                        style="background-image: url({{ $submission->user->image ? asset('storage/' . $submission->user->image) : asset('assets/images/users/default.jpg') }})"></span>
                    <h4 class="mb-1">{{ $submission->user->name }}</h4>
                    <p class="text-muted mb-1">{{ $submission->user->email }}</p>
                    <p class="text-muted">{{ $submission->user->phone }}</p>
                    
                    <div class="mt-4 text-start">
                        <table class="table table-bordered">
                            <tr>
                                <th>Submitted</th>
                                <td>{{ $submission->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if($submission->status == 'accepted')
                                        <span class="badge bg-success">Accepted</span>
                                    @elseif($submission->status == 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ROW-1 END -->

    <!-- Accept Modal -->
    <div class="modal fade" id="acceptModal" tabindex="-1" aria-labelledby="acceptModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('board.tasks.submissions.accept', $submission) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="acceptModalLabel">Evaluate Submission</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Please evaluate this submission before accepting it.</p>
                        <div class="mb-3">
                            <label for="score" class="form-label">Score (1-10)</label>
                            <input type="number" class="form-control" id="score" name="score" min="1" max="10" required>
                            <div class="form-text">Assign a score between 1 and 10 for this task submission.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Accept & Save Score</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
