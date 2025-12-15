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
            position: relative !important;
            background-color: #000;
            border-radius: 8px;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9998;
        }

        .loading-spinner {
            color: white;
            font-size: 18px;
        }

        .waiting-card {
            display: none;
            background: linear-gradient(135deg, #2196f3, #1976d2);
            color: white;
        }

        .waiting-card.show {
            display: block;
        }

        .spinner-grow {
            width: 3rem;
            height: 3rem;
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

    <!-- Waiting for Host to Rejoin Card (shown when meeting ends) -->
    <div class="card waiting-card" id="waiting-card">
        <div class="card-body text-center py-5">
            <div class="spinner-grow text-light mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h3 class="mt-3">Meeting Ended</h3>
            <p class="mb-2">The meeting has ended due to the 45-minute limit.</p>
            <p class="mb-4">Waiting for the host to start a new meeting...</p>
            <p class="small opacity-75">You will automatically join when the host starts the new meeting.</p>
        </div>
    </div>

    <!-- Join Button Card (hidden when meeting starts) -->
    <div class="row" id="join-card">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-4">Join Meeting</h3>
                    <p class="text-muted mb-4">Click the button below to join the meeting.</p>

                    <button id="join-meeting" class="btn btn-primary btn-lg">
                        <i class="fe fe-video me-2"></i> Join Meeting
                    </button>
                    <div id="error-message" class="text-danger mt-3" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Zoom Meeting Card (shown when meeting starts) -->
    <div class="row" id="meeting-container" style="display:none;">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fe fe-video me-2"></i> {{ $session->title }}</h3>
                    <div class="card-options">
                        <span class="badge bg-success">
                            <i class="fe fe-circle me-1"></i> Live
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="zmmtg-root"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="loading-overlay" id="loading-overlay">
        <div class="loading-spinner">
            <i class="fa fa-spinner fa-spin fa-3x"></i>
            <p class="mt-3">Joining meeting...</p>
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

        // Wait for the page to fully load before initializing Zoom SDK
        window.addEventListener('load', function () {
            console.log('Page loaded, initializing Zoom SDK...');

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
                    leaveUrl: "{{ route('user.sessions.index') }}",
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

                // Handle meeting status changes
                ZoomMtg.inMeetingServiceListener('onMeetingStatus', function (data) {
                    console.log('Meeting status:', data);

                    if (data.meetingStatus === 1 || data.meetingStatus === 2) {
                        var loadingOverlay = document.getElementById('loading-overlay');
                        if (loadingOverlay) {
                            loadingOverlay.style.display = 'none';
                        }

                        document.getElementById('join-card').style.display = 'none';
                        document.getElementById('meeting-container').style.display = 'block';
                        document.getElementById('zmmtg-root').style.display = 'block';

                        console.log('Zoom interface shown');
                    } else if (data.meetingStatus === 3) {
                        // Meeting ended - show waiting card and poll for new meeting
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
            // Hide meeting container and show waiting card
            document.getElementById('meeting-container').style.display = 'none';
            document.getElementById('join-card').style.display = 'none';
            document.getElementById('zmmtg-root').style.display = 'none';
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

        function joinNewMeeting(sessionData) {
            // Update UI
            document.getElementById('waiting-card').classList.remove('show');
            document.getElementById('join-card').style.display = 'block';

            // Update meeting config with new session data
            meetingConfig.meetingNumber = sessionData.zoom_meeting_id;
            meetingConfig.passWord = sessionData.zoom_password;

            // Need to get new signature - redirect to the new session page
            window.location.href = '/sessions/' + sessionData.id + '/join';
        }
    </script>
@endsection