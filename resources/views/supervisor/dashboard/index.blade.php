@extends('layouts.supervisor-dashboard')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-6 col-lg-6 col-xl-3">
            <div class="card bg-primary img-card box-primary-shadow">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="text-white">
                            <h2 class="mb-0 number-font">{{$userCount + $highBoardCount + $boardCount}}</h2>
                            <p class="text-white mb-0">Total Members </p>
                        </div>
                        <div class="ms-auto"> <i class="fe fe-users text-white fs-30 me-2 mt-2"></i> </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- COL END -->
        <div class="col-sm-6 col-md-6 col-lg-6 col-xl-3">
            <div class="card bg-secondary img-card box-secondary-shadow">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="text-white">
                            <h2 class="mb-0 number-font">{{$highBoardCount}}</h2>
                            <p class="text-white mb-0">Total High Boards</p>
                        </div>
                        <div class="ms-auto"> <i class="fa fa-user-circle text-white fs-30 me-2 mt-2"></i> </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- COL END -->
        <div class="col-sm-6 col-md-6 col-lg-6 col-xl-3">
            <div class="card  bg-success img-card box-success-shadow">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="text-white">
                            <h2 class="mb-0 number-font">{{$boardCount}}</h2>
                            <p class="text-white mb-0">Total Boards</p>
                        </div>
                        <div class="ms-auto"> <i class="fa fa-user-o text-white fs-30 me-2 mt-2"></i> </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- COL END -->
        <div class="col-sm-6 col-md-6 col-lg-6 col-xl-3">
            <div class="card bg-info img-card box-info-shadow">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="text-white">
                            <h2 class="mb-0 number-font">{{$committeeCount}}</h2>
                            <p class="text-white mb-0">Total Committees</p>
                        </div>
                        <div class="ms-auto"> <i class="ion-briefcase text-white fs-30 me-2 mt-2"></i> </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- COL END -->
    </div>
@endsection
