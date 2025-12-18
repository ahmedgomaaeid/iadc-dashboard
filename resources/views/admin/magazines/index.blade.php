@extends('layouts.admin-dashboard')

@section('title', 'Magazines Management')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Magazines Management</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Magazines</li>
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
                    <h3 class="card-title">All Magazines</h3>
                    <a href="{{ route('admin.magazines.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>Add New Magazine
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-nowrap border-bottom">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cover</th>
                                    <th>Name</th>
                                    <th>PDF</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($magazines as $magazine)
                                    <tr>
                                        <td>{{ $magazine->id }}</td>
                                        <td>
                                            @if($magazine->image)
                                                <img src="{{ asset('storage/' . $magazine->image) }}" 
                                                     alt="{{ $magazine->name }}" 
                                                     class="rounded"
                                                     style="width: 50px; height: 70px; object-fit: cover;">
                                            @else
                                                <span class="text-muted">No cover</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $magazine->name }}</strong>
                                        </td>
                                        <td>
                                            <a href="{{ asset('storage/' . $magazine->pdf_file) }}" 
                                               target="_blank" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fe fe-file-text me-1"></i>View PDF
                                            </a>
                                        </td>
                                        <td>
                                            @if($magazine->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ $magazine->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.magazines.edit', $magazine) }}" 
                                                   class="btn btn-sm btn-info" title="Edit">
                                                    <i class="fe fe-edit"></i>
                                                </a>
                                                
                                                <form action="{{ route('admin.magazines.toggle-status', $magazine) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning" 
                                                            title="Toggle Status">
                                                        <i class="fe fe-{{ $magazine->is_active ? 'eye-off' : 'eye' }}"></i>
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('admin.magazines.destroy', $magazine) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this magazine?');">
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
                                        <td colspan="7" class="text-center">No magazines found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $magazines->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
