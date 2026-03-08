@extends('layouts.supervisor-dashboard')

@section('css')
<style>
    /* ── Stat Cards ── */
    .stat-card {
        border: none;
        border-radius: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        position: relative;
    }
    .stat-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    }
    .stat-card .card-body {
        padding: 1.5rem;
        position: relative;
        z-index: 1;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -30%;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
        z-index: 0;
    }
    .stat-card::after {
        content: '';
        position: absolute;
        bottom: -40%;
        left: -20%;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.05);
        z-index: 0;
    }
    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        background: rgba(255, 255, 255, 0.18);
        backdrop-filter: blur(10px);
        color: #fff;
    }
    .stat-value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -0.5px;
        color: #fff;
    }
    .stat-label {
        font-size: 0.85rem;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.85);
        margin-top: 4px;
    }
    .stat-sub {
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.6);
        margin-top: 2px;
    }

    /* Gradient Backgrounds */
    .gradient-primary   { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .gradient-success   { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .gradient-warning   { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .gradient-info      { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .gradient-secondary { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
    .gradient-dark      { background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%); }

    /* ── Chart Cards ── */
    .chart-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
    }
    .chart-card:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    }
    .chart-card .card-header {
        background: transparent;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .chart-card .card-header h4 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: #2d3748;
    }
    .chart-card .card-header .badge {
        font-size: 0.7rem;
        padding: 5px 10px;
        border-radius: 20px;
    }
    .chart-card .card-body {
        padding: 1.25rem 1.5rem;
    }

    /* ── Quick Stats Row ── */
    .quick-stats {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }
    .quick-stat-item {
        flex: 1;
        min-width: 100px;
        text-align: center;
        padding: 12px 8px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #edf2f7;
        transition: all 0.2s ease;
    }
    .quick-stat-item:hover {
        background: #edf2f7;
    }
    .quick-stat-item .qs-value {
        font-size: 1.4rem;
        font-weight: 800;
        color: #2d3748;
    }
    .quick-stat-item .qs-label {
        font-size: 0.72rem;
        color: #718096;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    /* ── Activity Table ── */
    .activity-table {
        border: none;
        border-radius: 16px;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
    }
    .activity-table .card-header {
        background: transparent;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        padding: 1.25rem 1.5rem;
    }
    .activity-table .card-header h4 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: #2d3748;
    }
    .activity-table .table {
        margin: 0;
    }
    .activity-table .table th {
        border-top: none;
        font-size: 0.72rem;
        text-transform: uppercase;
        color: #a0aec0;
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 0.75rem 1.25rem;
    }
    .activity-table .table td {
        padding: 0.85rem 1.25rem;
        vertical-align: middle;
        font-size: 0.875rem;
        color: #4a5568;
        border-color: #f1f5f9;
    }
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.3px;
    }
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    .status-accepted {
        background: #d4edda;
        color: #155724;
    }
    .status-rejected {
        background: #f8d7da;
        color: #721c24;
    }
    .deadline-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .deadline-urgent {
        background: #fed7d7;
        color: #c53030;
    }
    .deadline-soon {
        background: #fefcbf;
        color: #975a16;
    }
    .deadline-normal {
        background: #c6f6d5;
        color: #276749;
    }

    /* ── Page Title ── */
    .dashboard-welcome {
        margin-bottom: 1.5rem;
    }
    .dashboard-welcome h2 {
        font-size: 1.6rem;
        font-weight: 800;
        color: #1a202c;
        margin-bottom: 4px;
    }
    .dashboard-welcome p {
        color: #718096;
        font-size: 0.95rem;
        margin: 0;
    }

    /* ── Dark mode support ── */
    .dark-mode .chart-card .card-header h4,
    .dark-mode .activity-table .card-header h4 {
        color: #e2e8f0;
    }
    .dark-mode .quick-stat-item {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.1);
    }
    .dark-mode .quick-stat-item .qs-value {
        color: #e2e8f0;
    }
    .dark-mode .quick-stat-item .qs-label {
        color: #a0aec0;
    }
    .dark-mode .activity-table .table td {
        color: #cbd5e0;
        border-color: rgba(255, 255, 255, 0.06);
    }
    .dark-mode .dashboard-welcome h2 {
        color: #e2e8f0;
    }
    .dark-mode .dashboard-welcome p {
        color: #a0aec0;
    }

    /* ── Animate on load ── */
    .fade-in-up {
        animation: fadeInUp 0.5s ease both;
    }
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.1s; }
    .delay-3 { animation-delay: 0.15s; }
    .delay-4 { animation-delay: 0.2s; }
    .delay-5 { animation-delay: 0.25s; }
    .delay-6 { animation-delay: 0.3s; }
</style>
@endsection

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </div>
    </div>

    <!-- Welcome Section -->
    <div class="dashboard-welcome fade-in-up">
        <h2>Welcome back, {{ Auth::guard('supervisor')->user()->name }} 👋</h2>
        <p>Here's an overview of the IADC platform performance</p>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- STAT CARDS ROW                              -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="row">
        <div class="col-sm-6 col-md-6 col-lg-4 col-xl-2">
            <div class="card stat-card gradient-primary fade-in-up delay-1">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="stat-icon"><i class="fe fe-users"></i></div>
                    </div>
                    <div class="stat-value">{{ $userCount + $highBoardCount + $boardCount }}</div>
                    <div class="stat-label">Total Members</div>
                    <div class="stat-sub">Active member</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xl-2">
            <div class="card stat-card gradient-success fade-in-up delay-2">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="stat-icon"><i class="fa fa-user-circle"></i></div>
                    </div>
                    <div class="stat-value">{{ $highBoardCount }}</div>
                    <div class="stat-label">High Boards</div>
                    <div class="stat-sub">Active members</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xl-2">
            <div class="card stat-card gradient-warning fade-in-up delay-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="stat-icon"><i class="fa fa-user-o"></i></div>
                    </div>
                    <div class="stat-value">{{ $boardCount }}</div>
                    <div class="stat-label">Boards</div>
                    <div class="stat-sub">Active boards</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xl-2">
            <div class="card stat-card gradient-info fade-in-up delay-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="stat-icon"><i class="ion-briefcase"></i></div>
                    </div>
                    <div class="stat-value">{{ $committeeCount }}</div>
                    <div class="stat-label">Committees</div>
                    <div class="stat-sub">Active committees</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xl-2">
            <div class="card stat-card gradient-secondary fade-in-up delay-5">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="stat-icon"><i class="fe fe-check-square"></i></div>
                    </div>
                    <div class="stat-value">{{ $totalTasks }}</div>
                    <div class="stat-label">Total Tasks</div>
                    <div class="stat-sub">{{ $activeTasks }} active &bull; {{ $overdueTasks }} overdue</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xl-2">
            <div class="card stat-card gradient-dark fade-in-up delay-6">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="stat-icon"><i class="fe fe-video"></i></div>
                    </div>
                    <div class="stat-value">{{ $totalSessions }}</div>
                    <div class="stat-label">Meetings</div>
                    <div class="stat-sub">{{ $upcomingSessions }} upcoming</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- QUICK STATS BAR                             -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="row mb-4 fade-in-up" style="animation-delay: 0.35s;">
        <div class="col-12">
            <div class="quick-stats">
                <div class="quick-stat-item">
                    <div class="qs-value">{{ $lessonCount }}</div>
                    <div class="qs-label">Lessons</div>
                </div>
                <div class="quick-stat-item">
                    <div class="qs-value">{{ $quizCount }}</div>
                    <div class="qs-label">Quizzes</div>
                </div>
                <div class="quick-stat-item">
                    <div class="qs-value">{{ $articleCount }}</div>
                    <div class="qs-label">Articles</div>
                </div>
                <div class="quick-stat-item">
                    <div class="qs-value">{{ $eventCount }}</div>
                    <div class="qs-label">Events</div>
                </div>
                <div class="quick-stat-item">
                    <div class="qs-value">{{ $formSubmissionCount }}</div>
                    <div class="qs-label">Form Subs</div>
                </div>
                <div class="quick-stat-item">
                    <div class="qs-value">{{ $subscriberCount }}</div>
                    <div class="qs-label">Subscribers</div>
                </div>
                <div class="quick-stat-item">
                    <div class="qs-value">{{ $unreadMessages }}<span style="font-size:0.7rem;color:#a0aec0;">/{{ $totalMessages }}</span></div>
                    <div class="qs-label">Messages</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- CHARTS ROW 1                                -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card chart-card fade-in-up" style="animation-delay: 0.4s;">
                <div class="card-header">
                    <h4><i class="fe fe-bar-chart-2 me-2" style="color:#667eea;"></i> Members per Committee</h4>
                    <span class="badge bg-primary-transparent text-primary">{{ $committeeCount }} Committees</span>
                </div>
                <div class="card-body">
                    <div id="chart-members-committee"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card chart-card fade-in-up" style="animation-delay: 0.45s;">
                <div class="card-header">
                    <h4><i class="fe fe-pie-chart me-2" style="color:#f5576c;"></i> Task Submissions</h4>
                    <span class="badge bg-warning-transparent text-warning">{{ $totalSubmissions }} Total</span>
                </div>
                <div class="card-body">
                    <div id="chart-submission-status"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- CHARTS ROW 2                                -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card chart-card fade-in-up" style="animation-delay: 0.5s;">
                <div class="card-header">
                    <h4><i class="fe fe-trending-up me-2" style="color:#11998e;"></i> User Growth</h4>
                    <span class="badge bg-success-transparent text-success">Last 6 Months</span>
                </div>
                <div class="card-body">
                    <div id="chart-user-growth"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card chart-card fade-in-up" style="animation-delay: 0.55s;">
                <div class="card-header">
                    <h4><i class="fe fe-layers me-2" style="color:#a18cd1;"></i> Content Overview</h4>
                    <span class="badge bg-info-transparent text-info">All Content</span>
                </div>
                <div class="card-body">
                    <div id="chart-content-overview"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- ACTIVITY TABLES                             -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="row">
        <!-- Upcoming Deadlines -->
        <div class="col-lg-6">
            <div class="card activity-table fade-in-up" style="animation-delay: 0.6s;">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h4><i class="fe fe-clock me-2" style="color:#e53e3e;"></i> Upcoming Deadlines</h4>
                    <span class="badge bg-danger-transparent text-danger">{{ $overdueTasks }} Overdue</span>
                </div>
                <div class="card-body p-0">
                    @if($upcomingTasks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Committee</th>
                                    <th>Deadline</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($upcomingTasks as $task)
                                @php
                                    $hoursLeft = now()->diffInHours($task->deadline, false);
                                    $deadlineClass = $hoursLeft <= 24 ? 'deadline-urgent' : ($hoursLeft <= 72 ? 'deadline-soon' : 'deadline-normal');
                                @endphp
                                <tr>
                                    <td class="fw-semibold">{{ Str::limit($task->title, 30) }}</td>
                                    <td>{{ $task->committee ? $task->committee->name : '—' }}</td>
                                    <td>
                                        <span class="deadline-badge {{ $deadlineClass }}">
                                            <i class="fe fe-clock" style="font-size:11px;"></i>
                                            {{ $task->deadline->format('M d, H:i') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($hoursLeft <= 24)
                                            <span class="status-badge status-rejected">Urgent</span>
                                        @elseif($hoursLeft <= 72)
                                            <span class="status-badge status-pending">Soon</span>
                                        @else
                                            <span class="status-badge status-accepted">On Track</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fe fe-check-circle" style="font-size:40px; color:#38ef7d;"></i>
                        <p class="mt-3 text-muted">No upcoming deadlines</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Latest Submissions -->
        <div class="col-lg-6">
            <div class="card activity-table fade-in-up" style="animation-delay: 0.65s;">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h4><i class="fe fe-file-text me-2" style="color:#4facfe;"></i> Latest Submissions</h4>
                    <span class="badge bg-primary-transparent text-primary">{{ $pendingSubmissions }} Pending</span>
                </div>
                <div class="card-body p-0">
                    @if($latestSubmissions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Task</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($latestSubmissions as $submission)
                                <tr>
                                    <td class="fw-semibold">{{ $submission->user ? $submission->user->name : '—' }}</td>
                                    <td>{{ $submission->task ? Str::limit($submission->task->title, 25) : '—' }}</td>
                                    <td>
                                        <span class="status-badge status-{{ $submission->status }}">
                                            {{ ucfirst($submission->status) }}
                                        </span>
                                    </td>
                                    <td style="color:#a0aec0; font-size:0.8rem;">{{ $submission->created_at->diffForHumans() }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fe fe-inbox" style="font-size:40px; color:#a0aec0;"></i>
                        <p class="mt-3 text-muted">No submissions yet</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ── Color Palette ──
        const colors = {
            primary: '#667eea',
            success: '#11998e',
            warning: '#f5576c',
            info: '#4facfe',
            purple: '#a18cd1',
            pink: '#f093fb',
            yellow: '#fee140',
            green: '#38ef7d',
        };

        // ── Shared chart config ──
        const isDark = document.body.classList.contains('dark-mode');
        const textColor = isDark ? '#a0aec0' : '#718096';
        const gridColor = isDark ? 'rgba(255,255,255,0.06)' : '#f1f5f9';

        // ═══════════════════════════════════════════
        // CHART 1: Members per Committee (Bar)
        // ═══════════════════════════════════════════
        new ApexCharts(document.querySelector("#chart-members-committee"), {
            chart: {
                type: 'bar',
                height: 320,
                toolbar: { show: false },
                fontFamily: 'inherit',
            },
            series: [{
                name: 'Members',
                data: @json($committeeMembersData)
            }],
            xaxis: {
                categories: @json($committeeMembersLabels),
                labels: {
                    style: { colors: textColor, fontSize: '11px' },
                    rotate: -45,
                    rotateAlways: @json(count($committeeMembersLabels) > 6),
                }
            },
            yaxis: {
                labels: { style: { colors: textColor, fontSize: '11px' } }
            },
            colors: [colors.primary],
            plotOptions: {
                bar: {
                    borderRadius: 8,
                    columnWidth: '55%',
                    distributed: true,
                }
            },
            dataLabels: { enabled: false },
            grid: {
                borderColor: gridColor,
                strokeDashArray: 4,
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: 'vertical',
                    shadeIntensity: 0.3,
                    opacityFrom: 1,
                    opacityTo: 0.8,
                }
            },
            tooltip: { theme: isDark ? 'dark' : 'light' },
            legend: { show: false },
        }).render();

        // ═══════════════════════════════════════════
        // CHART 2: Task Submission Status (Donut)
        // ═══════════════════════════════════════════
        new ApexCharts(document.querySelector("#chart-submission-status"), {
            chart: {
                type: 'donut',
                height: 320,
                fontFamily: 'inherit',
            },
            series: @json($submissionStatusData),
            labels: @json($submissionStatusLabels),
            colors: ['#f6c343', '#38ef7d', '#f5576c'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                fontSize: '14px',
                                fontWeight: 700,
                                color: textColor,
                            }
                        }
                    }
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) { return Math.round(val) + '%'; },
                style: { fontSize: '12px', fontWeight: 700 }
            },
            stroke: { width: 3, colors: [isDark ? '#1a202c' : '#fff'] },
            legend: {
                position: 'bottom',
                fontSize: '12px',
                labels: { colors: textColor },
                markers: { width: 10, height: 10, radius: 12 }
            },
            tooltip: { theme: isDark ? 'dark' : 'light' },
        }).render();

        // ═══════════════════════════════════════════
        // CHART 3: User Growth (Area)
        // ═══════════════════════════════════════════
        new ApexCharts(document.querySelector("#chart-user-growth"), {
            chart: {
                type: 'area',
                height: 320,
                toolbar: { show: false },
                fontFamily: 'inherit',
            },
            series: [{
                name: 'New Users',
                data: @json($monthlyUserData)
            }],
            xaxis: {
                categories: @json($monthlyLabels),
                labels: { style: { colors: textColor, fontSize: '11px' } }
            },
            yaxis: {
                labels: { style: { colors: textColor, fontSize: '11px' } }
            },
            colors: [colors.success],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.05,
                    stops: [0, 95, 100],
                }
            },
            stroke: { curve: 'smooth', width: 3 },
            dataLabels: { enabled: false },
            grid: {
                borderColor: gridColor,
                strokeDashArray: 4,
            },
            markers: {
                size: 5,
                colors: ['#fff'],
                strokeColors: colors.success,
                strokeWidth: 3,
                hover: { size: 7 }
            },
            tooltip: {
                theme: isDark ? 'dark' : 'light',
                y: { formatter: function (val) { return val + ' users'; } }
            },
        }).render();

        // ═══════════════════════════════════════════
        // CHART 4: Content Overview (Horizontal Bar)
        // ═══════════════════════════════════════════
        new ApexCharts(document.querySelector("#chart-content-overview"), {
            chart: {
                type: 'bar',
                height: 320,
                toolbar: { show: false },
                fontFamily: 'inherit',
            },
            series: [{
                name: 'Count',
                data: @json($contentData)
            }],
            xaxis: {
                categories: @json($contentLabels),
                labels: { style: { colors: textColor, fontSize: '11px' } }
            },
            yaxis: {
                labels: { style: { colors: textColor, fontSize: '11px' } }
            },
            colors: [colors.purple, colors.info, colors.pink, colors.success],
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 8,
                    barHeight: '50%',
                    distributed: true,
                }
            },
            dataLabels: {
                enabled: true,
                style: { fontSize: '13px', fontWeight: 700, colors: ['#fff'] },
                offsetX: 5,
            },
            grid: {
                borderColor: gridColor,
                strokeDashArray: 4,
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: 'horizontal',
                    shadeIntensity: 0.2,
                    opacityFrom: 1,
                    opacityTo: 0.85,
                }
            },
            tooltip: { theme: isDark ? 'dark' : 'light' },
            legend: { show: false },
        }).render();
    });
</script>
@endsection
