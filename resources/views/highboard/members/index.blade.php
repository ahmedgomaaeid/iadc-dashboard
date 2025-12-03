@extends('layouts.highboard-dashboard')

@section('title', 'Members Management')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Members Management</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('highboard.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Members</li>
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
                    <h3 class="card-title">All Members in Your Field</h3>
                    <a href="{{ route('highboard.members.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>Add New Member
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
                                    <th>University</th>
                                    <th>Faculty</th>
                                    <th>Academic Year</th>
                                    <th>Committees</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($members as $member)
                                    <tr>
                                        <td>{{ $member->id }}</td>
                                        <td>{{ $member->name }}</td>
                                        <td>{{ $member->email }}</td>
                                        <td>{{ $member->phone ?? 'N/A' }}</td>
                                        <td>{{ $member->university ?? 'N/A' }}</td>
                                        <td>{{ $member->faculty ?? 'N/A' }}</td>
                                        <td>{{ $member->academic_year ?? 'N/A' }}</td>
                                        <td>
                                            @forelse($member->committees as $committee)
                                                <span class="badge bg-info">{{ $committee->name }}</span>
                                            @empty
                                                <span class="badge bg-secondary">No Committee</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            @if($member->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ $member->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('highboard.members.edit', $member) }}" 
                                                   class="btn btn-sm btn-info" title="Edit">
                                                    <i class="fe fe-edit"></i>
                                                </a>
                                                
                                                <form action="{{ route('highboard.members.toggle-status', $member) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning" 
                                                            title="Toggle Status">
                                                        <i class="fe fe-{{ $member->is_active ? 'eye-off' : 'eye' }}"></i>
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('highboard.login-as-member', $member->id) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('You will be logged in as {{ $member->name }}. Continue?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" 
                                                            title="Login As This User">
                                                        <i class="fe fe-log-in"></i>
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('highboard.members.destroy', $member) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to deactivate this member?');">
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
                                        <td colspan="11" class="text-center">No members found in your field.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $members->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
