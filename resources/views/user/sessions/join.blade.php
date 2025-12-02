@extends('layouts.user-dashboard')

@section('title', 'Join Session: ' . $session->title)

@section('css')
    <link type="text/css" rel="stylesheet" href="https://source.zoom.us/3.8.10/css/bootstrap.css" />
    <link type="text/css" rel="stylesheet" href="https://source.zoom.us/3.8.10/css/react-select.css" />
    <style>
        #zmmtg-root {
            display: none;
            width: 100%;
            height: 80vh; /* 80% of viewport height to leave room for content above */
            min-height: 600px;
            position: relative;
            background-color: #000;
            border-radius: 8px;
            overflow: hidden;
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
        // Declare meetingConfig globally
        var meetingConfig;
        
        // Wait for the page to fully load before initializing Zoom SDK
        window.addEventListener('load', function() {
            console.log('Page loaded, initializing Zoom SDK...');
            
            try {
                // Check if Zoom SDK is available
                if (typeof ZoomMtg === 'undefined') {
                    console.error('Zoom SDK (ZoomMtg) is not defined! The SDK scripts failed to load.');
                    document.getElementById('error-message').innerText = 'Failed to load Zoom SDK. Please refresh the page.';
                    document.getElementById('error-message').style.display = 'block';
                    return;
                }

                console.log('Zoom SDK is available');
                
                // Try to get version only if getVersion exists
                if (typeof ZoomMtg.getVersion === 'function') {
                    console.log('Zoom SDK version:', ZoomMtg.getVersion());
                }
                
                // Configure Zoom SDK
                ZoomMtg.setZoomJSLib('https://source.zoom.us/3.8.10/lib', '/av');
                ZoomMtg.preLoadWasm();
                ZoomMtg.prepareWebSDK();
                console.log('Zoom SDK configured successfully');

                // Meeting configuration
                meetingConfig = {
                    apiKey: "{{ env('ZOOM_CLIENT_ID') }}",
                    meetingNumber: "{{ $session->zoom_meeting_id }}",
                    userName: "{{ $user->name }}",
                    passWord: "{{ $session->zoom_password ?? '' }}",
                    leaveUrl: "{{ route('user.sessions.index') }}",
                    role: 0, // 0 for participant, 1 for host
                    userEmail: "{{ $user->email }}",
                    signature: "{{ $signature }}",
                };

                console.log('Meeting config:', {
                    meetingNumber: meetingConfig.meetingNumber,
                    userName: meetingConfig.userName,
                    hasSignature: !!meetingConfig.signature,
                    hasApiKey: !!meetingConfig.apiKey
                });

                // Set up the join button event listener
                document.getElementById('join-meeting').addEventListener('click', function() {
                    var loadingOverlay = document.getElementById('loading-overlay');
                    var errorDiv = document.getElementById('error-message');
                    var joinButton = document.getElementById('join-meeting');
                    
                    // Show loading overlay
                    loadingOverlay.style.display = 'flex';
                    joinButton.disabled = true;
                    errorDiv.style.display = 'none';

                    console.log('Initializing Zoom meeting...');

                    ZoomMtg.init({
                        leaveUrl: meetingConfig.leaveUrl,
                        isSupportAV: true,
                        success: function(initResult) {
                            console.log('Zoom init success:', initResult);
                            
                            console.log('About to call ZoomMtg.join with params:', {
                                meetingNumber: meetingConfig.meetingNumber,
                                userName: meetingConfig.userName,
                                sdkKey: meetingConfig.apiKey,
                                hasPassword: !!meetingConfig.passWord,
                                hasSignature: !!meetingConfig.signature,
                                userEmail: meetingConfig.userEmail
                            });
                            
                            try {
                                ZoomMtg.join({
                                    meetingNumber: meetingConfig.meetingNumber,
                                    userName: meetingConfig.userName,
                                    signature: meetingConfig.signature,
                                    sdkKey: meetingConfig.apiKey,
                                    passWord: meetingConfig.passWord,
                                    userEmail: meetingConfig.userEmail,
                                    success: function(joinResult) {
                                        console.log('Join meeting success:', joinResult);
                                        loadingOverlay.style.display = 'none';
                                        document.getElementById('zmmtg-root').style.display = 'block';
                                    },
                                    error: function(joinError) {
                                        console.error('Join meeting error:', joinError);
                                        loadingOverlay.style.display = 'none';
                                        joinButton.disabled = false;
                                        errorDiv.style.display = 'block';
                                        
                                        var errorMessage = 'Error joining meeting: ';
                                        if (joinError.errorMessage) {
                                            errorMessage += joinError.errorMessage;
                                        } else if (joinError.result) {
                                            errorMessage += joinError.result;
                                        } else if (joinError.method) {
                                            errorMessage += joinError.method;
                                        } else {
                                            errorMessage += 'Unknown error. Please check your meeting credentials.';
                                        }
                                        
                                        errorDiv.innerText = errorMessage;
                                    }
                                });
                                console.log('ZoomMtg.join() called successfully');
                            } catch (e) {
                                console.error('Exception when calling ZoomMtg.join:', e);
                                loadingOverlay.style.display = 'none';
                                joinButton.disabled = false;
                                errorDiv.style.display = 'block';
                                errorDiv.innerText = 'Error calling ZoomMtg.join: ' + e.message;
                            }
                        },
                        error: function(initError) {
                            console.error('Zoom init error:', initError);
                            loadingOverlay.style.display = 'none';
                            joinButton.disabled = false;
                            errorDiv.style.display = 'block';
                            
                            var errorMessage = 'Error initializing Zoom: ';
                            if (initError.errorMessage) {
                                errorMessage += initError.errorMessage;
                            } else if (initError.result) {
                                errorMessage += initError.result;
                            } else if (initError.method) {
                                errorMessage += initError.method;
                            } else {
                                errorMessage += 'Failed to initialize Zoom SDK. Please refresh the page and try again.';
                            }
                            
                            errorDiv.innerText = errorMessage;
                        }
                    });
                });
                
                console.log('Join button event listener registered successfully');
                
                // Handle meeting status changes
                ZoomMtg.inMeetingServiceListener('onMeetingStatus', function(data) {
                    console.log('Meeting status:', data);
                    
                    // Status: 1 = Connecting/Joining, 2 = In Meeting, 3 = Ended
                    if (data.meetingStatus === 1 || data.meetingStatus === 2) {
                        // Hide loading overlay
                        var loadingOverlay = document.getElementById('loading-overlay');
                        if (loadingOverlay) {
                            loadingOverlay.style.display = 'none';
                        }
                        
                        // Hide join card and show meeting container
                        document.getElementById('join-card').style.display = 'none';
                        document.getElementById('meeting-container').style.display = 'block';
                        document.getElementById('zmmtg-root').style.display = 'block';
                        
                        console.log('Zoom interface shown, status:', data.meetingStatus);
                    } else if (data.meetingStatus === 3) { // Meeting ended
                        window.location.href = meetingConfig.leaveUrl;
                    }
                });
                
            } catch (error) {
                console.error('Error initializing Zoom SDK:', error);
                document.getElementById('error-message').innerText = 'Error initializing Zoom SDK: ' + error.message;
                document.getElementById('error-message').style.display = 'block';
            }
        });
    </script>
@endsection
