@extends('layouts.highboard-dashboard')

@section('title', isset($task) ? 'Edit Task' : 'Create Task')

@section('css')
    <link href="{{ asset('assets/plugins/summernote/summernote1.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ isset($task) ? 'Edit Task' : 'Create Task' }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('highboard.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('highboard.tasks.index') }}">Tasks</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    {{ isset($task) ? 'Edit' : 'Create' }}
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ isset($task) ? 'Edit Task' : 'New Task' }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ isset($task) ? route('highboard.tasks.update', $task) : route('highboard.tasks.store') }}" 
                          method="POST" 
                          enctype="multipart/form-data">
                        @csrf
                        @if(isset($task))
                            @method('PUT')
                        @endif

                        <!-- Title -->
                        <div class="mb-4">
                            <label for="title" class="form-label">Task Title <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title', $task->title ?? '') }}" 
                                   required
                                   placeholder="Enter task title">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Committee Selection -->
                        <div class="mb-4">
                            <label for="committee_id" class="form-label">Committee <span class="text-danger">*</span></label>
                            <select class="form-control form-select @error('committee_id') is-invalid @enderror" 
                                    id="committee_id" 
                                    name="committee_id" 
                                    required>
                                <option value="">Select Committee</option>
                                @foreach($committees as $committee)
                                    <option value="{{ $committee->id }}" 
                                        {{ (old('committee_id', $task->committee_id ?? '') == $committee->id) ? 'selected' : '' }}>
                                        {{ $committee->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('committee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Content -->
                        <div class="mb-4">
                            <label for="content" class="form-label">Content</label>
                            <textarea name="content" id="content" class="form-control @error('content') is-invalid @enderror">{{ old('content', $task->content ?? '') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="fe fe-info me-1"></i>Links in your content will be automatically detected and saved as tags
                            </small>
                        </div>

                        <!-- Attachments -->
                        <div class="mb-4">
                            <label class="form-label">Attachments (Optional)</label>
                            
                            @if(isset($task) && $task->attachments->count() > 0)
                                <div class="mb-3">
                                    <strong class="d-block mb-2">Current Attachments:</strong>
                                    @foreach($task->attachments as $attachment)
                                        <div class="d-flex justify-content-between align-items-center border p-2 mb-2 rounded">
                                            <div>
                                                <i class="{{ $attachment->icon }} me-2"></i>
                                                <span>{{ $attachment->file_name }}</span>
                                                <small class="text-muted ms-2">({{ $attachment->formatted_size }})</small>
                                            </div>
                                            <form action="{{ route('highboard.tasks.attachments.destroy', $attachment) }}" 
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
                                       {{ old('is_active', $task->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>Active</strong>
                                </label>
                            </div>
                            <small class="text-muted">Inactive tasks will be hidden from committee members</small>
                        </div>

                        <!-- Auto-detected Tags Preview -->
                        <div class="mb-4" id="tagsPreview" style="display: none;">
                            <label class="form-label">Auto-detected Links</label>
                            <div id="tagsContainer" class="border p-3 rounded bg-light"></div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fe fe-save me-2"></i>{{ isset($task) ? 'Update' : 'Create' }} Task
                            </button>
                            <a href="{{ route('highboard.tasks.index') }}" class="btn btn-secondary">
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
            placeholder: 'Write your task content here... URLs will be automatically detected as tags.',
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
    function updateLinkPreview(content) {
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
    }
</script>
@endsection
