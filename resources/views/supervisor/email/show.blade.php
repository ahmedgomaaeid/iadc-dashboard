@extends('layouts.supervisor-dashboard')

@section('css')
<style>
    /* ── Email Show Layout ── */
    .email-show-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 2px 20px rgba(0,0,0,0.06);
        overflow: hidden;
    }

    /* ── Toolbar ── */
    .email-toolbar {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.06);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
    .email-toolbar .btn-toolbar-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 600;
        border: 1px solid rgba(0,0,0,0.08);
        background: #fff;
        color: #4a5568;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .email-toolbar .btn-toolbar-action:hover {
        background: #f7fafc;
        border-color: rgba(0,0,0,0.12);
        color: #2d3748;
    }
    .email-toolbar .btn-reply {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border: none;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    .email-toolbar .btn-reply:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(102, 126, 234, 0.4);
        color: #fff;
    }
    .email-toolbar .btn-delete {
        color: #e53e3e;
        border-color: rgba(229, 62, 62, 0.2);
    }
    .email-toolbar .btn-delete:hover {
        background: #fed7d7;
        border-color: #e53e3e;
        color: #c53030;
    }

    /* ── Email Header ── */
    .email-header {
        padding: 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.06);
    }
    .email-subject-line {
        font-size: 1.35rem;
        font-weight: 800;
        color: #1a202c;
        margin-bottom: 1.25rem;
        line-height: 1.4;
    }
    .email-meta {
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }
    .email-meta-avatar {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1rem;
        color: #fff;
        flex-shrink: 0;
        text-transform: uppercase;
    }
    .email-meta-info {
        flex: 1;
    }
    .email-meta-from {
        font-weight: 700;
        font-size: 0.95rem;
        color: #2d3748;
    }
    .email-meta-from span {
        font-weight: 400;
        color: #a0aec0;
        font-size: 0.85rem;
    }
    .email-meta-to {
        font-size: 0.82rem;
        color: #718096;
        margin-top: 2px;
    }
    .email-meta-date {
        font-size: 0.8rem;
        color: #a0aec0;
        font-weight: 600;
        white-space: nowrap;
    }

    /* ── Email Body ── */
    .email-body-container {
        padding: 1.5rem;
        min-height: 200px;
    }
    .email-body-frame {
        width: 100%;
        border: none;
        min-height: 300px;
    }
    .email-body-text {
        font-size: 0.92rem;
        line-height: 1.7;
        color: #2d3748;
    }

    /* ── Attachments ── */
    .email-attachments {
        padding: 1rem 1.5rem;
        border-top: 1px solid rgba(0,0,0,0.06);
        background: #fafbfc;
    }
    .email-attachments-title {
        font-size: 0.82rem;
        font-weight: 700;
        color: #718096;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.75rem;
    }
    .attachment-item {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        border-radius: 12px;
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        margin-right: 8px;
        margin-bottom: 8px;
        text-decoration: none;
        color: #4a5568;
        font-weight: 600;
        font-size: 0.82rem;
        transition: all 0.2s ease;
    }
    .attachment-item:hover {
        border-color: #667eea;
        color: #667eea;
        box-shadow: 0 2px 8px rgba(102,126,234,0.15);
    }
    .attachment-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: linear-gradient(135deg, #f0f4ff, #e8ecff);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #667eea;
        font-size: 1rem;
    }
    .attachment-size {
        font-size: 0.72rem;
        color: #a0aec0;
        font-weight: 500;
    }

    /* ── Dark Mode ── */
    .dark-mode .email-show-card { background: #1e2130; }
    .dark-mode .email-toolbar { border-color: rgba(255,255,255,0.06); }
    .dark-mode .email-toolbar .btn-toolbar-action { background: #262a3e; border-color: rgba(255,255,255,0.08); color: #cbd5e0; }
    .dark-mode .email-toolbar .btn-toolbar-action:hover { background: #2d3148; color: #e2e8f0; }
    .dark-mode .email-header { border-color: rgba(255,255,255,0.06); }
    .dark-mode .email-subject-line { color: #f7fafc; }
    .dark-mode .email-meta-from { color: #e2e8f0; }
    .dark-mode .email-meta-to { color: #a0aec0; }
    .dark-mode .email-body-text { color: #e2e8f0; }
    .dark-mode .email-attachments { background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.06); }
    .dark-mode .attachment-item { background: #262a3e; border-color: rgba(255,255,255,0.08); color: #cbd5e0; }
    .dark-mode .attachment-icon { background: rgba(102,126,234,0.15); }

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
                <li class="breadcrumb-item"><a href="{{ route('supervisor.email.inbox') }}">Email</a></li>
                <li class="breadcrumb-item active" aria-current="page">View</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px;">
            <i class="fe fe-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px;">
            <i class="fe fe-alert-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card email-show-card fade-in-up">
        <!-- Toolbar -->
        <div class="email-toolbar">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ $email['folder'] === 'sent' ? route('supervisor.email.sent') : route('supervisor.email.inbox') }}"
                   class="btn-toolbar-action">
                    <i class="fe fe-arrow-left"></i> Back
                </a>
                <a href="{{ route('supervisor.email.compose', ['reply_to' => $email['uid'], 'folder' => $email['folder']]) }}"
                   class="btn-toolbar-action btn-reply">
                    <i class="fe fe-corner-up-left"></i> Reply
                </a>
                <form action="{{ route('supervisor.email.toggleRead', ['folder' => $email['folder'], 'uid' => $email['uid']]) }}"
                      method="POST" style="display: inline;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn-toolbar-action">
                        <i class="fe fe-eye-off"></i> Mark Unread
                    </button>
                </form>
            </div>
            <div>
                <form action="{{ route('supervisor.email.destroy', ['folder' => $email['folder'], 'uid' => $email['uid']]) }}"
                      method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this email?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-toolbar-action btn-delete">
                        <i class="fe fe-trash-2"></i> Delete
                    </button>
                </form>
            </div>
        </div>

        <!-- Email Header -->
        <div class="email-header">
            <div class="email-subject-line">{{ $email['subject'] }}</div>
            <div class="email-meta">
                @php
                    $colors = ['#667eea','#764ba2','#11998e','#f5576c','#4facfe','#ff8a5c','#38ef7d','#e44d26','#2d3748','#9f7aea'];
                    $fromName = explode('<', $email['from'])[0];
                    $initial = strtoupper(substr(trim($fromName), 0, 1));
                    $colorIndex = ord($initial) % count($colors);
                @endphp
                <div class="email-meta-avatar" style="background: {{ $colors[$colorIndex] }};">
                    {{ $initial }}
                </div>
                <div class="email-meta-info">
                    <div class="email-meta-from">
                        {{ $email['from'] }}
                    </div>
                    <div class="email-meta-to">
                        to {{ $email['to'] }}
                        @if($email['cc'])
                            <br>cc: {{ $email['cc'] }}
                        @endif
                    </div>
                </div>
                <div class="email-meta-date">
                    <i class="fe fe-clock me-1"></i>{{ $email['date'] }}
                </div>
            </div>
        </div>

        <!-- Email Body -->
        <div class="email-body-container">
            @if(str_contains(strtolower($email['body']), '<html') || str_contains(strtolower($email['body']), '<div') || str_contains(strtolower($email['body']), '<table'))
                <iframe class="email-body-frame" id="emailBodyFrame" sandbox="allow-same-origin" srcdoc="{{ $email['body'] }}"></iframe>
            @else
                <div class="email-body-text">{!! $email['body'] !!}</div>
            @endif
        </div>

        <!-- Attachments -->
        @if(count($email['attachments']) > 0)
            <div class="email-attachments">
                <div class="email-attachments-title">
                    <i class="fe fe-paperclip"></i> {{ count($email['attachments']) }} Attachment{{ count($email['attachments']) > 1 ? 's' : '' }}
                </div>
                @foreach($email['attachments'] as $attachment)
                    <a href="{{ route('supervisor.email.attachment', ['folder' => $email['folder'], 'uid' => $email['uid'], 'partNumber' => $attachment['part_number']]) }}"
                       class="attachment-item" download>
                        <div class="attachment-icon">
                            <i class="fe fe-file"></i>
                        </div>
                        <div>
                            <div>{{ $attachment['filename'] }}</div>
                            <div class="attachment-size">{{ number_format($attachment['size'] / 1024, 1) }} KB</div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection

@section('scripts')
<script>
    // Auto-resize iframe to fit content
    document.addEventListener('DOMContentLoaded', function() {
        var iframe = document.getElementById('emailBodyFrame');
        if (iframe) {
            iframe.addEventListener('load', function() {
                try {
                    var doc = iframe.contentDocument || iframe.contentWindow.document;
                    // Add base styles to iframe
                    var style = doc.createElement('style');
                    style.textContent = 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; line-height: 1.6; color: #2d3748; margin: 0; padding: 0; }';
                    doc.head.appendChild(style);
                    iframe.style.height = doc.body.scrollHeight + 40 + 'px';
                } catch(e) {}
            });
        }
    });
</script>
@endsection
