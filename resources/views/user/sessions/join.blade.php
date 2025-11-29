@extends('layouts.user-dashboard')

@section('title', 'Join Session: ' . $session->title)

@section('css')
    <link type="text/css" rel="stylesheet" href="https://source.zoom.us/2.18.0/css/bootstrap.css" />
    <link type="text/css" rel="stylesheet" href="https://source.zoom.us/2.18.0/css/react-select.css" />
    <style>
        #zmmtg-root {
            display: none;
            min-width: 100%;
            min-height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 9999;
            background-color: black;
        }
        .meeting-container {
            position: relative;
            width: 100%;
            height: calc(100vh - 200px);
            min-height: 600px;
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

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-4">Join Meeting</h3>
                    <p class="text-muted mb-4">Click the button below to join the meeting directly in your browser.</p>
                    
                    <button id="join-meeting" class="btn btn-primary btn-lg">
                        <i class="fe fe-video me-2"></i> Join Meeting
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="zmmtg-root"></div>
@endsection

@section('scripts')
    <script src="https://source.zoom.us/2.18.0/lib/vendor/react.min.js"></script>
    <script src="https://source.zoom.us/2.18.0/lib/vendor/react-dom.min.js"></script>
    <script src="https://source.zoom.us/2.18.0/lib/vendor/redux.min.js"></script>
    <script src="https://source.zoom.us/2.18.0/lib/vendor/redux-thunk.min.js"></script>
    <script src="https://source.zoom.us/2.18.0/lib/vendor/lodash.min.js"></script>
    <script src="https://source.zoom.us/zoom-meeting-2.18.0.min.js"></script>

    <script>
        ZoomMtg.preLoadWasm();
        ZoomMtg.prepareWebSDK();

        document.getElementById('join-meeting').addEventListener('click', function() {
            document.getElementById('zmmtg-root').style.display = 'block';

            var meetingConfig = {
                apiKey: "{{ env('ZOOM_CLIENT_ID') }}",
                meetingNumber: "{{ $session->zoom_meeting_id }}",
                userName: "{{ $user->name }}",
                passWord: "{{ $session->zoom_password }}",
                leaveUrl: "{{ route('user.sessions.index') }}",
                role: 0,
                userEmail: "{{ $user->email }}",
                signature: "{{ $signature }}",
                china: 0,
            };

            ZoomMtg.init({
                leaveUrl: meetingConfig.leaveUrl,
                success: function() {
                    ZoomMtg.join({
                        meetingNumber: meetingConfig.meetingNumber,
                        userName: meetingConfig.userName,
                        signature: meetingConfig.signature,
                        apiKey: meetingConfig.apiKey,
                        passWord: meetingConfig.passWord,
                        success: function(res) {
                            console.log('join meeting success');
                        },
                        error: function(res) {
                            console.log(res);
                        }
                    });
                },
                error: function(res) {
                    console.log(res);
                }
            });
        });
    </script>
@endsection
