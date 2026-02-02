@extends('layouts.board-dashboard')

@section('title', 'Manage Sessions')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Manage Sessions</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('board.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Sessions</li>
            </ol>
        </div>
    </div>

    <!-- Stats & Actions Row -->
    <div class="row mb-5">
        <div class="col-md-6 col-lg-3">
            <div class="card bg-primary img-card box-primary-shadow h-100">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="text-white">
                            <h2 class="mb-0 number-font">{{ count($sessions) }}</h2>
                            <p class="text-white mb-0">Total Sessions</p>
                        </div>
                        <div class="ms-auto"> <i class="fe fe-calendar text-white fs-30 me-2 mt-2"></i> </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-9">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h4 class="card-title mb-1">Google Calendar Integration</h4>
                        <p class="text-muted mb-0">Sync your sessions directly with Google Calendar</p>
                    </div>
                    
                    @if(!$isConnected)
                        <a href="{{ route('auth.google') }}" class="btn btn-white btn-lg shadow-sm border">
                            <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" width="20" class="me-2">
                            Connect Google Calendar
                        </a>
                    @else
                        <div class="d-flex align-items-center gap-3">
                            <span class="text-success fw-bold">
                                <i class="fe fe-check-circle me-1"></i> Connected
                            </span>
                            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#eventModal">
                                <i class="fe fe-plus me-1"></i> New Session
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Calendar Card -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">Calendar Schedule</h3>
                </div>
                <div class="card-body p-0">
                    <div id="calendar" class="p-4"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Create New Session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="errorAlert" class="alert alert-danger d-none"></div>
                    
                    <form id="createEventForm">
                        <div class="mb-3">
                            <label for="eventTitle" class="form-label">Session Title</label>
                            <input type="text" class="form-control" id="eventTitle" required placeholder="Enter session title">
                        </div>
                        
                        <input type="hidden" id="committee_id" value="{{ $committees[0]->id ?? '' }}">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_time" class="form-label">Start Time</label>
                                <input type="datetime-local" class="form-control" id="start_time" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_time" class="form-label">End Time</label>
                                <input type="datetime-local" class="form-control" id="end_time" required>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Loading Spinner -->
                    <div id="loadingSpinner" class="text-center d-none my-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Creating Google Meet session...</p>
                    </div>

                    <!-- Success Alert inside modal -->
                    <div id="successAlert" class="alert alert-success d-none">
                        Session created successfully!
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveEventBtn">Create Session</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Session Action Modal -->
    <div class="modal fade" id="sessionActionModal" tabindex="-1" aria-labelledby="sessionActionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header border-0 bg-gradient-primary text-white pb-4">
                    <div class="w-100 text-center">
                        <div class="mb-2">
                            <i class="fe fe-video fs-1"></i>
                        </div>
                        <h5 class="modal-title mb-1" id="sessionActionModalLabel">
                            <span id="actionSessionTitle"></span>
                        </h5>
                        <p class="mb-0 opacity-90" style="font-size: 0.875rem;">Choose an action for this session</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-5">
                    <div class="row g-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-primary btn-lg w-100 d-flex align-items-center justify-content-center" id="openInGoogleBtn">
                                <i class="fe fe-external-link me-2"></i>
                                <span>Open Meeting</span>
                            </button>
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-outline-danger btn-lg w-100 d-flex align-items-center justify-content-center" id="deleteSessionBtn">
                                <i class="fe fe-trash-2 me-2"></i>
                                <span>Delete Session</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4">
                    <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">
                        <i class="fe fe-x me-1"></i> Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('css')
<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<style>
    /* Custom Calendar Styling */
    .fc {
        font-size: 14px;
    }
    
    .fc-event {
        cursor: pointer;
        border-radius: 4px;
    }
    
    .fc-timegrid-slot {
        height: 3em !important;
    }
    
    .fc-timegrid-slot:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    .fc-button-primary {
        background-color: #6c5ffc !important;
        border-color: #6c5ffc !important;
    }
    
    .fc-button-primary:hover {
        background-color: #5a4de6 !important;
        border-color: #5a4de6 !important;
    }
    
    .fc-button-primary:not(:disabled).fc-button-active,
    .fc-button-primary:not(:disabled):active {
        background-color: #4839d4 !important;
        border-color: #4839d4 !important;
    }
    
    .fc-event-main {
        padding: 2px 4px;
    }
    
    .fc-daygrid-event {
        white-space: normal !important;
    }
    
    
    /* Active Meeting Event Styling */
    .fc-event.active-meeting-event {
        position: relative;
    }
    
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
    
    @keyframes blink {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.7;
        }
    }
    
    /* Session Action Modal Styling */
    #sessionActionModal .modal-content {
        border: none;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.25);
        overflow: hidden;
    }
    
    #sessionActionModal .bg-gradient-primary {
        background: linear-gradient(135deg, #6c5ffc 0%, #8b7cff 100%);
        padding: 2rem 1.5rem;
    }
    
    #sessionActionModal .modal-header .fs-1 {
        font-size: 3rem;
    }
    
    #sessionActionModal .btn-lg {
        padding: 14px 24px;
        font-weight: 600;
        font-size: 1rem;
        border-radius: 10px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-width: 2px;
    }
    
    #sessionActionModal #openInGoogleBtn {
        background: linear-gradient(135deg, #6c5ffc 0%, #8b7cff 100%);
        border: none;
        box-shadow: 0 4px 15px rgba(108, 95, 252, 0.3);
    }
    
    #sessionActionModal #openInGoogleBtn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(108, 95, 252, 0.4);
    }
    
    #sessionActionModal #deleteSessionBtn {
        color: #dc3545;
        border-color: #dc3545;
        background: transparent;
    }
    
    #sessionActionModal #deleteSessionBtn:hover {
        transform: translateY(-3px);
        background: #dc3545;
        border-color: #dc3545;
        color: white;
        box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3);
    }
    
    #sessionActionModal .btn-light {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        color: #6c757d;
        font-weight: 500;
        padding: 10px 20px;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    
    #sessionActionModal .btn-light:hover {
        background: #e9ecef;
        border-color: #adb5bd;
        color: #495057;
    }
    
    #sessionActionModal .modal-dialog {
        max-width: 420px;
    }
</style>
@endsection

@section('scripts')
    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    
    <script>
        window.calendarEvents = @json($calendarEvents);
        
        document.addEventListener('DOMContentLoaded', function() {
            @if($isConnected)
            var calendarEl = document.getElementById('calendar');
            var eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
            
            // Session Action Modal
            var actionModal = new bootstrap.Modal(document.getElementById('sessionActionModal'));
            var currentEventData = null;
            
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                themeSystem: 'bootstrap5',
                events: window.calendarEvents,
                selectable: true,
                editable: false,
                nowIndicator: true,
                allDaySlot: false,
                slotMinTime: '08:00:00',
                slotMaxTime: '22:00:00',
                height: 800,
                
                // Clicking on a date/time slot to create new event
                select: function(info) {
                    // Reset form
                    document.getElementById('createEventForm').reset();
                    document.getElementById('eventTitle').value = '';
                    // Set default committee
                    document.getElementById('committee_id').value = "{{ $committees[0]->id ?? '' }}";
                    
                    // Format datetime for input
                    const startTime = formatDateTimeLocal(info.start);
                    const endTime = formatDateTimeLocal(info.end);
                    
                    document.getElementById('start_time').value = startTime;
                    document.getElementById('end_time').value = endTime;
                    
                    // Hide error alert if visible
                    document.getElementById('errorAlert').classList.add('d-none');
                    
                    // Show the modal
                    eventModal.show();
                    
                    // Unselect after opening modal
                    calendar.unselect();
                },
                
                
                // Handle event click
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    
                    const eventId = info.event.id;
                    const eventTitle = info.event.title;
                    const eventUrl = info.event.url;
                    
                    // Store current event data
                    currentEventData = {
                        id: eventId,
                        url: eventUrl,
                        calendarEvent: info.event
                    };
                    
                    // Update modal content
                    document.getElementById('actionSessionTitle').textContent = eventTitle;
                    
                    // Show action modal
                    actionModal.show();
                },
                
                // Customize event rendering
                eventDidMount: function(info) {
                    // Add tooltip for active events
                    if (info.event.extendedProps.isActive) {
                        info.el.title = '🔴 LIVE NOW - Click to join or delete';
                    }
                },
                
                // Responsive settings
                windowResize: function(view) {
                    if (window.innerWidth < 768) {
                        calendar.changeView('timeGridDay');
                    }
                }
            });
            
            calendar.render();
            
            // Handle "Open in Google Calendar" button
            document.getElementById('openInGoogleBtn').addEventListener('click', function() {
                if (currentEventData && currentEventData.url) {
                    window.open(currentEventData.url, '_blank');
                    actionModal.hide();
                }
            });
            
            // Handle "Delete Session" button
            document.getElementById('deleteSessionBtn').addEventListener('click', function() {
                if (currentEventData) {
                    actionModal.hide();
                    deleteEvent(currentEventData.id, currentEventData.calendarEvent);
                }
            });
            
            // Format date for datetime-local input
            function formatDateTimeLocal(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                
                return `${year}-${month}-${day}T${hours}:${minutes}`;
            }
            
            // Handle Save Button
            document.getElementById('saveEventBtn').addEventListener('click', function() {
                const title = document.getElementById('eventTitle').value;
                const committeeId = document.getElementById('committee_id').value;
                const startTime = document.getElementById('start_time').value;
                const endTime = document.getElementById('end_time').value;
                
                if (!title || !committeeId || !startTime || !endTime) {
                    alert('Please fill in all fields');
                    return;
                }
                
                // Show loading state
                const spinner = document.getElementById('loadingSpinner');
                const form = document.getElementById('createEventForm');
                const errorAlert = document.getElementById('errorAlert');
                const successAlert = document.getElementById('successAlert');
                const saveBtn = document.getElementById('saveEventBtn');
                
                spinner.classList.remove('d-none');
                form.classList.add('d-none');
                errorAlert.classList.add('d-none');
                saveBtn.disabled = true;
                
                // Send AJAX request
                fetch("{{ route('board.sessions.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        title: title,
                        committee_id: committeeId,
                        start_time: startTime,
                        end_time: endTime
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    // Success handling
                    spinner.classList.add('d-none');
                    successAlert.classList.remove('d-none');
                    
                    // Add event to calendar
                    calendar.addEvent({
                        id: data.event.id,
                        title: data.event.title,
                        start: data.event.start_time,
                        end: data.event.end_time,
                        url: data.event.session_url
                    });
                    
                    // Close modal after delay
                    setTimeout(() => {
                        eventModal.hide();
                        successAlert.classList.add('d-none');
                        form.classList.remove('d-none');
                        saveBtn.disabled = false;
                    }, 1500);
                })
                .catch(error => {
                    spinner.classList.add('d-none');
                    form.classList.remove('d-none');
                    saveBtn.disabled = false;
                    
                    errorAlert.textContent = error.message || 'An error occurred while creating the session.';
                    errorAlert.classList.remove('d-none');
                });
            });
            
            // Delete event function
            function deleteEvent(eventId, calendarEvent) {
                if (!eventId) {
                    alert('Cannot delete this event - missing ID');
                    return;
                }
                
                // Send delete request
                fetch(`{{ url('board/sessions') }}/${eventId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => {
                    if (response.ok) {
                        calendarEvent.remove();
                        // Show success toast or alert
                        const toast = document.createElement('div');
                        toast.className = 'alert alert-success position-fixed top-0 end-0 m-3';
                        toast.style.zIndex = '9999';
                        toast.textContent = 'Session deleted successfully';
                        document.body.appendChild(toast);
                        setTimeout(() => toast.remove(), 3000);
                    } else {
                        return response.json().then(data => {
                            throw new Error(data.error || 'Failed to delete');
                        });
                    }
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                });
            }
            @endif
        });
    </script>
@endsection
