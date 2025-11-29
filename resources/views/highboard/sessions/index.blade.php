@extends('layouts.highboard-dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Sessions</h3>
                    <a href="{{ route('highboard.sessions.create') }}" class="btn btn-primary">Create Session</a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sessions as $session)
                                    <tr>
                                        <td>{{ $session->title }}</td>
                                        <td>{{ $session->start_time->format('Y-m-d H:i') }}</td>
                                        <td>{{ $session->end_time ? $session->end_time->format('Y-m-d H:i') : 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('highboard.sessions.edit', $session->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                            <form action="{{ route('highboard.sessions.destroy', $session->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                            </form>
                                            <a href="{{ route('highboard.sessions.join', $session->id) }}" class="btn btn-sm btn-success">Join</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
