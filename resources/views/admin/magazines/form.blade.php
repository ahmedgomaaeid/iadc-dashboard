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
                            <input type="file" 
                                   class="form-control @error('pdf_file') is-invalid @enderror" 
                                   id="pdf_file" 
                                   name="pdf_file"
                                   accept=".pdf"
                                   {{ !isset($magazine) ? 'required' : '' }}>
                            <small class="text-muted">Maximum file size: 50MB. Only PDF files are allowed.</small>
                            @error('pdf_file')
                                <div class="invalid-feedback">{{ $message }}</div>
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
</script>
@endsection
