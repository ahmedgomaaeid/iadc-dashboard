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
    <div class="row mt-2 mb-2">
        <div class="col-12">
            <div class="alert alert-info">
                <h4 class="alert-heading">Welcome, {{ $highboard->name }}!</h4>
                <p class="mb-0">You are managing the <strong>{{ $highboard->field->name }}</strong> field.</p>
            </div>
        </div>
    </div>

    {{-- Active Meeting Notification --}}
    @if($activeMeeting)
    <div class="row mt-2 mb-2">
        <div class="col-12">
            <div class="alert alert-primary alert-dismissible fade show active-meeting-banner" role="alert">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="live-indicator">
                            <i class="fe fe-video fs-2 text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading mb-1 text-white">
                            <i class="fe fe-radio me-2"></i>
                            Meeting in Progress
                        </h5>
                        <div class="mb-2">
                            <strong class="fs-5">{{ $activeMeeting->title }}</strong>
                        </div>
                        <p class="mb-0 opacity-90">
                            <i class="fe fe-clock me-1"></i>
                            <span>{{ $activeMeeting->start_time->format('g:i A') }} - {{ $activeMeeting->end_time->format('g:i A') }}</span>
                            <span class="mx-2">|</span>
                            <i class="fe fe-briefcase me-1"></i>
                            <span>{{ $activeMeeting->committee->name }}</span>
                        </p>
                    </div>
                    <div class="ms-auto d-flex gap-2 align-items-center">
                        <a href="{{ route('highboard.sessions.join', $activeMeeting->id) }}" class="btn btn-light btn-lg pulse-button">
                            <i class="fe fe-log-in me-2"></i>
                            Join Meeting
                        </a>
                        <button type="button" class="btn btn-outline-light btn-lg" id="copyMemberLinkBtn" data-link="{{ url('sessions/' . $activeMeeting->id . '/join') }}">
                            <i class="fe fe-copy me-2"></i>
                            Copy Member Link
                        </button>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    @endif

    {{-- Statistics Cards --}}
    <div class="row mt-2 mb-2">
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

@section('css')
<style>
    /* Active Meeting Banner Styling */
    .active-meeting-banner {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        animation: slideInDown 0.5s ease-out;
        position: relative;
        overflow: hidden;
    }
    
    .active-meeting-banner::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        animation: shimmer 3s infinite;
    }
    
    .active-meeting-banner h5,
    .active-meeting-banner p,
    .active-meeting-banner strong {
        color: white !important;
    }
    
    /* Live Indicator */
    .live-indicator {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: pulse 2s ease-in-out infinite;
        position: relative;
    }
    
    .live-indicator::before {
        content: '';
        position: absolute;
        top: -5px;
        right: -5px;
        width: 15px;
        height: 15px;
        background: #ff4444;
        border-radius: 50%;
        border: 3px solid white;
        animation: blink 1.5s ease-in-out infinite;
    }
    
    /* Pulse Button Animation */
    .pulse-button {
        animation: pulseButton 2s ease-in-out infinite;
        font-weight: 600;
        padding: 12px 30px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
    }
    
    .pulse-button:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    }
    
    /* Animations */
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
        }
        50% {
            transform: scale(1.05);
            box-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
        }
    }
    
    @keyframes blink {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }
    
    @keyframes pulseButton {
        0%, 100% {
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        50% {
            box-shadow: 0 4px 20px rgba(255,255,255,0.4);
        }
    }
    
    @keyframes shimmer {
        0% {
            left: -100%;
        }
        100% {
            left: 100%;
        }
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .active-meeting-banner .d-flex {
            flex-direction: column;
            text-align: center;
        }
        
        .active-meeting-banner .ms-auto {
            margin-top: 1rem;
            margin-left: 0 !important;
        }
        
        .live-indicator {
            margin: 0 auto 1rem;
        }
    }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const copyBtn = document.getElementById('copyMemberLinkBtn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            const link = this.getAttribute('data-link');
            navigator.clipboard.writeText(link).then(() => {
                // Show success feedback
                const originalHtml = this.innerHTML;
                this.innerHTML = '<i class="fe fe-check me-2"></i>Copied!';
                this.classList.remove('btn-outline-light');
                this.classList.add('btn-success');
                setTimeout(() => {
                    this.innerHTML = originalHtml;
                    this.classList.remove('btn-success');
                    this.classList.add('btn-outline-light');
                }, 2000);
            }).catch(err => {
                alert('Failed to copy link');
            });
        });
    }
});
</script>
@endsection
