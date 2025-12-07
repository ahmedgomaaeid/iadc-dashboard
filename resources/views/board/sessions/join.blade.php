@extends('layouts.board-dashboard')

@section('title', 'Join Session: ' . $session->title)

@section('css')
    <style>
        .warning-banner {
            background: linear-gradient(135deg, #ff9800, #f57c00);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .warning-banner.show {
            display: block;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.8;
            }
        }

        .meeting-timer {
            font-size: 24px;
            font-weight: bold;
        }

        .continuation-card {
            display: none;
            background: linear-gradient(135deg, #4caf50, #2e7d32);
            color: white;
        }

        .continuation-card.show {
            display: block;
        }
    </style>
@endsection

@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ $session->title }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('board.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('board.sessions.index') }}">Sessions</a></li>
                <li class="breadcrumb-item active" aria-current="page">Join Session</li>
            </ol>
        </div>
    </div>

    <!-- Warning Banner (shows at 40 minutes) -->
    <div class="warning-banner" id="warning-banner">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fe fe-alert-triangle me-2"></i>Meeting Ending Soon</h4>
                <p class="mb-0">Free Zoom meetings are limited to 45 minutes. Time remaining: <span class="meeting-timer"
                        id="time-remaining">5:00</span></p>
            </div>
            <button class="btn btn-light btn-lg" id="continue-meeting-btn" onclick="continueMeeting()">
                <i class="fe fe-refresh-cw me-2"></i>Continue Meeting
            </button>
        </div>
    </div>

    <!-- Continuation Success Card -->
    <div class="card continuation-card" id="continuation-card">
        <div class="card-body text-center py-5">
            <i class="fe fe-check-circle" style="font-size: 48px;"></i>
            <h3 class="mt-3">New Meeting Created!</h3>
            <p class="mb-4">A new meeting has been created. Click below to join.</p>
            <a href="#" id="new-meeting-link" class="btn btn-light btn-lg" target="_blank">
                <i class="fe fe-video me-2"></i>Join New Meeting
            </a>
            <p class="mt-3 small">Members will automatically join when you start the new meeting.</p>
        </div>
    </div>

    <div class="row" id="main-content">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center"
                    style="min-height: 400px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                    <h3 class="mb-4">Ready to start the meeting?</h3>
                    <p class="text-muted mb-4">As the host, please launch the meeting using the Zoom application to enable
                        auto-recording and host controls.</p>

                    @if($session->zoom_start_url)
                        <a href="{{ $session->zoom_start_url }}" class="btn btn-primary btn-lg mb-3" target="_blank"
                            id="launch-zoom-btn" onclick="markAsJoined()">
                            <i class="fe fe-video me-2"></i>Launch Zoom App
                        </a>
                        <p class="small text-muted">This will open the Zoom application on your device.</p>
                    @else
                        <div class="alert alert-warning">
                            Zoom meeting details are missing. Please contact support.
                        </div>
                    @endif

                    <div class="mt-4">
                        <p><strong>Meeting ID:</strong> {{ $session->zoom_meeting_id }}</p>
                        <p><strong>Password:</strong> {{ $session->zoom_password }}</p>
                    </div>

                    <!-- Timer Display -->
                    <div class="mt-4 p-3 bg-light rounded" id="timer-display">
                        <p class="mb-1 text-muted">Meeting Duration</p>
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

            // Show warning at 40 minutes (2400 seconds)
            if (elapsed >= 2400 && !warningShown) {
                showWarning();
                warningShown = true;
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
                        // Show continuation card
                        document.getElementById('warning-banner').style.display = 'none';
                        document.getElementById('main-content').style.display = 'none';
                        document.getElementById('continuation-card').classList.add('show');
                        document.getElementById('new-meeting-link').href = data.session.zoom_start_url;

                        // Reset timer for new meeting
                        meetingStartTime = null;
                        warningShown = false;
                    } else {
                        alert('Error: ' + (data.error || 'Failed to create new meeting'));
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fe fe-refresh-cw me-2"></i>Continue Meeting';
                    }
                })
                .catch(err => {
                    alert('Error creating new meeting: ' + err.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fe fe-refresh-cw me-2"></i>Continue Meeting';
                });
        }

        // For demo/testing: Show warning after 10 seconds (comment out in production)
        setTimeout(showWarning, 10000);
    </script>
@endsection