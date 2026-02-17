@extends('layouts.user-dashboard')

@section('title', $task->title)

@section('content')
    <!-- PAGE-HEADER -->
    <div class="page-header">
        <h1 class="page-title">{{ $task->title }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}"><i class="fe fe-home"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}"><i class="fe fe-check-square"></i> Tasks</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $task->title }}</li>
            </ol>
        </div>
    </div>
    <!-- PAGE-HEADER END -->

    <!-- ROW-1 -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="mb-4">
                        <h4 class="card-title">Description</h4>
                        @if($task->deadline)
                            <div class="alert alert-info mb-3">
                                <i class="fe fe-clock me-2"></i>
                                <strong>Deadline:</strong> {{ $task->deadline->format('M d, Y H:i') }}
                                @if($task->deadline->isPast())
                                    <span class="badge bg-danger ms-2">Expired</span>
                                @else
                                    <span class="badge bg-success ms-2">{{ $task->deadline->diffForHumans() }}</span>
                                @endif
                            </div>
                        @endif
                        {!! $task->content !!}
                    </div>

                    @if($task->attachments->count() > 0)
                        <div class="mt-5">
                            <h4 class="card-title"><i class="fe fe-paperclip me-2"></i> Attachments</h4>
                            <div class="list-group">
                                @foreach($task->attachments as $attachment)
                                    <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" class="list-group-item list-group-item-action d-flex align-items-center">
                                        <span class="avatar avatar-sm bg-success-transparent brround me-3">
                                            <i class="fe fe-file text-success"></i>
                                        </span>
                                        <div>
                                            <h6 class="mb-0">{{ $attachment->file_name }}</h6>
                                            <small class="text-muted">Click to download</small>
                                        </div>
                                        <div class="ms-auto">
                                            <i class="fe fe-download fs-16 text-muted"></i>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <div class="mt-5">
                        <a href="{{ route('tasks.index') }}" class="btn btn-light"><i class="fe fe-arrow-left me-1"></i> Back to Tasks</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fe fe-upload-cloud me-2"></i> Submission</h3>
                </div>
                <div class="card-body">
                    @if($submission)
                        <div class="alert alert-{{ $submission->status == 'accepted' ? 'success' : ($submission->status == 'rejected' ? 'danger' : 'warning') }} mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fe fe-{{ $submission->status == 'accepted' ? 'check-circle' : ($submission->status == 'rejected' ? 'x-circle' : 'alert-circle') }} me-2 fs-18"></i>
                                <div>
                                    <h5 class="alert-heading mb-0">Status: {{ ucfirst($submission->status) }}</h5>
                                </div>
                            </div>
                        </div>
                        
                        <div class="list-group mb-4">
                            <div class="list-group-item d-flex align-items-center">
                                <i class="fe fe-calendar me-3 text-muted"></i>
                                <div>
                                    <small class="text-muted d-block">Submitted on</small>
                                    <span class="fw-semibold">{{ $submission->created_at->format('M d, Y H:i') }}</span>
                                </div>
                            </div>
                        </div>

                        @if($submission->text_content)
                            <div class="mb-3">
                                <h6 class="mb-2"><i class="fe fe-file-text me-1"></i> Text Submission</h6>
                                <div class="p-3 bg-light rounded">
                                    {!! nl2br(e($submission->text_content)) !!}
                                </div>
                            </div>
                        @endif

                        @if($submission->file)
                            <a href="{{ asset('storage/' . $submission->file) }}" class="btn btn-info btn-block w-100 mb-3" target="_blank">
                                <i class="fe fe-eye me-1"></i> View File Submission
                            </a>
                        @endif
                        
                        @if($submission->status == 'rejected')
                             <div class="alert alert-danger mt-3">
                                <i class="fe fe-alert-triangle me-1"></i> Your submission was rejected. Please upload again.
                             </div>
                             @if($task->deadline && $task->deadline->isPast())
                                <div class="alert alert-danger mt-3">
                                    <i class="fe fe-alert-circle me-1"></i> The deadline has passed. You cannot resubmit.
                                </div>
                             @else
                                 <form action="{{ route('tasks.submit', $task) }}" method="POST" enctype="multipart/form-data" class="mt-3">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label class="form-label">Text Content (Optional)</label>
                                        <textarea class="form-control" name="text_content" rows="4" placeholder="Enter your answer here..."></textarea>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Upload File (Optional)</label>
                                        <div class="dropzone-container border rounded p-3 text-center" id="dropzone-resubmit">
                                            <div class="mb-2">
                                                <i class="fe fe-upload-cloud fs-30 text-primary"></i>
                                            </div>
                                            <h6 class="mb-2">Drag and drop file here or click to upload</h6>
                                            <small class="text-muted d-block mb-3">Supported formats: PDF, DOC, XLS, PPT, Images, Archives (Max: RM unlimited)</small>
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="browseButton-resubmit">Browse Files</button>
                                        </div>
                                        <div id="fileList-resubmit" class="mt-3"></div>
                                        <!-- Hidden input to store successfull upload -->
                                        <div id="uploadedFilesContainer-resubmit"></div>
                                    </div>
                                    @error('submission')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                    <button type="submit" class="btn btn-primary btn-block w-100"><i class="fe fe-refresh-cw me-1"></i> Resubmit</button>
                                </form>
                             @endif
                        @endif
                    @else
                        <div class="text-center mb-4">
                            <span class="bg-light brround p-3 d-inline-block">
                                <i class="fe fe-upload-cloud fs-30 text-primary"></i>
                            </span>
                            <h5 class="mt-3">Upload your solution</h5>
                            <p class="text-muted text-small">Submit your answer as text or file.</p>
                        </div>
                        @if($task->deadline && $task->deadline->isPast())
                            <div class="alert alert-danger mb-4">
                                <i class="fe fe-alert-circle me-2"></i>
                                <strong>Submission Closed:</strong> The deadline for this task has passed. You can no longer submit your work.
                            </div>
                        @else
                            <form action="{{ route('tasks.submit', $task) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group mb-4">
                                    <label class="form-label">Text Content (Optional)</label>
                                    <textarea class="form-control" name="text_content" rows="5" placeholder="Type your answer here..."></textarea>
                                </div>
                                
                                <div class="form-group mb-4">
                                    <label class="form-label">Upload File (Optional)</label>
                                    <div class="dropzone-container border rounded p-3 text-center" id="dropzone">
                                        <div class="mb-2">
                                            <i class="fe fe-upload-cloud fs-30 text-primary"></i>
                                        </div>
                                        <h6 class="mb-2">Drag and drop file here or click to upload</h6>
                                        <small class="text-muted d-block mb-3">Supported formats: PDF, DOC, XLS, PPT, Images, Archives (Max: RM unlimited)</small>
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="browseButton">Browse Files</button>
                                    </div>
                                    <div id="fileList" class="mt-3"></div>
                                    <!-- Hidden input to store successfull upload -->
                                    <div id="uploadedFilesContainer"></div>
                                </div>

                                @error('submission')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                                <button type="submit" class="btn btn-primary btn-block w-100"><i class="fe fe-send me-1"></i> Submit</button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- ROW-1 END -->
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to initialize Resumable
        function initResumable(dropzoneId, browseBtnId, fileListId, containerId, uniqueIdSuffix = '') {
            var dropzone = document.getElementById(dropzoneId);
            var browseBtn = document.getElementById(browseBtnId);
            
            if (!dropzone || !browseBtn) return;

            var r = new Resumable({
                target: '{{ route('upload.chunk') }}',
                query: {_token: '{{ csrf_token() }}'},
                fileType: ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'zip', 'rar'],
                chunkSize: 2 * 1024 * 1024, // 2MB chunk size
                headers: {
                    'Accept': 'application/json'
                },
                testChunks: false,
                throttleProgressCallbacks: 1,
                maxFiles: 1 // Only allow 1 file for submission
            });

            r.assignBrowse(browseBtn);
            r.assignDrop(dropzone);

            r.on('fileAdded', function(file){
                // Clear previous files since we only allow 1
                var fileList = document.getElementById(fileListId);
                fileList.innerHTML = '';
                
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
                            <input type="hidden" name="uploaded_file" value="" class="file-path-input">
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
        }

        function formatSize(size) {
            if(size < 1024) return size + ' B';
            var i = Math.floor(Math.log(size) / Math.log(1024));
            return (size / Math.pow(1024, i)).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
        }

        // Initialize for main submission form
        initResumable('dropzone', 'browseButton', 'fileList', 'uploadedFilesContainer');

        // Initialize for resubmission form if it exists
        initResumable('dropzone-resubmit', 'browseButton-resubmit', 'fileList-resubmit', 'uploadedFilesContainer-resubmit', '-resubmit');
    });
</script>
@endsection
