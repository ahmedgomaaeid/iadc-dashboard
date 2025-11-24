@extends('layouts.user-dashboard')

@section('title', 'Quizzes')

@section('content')
    <!-- PAGE-HEADER -->
    <div class="page-header">
        <h1 class="page-title">Quizzes</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Quizzes</li>
            </ol>
        </div>
    </div>
    <!-- PAGE-HEADER END -->

    <!-- ROW-1 -->
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xl-12">
            <div class="card bg-success-gradient text-white">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h3 class="card-title text-white"><i class="fe fe-filter me-2"></i> Filter Quizzes</h3>
                            <p class="mb-0">Select a committee to filter quizzes.</p>
                        </div>
                        <div class="col-md-4">
                            <form action="{{ route('tasks.index') }}" method="GET">
                                <select name="committee_id" class="form-control form-select" onchange="this.form.submit()" style="background-color: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3);">
                                    <option value="" class="text-dark">All Committees</option>
                                    @foreach($committees as $committee)
                                        <option value="{{ $committee->id }}" {{ $selectedCommitteeId == $committee->id ? 'selected' : '' }} class="text-dark">
                                            {{ $committee->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ROW-1 END -->

    <!-- ROW-2 -->
    <div class="row">
        @forelse($tasks as $task)
            <div class="col-md-6 col-xl-4">
                <div class="card">
                    <div class="card-body d-flex flex-column">
                        <h4 class="card-title">
                            <a href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a>
                            <span class="badge bg-success-transparent text-success float-end fs-12">{{ $task->committee->name }}</span>
                        </h4>
                        <div class="text-muted mb-3">{{ Str::limit(strip_tags($task->content), 100) }}</div>
                        
                        <div class="mt-3 mb-3">
                            @if($task->submissions->count() > 0)
                                @php $submission = $task->submissions->first(); @endphp
                                @if($submission->status == 'accepted')
                                    <span class="badge bg-success"><i class="fe fe-check me-1"></i> Accepted</span>
                                @elseif($submission->status == 'rejected')
                                    <span class="badge bg-danger"><i class="fe fe-x me-1"></i> Rejected</span>
                                @else
                                    <span class="badge bg-warning"><i class="fe fe-clock me-1"></i> Pending</span>
                                @endif
                            @else
                                <span class="badge bg-secondary"><i class="fe fe-minus me-1"></i> Not Submitted</span>
                            @endif
                        </div>

                        <div class="d-flex align-items-center pt-2 mt-auto">
                            <div class="ms-auto">
                                <a href="{{ route('tasks.show', $task) }}" class="btn btn-success"><i class="fe fe-eye me-1"></i> View Quiz</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fe fe-slash fs-50 text-muted mb-3"></i>
                        <h3 class="text-muted">No quizzes found.</h3>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
    <div class="d-flex justify-content-center">
        {{ $tasks->links() }}
    </div>
    <!-- ROW-2 END -->
@endsection
