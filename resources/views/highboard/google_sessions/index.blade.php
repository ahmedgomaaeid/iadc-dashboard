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
                        <div id="calendar"></div>
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
                            <label for="eventTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="eventTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="eventDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="eventDescription" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="start_time" class="form-label">Start Time</label>
                            <input type="datetime-local" class="form-control" id="start_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="end_time" class="form-label">End Time</label>
                            <input type="datetime-local" class="form-control" id="end_time" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveEventBtn">Save Session</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            window.calendarEvents = @json($sessions->map(function($session) {
                return [
                    'title' => $session->title,
                    'start' => $session->start_time->toIso8601String(),
                    'end' => $session->end_time->toIso8601String(),
                    'url' => $session->session_url
                ];
            }));
        </script>
        @vite('resources/js/calendar.js')
    @endpush
            </div>
        </div>
    </div>
</div>
@endsection
