@extends('layouts.admin-dashboard')

@section('title', 'Newsletter Subscribers')

@section('content')
    <!-- PAGE-HEADER -->
    <div class="page-header">
        <h1 class="page-title">Newsletter Subscribers</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Newsletter Subscribers</li>
            </ol>
        </div>
    </div>
    <!-- PAGE-HEADER END -->

    <!-- ROW -->
    <div class="row">
        <div class="col-lg-12">
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-success-transparent">
                        <div class="card-body text-center">
                            <h3 class="mb-1 text-success">{{ $totalActive }}</h3>
                            <p class="mb-0 text-muted">Active Subscribers</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning-transparent">
                        <div class="card-body text-center">
                            <h3 class="mb-1 text-warning">{{ $totalInactive }}</h3>
                            <p class="mb-0 text-muted">Inactive Subscribers</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-primary-transparent">
                        <div class="card-body text-center">
                            <h3 class="mb-1 text-primary">{{ $totalActive + $totalInactive }}</h3>
                            <p class="mb-0 text-muted">Total Subscribers</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">All Subscribers</h3>
                    <a href="{{ route('admin.newsletter-subscribers.export') }}" class="btn btn-success">
                        <i class="fe fe-download me-1"></i> Export CSV
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="45%">Email</th>
                                    <th width="15%">Status</th>
                                    <th width="20%">Subscribed At</th>
                                    <th width="15%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subscribers as $subscriber)
                                    <tr>
                                        <td>{{ $subscriber->id }}</td>
                                        <td>
                                            <a href="mailto:{{ $subscriber->email }}">{{ $subscriber->email }}</a>
                                        </td>
                                        <td class="text-center">
                                            @if($subscriber->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-warning">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ $subscriber->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <form action="{{ route('admin.newsletter-subscribers.toggle-status', $subscriber->id) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @if($subscriber->is_active)
                                                        <button type="submit" class="btn btn-sm btn-warning" title="Deactivate">
                                                            <i class="fe fe-pause"></i>
                                                        </button>
                                                    @else
                                                        <button type="submit" class="btn btn-sm btn-success" title="Activate">
                                                            <i class="fe fe-play"></i>
                                                        </button>
                                                    @endif
                                                </form>
                                                <form action="{{ route('admin.newsletter-subscribers.destroy', $subscriber->id) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to remove this subscriber?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fe fe-users mb-2" style="font-size: 48px;"></i>
                                                <p class="mb-0">No subscribers yet.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $subscribers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ROW END -->
@endsection
