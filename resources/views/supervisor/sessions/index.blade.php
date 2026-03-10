@extends('layouts.supervisor-dashboard')

@section('css')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<style>
    /* ── Session Stat Cards ── */
    .session-stat-card {
        border: none;
        border-radius: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        position: relative;
    }
    .session-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    }
    .session-stat-card .card-body {
        padding: 1.25rem;
        position: relative;
        z-index: 1;
    }
    .session-stat-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -30%;
        width: 160px;
        height: 160px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
        z-index: 0;
    }
    .session-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        background: rgba(255, 255, 255, 0.18);
        backdrop-filter: blur(10px);
        color: #fff;
    }
    .session-stat-value {
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 1;
        color: #fff;
    }
    .session-stat-label {
        font-size: 0.8rem;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.85);
        margin-top: 4px;
    }

    .gradient-session-total    { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .gradient-session-upcoming { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .gradient-session-done     { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .gradient-session-live     { background: linear-gradient(135deg, #f5576c 0%, #ff8a5c 100%); }

    /* ── Calendar Card ── */
    .calendar-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }
    .calendar-card .card-header {
        background: transparent;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        padding: 1.25rem 1.5rem;
    }
    .calendar-card .card-header h4 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: #2d3748;
    }
    .calendar-card .card-body {
        padding: 1.25rem 1.5rem;
    }

    /* ── Committee Legend ── */
    .committee-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 1.25rem;
    }
    .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 600;
        background: #f8fafc;
        border: 1px solid #edf2f7;
        cursor: pointer;
        transition: all 0.2s ease;
        user-select: none;
    }
    .legend-item:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .legend-item.active {
        border-color: transparent;
        color: #fff;
    }
    .legend-item .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .legend-item.filtered-out {
        opacity: 0.4;
    }

    /* ── FullCalendar Overrides ── */
    .fc {
        font-family: inherit;
        font-size: 13px;
    }
    .fc .fc-toolbar-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #2d3748;
    }
    .fc .fc-button {
        border-radius: 8px !important;
        font-weight: 600;
        font-size: 0.8rem;
        padding: 6px 14px;
        border: none;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: all 0.2s ease;
    }
    .fc .fc-button-primary {
        background: #667eea;
    }
    .fc .fc-button-primary:hover {
        background: #5a6fd6;
    }
    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:not(:disabled):active {
        background: #4c5ec2;
    }
    .fc .fc-daygrid-day-number {
        font-weight: 600;
        color: #4a5568;
        padding: 6px 10px;
    }
    .fc .fc-daygrid-day.fc-day-today {
        background: rgba(102, 126, 234, 0.06) !important;
    }
    .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
        background: #667eea;
        color: #fff;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .fc-event {
        cursor: pointer;
        border-radius: 6px !important;
        padding: 2px 6px;
        font-size: 0.78rem;
        font-weight: 600;
        border: none !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .fc-event:hover {
        transform: scale(1.02);
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    }
    .fc-timegrid-slot {
        height: 3em !important;
    }
    .fc .fc-col-header-cell-cushion {
        font-weight: 700;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #718096;
    }

    /* ── Active/Live event pulse ── */
    .active-meeting-event {
        animation: liveGlow 2s ease-in-out infinite;
        position: relative;
    }
    @keyframes liveGlow {
        0%, 100% { box-shadow: 0 0 0 0 rgba(255, 68, 68, 0.4); }
        50% { box-shadow: 0 0 0 8px rgba(255, 68, 68, 0); }
    }

    /* ── Past events ── */
    .past-event {
        opacity: 0.55;
    }

    /* ── Fade In ── */
    .fade-in-up {
        animation: fadeInUp 0.5s ease both;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.1s; }
    .delay-3 { animation-delay: 0.15s; }
    .delay-4 { animation-delay: 0.2s; }

    /* ── Dark Mode ── */
    .dark-mode .calendar-card .card-header h4 { color: #e2e8f0; }
    .dark-mode .fc .fc-toolbar-title { color: #e2e8f0; }
    .dark-mode .fc .fc-daygrid-day-number { color: #cbd5e0; }
    .dark-mode .fc .fc-col-header-cell-cushion { color: #a0aec0; }
    .dark-mode .legend-item { background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); color: #cbd5e0; }
    .dark-mode .fc .fc-daygrid-day.fc-day-today { background: rgba(102, 126, 234, 0.1) !important; }
    .dark-mode .fc th, .dark-mode .fc td { border-color: rgba(255,255,255,0.06); }
    .dark-mode .fc .fc-scrollgrid { border-color: rgba(255,255,255,0.06); }

    /* ── SweetAlert Custom ── */
    .session-detail-label {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #a0aec0;
        margin-bottom: 2px;
    }
    .session-detail-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 10px;
    }
</style>
@endsection

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">Sessions</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Sessions</li>
            </ol>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card session-stat-card gradient-session-total fade-in-up delay-1">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="session-stat-icon"><i class="fe fe-calendar"></i></div>
                    </div>
                    <div class="session-stat-value">{{ $totalSessions }}</div>
                    <div class="session-stat-label">Total Sessions</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card session-stat-card gradient-session-upcoming fade-in-up delay-2">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="session-stat-icon"><i class="fe fe-clock"></i></div>
                    </div>
                    <div class="session-stat-value">{{ $upcomingSessions }}</div>
                    <div class="session-stat-label">Upcoming</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card session-stat-card gradient-session-done fade-in-up delay-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="session-stat-icon"><i class="fe fe-check-circle"></i></div>
                    </div>
                    <div class="session-stat-value">{{ $completedSessions }}</div>
                    <div class="session-stat-label">Completed</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card session-stat-card gradient-session-live fade-in-up delay-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="session-stat-icon"><i class="fe fe-radio"></i></div>
                    </div>
                    <div class="session-stat-value">{{ $liveSessions }}</div>
                    <div class="session-stat-label">Live Now</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Section -->
    <div class="row">
        <div class="col-12">
            <div class="card calendar-card fade-in-up" style="animation-delay: 0.25s;">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h4><i class="fe fe-calendar me-2" style="color:#667eea;"></i> Sessions Calendar</h4>
                    <span class="badge bg-primary-transparent text-primary" style="font-size:0.75rem; padding:6px 14px; border-radius:20px;">
                        {{ $totalSessions }} Total &bull; {{ $upcomingSessions }} Upcoming
                    </span>
                </div>
                <div class="card-body">
                    <!-- Committee Legend / Filter -->
                    <div class="committee-legend" id="committeeFilter">
                        <div class="legend-item active" data-committee="all"
                             style="background: #667eea; border-color: #667eea; color: #fff;">
                            <i class="fe fe-grid" style="font-size:12px;"></i> All
                        </div>
                        @foreach($committeeList as $committee)
                        <div class="legend-item" data-committee="{{ $committee['id'] }}">
                            <span class="legend-dot" style="background: {{ $committee['color'] }};"></span>
                            {{ $committee['name'] }}
                        </div>
                        @endforeach
                    </div>

                    <!-- Calendar Container -->
                    <div id="sessionsCalendar"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('sessionsCalendar');
        var allEvents = @json($calendarEvents);
        var activeFilter = 'all';

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: allEvents,
            nowIndicator: true,
            allDaySlot: false,
            slotMinTime: '08:00:00',
            slotMaxTime: '23:59:59',
            height: 'auto',
            dayMaxEvents: 3,
            eventDisplay: 'block',

            eventClick: function(info) {
                info.jsEvent.preventDefault();
                var event = info.event;
                var props = event.extendedProps;
                var start = event.start;
                var end = event.end;
                var now = new Date();

                // Format times
                var startStr = start ? start.toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' }) : '';
                var timeStr = start ? start.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : '';
                var endTimeStr = end ? end.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : '';

                // Status badge
                var statusHtml = '';
                if (props.isActive) {
                    statusHtml = '<span class="badge" style="background:linear-gradient(135deg,#f5576c,#ff8a5c); color:#fff; padding:5px 12px; border-radius:20px; font-size:0.75rem;"><i class="fe fe-radio me-1"></i> LIVE NOW</span>';
                } else if (props.isPast) {
                    statusHtml = '<span class="badge" style="background:#e2e8f0; color:#718096; padding:5px 12px; border-radius:20px; font-size:0.75rem;"><i class="fe fe-check me-1"></i> Completed</span>';
                } else {
                    statusHtml = '<span class="badge" style="background:linear-gradient(135deg,#11998e,#38ef7d); color:#fff; padding:5px 12px; border-radius:20px; font-size:0.75rem;"><i class="fe fe-clock me-1"></i> Upcoming</span>';
                }

                Swal.fire({
                    html: `
                        <div style="text-align:left; padding: 8px 0;">
                            <div style="margin-bottom:16px; text-align:center;">
                                ${statusHtml}
                            </div>
                            <div class="session-detail-label">Session Title</div>
                            <div class="session-detail-value">${event.title}</div>

                            <div class="session-detail-label">Committee</div>
                            <div class="session-detail-value">
                                <span style="display:inline-flex; align-items:center; gap:6px;">
                                    <span style="width:10px; height:10px; border-radius:50%; background:${event.backgroundColor}; display:inline-block;"></span>
                                    ${props.committeeName}
                                </span>
                            </div>

                            <div class="session-detail-label">Date</div>
                            <div class="session-detail-value">${startStr}</div>

                            <div class="session-detail-label">Time</div>
                            <div class="session-detail-value">${timeStr} — ${endTimeStr}</div>

                            ${props.description ? `<div class="session-detail-label">Description</div><div class="session-detail-value" style="font-weight:400; color:#4a5568;">${props.description}</div>` : ''}
                        </div>
                    `,
                    showConfirmButton: true,
                    confirmButtonText: 'Close',
                    confirmButtonColor: '#667eea',
                    width: 420,
                    customClass: {
                        popup: 'rounded-4'
                    }
                });
            },

            eventDidMount: function(info) {
                // Tooltip on hover
                var props = info.event.extendedProps;
                var statusText = props.isActive ? '🔴 LIVE' : (props.isPast ? '✅ Ended' : '🕐 Upcoming');
                info.el.title = `${info.event.title}\n${props.committeeName}\n${statusText}`;
            },

            windowResize: function(view) {
                if (window.innerWidth < 768) {
                    calendar.changeView('timeGridDay');
                }
            }
        });

        calendar.render();

        // ── Committee Filter Logic ──
        var legendItems = document.querySelectorAll('#committeeFilter .legend-item');
        legendItems.forEach(function(item) {
            item.addEventListener('click', function() {
                var committee = this.dataset.committee;

                // Update active states
                legendItems.forEach(function(li) {
                    li.classList.remove('active', 'filtered-out');
                    li.style.background = '';
                    li.style.borderColor = '';
                    li.style.color = '';
                });

                if (committee === 'all') {
                    activeFilter = 'all';
                    this.classList.add('active');
                    this.style.background = '#667eea';
                    this.style.borderColor = '#667eea';
                    this.style.color = '#fff';
                } else {
                    activeFilter = parseInt(committee);
                    this.classList.add('active');
                    var dot = this.querySelector('.legend-dot');
                    if (dot) {
                        var dotColor = dot.style.background;
                        this.style.background = dotColor;
                        this.style.borderColor = dotColor;
                        this.style.color = '#fff';
                    }
                    // Dim other items
                    legendItems.forEach(function(li) {
                        if (li.dataset.committee !== committee && li.dataset.committee !== 'all') {
                            li.classList.add('filtered-out');
                        }
                    });
                }

                // Filter events
                calendar.removeAllEvents();
                var filtered = activeFilter === 'all'
                    ? allEvents
                    : allEvents.filter(function(e) { return e.extendedProps.committeeId == activeFilter; });
                calendar.addEventSource(filtered);
            });
        });
    });
</script>
@endsection
