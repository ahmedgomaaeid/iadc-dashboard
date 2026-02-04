@extends('layouts.highboard-dashboard')

@section('content')
<div class="container-fluid">
    <!-- PAGE-HEADER -->
    <div class="page-header">
        <h1 class="page-title">Evaluations Dashboard</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('highboard.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Evaluations</li>
            </ol>
        </div>
    </div>
    <!-- PAGE-HEADER END -->

    <div class="row g-4">
        <!-- Participation Evaluation Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <span class="avatar avatar-xl bg-success-transparent">
                            <i class="fe fe-users fs-30 text-success"></i>
                        </span>
                    </div>
                    <h4 class="card-title fw-bold">Participation</h4>
                    <p class="card-text text-muted mb-4">Evaluate committee members on their overall participation and engagement.</p>
                    <a href="{{ route('highboard.evaluations.participation') }}" class="btn btn-success w-100 py-2">
                        <i class="fe fe-plus-circle me-2"></i>Create Evaluation
                    </a>
                    <div class="mt-3 text-muted small">
                        <i class="fe fe-check-circle me-1"></i>{{ $participationCount }} evaluations recorded
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Sessions Card -->
        <div class="col-md-6 col-lg-8">
            <div class="card h-100">
                <div class="card-header border-bottom">
                    <h3 class="card-title"><i class="fe fe-video me-2"></i>Recent Sessions Interaction</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Session Title</th>
                                    <th>Committee</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSessions as $session)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="avatar avatar-sm bg-primary-transparent me-2">
                                                    <i class="fe fe-calendar text-primary"></i>
                                                </span>
                                                {{ $session->title }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-transparent text-info">{{ $session->committee->name }}</span>
                                        </td>
                                        <td>{{ $session->start_time->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('highboard.evaluations.interaction', $session->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fe fe-edit-2 me-1"></i>Evaluate
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">
                                            <i class="fe fe-calendar fs-50 d-block mb-3"></i>
                                            No recent sessions found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
