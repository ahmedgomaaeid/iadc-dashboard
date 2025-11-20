@extends('layouts.highboard-dashboard')

@section('title', 'Highboard Dashboard')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Highboard Dashboard</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('highboard.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </div>
    </div>

    {{-- Welcome Message --}}
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <h4 class="alert-heading">Welcome, {{ $highboard->name }}!</h4>
                <p class="mb-0">You are managing the <strong>{{ $highboard->field->name }}</strong> field.</p>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row">
        <div class="col-sm-6 col-md-6 col-lg-6 col-xl-4">
            <div class="card bg-primary img-card box-primary-shadow">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="text-white">
                            <h2 class="mb-0 number-font">{{ $totalUsers }}</h2>
                            <p class="text-white mb-0">Total Members</p>
                        </div>
                        <div class="ms-auto"> <i class="fe fe-users text-white fs-30 me-2 mt-2"></i> </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-md-6 col-lg-6 col-xl-4">
            <div class="card bg-secondary img-card box-secondary-shadow">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="text-white">
                            <h2 class="mb-0 number-font">{{ $totalBoards }}</h2>
                            <p class="text-white mb-0">Total Board Members</p>
                        </div>
                        <div class="ms-auto"> <i class="fe fe-user-check text-white fs-30 me-2 mt-2"></i> </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-md-6 col-lg-6 col-xl-4">
            <div class="card bg-success img-card box-success-shadow">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="text-white">
                            <h2 class="mb-0 number-font">{{ $totalCommittees }}</h2>
                            <p class="text-white mb-0">Total Committees</p>
                        </div>
                        <div class="ms-auto"> <i class="fe fe-briefcase text-white fs-30 me-2 mt-2"></i> </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Committees Overview --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Committees Overview</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-nowrap border-bottom">
                            <thead>
                                <tr>
                                    <th>Committee Name</th>
                                    <th>Status</th>
                                    <th>Total Members</th>
                                    <th>Board Members</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($committees as $committee)
                                    <tr>
                                        <td>{{ $committee->name }}</td>
                                        <td>
                                            @if($committee->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $committee->users_count }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $committee->boards_count }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No committees found in your field.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
