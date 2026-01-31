@extends('layouts.user-dashboard')

@section('title', 'Join Session: ' . $session->title)

@section('css')
    <link type="text/css" rel="stylesheet" href="https://source.zoom.us/3.8.10/css/bootstrap.css" />
    <link type="text/css" rel="stylesheet" href="https://source.zoom.us/3.8.10/css/react-select.css" />
    <style>
        body {
            overflow: scroll !important;
        }

        .breadcrumb {
            background-color: transparent !important;
        }

        #zmmtg-root {
            display: none;
            width: 100%;
            min-height: 600px;
            height: calc(100vh - 200px);
            position: relative !important;
            background-color: #000;
            border-radius: 12px;
            overflow: hidden;
        }

        /* Ensure Zoom toolbar and bottom buttons are visible */
        #zmmtg-root > div {
            height: 100% !important;
        }

        /* Fix Zoom fullscreen to use the Zoom SDK's native fullscreen */
        #zmmtg-root:fullscreen,
        #zmmtg-root:-webkit-full-screen,
        #zmmtg-root:-moz-full-screen,
        #zmmtg-root:-ms-fullscreen {
            width: 100vw !important;
            height: 100vh !important;
            border-radius: 0;
            position: fixed !important;
            top: 0;
            left: 0;
            z-index: 99999;
        }

        @media (min-width: 768px) {
            #zmmtg-root {
                min-height: 650px;
                height: calc(100vh - 180px);
            }
        }

        @media (min-width: 1200px) {
            #zmmtg-root {
                min-height: 700px;
                height: calc(100vh - 160px);
            }
        }

        /* Mobile: Make meeting fullscreen in landscape */
        @media (max-width: 768px) {
            #zmmtg-root {
                min-height: 400px;
                height: calc(100vh - 120px);
                border-radius: 0;
            }

            /* Hide header and other elements when meeting is active on mobile */
            body.meeting-active .page-header,
            body.meeting-active .app-sidebar,
            body.meeting-active .app-header,
            body.meeting-active .breadcrumb {
                display: none !important;
            }

            body.meeting-active .app-content {
                margin: 0 !important;
                padding: 0 !important;
            }

            body.meeting-active #meeting-container {
                margin: 0 !important;
                padding: 0 !important;
            }

            body.meeting-active .meeting-card {
                border-radius: 0 !important;
                margin: 0 !important;
            }

            body.meeting-active .meeting-card .card-header {
                display: none !important;
            }

            body.meeting-active #zmmtg-root {
                height: 100vh !important;
                width: 100vw !important;
                position: fixed !important;
                top: 0;
                left: 0;
                z-index: 9999;
            }
        }

        /* Landscape mode on mobile - force full screen */
        @media (max-width: 992px) and (orientation: landscape) {
            body.meeting-active {
                overflow: hidden !important;
            }

            body.meeting-active .page-header,
            body.meeting-active .app-sidebar,
            body.meeting-active .app-header,
            body.meeting-active .breadcrumb,
            body.meeting-active .meeting-card .card-header {
                display: none !important;
            }

            body.meeting-active #zmmtg-root {
                height: 100vh !important;
                width: 100vw !important;
                min-height: 100vh !important;
                position: fixed !important;
                top: 0;
                left: 0;
                z-index: 99999;
                border-radius: 0 !important;
            }
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9998;
            backdrop-filter: blur(5px);
        }

        .loading-spinner {
            color: white;
            font-size: 18px;
            text-align: center;
        }

        .loading-spinner i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        /* Status Cards Base Styling */
        .status-card {
            display: none;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }

        .status-card.show {
            display: block;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Waiting Card */
        .waiting-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .waiting-card .card-body {
            padding: 3rem 1.5rem;
        }

        @media (min-width: 768px) {
            .waiting-card .card-body {
                padding: 4rem 2rem;
            }
        }

        .waiting-card .status-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            animation: pulse 2s infinite;
        }

        .waiting-card .status-icon i {
            font-size: 2rem;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 0 15px rgba(255, 255, 255, 0);
            }
        }

        /* Meeting Ended Card */
        .meeting-ended-card {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
        }

        .meeting-ended-card .card-body {
            padding: 3rem 1.5rem;
        }

        @media (min-width: 768px) {
            .meeting-ended-card .card-body {
                padding: 4rem 2rem;
            }
        }

        .meeting-ended-card .status-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .meeting-ended-card .status-icon i {
            font-size: 2rem;
        }

        /* Auto-joining Card */
        .autojoining-card {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .autojoining-card .card-body {
            padding: 3rem 1.5rem;
        }

        /* Join Card */
        .join-card {
            border-radius: 16px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .join-card .card-body {
            padding: 2.5rem 1.5rem;
        }

        @media (min-width: 768px) {
            .join-card .card-body {
                padding: 3rem 2rem;
            }
        }

        .join-card .meeting-icon {
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

        .join-card .meeting-icon i {
            font-size: 2.5rem;
            color: white;
        }

        .join-card .btn-join {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
        }

        .join-card .btn-join:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
        }

        .join-card .btn-join:disabled {
            opacity: 0.7;
            transform: none;
        }

        /* Meeting Container */
        .meeting-card {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        .meeting-card .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 1.5rem;
            border: none;
        }

        .meeting-card .card-header .card-title {
            color: white;
            margin: 0;
            font-weight: 600;
        }

        .live-badge {
            background: #38ef7d !important;
            animation: livePulse 2s infinite;
            font-weight: 600;
        }

        @keyframes livePulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }

        /* Progress dots for waiting */
        .progress-dots {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 1.5rem;
        }

        .progress-dots span {
            width: 10px;
            height: 10px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            animation: dotPulse 1.5s infinite;
        }

        .progress-dots span:nth-child(2) {
            animation-delay: 0.3s;
        }

        .progress-dots span:nth-child(3) {
            animation-delay: 0.6s;
        }

        @keyframes dotPulse {
            0%, 100% {
                transform: scale(1);
                background: rgba(255, 255, 255, 0.5);
            }
            50% {
                transform: scale(1.3);
                background: rgba(255, 255, 255, 1);
            }
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .page-header h1 {
                font-size: 1.5rem;
            }

            .status-card h3 {
                font-size: 1.3rem;
            }

            .status-card p {
                font-size: 0.9rem;
            }

            .join-card .meeting-icon {
                width: 80px;
                height: 80px;
            }

            .join-card .meeting-icon i {
                font-size: 2rem;
            }
        }
        #wc-loading {
            display: none !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ $session->title }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('user.sessions.index') }}">Sessions</a></li>
                <li class="breadcrumb-item active" aria-current="page">Join Session</li>
            </ol>
        </div>
    </div>

    <!-- Auto-joining Card (shown when auto-join is in progress) -->
    <div class="card status-card autojoining-card" id="autojoining-card" style="display:none;">
        <div class="card-body text-center">
            <div class="status-icon" style="background: rgba(255,255,255,0.2); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <i class="fe fe-zap" style="font-size: 2rem;"></i>
            </div>
            <h3 class="mt-3 mb-3">Joining Meeting...</h3>
            <p class="mb-2 opacity-90">You're being connected to the continuation meeting.</p>
            <p class="small opacity-75">Please wait, this will only take a moment.</p>
            <div class="progress-dots">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>

    <!-- Waiting for Host to Rejoin Card (shown when meeting ends) -->
    <div class="card status-card waiting-card" id="waiting-card">
        <div class="card-body text-center">
            <div class="status-icon">
                <i class="fe fe-clock"></i>
            </div>
            <h3 class="mb-3">Meeting Session Ended</h3>
            <p class="mb-2 opacity-90">The meeting has ended due to the 45-minute limit.</p>
            <p class="mb-0 opacity-75">Waiting for the host to start the continuation...</p>
            <div class="progress-dots">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <p class="small opacity-75 mt-4">
                <i class="fe fe-info me-1"></i>
                You will automatically join when the host starts the new meeting.
            </p>
        </div>
    </div>

    <!-- Meeting Finally Ended Card (shown when host ends meeting permanently) -->
    <div class="card status-card meeting-ended-card" id="meeting-ended-card">
        <div class="card-body text-center">
            <div class="status-icon">
                <i class="fe fe-check"></i>
            </div>
            <h3 class="mb-3">Meeting Has Concluded</h3>
            <p class="mb-4 opacity-90">The host has ended this meeting session.<br>Thank you for attending!</p>
            <a href="{{ route('user.sessions.index') }}" class="btn btn-light btn-lg px-4">
                <i class="fe fe-arrow-left me-2"></i>Back to Sessions
            </a>
        </div>
    </div>

    <!-- Join Button Card (hidden when meeting starts) -->
    <div class="row" id="join-card">
        <div class="col-12 col-lg-8 col-xl-6 mx-auto">
            <div class="card join-card">
                <div class="card-body text-center">
                    <div class="meeting-icon">
                        <i class="fe fe-video"></i>
                    </div>
                    <h3 class="mb-3">Ready to Join</h3>
                    <p class="text-muted mb-4">{{ $session->title }}</p>

                    <button id="join-meeting" class="btn btn-primary btn-lg btn-join">
                        <i class="fe fe-video me-2"></i>Join Meeting
                    </button>
                    
                    <div id="error-message" class="alert alert-danger mt-4" style="display:none;"></div>
                    
                    <div class="mt-4 pt-3 border-top">
                        <small class="text-muted">
                            <i class="fe fe-shield me-1"></i>
                            Your audio and video are off by default
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Zoom Meeting Card (shown when meeting starts) -->
    <div class="row" id="meeting-container" style="display:none;">
        <div class="col-12">
            <div class="card meeting-card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h3 class="card-title mb-0">
                        <i class="fe fe-video me-2"></i>{{ $session->title }}
                    </h3>
                    <span class="badge live-badge">
                        <i class="fe fe-radio me-1"></i>LIVE
                    </span>
                </div>
                <div class="card-body p-0">
                    <div id="zmmtg-root"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="loading-overlay" id="loading-overlay">
        <div class="loading-spinner">
            <div class="spinner-border text-light mb-3" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mb-0">Connecting to meeting...</p>
            <small class="opacity-75">Please wait</small>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://source.zoom.us/3.8.10/lib/vendor/react.min.js"></script>
    <script src="https://source.zoom.us/3.8.10/lib/vendor/react-dom.min.js"></script>
    <script src="https://source.zoom.us/3.8.10/lib/vendor/redux.min.js"></script>
    <script src="https://source.zoom.us/3.8.10/lib/vendor/redux-thunk.min.js"></script>
    <script src="https://source.zoom.us/3.8.10/lib/vendor/lodash.min.js"></script>
    <script src="https://source.zoom.us/3.8.10/zoom-meeting-3.8.10.min.js"></script>

    <script>
        // Global variables
        var meetingConfig;
        var sessionId = {{ $session->id }};
        var pollInterval = null;
        var currentMeetingId = "{{ $session->zoom_meeting_id }}";
        var isWaitingForContinuation = new URLSearchParams(window.location.search).get('waiting') === '1';
        var shouldAutoJoin = new URLSearchParams(window.location.search).get('autojoin') === '1';

        // Build leave URL with waiting parameter
        function getLeaveUrl() {
            var url = new URL(window.location.href);
            url.searchParams.set('waiting', '1');
            return url.toString();
        }

        // Check for continuation on page load (in case page was reloaded after meeting ended)
        function checkForContinuationOnLoad() {
            if (!isWaitingForContinuation) {
                return; // Not waiting, proceed normally
            }

            console.log('Page loaded in waiting mode, checking for continuation...');
            
            fetch('/api/sessions/' + sessionId + '/latest')
                .then(response => response.json())
                .then(data => {
                    console.log('Continuation check:', data);
                    
                    // If meeting is permanently ended, show ended card
                    if (data.is_finally_ended) {
                        showMeetingEndedCard();
                        return;
                    }
                    
                    // If there's a newer session and creator has joined, redirect to it
                    if (!data.is_same && data.creator_joined) {
                        console.log('Continuation found, redirecting...');
                        window.location.href = '/sessions/' + data.id + '/join';
                        return;
                    }
                    
                    // Still waiting - show waiting card and start polling
                    showWaitingForContinuation();
                })
                .catch(err => {
                    console.error('Error checking for continuation:', err);
                    // On error, still show waiting card
                    showWaitingForContinuation();
                });
        }

        // Run continuation check on page load
        checkForContinuationOnLoad();

        // Wait for the page to fully load before initializing Zoom SDK
        window.addEventListener('load', function () {
            console.log('Page loaded, initializing Zoom SDK...');

            // If waiting for continuation, don't initialize Zoom SDK
            if (isWaitingForContinuation) {
                console.log('Waiting mode active, skipping Zoom SDK init');
                return;
            }

            try {
                if (typeof ZoomMtg === 'undefined') {
                    console.error('Zoom SDK (ZoomMtg) is not defined!');
                    document.getElementById('error-message').innerText = 'Failed to load Zoom SDK. Please refresh the page.';
                    document.getElementById('error-message').style.display = 'block';
                    return;
                }

                console.log('Zoom SDK is available');

                ZoomMtg.setZoomJSLib('https://source.zoom.us/3.8.10/lib', '/av');
                ZoomMtg.preLoadWasm();
                ZoomMtg.prepareWebSDK();
                console.log('Zoom SDK configured successfully');

                meetingConfig = {
                    apiKey: "{{ env('ZOOM_CLIENT_ID') }}",
                    meetingNumber: "{{ $session->zoom_meeting_id }}",
                    userName: "{{ $user->name }}",
                    passWord: "{{ $session->zoom_password ?? '' }}",
                    leaveUrl: getLeaveUrl(), // URL with waiting=1 parameter
                    role: 0,
                    userEmail: "{{ $user->email }}",
                    signature: "{{ $signature }}",
                };

                console.log('Meeting config ready');

                // Join button event listener
                document.getElementById('join-meeting').addEventListener('click', function () {
                    joinMeeting(meetingConfig);
                });

                console.log('Join button event listener registered successfully');

                // Auto-join if redirected from continuation
                if (shouldAutoJoin) {
                    console.log('Auto-join enabled, joining meeting automatically...');
                    // Show auto-joining card
                    document.getElementById('join-card').style.display = 'none';
                    document.getElementById('autojoining-card').style.display = 'block';
                    document.getElementById('autojoining-card').classList.add('show');
                    
                    setTimeout(function() {
                        joinMeeting(meetingConfig);
                    }, 1500); // Small delay to ensure everything is ready
                }

                // Handle meeting status changes
                ZoomMtg.inMeetingServiceListener('onMeetingStatus', function (data) {
                    console.log('Meeting status:', data);

                    if (data.meetingStatus === 1 || data.meetingStatus === 2) {
                        var loadingOverlay = document.getElementById('loading-overlay');
                        if (loadingOverlay) {
                            loadingOverlay.style.display = 'none';
                        }

                        document.getElementById('join-card').style.display = 'none';
                        document.getElementById('autojoining-card').classList.remove('show');
                        document.getElementById('meeting-container').style.display = 'block';
                        document.getElementById('zmmtg-root').style.display = 'block';
                        
                        // Add meeting-active class for mobile fullscreen
                        document.body.classList.add('meeting-active');

                        console.log('Zoom interface shown');
                    } else if (data.meetingStatus === 3) {
                        // Meeting ended - remove meeting-active class
                        document.body.classList.remove('meeting-active');
                        
                        // Show waiting card and poll for new meeting
                        console.log('Meeting ended, starting to poll for continuation...');
                        showWaitingForContinuation();
                    }
                });

            } catch (error) {
                console.error('Error initializing Zoom SDK:', error);
                document.getElementById('error-message').innerText = 'Error initializing Zoom SDK: ' + error.message;
                document.getElementById('error-message').style.display = 'block';
            }
        });

        function joinMeeting(config) {
            var loadingOverlay = document.getElementById('loading-overlay');
            var errorDiv = document.getElementById('error-message');
            var joinButton = document.getElementById('join-meeting');

            loadingOverlay.style.display = 'flex';
            joinButton.disabled = true;
            errorDiv.style.display = 'none';

            console.log('Initializing Zoom meeting...');

            ZoomMtg.init({
                leaveUrl: config.leaveUrl,
                isSupportAV: true,
                success: function (initResult) {
                    console.log('Zoom init success:', initResult);

                    try {
                        ZoomMtg.join({
                            meetingNumber: config.meetingNumber,
                            userName: config.userName,
                            signature: config.signature,
                            sdkKey: config.apiKey,
                            passWord: config.passWord,
                            userEmail: config.userEmail,
                            success: function (joinResult) {
                                console.log('Join meeting success:', joinResult);
                                loadingOverlay.style.display = 'none';
                                document.getElementById('zmmtg-root').style.display = 'block';
                                currentMeetingId = config.meetingNumber;
                            },
                            error: function (joinError) {
                                console.error('Join meeting error:', joinError);
                                loadingOverlay.style.display = 'none';
                                joinButton.disabled = false;
                                errorDiv.style.display = 'block';
                                errorDiv.innerText = 'Error joining meeting. Please try again.';
                            }
                        });
                    } catch (e) {
                        console.error('Exception when calling ZoomMtg.join:', e);
                        loadingOverlay.style.display = 'none';
                        joinButton.disabled = false;
                        errorDiv.style.display = 'block';
                        errorDiv.innerText = 'Error calling ZoomMtg.join: ' + e.message;
                    }
                },
                error: function (initError) {
                    console.error('Zoom init error:', initError);
                    loadingOverlay.style.display = 'none';
                    joinButton.disabled = false;
                    errorDiv.style.display = 'block';
                    errorDiv.innerText = 'Error initializing Zoom. Please refresh and try again.';
                }
            });
        }

        function showWaitingForContinuation() {
            // Remove meeting-active class to restore normal layout
            document.body.classList.remove('meeting-active');
            
            // Hide meeting container and show waiting card
            document.getElementById('meeting-container').style.display = 'none';
            document.getElementById('join-card').style.display = 'none';
            document.getElementById('zmmtg-root').style.display = 'none';
            document.getElementById('autojoining-card').classList.remove('show');
            document.getElementById('waiting-card').classList.add('show');

            // Start polling for new session
            startPollingForNewSession();
        }

        function startPollingForNewSession() {
            console.log('Starting to poll for new session...');

            pollInterval = setInterval(function () {
                fetch('/api/sessions/' + sessionId + '/latest')
                    .then(response => response.json())
                    .then(data => {
                        console.log('Poll response:', data);

                        // Check if meeting is permanently ended
                        if (data.is_finally_ended) {
                            console.log('Meeting has been permanently ended by host.');
                            clearInterval(pollInterval);
                            showMeetingEndedCard();
                            return;
                        }

                        // Check if there's a new session and creator has joined
                        if (!data.is_same && data.creator_joined) {
                            console.log('New session found, creator has joined. Joining new meeting...');
                            clearInterval(pollInterval);

                            // Fetch new signature for the new meeting
                            joinNewMeeting(data);
                        }
                    })
                    .catch(err => {
                        console.error('Error polling for new session:', err);
                    });
            }, 5000); // Poll every 5 seconds
        }

        function showMeetingEndedCard() {
            document.getElementById('waiting-card').classList.remove('show');
            document.getElementById('autojoining-card').classList.remove('show');
            document.getElementById('meeting-container').style.display = 'none';
            document.getElementById('join-card').style.display = 'none';
            document.getElementById('meeting-ended-card').classList.add('show');
        }

        function joinNewMeeting(sessionData) {
            // Hide waiting card
            document.getElementById('waiting-card').classList.remove('show');
            
            // Redirect to the new session page with autojoin parameter
            window.location.href = '/sessions/' + sessionData.id + '/join?autojoin=1';
        }
    </script>
@endsection