@extends('layouts.highboard-dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Sessions</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if(!$isConnected)
                        <div class="d-flex justify-content-center align-items-center" style="min-height: 400px;">
                            <div class="text-center">
                                <h4>Connect your Google Calendar to manage sessions</h4>
                                <p class="text-muted mb-4">You need to sign in with Google to create and sync meetings.</p>
                                <a href="{{ route('highboard.auth.google') }}" class="btn btn-danger btn-lg">
                                    <i class="fab fa-google me-2"></i> Sign in with Google
                                </a>
                            </div>
                        </div>
                    @else
                        <div id="calendar" style="min-height: 800px;"></div>
                    @endif
                </div>

    <!-- Event Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Create New Session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="eventForm">
                        <div class="mb-3">
                            <label for="eventTitle" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="eventTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="committee_id" class="form-label">Committee <span class="text-danger">*</span></label>
                            <select class="form-control" id="committee_id" required>
                                <option value="">Select Committee</option>
                                @foreach($committees as $committee)
                                    <option value="{{ $committee->id }}">{{ $committee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="start_time" class="form-label">Start Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="start_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="end_time" class="form-label">End Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="end_time" required>
                        </div>
                        <div id="errorAlert" class="alert alert-danger d-none"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveEventBtn">
                        <span class="btn-text">Save Session</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
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
        console.log('Calendar Events from backend:', window.calendarEvents);
        
        document.addEventListener('DOMContentLoaded', function() {
            @if($isConnected)
            var calendarEl = document.getElementById('calendar');
            var eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
            var actionModal = new bootstrap.Modal(document.getElementById('sessionActionModal'));
            
            // Store current event data for action modal
            let currentEventData = null;
            
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                slotMinTime: '08:00:00',
                slotMaxTime: '20:00:00',
                slotDuration: '00:30:00',
                allDaySlot: false,
                expandRows: true,
                height: 'auto',
                editable: false,
                selectable: true,
                selectMirror: true,
                nowIndicator: true,
                events: window.calendarEvents,
                
                // Handle date/time slot selection
                select: function(info) {
                    // Pre-fill the modal with selected date/time
                    document.getElementById('eventTitle').value = '';
                    document.getElementById('committee_id').value = '';
                    
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
                    
                    console.log('Event clicked:', info.event);
                    console.log('Event ID:', info.event.id);
                    
                    const eventId = info.event.id;
                    const eventTitle = info.event.title;
                    const eventUrl = info.event.url;
                    
                    if (!eventId) {
                        alert('Cannot delete this event - missing ID. This might be an old event. Please refresh the page.');
                        if (eventUrl) {
                            window.open(eventUrl, '_blank');
                        }
                        return;
                    }
                    
                    // Store event data for modal actions
                    currentEventData = {
                        id: eventId,
                        title: eventTitle,
                        url: eventUrl,
                        calendarEvent: info.event
                    };
                    
                    // Update modal title with session name
                    document.getElementById('actionSessionTitle').textContent = eventTitle;
                    
                    // Show the action modal
                    actionModal.show();
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
            
            // Handle save event button
            document.getElementById('saveEventBtn').addEventListener('click', function() {
                const btn = this;
                const btnText = btn.querySelector('.btn-text');
                const spinner = btn.querySelector('.spinner-border');
                const errorAlert = document.getElementById('errorAlert');
                
                // Validate form
                const title = document.getElementById('eventTitle').value.trim();
                const committeeId = document.getElementById('committee_id').value;
                const startTime = document.getElementById('start_time').value;
                const endTime = document.getElementById('end_time').value;
                
                if (!title || !committeeId || !startTime || !endTime) {
                    errorAlert.textContent = 'Please fill in all required fields.';
                    errorAlert.classList.remove('d-none');
                    return;
                }
                
                if (new Date(endTime) <= new Date(startTime)) {
                    errorAlert.textContent = 'End time must be after start time.';
                    errorAlert.classList.remove('d-none');
                    return;
                }
                
                // Show loading state
                btn.disabled = true;
                btnText.classList.add('d-none');
                spinner.classList.remove('d-none');
                errorAlert.classList.add('d-none');
                
                // Submit form via AJAX
                fetch('{{ route("highboard.sessions.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
                    
                    console.log('Event created successfully:', data.event);
                    
                    // Add event to calendar with ID
                    calendar.addEvent({
                        id: data.event.id,
                        title: data.event.title,
                        start: data.event.start_time,
                        end: data.event.end_time,
                        url: data.event.session_url
                    });
                    
                    // Close modal and reset form
                    eventModal.hide();
                    document.getElementById('eventForm').reset();
                    
                    // Show success message
                    const successAlert = document.createElement('div');
                    successAlert.className = 'alert alert-success alert-dismissible fade show';
                    successAlert.innerHTML = `
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.querySelector('.card-body').insertBefore(
                        successAlert,
                        document.getElementById('calendar')
                    );
                    
                    // Auto-dismiss after 5 seconds
                    setTimeout(() => {
                        successAlert.remove();
                    }, 5000);
                })
                .catch(error => {
                    errorAlert.textContent = error.message || 'An error occurred while creating the session.';
                    errorAlert.classList.remove('d-none');
                })
                .finally(() => {
                    // Reset button state
                    btn.disabled = false;
                    btnText.classList.remove('d-none');
                    spinner.classList.add('d-none');
                });
            });
            
            // Delete event function
            function deleteEvent(eventId, calendarEvent) {
                if (!eventId) {
                    alert('Cannot delete this event - missing ID');
                    return;
                }
                
                // Send delete request
                fetch(`{{ url('highboard/sessions') }}/${eventId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    // Remove event from calendar
                    calendarEvent.remove();
                    
                    // Show success message
                    const successAlert = document.createElement('div');
                    successAlert.className = 'alert alert-success alert-dismissible fade show';
                    successAlert.innerHTML = `
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.querySelector('.card-body').insertBefore(
                        successAlert,
                        document.getElementById('calendar')
                    );
                    
                    // Auto-dismiss after 5 seconds
                    setTimeout(() => {
                        successAlert.remove();
                    }, 5000);
                })
                .catch(error => {
                    alert('Error deleting session: ' + (error.message || 'Unknown error'));
                });
            }
            @endif
        });
    </script>
@endsection
