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

                        <!-- Deadline -->
                        <div class="mb-4">
                            <label for="deadline" class="form-label">Deadline (Optional)</label>
                            <input type="datetime-local" 
                                   class="form-control @error('deadline') is-invalid @enderror" 
                                   id="deadline" 
                                   name="deadline" 
                                   value="{{ old('deadline', isset($task) && $task->deadline ? $task->deadline->format('Y-m-d\TH:i') : '') }}">
                            @error('deadline')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Leave empty for no deadline</small>
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
                                            <button type="button" class="btn btn-sm btn-danger delete-attachment-btn"
                                                    data-attachment-id="{{ $attachment->id }}"
                                                    onclick="if(confirm('Delete this attachment?')) document.getElementById('delete-attachment-{{ $attachment->id }}').submit();">
                                                <i class="fe fe-trash-2"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="dropzone-container border rounded p-3 text-center" id="dropzone">
                                <div class="mb-2">
                                    <i class="fe fe-upload-cloud fs-30 text-primary"></i>
                                </div>
                                <h5 class="mb-2">Drag and drop files here or click to upload</h5>
                                <small class="text-muted d-block mb-3">Supported formats: PDF, DOC, XLS, PPT, Images, Archives (Max: RM unlimited)</small>
                                <button type="button" class="btn btn-outline-primary" id="browseButton">Browse Files</button>
                            </div>

                            <div id="fileList" class="mt-3"></div>
                            
                            <!-- Hidden input to store successfull uploads -->
                            <div id="uploadedFilesContainer"></div>
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

    {{-- Delete attachment forms placed outside the main form to avoid nested forms --}}
    @if(isset($task) && $task->attachments->count() > 0)
        @foreach($task->attachments as $attachment)
            <form id="delete-attachment-{{ $attachment->id }}" 
                  action="{{ route('highboard.tasks.attachments.destroy', $attachment) }}" 
                  method="POST" style="display:none;">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    @endif
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
                    $('#content').val(contents);
                    var text = $('<div>').html(contents).text();
                    updateLinkPreview(text);
                }
            }
        });

        var initialContent = $('#content').summernote('code');
        var initialText = $('<div>').html(initialContent).text();
        updateLinkPreview(initialText);

        $('form').on('submit', function() {
            if ($('#content').summernote('isEmpty')) {
                $('#content').val('');
            } else {
                $('#content').val($('#content').summernote('code'));
            }
        });
    });

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

    // Resumable.js Implementation
    var r = new Resumable({
        target: '{{ route('upload.chunk') }}',
        query: {_token: '{{ csrf_token() }}'},
        fileType: ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'zip', 'rar'],
        chunkSize: 2 * 1024 * 1024, // 2MB chunk size
        headers: {
            'Accept': 'application/json'
        },
        testChunks: false,
        throttleProgressCallbacks: 1
    });

    r.assignBrowse(document.getElementById('browseButton'));
    r.assignDrop(document.getElementById('dropzone'));

    r.on('fileAdded', function(file){
        var fileList = document.getElementById('fileList');
        var html = `
            <div id="file-${file.uniqueIdentifier}" class="card mb-2">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-truncate fw-bold me-2" style="max-width: 70%;">${file.fileName}</span>
                        <span class="badge bg-secondary">${formatSize(file.size)}</span>
                    </div>
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted status-text mt-1 d-block">Waiting...</small>
                    <input type="hidden" name="uploaded_files[]" value="" class="file-path-input">
                </div>
            </div>
        `;
        fileList.insertAdjacentHTML('beforeend', html);
        r.upload();
    });

    r.on('fileProgress', function(file){
        var progress = Math.floor(file.progress() * 100);
        var el = document.getElementById('file-' + file.uniqueIdentifier);
        el.querySelector('.progress-bar').style.width = progress + '%';
        el.querySelector('.status-text').innerText = 'Uploading... ' + progress + '%';
    });

    r.on('fileSuccess', function(file, message){
        var response = JSON.parse(message);
        var el = document.getElementById('file-' + file.uniqueIdentifier);
        el.querySelector('.progress-bar').classList.add('bg-success');
        el.querySelector('.status-text').innerText = 'Completed';
        el.querySelector('.status-text').classList.add('text-success');
        
        // Add path to hidden input
        el.querySelector('.file-path-input').value = response.path;
    });

    r.on('fileError', function(file, message){
        var el = document.getElementById('file-' + file.uniqueIdentifier);
        el.querySelector('.progress-bar').classList.add('bg-danger');
        el.querySelector('.status-text').innerText = 'Error: ' + message;
        el.querySelector('.status-text').classList.add('text-danger');
    });

    function formatSize(size) {
        if(size < 1024) return size + ' B';
        var i = Math.floor(Math.log(size) / Math.log(1024));
        return (size / Math.pow(1024, i)).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
    }
</script>
@endsection
