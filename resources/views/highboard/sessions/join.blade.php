@extends('layouts.highboard-dashboard')

@section('title', 'Join Session: ' . $session->title)

@section('css')
    <style>
        /* Warning Banner */
        .warning-banner {
            background: linear-gradient(135deg, #ff9f43 0%, #ee5a24 100%);
            color: white;
            padding: 20px 25px;
            border-radius: 16px;
            margin-bottom: 20px;
            display: none;
            box-shadow: 0 10px 30px rgba(238, 90, 36, 0.3);
        }

        .warning-banner.show {
            display: block;
            animation: slideDown 0.5s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .warning-banner h4 {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .warning-banner .meeting-timer {
            font-size: 1.5rem;
            font-weight: 700;
            background: rgba(255,255,255,0.2);
            padding: 2px 12px;
            border-radius: 8px;
        }

        .warning-banner .btn {
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .warning-banner .btn:hover {
            transform: translateY(-2px);
        }

        .warning-banner .btn-light {
            background: white;
            color: #ee5a24;
        }

        .warning-banner .btn-danger {
            background: rgba(255,255,255,0.2);
            border: 2px solid white;
        }

        .warning-banner .btn-danger:hover {
            background: white;
            color: #ee5a24;
        }

        /* Continuation Card */
        .continuation-card {
            display: none;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(17, 153, 142, 0.3);
        }

        .continuation-card.show {
            display: block;
            animation: slideDown 0.5s ease-out;
        }

        .continuation-card .card-body {
            padding: 3rem 2rem;
        }

        .continuation-card .status-icon {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .continuation-card .status-icon i {
            font-size: 2rem;
        }

        /* Host Card */
        .host-card {
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .host-card .card-body {
            padding: 3rem 2rem;
        }

        .host-card .meeting-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .host-card .meeting-icon i {
            font-size: 2.5rem;
            color: white;
        }

        .host-card .btn-launch {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
        }

        .host-card .btn-launch:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
        }

        /* Meeting Details */
        .meeting-details {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }

        .meeting-details .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
        }

        .meeting-details .detail-item:not(:last-child) {
            border-bottom: 1px solid #e9ecef;
        }

        .meeting-details .detail-label {
            color: #6c757d;
            font-weight: 500;
        }

        .meeting-details .detail-value {
            font-weight: 600;
            font-family: monospace;
            background: white;
            padding: 4px 12px;
            border-radius: 6px;
        }

        /* Timer Display */
        .timer-display {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 2rem;
            border-radius: 12px;
            margin-top: 1.5rem;
        }

        .timer-display .timer-label {
            opacity: 0.8;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .timer-display .meeting-timer {
            font-size: 2.5rem;
            font-weight: 700;
            font-family: monospace;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .warning-banner {
                padding: 15px;
            }
            
            .warning-banner .btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
            
            .host-card .card-body {
                padding: 2rem 1.5rem;
            }
            
            .host-card .meeting-icon {
                width: 80px;
                height: 80px;
            }
            
            .host-card .meeting-icon i {
                font-size: 2rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ $session->title }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('highboard.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('highboard.sessions.index') }}">Sessions</a></li>
                <li class="breadcrumb-item active" aria-current="page">Host Session</li>
            </ol>
        </div>
    </div>

    <!-- Warning Banner (shows at 40 minutes) -->
    <div class="warning-banner" id="warning-banner">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h4><i class="fe fe-alert-triangle me-2"></i>Meeting Ending Soon</h4>
                <p class="mb-0">Free Zoom meetings are limited to 45 minutes. Time remaining: <span class="meeting-timer" id="time-remaining">5:00</span></p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-light" id="continue-meeting-btn" onclick="continueMeeting()">
                    <i class="fe fe-refresh-cw me-2"></i>Continue Meeting
                </button>
                <button class="btn btn-danger" id="end-meeting-btn" onclick="endMeeting()">
                    <i class="fe fe-x-circle me-2"></i>End Meeting
                </button>
            </div>
        </div>
    </div>

    <!-- Continuation Success Card -->
    <div class="card continuation-card" id="continuation-card">
        <div class="card-body text-center">
            <div class="status-icon">
                <i class="fe fe-check"></i>
            </div>
            <h3 class="mb-3">New Meeting Created!</h3>
            <p class="opacity-90 mb-4">A continuation meeting has been created successfully.</p>
            <a href="#" id="new-meeting-link" class="btn btn-light btn-lg px-4" target="_blank">
                <i class="fe fe-video me-2"></i>Join New Meeting
            </a>
            <p class="mt-4 small opacity-75">
                <i class="fe fe-users me-1"></i>
                Members will automatically join when you start the new meeting.
            </p>
        </div>
    </div>

    <div class="row" id="main-content">
        <div class="col-12 col-lg-8 col-xl-6 mx-auto">
            <div class="card host-card">
                <div class="card-body text-center">
                    <div class="meeting-icon">
                        <i class="fe fe-video"></i>
                    </div>
                    <h3 class="mb-3">Ready to Host</h3>
                    <p class="text-muted mb-4">Launch the meeting using the Zoom application for full host controls and auto-recording.</p>

                    @if($session->zoom_start_url)
                        <a href="{{ $session->zoom_start_url }}" class="btn btn-primary btn-lg btn-launch mb-3" target="_blank"
                            id="launch-zoom-btn" onclick="markAsJoined()">
                            <i class="fe fe-external-link me-2"></i>Launch Zoom App
                        </a>
                        <p class="small text-muted">Opens in your Zoom application</p>
                    @else
                        <div class="alert alert-warning">
                            <i class="fe fe-alert-triangle me-2"></i>
                            Zoom meeting details are missing. Please contact support.
                        </div>
                    @endif

                    <div class="meeting-details">
                        <div class="detail-item">
                            <span class="detail-label"><i class="fe fe-hash me-2"></i>Meeting ID</span>
                            <span class="detail-value">{{ $session->zoom_meeting_id }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label"><i class="fe fe-lock me-2"></i>Password</span>
                            <span class="detail-value">{{ $session->zoom_password }}</span>
                        </div>
                    </div>

                    <!-- Timer Display -->
                    <div class="timer-display" id="timer-display">
                        <p class="timer-label mb-1"><i class="fe fe-clock me-1"></i>Meeting Duration</p>
                        <span class="meeting-timer" id="elapsed-time">00:00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        var sessionId = {{ $session->id }};
        var meetingStartTime = null;
        var timerInterval = null;
        var warningShown = false;
        var csrfToken = '{{ csrf_token() }}';

        // Start timer when launch button is clicked
        function markAsJoined() {
            meetingStartTime = new Date();
            startTimer();

            // Mark as joined via API
            fetch('/api/sessions/' + sessionId + '/mark-joined', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
        }

        function startTimer() {
            timerInterval = setInterval(updateTimer, 1000);
        }

        function updateTimer() {
            if (!meetingStartTime) return;

            var now = new Date();
            var elapsed = Math.floor((now - meetingStartTime) / 1000);
            var minutes = Math.floor(elapsed / 60);
            var seconds = elapsed % 60;

            document.getElementById('elapsed-time').textContent =
                String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

            // Show warning and auto-recreate at 40 minutes (2400 seconds)
            if (elapsed >= 2400 && !warningShown) {
                warningShown = true;
                showWarning();
                
                // Auto-trigger recreation
                var btn = document.getElementById('continue-meeting-btn');
                btn.innerHTML = '<i class="fe fe-loader fe-spin me-2"></i>Auto-recreating in 3s...';
                
                setTimeout(function() {
                    continueMeeting();
                }, 3000);
            }

            // Update remaining time in warning banner
            if (warningShown) {
                var remaining = 2700 - elapsed; // 45 minutes = 2700 seconds
                if (remaining > 0) {
                    var remMin = Math.floor(remaining / 60);
                    var remSec = remaining % 60;
                    document.getElementById('time-remaining').textContent =
                        remMin + ':' + String(remSec).padStart(2, '0');
                } else {
                    document.getElementById('time-remaining').textContent = '0:00';
                }
            }
        }

        function showWarning() {
            document.getElementById('warning-banner').classList.add('show');
        }

        function continueMeeting() {
            var btn = document.getElementById('continue-meeting-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fe fe-loader fe-spin me-2"></i>Creating...';
            document.getElementById('end-meeting-btn').disabled = true;

            fetch('/api/sessions/' + sessionId + '/recreate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Auto-redirect to new meeting
                        document.getElementById('warning-banner').style.display = 'none';
                        document.getElementById('main-content').innerHTML = '<div class="card"><div class="card-body text-center py-5"><i class="fe fe-check-circle text-success" style="font-size:48px;"></i><h3 class="mt-3">Redirecting to new meeting...</h3></div></div>';

                        // Open the new meeting
                        window.open(data.session.zoom_start_url, '_blank');

                        // Redirect this page to the new session join page
                        window.location.href = '{{ env('APP_URL') }}/highboard/sessions/' + data.session.id + '/join';
                    } else {
                        alert('Error: ' + (data.error || 'Failed to create new meeting'));
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fe fe-refresh-cw me-2"></i>Continue Meeting';
                        document.getElementById('end-meeting-btn').disabled = false;
                    }
                })
                .catch(err => {
                    alert('Error creating new meeting: ' + err.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fe fe-refresh-cw me-2"></i>Continue Meeting';
                    document.getElementById('end-meeting-btn').disabled = false;
                });
        }

        function endMeeting() {
            if (!confirm('Are you sure you want to end the meeting permanently? Members will be notified that the meeting has ended.')) {
                return;
            }

            var btn = document.getElementById('end-meeting-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fe fe-loader fe-spin me-2"></i>Ending...';
            document.getElementById('continue-meeting-btn').disabled = true;

            fetch('/api/sessions/' + sessionId + '/mark-ended', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('warning-banner').style.display = 'none';
                        document.getElementById('main-content').innerHTML = '<div class="card"><div class="card-body text-center py-5"><i class="fe fe-check-circle text-success" style="font-size:48px;"></i><h3 class="mt-3">Meeting Ended</h3><p class="text-muted">The meeting has been ended. Members have been notified.</p><a href="{{ route("highboard.sessions.index") }}" class="btn btn-primary mt-3">Back to Sessions</a></div></div>';
                    } else {
                        alert('Error: ' + (data.error || 'Failed to end meeting'));
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fe fe-x-circle me-2"></i>End Meeting';
                        document.getElementById('continue-meeting-btn').disabled = false;
                    }
                })
                .catch(err => {
                    alert('Error ending meeting: ' + err.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fe fe-x-circle me-2"></i>End Meeting';
                    document.getElementById('continue-meeting-btn').disabled = false;
                });
        }

        setTimeout(showWarning, 10000);
    </script>
@endsection