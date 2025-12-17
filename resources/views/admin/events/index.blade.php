@extends('layouts.admin-dashboard')

@section('title', 'Events & Visits Management')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Events & Visits Management</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Events & Visits</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">All Events & Visits</h3>
                    <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>Add New Event/Visit
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-nowrap border-bottom">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Place</th>
                                    <th>Register</th>
                                    <th>Partners</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($events as $event)
                                    <tr>
                                        <td>{{ $event->id }}</td>
                                        <td>
                                            @if($event->image)
                                                <img src="{{ asset('storage/' . $event->image) }}" 
                                                     alt="{{ $event->name }}" 
                                                     class="rounded"
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <span class="text-muted">No image</span>
                                            @endif
                                        </td>
                                        <td>{{ $event->name }}</td>
                                        <td>
                                            @if($event->type === 'event')
                                                <span class="badge bg-primary">Event</span>
                                            @else
                                                <span class="badge bg-info">Visit</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $event->date_from->format('M d, Y') }}
                                            @if($event->date_to)
                                                <br><small class="text-muted">to {{ $event->date_to->format('M d, Y') }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $event->place }}</td>
                                        <td>
                                            @if($event->register_link)
                                                @if($event->register_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            @else
                                                <span class="badge bg-warning">No Link</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-purple">{{ $event->partners->count() }} Partners</span>
                                        </td>
                                        <td>
                                            @if($event->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.events.edit', $event) }}" 
                                                   class="btn btn-sm btn-info" title="Edit">
                                                    <i class="fe fe-edit"></i>
                                                </a>
                                                
                                                <form action="{{ route('admin.events.toggle-status', $event) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning" 
                                                            title="Toggle Status">
                                                        <i class="fe fe-{{ $event->is_active ? 'eye-off' : 'eye' }}"></i>
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('admin.events.destroy', $event) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this event?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            title="Delete">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No events or visits found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $events->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
