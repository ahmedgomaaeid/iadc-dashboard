@extends('layouts.admin-dashboard')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Highboard Management</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Highboard</li>
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
                    <h3 class="card-title">All Highboard Members</h3>
                    <a href="{{ route('admin.highboards.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>Add New Highboard Member
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-nowrap border-bottom">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Field</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($highboards as $highboard)
                                    <tr>
                                        <td>{{ $highboard->id }}</td>
                                        <td>{{ $highboard->name }}</td>
                                        <td>{{ $highboard->email }}</td>
                                        <td>{{ $highboard->phone ?? 'N/A' }}</td>
                                        <td>
                                            @if($highboard->field)
                                                <span class="badge bg-info">{{ $highboard->field->name }}</span>
                                            @else
                                                <span class="badge bg-secondary">No Field</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($highboard->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ $highboard->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.highboards.edit', $highboard) }}" 
                                                   class="btn btn-sm btn-info" title="Edit">
                                                    <i class="fe fe-edit"></i>
                                                </a>
                                                
                                                <form action="{{ route('admin.highboards.toggle-status', $highboard) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning" 
                                                            title="Toggle Status">
                                                        <i class="fe fe-{{ $highboard->is_active ? 'eye-off' : 'eye' }}"></i>
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('admin.login-as-highboard', $highboard->id) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('You will be logged in as {{ $highboard->name }}. Continue?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" 
                                                            title="Login As This User">
                                                        <i class="fe fe-log-in"></i>
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('admin.highboards.destroy', $highboard) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to deactivate this highboard member?');">
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
                                        <td colspan="8" class="text-center">No highboard members found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $highboards->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
