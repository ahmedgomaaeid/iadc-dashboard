@extends('layouts.supervisor-dashboard')

@section('css')
<style>
    /* ── Email Layout ── */
    .email-wrapper {
        display: flex;
        gap: 0;
        min-height: calc(100vh - 200px);
    }
    .email-sidebar {
        width: 240px;
        flex-shrink: 0;
        background: #fff;
        border-radius: 16px 0 0 16px;
        border-right: 1px solid rgba(0,0,0,0.06);
        padding: 1.5rem;
    }
    .email-main {
        flex: 1;
        background: #fff;
        border-radius: 0 16px 16px 0;
        overflow: hidden;
    }

    /* ── Compose Button ── */
    .btn-compose {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: #fff;
        font-weight: 700;
        font-size: 0.9rem;
        padding: 12px 20px;
        border-radius: 12px;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.35);
    }
    .btn-compose:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.45);
        color: #fff;
    }

    /* ── Folder Nav ── */
    .folder-nav {
        list-style: none;
        padding: 0;
        margin: 1.5rem 0 0;
    }
    .folder-nav li a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-radius: 10px;
        color: #4a5568;
        font-weight: 600;
        font-size: 0.88rem;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .folder-nav li a:hover {
        background: rgba(102, 126, 234, 0.08);
        color: #667eea;
    }
    .folder-nav li a.active {
        background: rgba(102, 126, 234, 0.12);
        color: #667eea;
    }
    .folder-nav li a .badge {
        margin-left: auto;
        background: linear-gradient(135deg, #f5576c, #ff8a5c);
        color: #fff;
        font-size: 0.7rem;
        padding: 3px 8px;
        border-radius: 20px;
        font-weight: 700;
    }
    .folder-nav li a i {
        font-size: 16px;
        width: 20px;
        text-align: center;
    }

    /* ── Email List Header ── */
    .email-list-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.06);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    .email-list-header h5 {
        margin: 0;
        font-weight: 700;
        font-size: 1.1rem;
        color: #2d3748;
    }
    .email-count-badge {
        background: #f0f4ff;
        color: #667eea;
        font-weight: 700;
        font-size: 0.75rem;
        padding: 4px 12px;
        border-radius: 20px;
    }

    /* ── Email List ── */
    .email-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .email-item {
        display: flex;
        align-items: center;
        padding: 14px 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.04);
        cursor: pointer;
        transition: all 0.15s ease;
        gap: 12px;
        text-decoration: none;
        color: inherit;
    }
    .email-item:hover {
        background: rgba(102, 126, 234, 0.04);
        color: inherit;
    }
    .email-item.unread {
        background: rgba(102, 126, 234, 0.03);
    }
    .email-item.unread .email-sender,
    .email-item.unread .email-subject-text {
        font-weight: 800;
        color: #1a202c;
    }
    .email-unread-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #667eea;
        flex-shrink: 0;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
    }
    .email-unread-dot.read {
        background: transparent;
        box-shadow: none;
    }
    .email-avatar {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.85rem;
        color: #fff;
        flex-shrink: 0;
        text-transform: uppercase;
    }
    .email-content {
        flex: 1;
        min-width: 0;
    }
    .email-sender {
        font-weight: 600;
        font-size: 0.88rem;
        color: #2d3748;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .email-subject {
        display: flex;
        align-items: baseline;
        gap: 6px;
    }
    .email-subject-text {
        font-weight: 500;
        font-size: 0.84rem;
        color: #4a5568;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .email-date {
        font-size: 0.75rem;
        color: #a0aec0;
        font-weight: 600;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .email-actions-cell {
        display: flex;
        gap: 4px;
        flex-shrink: 0;
    }
    .email-actions-cell .btn {
        padding: 4px 8px;
        border-radius: 8px;
        font-size: 0.75rem;
    }

    /* ── Pagination ── */
    .email-pagination {
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-top: 1px solid rgba(0,0,0,0.06);
    }
    .email-pagination .page-info {
        font-size: 0.8rem;
        color: #718096;
        font-weight: 600;
    }
    .email-pagination .pagination-btns {
        display: flex;
        gap: 6px;
    }
    .email-pagination .pagination-btns .btn {
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    /* ── Empty State ── */
    .email-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 4rem 2rem;
        text-align: center;
    }
    .email-empty-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f0f4ff, #e8ecff);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: #667eea;
        margin-bottom: 1.5rem;
    }
    .email-empty h5 {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }
    .email-empty p {
        color: #718096;
        font-size: 0.9rem;
        max-width: 320px;
    }

    /* ── Alert Banner ── */
    .email-alert {
        margin: 1rem 1.5rem;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 0.88rem;
        font-weight: 600;
    }
    .email-alert-warning {
        background: #fff8e1;
        color: #e65100;
        border: 1px solid #ffe0b2;
    }
    .email-alert-danger {
        background: #fce4ec;
        color: #c62828;
        border: 1px solid #ffcdd2;
    }
    .email-alert-success {
        background: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #c8e6c9;
    }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .email-wrapper {
            flex-direction: column;
        }
        .email-sidebar {
            width: 100%;
            border-radius: 16px 16px 0 0;
            border-right: none;
            border-bottom: 1px solid rgba(0,0,0,0.06);
            padding: 1rem;
        }
        .folder-nav {
            display: flex;
            gap: 4px;
            margin-top: 1rem;
        }
        .folder-nav li a {
            padding: 8px 12px;
            font-size: 0.8rem;
        }
        .email-main {
            border-radius: 0 0 16px 16px;
        }
        .email-item {
            padding: 12px 1rem;
        }
    }

    /* ── Dark Mode ── */
    .dark-mode .email-sidebar { background: #1e2130; border-color: rgba(255,255,255,0.06); }
    .dark-mode .email-main { background: #1e2130; }
    .dark-mode .email-list-header { border-color: rgba(255,255,255,0.06); }
    .dark-mode .email-list-header h5 { color: #e2e8f0; }
    .dark-mode .email-item { border-color: rgba(255,255,255,0.04); }
    .dark-mode .email-item:hover { background: rgba(102, 126, 234, 0.08); }
    .dark-mode .email-item.unread { background: rgba(102, 126, 234, 0.06); }
    .dark-mode .email-item.unread .email-sender,
    .dark-mode .email-item.unread .email-subject-text { color: #f7fafc; }
    .dark-mode .email-sender { color: #e2e8f0; }
    .dark-mode .email-subject-text { color: #a0aec0; }
    .dark-mode .email-date { color: #718096; }
    .dark-mode .folder-nav li a { color: #a0aec0; }
    .dark-mode .folder-nav li a:hover { color: #667eea; background: rgba(102,126,234,0.1); }
    .dark-mode .folder-nav li a.active { color: #667eea; background: rgba(102,126,234,0.15); }
    .dark-mode .email-pagination { border-color: rgba(255,255,255,0.06); }
    .dark-mode .email-count-badge { background: rgba(102,126,234,0.15); }
    .dark-mode .email-empty h5 { color: #e2e8f0; }
    .dark-mode .email-empty p { color: #718096; }

    /* ── Fade In ── */
    .fade-in-up {
        animation: fadeInUp 0.5s ease both;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">Email</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Email</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="email-alert email-alert-success fade-in-up">
            <i class="fe fe-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="email-alert email-alert-danger fade-in-up">
            <i class="fe fe-alert-circle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="card fade-in-up" style="border: none; border-radius: 16px; box-shadow: 0 2px 20px rgba(0,0,0,0.06); overflow: hidden;">
        <div class="email-wrapper">
            <!-- Sidebar -->
            <div class="email-sidebar">
                <a href="{{ route('supervisor.email.compose') }}" class="btn btn-compose">
                    <i class="fe fe-edit-3"></i> Compose
                </a>
                <ul class="folder-nav">
                    <li>
                        <a href="{{ route('supervisor.email.inbox') }}" class="{{ $currentFolder === 'inbox' ? 'active' : '' }}">
                            <i class="fe fe-inbox"></i> Inbox
                            @if(isset($unreadCount) && $unreadCount > 0)
                                <span class="badge">{{ $unreadCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('supervisor.email.sent') }}" class="{{ $currentFolder === 'sent' ? 'active' : '' }}">
                            <i class="fe fe-send"></i> Sent
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="email-main">
                @if(isset($noCredentials) && $noCredentials)
                    <div class="email-alert email-alert-warning" style="margin-top: 1.5rem;">
                        <i class="fe fe-alert-triangle"></i>
                        No email credentials configured. Please ask the administrator to set your mail server credentials.
                    </div>
                @elseif(isset($connectionError) && $connectionError)
                    <div class="email-alert email-alert-danger" style="margin-top: 1.5rem;">
                        <i class="fe fe-wifi-off"></i>
                        Could not connect to the mail server. Please check your credentials or try again later.
                    </div>
                @else
                    <!-- Header -->
                    <div class="email-list-header">
                        <div class="d-flex align-items-center gap-3">
                            <h5>
                                <i class="fe {{ $currentFolder === 'inbox' ? 'fe-inbox' : 'fe-send' }} me-2" style="color: #667eea;"></i>
                                {{ $currentFolder === 'inbox' ? 'Inbox' : 'Sent Mail' }}
                            </h5>
                            <span class="email-count-badge">{{ $totalEmails }} emails</span>
                        </div>
                    </div>

                    @if(count($emails) > 0)
                        <!-- Email List -->
                        <ul class="email-list">
                            @foreach($emails as $email)
                                @php
                                    $colors = ['#667eea','#764ba2','#11998e','#f5576c','#4facfe','#ff8a5c','#38ef7d','#e44d26','#2d3748','#9f7aea'];
                                    $initial = strtoupper(substr(strip_tags($currentFolder === 'inbox' ? $email['from'] : $email['to']), 0, 1));
                                    $colorIndex = ord($initial) % count($colors);
                                @endphp
                                <a href="{{ route('supervisor.email.show', ['folder' => $currentFolder, 'uid' => $email['uid']]) }}"
                                   class="email-item {{ !$email['seen'] ? 'unread' : '' }}">
                                    <div class="email-unread-dot {{ $email['seen'] ? 'read' : '' }}"></div>
                                    <div class="email-avatar" style="background: {{ $colors[$colorIndex] }};">
                                        {{ $initial }}
                                    </div>
                                    <div class="email-content">
                                        <div class="email-sender">
                                            {{ $currentFolder === 'inbox' ? $email['from'] : $email['to'] }}
                                        </div>
                                        <div class="email-subject">
                                            <span class="email-subject-text">{{ $email['subject'] }}</span>
                                        </div>
                                    </div>
                                    <div class="email-date">{{ $email['date'] }}</div>
                                </a>
                            @endforeach
                        </ul>

                        <!-- Pagination -->
                        @if($totalPages > 1)
                            <div class="email-pagination">
                                <span class="page-info">Page {{ $page }} of {{ $totalPages }}</span>
                                <div class="pagination-btns">
                                    @if($page > 1)
                                        <a href="{{ route($currentFolder === 'inbox' ? 'supervisor.email.inbox' : 'supervisor.email.sent', ['page' => $page - 1]) }}"
                                           class="btn btn-light">
                                            <i class="fe fe-chevron-left"></i> Prev
                                        </a>
                                    @endif
                                    @if($page < $totalPages)
                                        <a href="{{ route($currentFolder === 'inbox' ? 'supervisor.email.inbox' : 'supervisor.email.sent', ['page' => $page + 1]) }}"
                                           class="btn btn-light">
                                            Next <i class="fe fe-chevron-right"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="email-empty">
                            <div class="email-empty-icon">
                                <i class="fe {{ $currentFolder === 'inbox' ? 'fe-inbox' : 'fe-send' }}"></i>
                            </div>
                            <h5>No emails found</h5>
                            <p>Your {{ $currentFolder === 'inbox' ? 'inbox' : 'sent folder' }} is empty.</p>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection
