@extends('layouts.board-dashboard')

@section('title', isset($lesson) ? 'Edit Lesson' : 'Create Lesson')

@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ isset($lesson) ? 'Edit Lesson' : 'Create Lesson' }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('board.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('board.lessons.index') }}">Lessons</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    {{ isset($lesson) ? 'Edit' : 'Create' }}
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ isset($lesson) ? 'Edit Lesson' : 'New Lesson' }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ isset($lesson) ? route('board.lessons.update', $lesson) : route('board.lessons.store') }}" 
                          method="POST" 
                          enctype="multipart/form-data">
                        @csrf
                        @if(isset($lesson))
                            @method('PUT')
                        @endif

                        <!-- Title -->
                        <div class="mb-4">
                            <label for="title" class="form-label">Lesson Title <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title', $lesson->title ?? '') }}" 
                                   required
                                   placeholder="Enter lesson title">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Content -->
                        <div class="mb-4">
                            <label for="content" class="form-label">Content</label>
                            <textarea name="content" id="content" class="form-control @error('content') is-invalid @enderror">{{ old('content', $lesson->content ?? '') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="fe fe-info me-1"></i>Links in your content will be automatically detected and saved as tags
                            </small>
                        </div>

                        <!-- YouTube Video URL -->
                        <div class="mb-4">
                            <label for="youtube_url" class="form-label">YouTube Video URL (Optional)</label>
                            
                            @if(isset($lesson) && $lesson->youtube_video_id)
                                <div class="alert alert-info d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <i class="fe fe-youtube me-2"></i>
                                        <strong>Current Video ID:</strong> {{ $lesson->youtube_video_id }}
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remove_video" id="remove_video" value="1">
                                        <label class="form-check-label" for="remove_video">
                                            Remove
                                        </label>
                                    </div>
                                </div>
                            @endif

                            <input type="url" 
                                   class="form-control @error('youtube_url') is-invalid @enderror" 
                                   id="youtube_url" 
                                   name="youtube_url"
                                   value="{{ old('youtube_url', isset($lesson) && $lesson->youtube_video_id ? 'https://www.youtube.com/watch?v=' . $lesson->youtube_video_id : '') }}"
                                   placeholder="https://www.youtube.com/watch?v=xxxxx or https://youtu.be/xxxxx">
                            @error('youtube_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="fe fe-info me-1"></i>Paste a YouTube video URL (supports youtube.com, youtu.be, and embed formats)
                            </small>
                        </div>

                        <!-- Attachments -->
                        <div class="mb-4">
                            <label class="form-label">Attachments (Optional)</label>
                            
                            @if(isset($lesson) && $lesson->attachments->count() > 0)
                                <div class="mb-3">
                                    <strong class="d-block mb-2">Current Attachments:</strong>
                                    @foreach($lesson->attachments as $attachment)
                                        <div class="d-flex justify-content-between align-items-center border p-2 mb-2 rounded">
                                            <div>
                                                <i class="{{ $attachment->icon }} me-2"></i>
                                                <span>{{ $attachment->file_name }}</span>
                                                <small class="text-muted ms-2">({{ $attachment->formatted_size }})</small>
                                            </div>
                                            <form action="{{ route('board.lessons.attachments.destroy', $attachment) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Delete this attachment?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fe fe-trash-2"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="dropzone-container">
                                <input type="file" 
                                       class="form-control @error('attachments.*') is-invalid @enderror" 
                                       id="attachments" 
                                       name="attachments[]"
                                       multiple
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.rar">
                                @error('attachments.*')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Supported formats: PDF, DOC, XLS, PPT, Images, Archives (Max: 10MB each)</small>
                            </div>
                        </div>

                        <!-- Active Status -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $lesson->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>Active</strong>
                                </label>
                            </div>
                            <small class="text-muted">Inactive lessons will be hidden from committee members</small>
                        </div>

                        <!-- Auto-detected Tags Preview -->
                        <div class="mb-4" id="tagsPreview" style="display: none;">
                            <label class="form-label">Auto-detected Links</label>
                            <div id="tagsContainer" class="border p-3 rounded bg-light"></div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fe fe-save me-2"></i>{{ isset($lesson) ? 'Update' : 'Create' }} Lesson
                            </button>
                            <a href="{{ route('board.lessons.index') }}" class="btn btn-secondary">
                                <i class="fe fe-x me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="{{ asset('assets/plugins/summernote/summernote1.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#content').summernote({
            placeholder: 'Write your lesson content here... URLs will be automatically detected as tags.',
            tabsize: 2,
            height: 300,
            callbacks: {
                onChange: function(contents, $editable) {
                    // Sync content to textarea immediately on change
                    $('#content').val(contents);
                    
                    // Update link preview
                    // We strip HTML tags to find links in the text content
                    var text = $('<div>').html(contents).text();
                    updateLinkPreview(text);
                }
            }
        });

        // Initial check for links
        var initialContent = $('#content').summernote('code');
        var initialText = $('<div>').html(initialContent).text();
        updateLinkPreview(initialText);

        // Ensure content is synced on form submit
        $('form').on('submit', function() {
            if ($('#content').summernote('isEmpty')) {
                $('#content').val('');
            } else {
                $('#content').val($('#content').summernote('code'));
            }
        });
    });
// Auto-detect links in content
document.getElementById('content').addEventListener('input', function() {
    const content = this.value;
    const urlRegex = /https?:\/\/[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/))/g;
    const links = content.match(urlRegex);
    
    const tagsPreview = document.getElementById('tagsPreview');
    const tagsContainer = document.getElementById('tagsContainer');
    
    if (links && links.length > 0) {
        tagsPreview.style.display = 'block';
        tagsContainer.innerHTML = links.map(link => 
            `<span class="badge bg-info me-2 mb-2">
                <i class="fe fe-link me-1"></i>${link}
            </span>`
        ).join('');
    } else {
        tagsPreview.style.display = 'none';
    }
});

// Trigger on page load for edit mode
window.addEventListener('load', function() {
    document.getElementById('content').dispatchEvent(new Event('input'));
});
</script>
@endsection