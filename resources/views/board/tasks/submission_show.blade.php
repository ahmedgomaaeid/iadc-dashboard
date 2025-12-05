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

                    @if($submission->file)
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
                        <form action="{{ route('board.tasks.submissions.accept', $submission) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('Accept this submission?')">
                                <i class="fe fe-check"></i> Accept
                            </button>
                        </form>
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
@endsection
