@extends('layouts.supervisor-dashboard')

@section('css')
<style>
    /* ── Compose Card ── */
    .compose-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 2px 20px rgba(0,0,0,0.06);
        overflow: hidden;
    }
    .compose-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.06);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .compose-header h5 {
        margin: 0;
        font-weight: 700;
        font-size: 1.1rem;
        color: #2d3748;
    }
    .compose-body {
        padding: 1.5rem;
    }

    /* ── Form Fields ── */
    .compose-field {
        display: flex;
        align-items: center;
        border-bottom: 1px solid rgba(0,0,0,0.06);
        padding: 10px 0;
    }
    .compose-field:last-of-type {
        border-bottom: none;
    }
    .compose-field label {
        width: 80px;
        font-weight: 700;
        font-size: 0.85rem;
        color: #667eea;
        flex-shrink: 0;
        margin: 0;
    }
    .compose-field input {
        flex: 1;
        border: none;
        outline: none;
        font-size: 0.92rem;
        font-weight: 500;
        color: #2d3748;
        background: transparent;
        padding: 6px 0;
    }
    .compose-field input::placeholder {
        color: #cbd5e0;
    }

    /* ── Attachments Area ── */
    .compose-attachments {
        padding: 1rem 1.5rem;
        border-top: 1px solid rgba(0,0,0,0.06);
        background: #fafbfc;
    }
    .compose-attachments label {
        font-weight: 700;
        font-size: 0.82rem;
        color: #718096;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        margin: 0;
    }
    .compose-attachments input[type="file"] {
        display: none;
    }
    .attach-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 18px;
        border-radius: 10px;
        background: #fff;
        border: 1px dashed rgba(0,0,0,0.15);
        color: #667eea;
        font-weight: 600;
        font-size: 0.82rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .attach-btn:hover {
        border-color: #667eea;
        background: rgba(102, 126, 234, 0.05);
    }
    .selected-files {
        margin-top: 10px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .selected-file-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 8px;
        background: #e8ecff;
        color: #667eea;
        font-size: 0.78rem;
        font-weight: 600;
    }

    /* ── Footer ── */
    .compose-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid rgba(0,0,0,0.06);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .btn-send {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: #fff;
        font-weight: 700;
        font-size: 0.9rem;
        padding: 12px 32px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.35);
    }
    .btn-send:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.45);
        color: #fff;
    }
    .btn-discard {
        background: transparent;
        border: 1px solid rgba(0,0,0,0.1);
        color: #718096;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 10px 20px;
        border-radius: 10px;
        transition: all 0.2s ease;
    }
    .btn-discard:hover {
        background: #f7fafc;
        color: #4a5568;
    }

    /* ── Reply Quote ── */
    .reply-quote {
        margin-top: 1rem;
        padding: 1rem 1.25rem;
        background: #f8fafc;
        border-left: 4px solid #667eea;
        border-radius: 0 10px 10px 0;
        font-size: 0.85rem;
        color: #718096;
        max-height: 200px;
        overflow-y: auto;
    }
    .reply-quote-header {
        font-weight: 700;
        font-size: 0.78rem;
        color: #a0aec0;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ── Summernote Overrides ── */
    .note-editor.note-frame {
        border: 1px solid rgba(0,0,0,0.08) !important;
        border-radius: 12px !important;
        overflow: hidden;
    }
    .note-editor .note-toolbar {
        background: #fafbfc !important;
        border-bottom: 1px solid rgba(0,0,0,0.06) !important;
        padding: 6px 10px !important;
    }
    .note-editor .note-editing-area .note-editable {
        padding: 1rem 1.25rem !important;
        font-size: 0.92rem !important;
        line-height: 1.7 !important;
        min-height: 250px !important;
    }

    /* ── Dark Mode ── */
    .dark-mode .compose-card { background: #1e2130; }
    .dark-mode .compose-header { border-color: rgba(255,255,255,0.06); }
    .dark-mode .compose-header h5 { color: #e2e8f0; }
    .dark-mode .compose-field { border-color: rgba(255,255,255,0.06); }
    .dark-mode .compose-field input { color: #e2e8f0; }
    .dark-mode .compose-field input::placeholder { color: #4a5568; }
    .dark-mode .compose-attachments { background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.06); }
    .dark-mode .compose-footer { border-color: rgba(255,255,255,0.06); }
    .dark-mode .attach-btn { background: #262a3e; border-color: rgba(255,255,255,0.1); }
    .dark-mode .reply-quote { background: rgba(255,255,255,0.03); }
    .dark-mode .note-editor.note-frame { border-color: rgba(255,255,255,0.08) !important; }
    .dark-mode .note-editor .note-toolbar { background: rgba(255,255,255,0.03) !important; border-color: rgba(255,255,255,0.06) !important; }
    .dark-mode .note-editor .note-editing-area .note-editable { background: #1e2130 !important; color: #e2e8f0 !important; }

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
        <h1 class="page-title">{{ $replyTo ? 'Reply' : 'Compose Email' }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('supervisor.email.inbox') }}">Email</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $replyTo ? 'Reply' : 'Compose' }}</li>
            </ol>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px;">
            <i class="fe fe-alert-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('supervisor.email.send') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card compose-card fade-in-up">
            <!-- Header -->
            <div class="compose-header">
                <h5>
                    <i class="fe {{ $replyTo ? 'fe-corner-up-left' : 'fe-edit-3' }} me-2" style="color: #667eea;"></i>
                    {{ $replyTo ? 'Reply to Email' : 'New Email' }}
                </h5>
                <a href="{{ route('supervisor.email.inbox') }}" class="btn-discard">
                    <i class="fe fe-x me-1"></i> Cancel
                </a>
            </div>

            <!-- Compose Fields -->
            <div class="compose-body">
                <div class="compose-field">
                    <label for="emailTo">To</label>
                    <input type="email" name="to" id="emailTo"
                           placeholder="recipient@example.com"
                           value="{{ old('to', $replyTo ? $replyTo['to'] : '') }}"
                           required>
                </div>
                @error('to')
                    <div class="text-danger px-2 pb-2" style="font-size: 0.8rem; margin-left: 80px;">{{ $message }}</div>
                @enderror

                <div class="compose-field">
                    <label for="emailSubject">Subject</label>
                    <input type="text" name="subject" id="emailSubject"
                           placeholder="Email subject..."
                           value="{{ old('subject', $replyTo ? $replyTo['subject'] : '') }}"
                           required>
                </div>
                @error('subject')
                    <div class="text-danger px-2 pb-2" style="font-size: 0.8rem; margin-left: 80px;">{{ $message }}</div>
                @enderror

                <!-- Rich Text Editor -->
                <div class="mt-3">
                    <textarea name="body" id="emailBody">{{ old('body', '') }}</textarea>
                </div>
                @error('body')
                    <div class="text-danger px-2 pt-2" style="font-size: 0.8rem;">{{ $message }}</div>
                @enderror

                <!-- Reply Quote -->
                @if($replyTo)
                    <div class="reply-quote">
                        <div class="reply-quote-header">
                            <i class="fe fe-corner-down-right"></i>
                            On {{ $replyTo['date'] }}, {{ $replyTo['from'] }} wrote:
                        </div>
                        <div>{!! Str::limit(strip_tags($replyTo['body']), 1000) !!}</div>
                    </div>
                @endif
            </div>

            <!-- Attachments -->
            <div class="compose-attachments">
                <label class="attach-btn" for="emailAttachments">
                    <i class="fe fe-paperclip"></i> Attach Files
                </label>
                <input type="file" name="attachments[]" id="emailAttachments" multiple>
                <div class="selected-files" id="selectedFiles"></div>
            </div>

            <!-- Footer -->
            <div class="compose-footer">
                <button type="submit" class="btn-send" id="sendBtn">
                    <i class="fe fe-send"></i> Send Email
                </button>
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size: 0.78rem; color: #a0aec0;">
                        <i class="fe fe-lock me-1"></i> Sending as {{ Auth::guard('supervisor')->user()->server_mail ?? 'Not configured' }}
                    </span>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize Summernote
        $('#emailBody').summernote({
            height: 300,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'italic', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'hr']],
                ['view', ['fullscreen', 'codeview']],
            ],
            placeholder: 'Write your message here...',
            callbacks: {
                onInit: function() {
                    // Style adjustments
                }
            }
        });

        // File input display
        document.getElementById('emailAttachments').addEventListener('change', function(e) {
            var container = document.getElementById('selectedFiles');
            container.innerHTML = '';
            for (var i = 0; i < this.files.length; i++) {
                var fileSize = (this.files[i].size / 1024).toFixed(1);
                var div = document.createElement('div');
                div.className = 'selected-file-item';
                div.innerHTML = '<i class="fe fe-file"></i> ' + this.files[i].name + ' <span style="opacity:0.6;">(' + fileSize + ' KB)</span>';
                container.appendChild(div);
            }
        });

        // Prevent double submit
        document.querySelector('form').addEventListener('submit', function() {
            var btn = document.getElementById('sendBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fe fe-loader fe-spin"></i> Sending...';
        });
    });
</script>
@endsection
