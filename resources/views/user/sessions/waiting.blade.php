@extends('layouts.user-dashboard')

@section('title', 'Waiting for Session: ' . $session->title)

@section('css')
<style>
    .waiting-container {
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    .waiting-icon {
        font-size: 64px;
        color: #6c757d;
        margin-bottom: 20px;
    }
    .spinner-border {
        width: 3rem;
        height: 3rem;
        margin-bottom: 20px;
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
                <li class="breadcrumb-item active" aria-current="page">Waiting</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="waiting-container">
                        <div>
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h3 class="mb-3">Waiting for Host to Join</h3>
                            <p class="text-muted">The meeting host hasn't joined yet. Please wait...</p>
                            <p class="text-muted mb-4">This page will automatically refresh when the host joins the meeting.</p>
                            
                            <div class="session-details mt-4">
                                <p><strong>Session:</strong> {{ $session->title }}</p>
                                @if($session->description)
                                    <p><strong>Description:</strong> {{ $session->description }}</p>
                                @endif
                                <p><strong>Scheduled Start:</strong> {{ $session->start_time->format('M d, Y h:i A') }}</p>
                                @if($session->committee)
                                    <p><strong>Committee:</strong> {{ $session->committee->name }}</p>
                                @endif
                            </div>

                            <div class="mt-4">
                                <a href="{{ route('user.sessions.index') }}" class="btn btn-secondary">
                                    <i class="fe fe-arrow-left me-2"></i>Back to Sessions
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // Auto-refresh every 10 seconds to check if creator has joined
    setTimeout(function() {
        location.reload();
    }, 10000);
</script>
@endsection
