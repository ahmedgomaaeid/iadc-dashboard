@extends('layouts.admin-dashboard')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Committees Management</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Committees</li>
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
                    <h3 class="card-title">All Committees</h3>
                    <a href="{{ route('admin.committees.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>Add New Committee
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-nowrap border-bottom">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Field</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($committees as $committee)
                                    <tr>
                                        <td>{{ $committee->id }}</td>
                                        <td>{{ $committee->name }}</td>
                                        <td>
                                            @if($committee->field)
                                                <span class="badge bg-info">{{ $committee->field->name }}</span>
                                            @else
                                                <span class="badge bg-secondary">No Field</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($committee->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ $committee->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.committees.edit', $committee) }}" 
                                                   class="btn btn-sm btn-info" title="Edit">
                                                    <i class="fe fe-edit"></i>
                                                </a>
                                                
                                                <form action="{{ route('admin.committees.toggle-status', $committee) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning" 
                                                            title="Toggle Status">
                                                        <i class="fe fe-{{ $committee->is_active ? 'eye-off' : 'eye' }}"></i>
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('admin.committees.destroy', $committee) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to deactivate this committee?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            title="Deactivate">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No committees found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $committees->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
