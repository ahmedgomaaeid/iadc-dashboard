@extends('layouts.board-dashboard')

@section('title', 'Join Session: ' . $session->title)

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

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center" style="min-height: 400px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                    <h3 class="mb-4">Ready to start the meeting?</h3>
                    <p class="text-muted mb-4">As the host, please launch the meeting using the Zoom application to enable auto-recording and host controls.</p>
                    
                    @if($session->zoom_start_url)
                        <a href="{{ $session->zoom_start_url }}" class="btn btn-primary btn-lg mb-3" target="_blank">
                            <i class="fe fe-video me-2"></i> Launch Zoom App
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
                </div>
            </div>
        </div>
    </div>
@endsection
