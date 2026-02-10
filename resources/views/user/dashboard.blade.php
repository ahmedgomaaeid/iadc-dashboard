@extends('layouts.user-dashboard')

@section('title', 'User Dashboard')

@section('content')
    <!-- PAGE-HEADER -->
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
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
                            <h3 class="card-title text-white"><i class="fe fe-filter me-2"></i> Filter Content</h3>
                            <p class="mb-0">Select a committee to see specific lessons and quizzes.</p>
                        </div>
                        <div class="col-md-4">
                            <form action="{{ route('user.dashboard') }}" method="GET">
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
                        <a href="{{ route('user.sessions.join', $activeMeeting->id) }}" target="_blank" class="btn btn-light btn-lg pulse-button">
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
    <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-6">
            <div class="card">
                <div class="card-header border-bottom-0">
                    <h3 class="card-title"><i class="fe fe-award text-primary me-2"></i> New Quizzes</h3>
                    <div class="card-options">
                        <a href="{{ route('user.quizzes.index') }}" class="btn btn-sm btn-light">View All <i class="fe fe-arrow-right ms-1"></i></a>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 text-nowrap border-bottom">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0">Title</th>
                                    <th class="border-bottom-0">Committee</th>
                                    <th class="border-bottom-0">Date</th>
                                    <th class="border-bottom-0">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentQuizzes as $quiz)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="bg-primary-transparent brround p-2 me-2">
                                                    <i class="fe fe-award text-primary"></i>
                                                </span>
                                                <a href="{{ route('quiz.show', $quiz->id) }}">
                                                    {{ Str::limit($quiz->name, 20) }}
                                                </a>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light text-dark">{{ $quiz->committee->name }}</span></td>
                                        <td>{{ $quiz->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('quiz.show', $quiz->id) }}" class="btn btn-primary btn-sm btn-icon"><i class="fe fe-play"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">
                                            <i class="fe fe-slash fs-30 mb-2 d-block"></i>
                                            No recent quizzes found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-6">
            <div class="card">
                <div class="card-header border-bottom-0">
                    <h3 class="card-title"><i class="fe fe-award text-primary me-2"></i> New Tasks</h3>
                    <div class="card-options">
                        <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-light">View All <i class="fe fe-arrow-right ms-1"></i></a>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 text-nowrap border-bottom">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0">Title</th>
                                    <th class="border-bottom-0">Committee</th>
                                    <th class="border-bottom-0">Date</th>
                                    <th class="border-bottom-0">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTasks as $task)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="bg-primary-transparent brround p-2 me-2">
                                                    <i class="fe fe-award text-primary"></i>
                                                </span>
                                                <a href="{{ route('tasks.show', $task->id) }}">
                                                    {{ Str::limit($task->title, 20) }}
                                                </a>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light text-dark">{{ $task->committee->name }}</span></td>
                                        <td>{{ $task->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-primary btn-sm btn-icon"><i class="fe fe-play"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">
                                            <i class="fe fe-slash fs-30 mb-2 d-block"></i>
                                            No recent tasks found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ROW-2 -->
    <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <div class="card">
                <div class="card-header border-bottom-0">
                    <h3 class="card-title"><i class="fe fe-book-open text-primary me-2"></i> New Lessons</h3>
                    <div class="card-options">
                        <a href="{{ route('lessons.index') }}" class="btn btn-sm btn-light">View All <i class="fe fe-arrow-right ms-1"></i></a>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 text-nowrap border-bottom">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0">Title</th>
                                    <th class="border-bottom-0">Committee</th>
                                    <th class="border-bottom-0">Date</th>
                                    <th class="border-bottom-0">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLessons as $lesson)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="bg-primary-transparent brround p-2 me-2">
                                                    <i class="fe fe-video text-primary"></i>
                                                </span>
                                                <a href="{{ route('lessons.show', $lesson) }}">
                                                    {{ Str::limit($lesson->title, 20) }}
                                                </a>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light text-dark">{{ $lesson->committee->name }}</span></td>
                                        <td>{{ $lesson->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('lessons.show', $lesson) }}" class="btn btn-primary btn-sm btn-icon"><i class="fe fe-play"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">
                                            <i class="fe fe-slash fs-30 mb-2 d-block"></i>
                                            No recent lessons found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ROW-2 END -->

    <!-- Calendar Row -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fe fe-calendar me-2"></i> Sessions Calendar</h3>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- ROW-2 END -->
@endsection

@section('css')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
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

    /* Calendar Styles */
    .fc { font-size: 14px; }
    .fc-event { cursor: pointer; border-radius: 4px; }
    .fc-timegrid-slot { height: 3em !important; }
    .fc-event.active-meeting-event { position: relative; }
    .fc-event.active-meeting-event .fc-event-title::before {
        content: '🔴 LIVE';
        display: inline-block;
        margin-right: 6px;
        font-weight: bold;
        font-size: 0.75em;
        padding: 2px 6px;
        background: rgba(255, 68, 68, 0.9);
        color: white;
        border-radius: 4px;
        animation: blink 1.5s ease-in-out infinite;
    }
</style>
@endsection

@section('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendarEvents = @json($calendarEvents);
        
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            themeSystem: 'bootstrap5',
            events: calendarEvents,
            nowIndicator: true,
            allDaySlot: false,
            slotMinTime: '10:00:00',
            slotMaxTime: '23:59:59',
            height: 'auto',
            
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                var event = info.event;
                var props = event.extendedProps;
                
                if (props.isActive) {
                    Swal.fire({
                        title: 'Join Meeting?',
                        html: `
                            <div class="text-start">
                                <p><strong>Topic:</strong> ${event.title}</p>
                                <p><strong>Committee:</strong> ${props.committeeName}</p>
                                <p><strong>Status:</strong> <span class="badge bg-success">LIVE NOW</span></p>
                            </div>
                        `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Join Now',
                        confirmButtonColor: '#28a745',
                        cancelButtonText: 'Close'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Use tracking route
                            const trackingUrl = `{{ url('user/sessions') }}/${event.id}/join`;
                            window.open(trackingUrl, '_blank');
                        }
                    });
                } else {
                    // Check if meeting is in future or past
                    var now = new Date();
                    var start = event.start;
                    var message = start > now 
                        ? 'This meeting hasn\'t started yet.' 
                        : 'This meeting has already ended.';
                        
                    Swal.fire({
                        title: 'Meeting Not Active',
                        text: message,
                        icon: 'info',
                        confirmButtonColor: '#6c5ffc'
                    });
                }
            },
            
            eventDidMount: function(info) {
                if (info.event.extendedProps.isActive) {
                    info.el.title = '🔴 LIVE NOW - Click to Join';
                    info.el.style.borderColor = '#ff4444';
                    info.el.style.backgroundColor = '#ff4444';
                }
            },
            
            windowResize: function(view) {
                if (window.innerWidth < 768) {
                    calendar.changeView('timeGridDay');
                }
            }
        });
        
        calendar.render();
    });
</script>
@endsection
