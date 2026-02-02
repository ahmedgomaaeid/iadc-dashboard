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

    {{-- Active Meeting Notification --}}
    @if(isset($activeMeeting) && $activeMeeting)
    <div class="row mt-2 mb-2">
        <div class="col-12">
            <div class="alert alert-primary alert-dismissible fade show active-meeting-banner" role="alert">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="live-indicator">
                            <i class="fe fe-video fs-2 text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading mb-1 text-white">
                            <i class="fe fe-radio me-2"></i>
                            Meeting in Progress
                        </h5>
                        <div class="mb-2">
                            <strong class="fs-5">{{ $activeMeeting->title }}</strong>
                        </div>
                        <p class="mb-0 opacity-90">
                            <i class="fe fe-clock me-1"></i>
                            <span>{{ $activeMeeting->start_time->format('g:i A') }} - {{ $activeMeeting->end_time->format('g:i A') }}</span>
                            @if(isset($activeMeeting->committee))
                            <span class="mx-2">|</span>
                            <i class="fe fe-briefcase me-1"></i>
                            <span>{{ $activeMeeting->committee->name }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ $activeMeeting->session_url }}" target="_blank" class="btn btn-light btn-lg pulse-button">
                            <i class="fe fe-log-in me-2"></i>
                            Join Meeting
                        </a>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    @endif

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

@section('css')
<style>
    /* Active Meeting Banner Styling */
    .active-meeting-banner {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        animation: slideInDown 0.5s ease-out;
        position: relative;
        overflow: hidden;
    }
    
    .active-meeting-banner::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        animation: shimmer 3s infinite;
    }
    
    .active-meeting-banner h5,
    .active-meeting-banner p,
    .active-meeting-banner strong {
        color: white !important;
    }
    
    .live-indicator {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: pulse 2s ease-in-out infinite;
        position: relative;
    }
    
    .live-indicator::before {
        content: '';
        position: absolute;
        top: -5px;
        right: -5px;
        width: 15px;
        height: 15px;
        background: #ff4444;
        border-radius: 50%;
        border: 3px solid white;
        animation: blink 1.5s ease-in-out infinite;
    }
    
    .pulse-button {
        animation: pulseButton 2s ease-in-out infinite;
        font-weight: 600;
        padding: 12px 30px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
    }
    
    .pulse-button:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    }
    
    @keyframes slideInDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4); }
        50% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(255, 255, 255, 0); }
    }
    
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    @keyframes pulseButton {
        0%, 100% { box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        50% { box-shadow: 0 4px 20px rgba(255,255,255,0.4); }
    }
    
    @keyframes shimmer {
        0% { left: -100%; }
        100% { left: 100%; }
    }
    
    @media (max-width: 768px) {
        .active-meeting-banner .d-flex { flex-direction: column; text-align: center; }
        .active-meeting-banner .ms-auto { margin-top: 1rem; margin-left: 0 !important; }
        .live-indicator { margin: 0 auto 1rem; }
    }
</style>
@endsection
