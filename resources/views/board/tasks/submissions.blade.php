@extends('layouts.board-dashboard')

@section('title', 'Task Submissions')

@section('content')
    <!-- PAGE-HEADER -->
    <div class="page-header">
        <h1 class="page-title">Task Submissions</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('board.dashboard') }}"><i class="fe fe-home"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('board.tasks.index') }}"><i class="fe fe-check-square"></i> Tasks</a></li>
                <li class="breadcrumb-item active" aria-current="page">Submissions</li>
            </ol>
        </div>
    </div>
    <!-- PAGE-HEADER END -->

    <!-- ROW-1 -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fe fe-list me-2"></i> All Submissions</h3>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('board.tasks.submissions') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Filter by Task</label>
                                    <select name="task_id" class="form-control" onchange="this.form.submit()">
                                        <option value="">All Tasks</option>
                                        @foreach($tasks as $task)
                                            <option value="{{ $task->id }}" {{ $taskId == $task->id ? 'selected' : '' }}>
                                                {{ $task->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>

                    @if($submissions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Task</th>
                                        <th>Submission</th>
                                        <th>Submitted Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($submissions as $submission)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="avatar avatar-sm brround me-2" style="background-image: url({{ $submission->user->image ? asset('storage/' . $submission->user->image) : asset('assets/images/users/default.jpg') }})"></span>
                                                    <div>
                                                        <h6 class="mb-0">{{ $submission->user->name }}</h6>
                                                        <small class="text-muted">{{ $submission->user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('board.tasks.show', $submission->task) }}">
                                                    {{ Str::limit($submission->task->title, 30) }}
                                                </a>
                                            </td>
                                            <td>
                                                @if($submission->text_content)
                                                    <div class="mb-2">
                                                        <i class="fe fe-file-text text-primary me-1"></i>
                                                        <small>Text: {{ Str::limit($submission->text_content, 50) }}</small>
                                                    </div>
                                                @endif
                                                @if($submission->file)
                                                    <div>
                                                        <a href="{{ asset('storage/' . $submission->file) }}" target="_blank" class="text-info">
                                                            <i class="fe fe-paperclip me-1"></i> View File
                                                        </a>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>{{ $submission->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                @if($submission->status == 'accepted')
                                                    <span class="badge bg-success">Accepted</span>
                                                @elseif($submission->status == 'rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                @else
                                                    <span class="badge bg-warning">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($submission->status == 'pending')
                                                    <div class="btn-group" role="group">
                                                        <form action="{{ route('board.tasks.submissions.accept', $submission) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Accept this submission?')">
                                                                <i class="fe fe-check"></i> Accept
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('board.tasks.submissions.reject', $submission) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Reject this submission?')">
                                                                <i class="fe fe-x"></i> Reject
                                                            </button>
                                                        </form>
                                                    </div>
                                                @else
                                                    <span class="text-muted">No action needed</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $submissions->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fe fe-inbox fs-60 text-muted"></i>
                            <h4 class="mt-3">No Submissions Found</h4>
                            <p class="text-muted">There are no task submissions yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- ROW-1 END -->
@endsection
