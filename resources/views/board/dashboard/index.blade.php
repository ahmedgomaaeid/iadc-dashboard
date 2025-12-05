@extends('layouts.board-dashboard')

@section('title', 'Board Dashboard')

@section('content')
    <!-- PAGE-HEADER -->
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </div>
    </div>
    <!-- PAGE-HEADER END -->

    <!-- Welcome Message -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-1">Welcome, {{ $board->name }}!</h4>
                    <p class="text-muted">
                        @if($board->committee)
                            You are managing members of <strong>{{ $board->committee->name }}</strong> committee.
                        @else
                            You are managing your committee members.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row">
        <div class="col-lg-4 col-md-12 col-sm-12 col-xl-4">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="mt-2">
                            <h6 class="">Total Members</h6>
                            <h2 class="mb-0 number-font">{{ $totalMembers }}</h2>
                        </div>
                        <div class="ms-auto">
                            <div class="chart-wrapper mt-1">
                                <span class="avatar avatar-lg bg-primary-transparent text-primary">
                                    <i class="fe fe-users fs-20"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 col-sm-12 col-xl-4">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="mt-2">
                            <h6 class="">Active Members</h6>
                            <h2 class="mb-0 number-font">{{ $activeMembers }}</h2>
                        </div>
                        <div class="ms-auto">
                            <div class="chart-wrapper mt-1">
                                <span class="avatar avatar-lg bg-success-transparent text-success">
                                    <i class="fe fe-user-check fs-20"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 col-sm-12 col-xl-4">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="mt-2">
                            <h6 class="">Inactive Members</h6>
                            <h2 class="mb-0 number-font">{{ $inactiveMembers }}</h2>
                        </div>
                        <div class="ms-auto">
                            <div class="chart-wrapper mt-1">
                                <span class="avatar avatar-lg bg-warning-transparent text-warning">
                                    <i class="fe fe-user-x fs-20"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fe fe-list me-2"></i> New Tasks Submissions</h3>
                </div>
                <div class="card-body">

                    @if($submissions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Task</th>
                                        <th>Submitted Date</th>
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
                                            <td>{{ $submission->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('board.tasks.submissions.show', $submission) }}" class="btn btn-sm btn-info me-1">
                                                    <i class="fe fe-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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

@endsection
