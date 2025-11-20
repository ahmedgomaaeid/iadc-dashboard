@extends('layouts.admin-dashboard')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Board Management</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Board</li>
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
                    <h3 class="card-title">All Board Members</h3>
                    <a href="{{ route('admin.boards.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>Add New Board Member
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
                                    <th>Committee</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($boards as $board)
                                    <tr>
                                        <td>{{ $board->id }}</td>
                                        <td>{{ $board->name }}</td>
                                        <td>{{ $board->email }}</td>
                                        <td>{{ $board->phone ?? 'N/A' }}</td>
                                        <td>
                                            @if($board->field)
                                                <span class="badge bg-primary">{{ $board->field->name }}</span>
                                            @else
                                                <span class="badge bg-secondary">No Field</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($board->committee)
                                                <span class="badge bg-info">{{ $board->committee->name }}</span>
                                            @else
                                                <span class="badge bg-secondary">No Committee</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($board->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ $board->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.boards.edit', $board) }}" 
                                                   class="btn btn-sm btn-info" title="Edit">
                                                    <i class="fe fe-edit"></i>
                                                </a>
                                                
                                                <form action="{{ route('admin.boards.toggle-status', $board) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning" 
                                                            title="Toggle Status">
                                                        <i class="fe fe-{{ $board->is_active ? 'eye-off' : 'eye' }}"></i>
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('admin.login-as-board', $board->id) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('You will be logged in as {{ $board->name }}. Continue?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" 
                                                            title="Login As This User">
                                                        <i class="fe fe-log-in"></i>
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('admin.boards.destroy', $board) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to deactivate this board member?');">
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
                                        <td colspan="9" class="text-center">No board members found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $boards->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
