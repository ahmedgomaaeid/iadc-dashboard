@extends('layouts.board-dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Session</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('board.sessions.update', $session->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group mb-3">
                            <label for="title">Title</label>
                            <input type="text" name="title" id="title" class="form-control" value="{{ $session->title }}" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3">{{ $session->description }}</textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label for="start_time">Start Time</label>
                            <input type="datetime-local" name="start_time" id="start_time" class="form-control" value="{{ $session->start_time->format('Y-m-d\TH:i') }}" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="end_time">End Time</label>
                            <input type="datetime-local" name="end_time" id="end_time" class="form-control" value="{{ $session->end_time ? $session->end_time->format('Y-m-d\TH:i') : '' }}">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Session</button>
                        <a href="{{ route('board.sessions.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
