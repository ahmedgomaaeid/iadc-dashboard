@extends('layouts.admin-dashboard')

@section('title', 'Leaderboard')
@section('css')
<link rel="stylesheet" href="{{ asset('css/leaderboard.css') }}">
<style>
    .violation-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .violation-badge:hover {
        transform: scale(1.05);
    }
    .violation-clean {
        background-color: #d1fae5;
        color: #065f46;
    }
    .violation-minor {
        background-color: #fef3c7;
        color: #92400e;
    }
    .violation-suspicious {
        background-color: #fee2e2;
        color: #991b1b;
    }
    .violation-item {
        display: flex;
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid #e5e7eb;
    }
    .violation-item:last-child {
        border-bottom: none;
    }
    .violation-type-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 0.9rem;
    }
    .violation-type-fullscreen {
        background-color: #fee2e2;
        color: #ef4444;
    }
    .violation-type-tab {
        background-color: #fef3c7;
        color: #f59e0b;
    }
</style>
@endsection
@section('content')
    <div class="page-header">
        <h1 class="page-title">Leaderboard</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Leaderboard</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-trophy text-warning me-2"></i>
                        Live Leaderboard
                    </h1>
                    <p class="text-muted mb-0">{{ $quiz->name }}</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="badge bg-success pulse-badge" id="live-indicator">
                        <i class="fas fa-circle me-1"></i> LIVE
                    </div>
                    <a href="{{ route('admin.quizzes.leaderboard.export', $quiz) }}" class="btn btn-success">
                        <i class="fas fa-file-excel me-2"></i>Export to Excel
                    </a>
                    <form action="{{ route('admin.quizzes.leaderboard.clear', $quiz) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to clear all leaderboard data? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash-alt me-2"></i>Clear Board
                        </button>
                    </form>
                    <a href="{{ route('admin.quizzes.show', $quiz) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Quiz
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-2">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="p-3">
                                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                <h4 class="mb-0" id="total-participants">0</h4>
                                <small class="text-muted">Total Participants</small>
                            </div>
                        </div>
                        <div class="col-md-4 border-start border-end">
                            <div class="p-3">
                                <i class="fas fa-crown fa-2x text-warning mb-2"></i>
                                <h4 class="mb-0" id="top-score">0</h4>
                                <small class="text-muted">Highest Score</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3">
                                <i class="fas fa-clock fa-2x text-info mb-2"></i>
                                <h4 class="mb-0" id="last-update">Just now</h4>
                                <small class="text-muted">Last Updated</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list-ol me-2"></i>Rankings
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div id="leaderboard-container" class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th width="80" class="text-center">Rank</th>
                                    <th>Participant</th>
                                    <th width="120" class="text-center">Score</th>
                                    <th width="150" class="text-center">Violations</th>
                                    <th width="150" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="leaderboard-body">
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                                        <p>Loading leaderboard data...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<!-- Violation Details Modal -->
<div class="modal fade" id="violationModal" tabindex="-1" aria-labelledby="violationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="violationModalLabel">
                    <i class="fas fa-flag me-2"></i>Violation Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 class="mb-3" id="violation-participant-name"></h6>
                <div id="violation-details-list"></div>
            </div>
        </div>
    </div>
</div>


@section('scripts')
    <script>
        const QUIZ_ID = {{ $quiz->id }};
        const UPDATE_INTERVAL = 2000; // 2 seconds
        let previousLeaderboard = [];
        let updateTimer = null;
        let allLeaderboardData = []; // Store full data for modal access
    </script>
    <script src="{{ asset('js/leaderboard.js') }}"></script>
@endsection
