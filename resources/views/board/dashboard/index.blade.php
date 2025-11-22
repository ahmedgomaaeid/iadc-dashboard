@extends('layouts.board-dashboard')

@section('title', 'Board Dashboard')

@section('content')
    <!-- PAGE-HEADER -->
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </div>
    </div>
    <!-- PAGE-HEADER END -->

    <!-- Welcome Message -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-1">Welcome, {{ $board->name }}!</h4>
                    <p class="text-muted">
                        @if($board->committee)
                            You are managing members of <strong>{{ $board->committee->name }}</strong> committee.
                        @else
                            You are managing your committee members.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row">
        <div class="col-lg-4 col-md-12 col-sm-12 col-xl-4">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="mt-2">
                            <h6 class="">Total Members</h6>
                            <h2 class="mb-0 number-font">{{ $totalMembers }}</h2>
                        </div>
                        <div class="ms-auto">
                            <div class="chart-wrapper mt-1">
                                <span class="avatar avatar-lg bg-primary-transparent text-primary">
                                    <i class="fe fe-users fs-20"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 col-sm-12 col-xl-4">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="mt-2">
                            <h6 class="">Active Members</h6>
                            <h2 class="mb-0 number-font">{{ $activeMembers }}</h2>
                        </div>
                        <div class="ms-auto">
                            <div class="chart-wrapper mt-1">
                                <span class="avatar avatar-lg bg-success-transparent text-success">
                                    <i class="fe fe-user-check fs-20"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 col-sm-12 col-xl-4">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="mt-2">
                            <h6 class="">Inactive Members</h6>
                            <h2 class="mb-0 number-font">{{ $inactiveMembers }}</h2>
                        </div>
                        <div class="ms-auto">
                            <div class="chart-wrapper mt-1">
                                <span class="avatar avatar-lg bg-warning-transparent text-warning">
                                    <i class="fe fe-user-x fs-20"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
