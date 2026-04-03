@extends('layouts.admin-dashboard')

@section('title', isset($magazine) ? 'Edit Magazine' : 'Create Magazine')

@section('css')
<style>
    .cover-preview {
        max-width: 200px;
        max-height: 280px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #dee2e6;
    }
    .pdf-info {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #dee2e6;
    }
</style>
@endsection

@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ isset($magazine) ? 'Edit Magazine' : 'Create Magazine' }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.magazines.index') }}">Magazines</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ isset($magazine) ? 'Edit' : 'Create' }}</li>
            </ol>
        </div>
    </div>

    <form id="magazine-form" action="{{ isset($magazine) ? route('admin.magazines.update', $magazine) : route('admin.magazines.store') }}" 
          method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($magazine))
            @method('PUT')
        @endif

        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Magazine Information</h3>
                    </div>
                    <div class="card-body">
                        <!-- Name -->
                        <div class="mb-4">
                            <label for="name" class="form-label">Magazine Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $magazine->name ?? '') }}" 
                                   placeholder="e.g. IADC Magazine Issue #1"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- PDF File -->
                        <div class="mb-4">
                            <label for="pdf_file" class="form-label">
                                PDF File 
                                @if(!isset($magazine))
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                            
                            <!-- Hidden input for chunk path -->
                            <input type="hidden" name="uploaded_pdf" id="uploaded_pdf_input" value="">

                            <!-- Dropzone container -->
                            <div class="dropzone-container border rounded p-3 text-center" id="dropzone">
                                <div class="mb-2">
                                    <i class="fe fe-upload-cloud fs-30 text-primary"></i>
                                </div>
                                <h5 class="mb-2">Drag and drop PDF file here or click to upload</h5>
                                <small class="text-muted d-block mb-3">Maximum file size: 50MB. Only PDF files are allowed.</small>
                                <button type="button" class="btn btn-outline-primary" id="browseButton">Browse File</button>
                            </div>

                            <div id="fileList" class="mt-3"></div>

                            @error('pdf_file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('uploaded_pdf')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            @if(isset($magazine) && $magazine->pdf_file)
                                <div class="pdf-info mt-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fe fe-file-text text-danger me-3" style="font-size: 32px;"></i>
                                        <div class="flex-grow-1">
                                            <strong>Current PDF:</strong><br>
                                            <small class="text-muted">{{ basename($magazine->pdf_file) }}</small>
                                        </div>
                                        <a href="{{ asset('storage/' . $magazine->pdf_file) }}" 
                                           target="_blank" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fe fe-external-link me-1"></i>View
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $magazine->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>Active</strong> - Magazine will be visible on the website
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Cover Image -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Cover Image</h3>
                    </div>
                    <div class="card-body text-center">
                        @if(isset($magazine) && $magazine->image)
                            <img src="{{ asset('storage/' . $magazine->image) }}" 
                                 alt="Cover" 
                                 id="cover-preview"
                                 class="cover-preview mb-3">
                        @else
                            <img src="{{ asset('assets/images/media/12.jpg') }}" 
                                 alt="Cover Preview" 
                                 id="cover-preview"
                                 class="cover-preview mb-3"
                                 style="display: none;">
                            <div class="text-muted mb-3" id="no-cover-text">
                                <i class="fe fe-image" style="font-size: 48px;"></i>
                                <p class="mt-2">No cover image selected</p>
                            </div>
                        @endif
                        
                        <input type="file" 
                               class="form-control @error('image') is-invalid @enderror" 
                               id="image" 
                               name="image"
                               accept="image/*"
                               onchange="previewCover(this)">
                        <small class="text-muted d-block mt-2">Recommended: 400x560px (portrait)</small>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fe fe-save me-2"></i>{{ isset($magazine) ? 'Update' : 'Create' }} Magazine
                            </button>
                            <a href="{{ route('admin.magazines.index') }}" class="btn btn-secondary">
                                <i class="fe fe-x me-2"></i>Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
<script src="{{route('index')}}/assets/js/resumable.min.js"></script>
<script>
    function previewCover(input) {
        const preview = document.getElementById('cover-preview');
        const noCoverText = document.getElementById('no-cover-text');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                if (noCoverText) {
                    noCoverText.style.display = 'none';
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Resumable.js Implementation
    var r = new Resumable({
        target: '{{ route('upload.chunk') }}',
        query: {_token: '{{ csrf_token() }}'},
        fileType: ['pdf'],
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
        // Clear previous files if any, to only allow 1 PDF
        r.files = [file];
        var fileList = document.getElementById('fileList');
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
                </div>
            </div>
        `;
        fileList.insertAdjacentHTML('beforeend', html);
        r.upload();
    });

    r.on('fileProgress', function(file){
        var progress = Math.floor(file.progress() * 100);
        var el = document.getElementById('file-' + file.uniqueIdentifier);
        if(el) {
            el.querySelector('.progress-bar').style.width = progress + '%';
            el.querySelector('.status-text').innerText = 'Uploading... ' + progress + '%';
        }
    });

    r.on('fileSuccess', function(file, message){
        var response = JSON.parse(message);
        var el = document.getElementById('file-' + file.uniqueIdentifier);
        if(el) {
            el.querySelector('.progress-bar').classList.add('bg-success');
            el.querySelector('.status-text').innerText = 'Completed';
            el.querySelector('.status-text').classList.add('text-success');
        }
        
        // Add path to hidden input
        document.getElementById('uploaded_pdf_input').value = response.path;
    });

    r.on('fileError', function(file, message){
        var el = document.getElementById('file-' + file.uniqueIdentifier);
        if(el) {
            el.querySelector('.progress-bar').classList.add('bg-danger');
            el.querySelector('.status-text').innerText = 'Error: ' + message;
            el.querySelector('.status-text').classList.add('text-danger');
        }
    });

    function formatSize(size) {
        if(size < 1024) return size + ' B';
        var i = Math.floor(Math.log(size) / Math.log(1024));
        return (size / Math.pow(1024, i)).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
    }
</script>
@endsection
