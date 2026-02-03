@extends('layouts.board-dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-primary mb-4"><i class="fas fa-star-half-alt me-2"></i>Evaluations Dashboard</h2>
        </div>
    </div>

    <div class="row g-4">
        <!-- Participation Evaluation Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card bg-dark text-white h-100 border-0 shadow-sm">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="fas fa-users fa-3x text-success"></i>
                    </div>
                    <h4 class="card-title fw-bold">Participation</h4>
                    <p class="card-text text-muted mb-4">Evaluate your committee members on their overall participation and engagement.</p>
                    <a href="{{ route('board.evaluations.participation') }}" class="btn btn-success w-100 py-2">
                        <i class="fas fa-plus-circle me-2"></i>Create Evaluation
                    </a>
                    <div class="mt-3 text-muted small">
                        {{ $participationCount }} evaluations recorded
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Sessions Card -->
        <div class="col-md-6 col-lg-8">
            <div class="card bg-dark text-white h-100 border-0 shadow-sm">
                <div class="card-header border-0 bg-transparent">
                    <h4 class="card-title text-info mb-0">Recent Sessions Interaction</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Session Title</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSessions as $session)
                                    <tr>
                                        <td>{{ $session->title }}</td>
                                        <td>{{ $session->start_time->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('board.evaluations.interaction', $session->id) }}" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-user-edit me-1"></i>Evaluate Interaction
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">No recent sessions found.</td>
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
