@extends('layouts.admin-dashboard')

@section('title', isset($article) ? 'Edit Article' : 'Create Article')

@section('css')
<style>
    .article-image-preview {
        width: 100%;
        max-width: 400px;
        height: 200px;
        object-fit: cover;
        border-radius: 10px;
        margin-top: 10px;
        border: 3px solid #dee2e6;
    }
    .type-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .type-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .type-card.selected {
        border-color: #5e72e4;
        background-color: rgba(94, 114, 228, 0.1);
    }
    .type-card .badge {
        font-size: 14px;
        padding: 8px 16px;
    }
</style>
@endsection

@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ isset($article) ? 'Edit Article' : 'Create Article' }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.articles.index') }}">Articles</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    {{ isset($article) ? 'Edit' : 'Create' }}
                </li>
            </ol>
        </div>
    </div>

    <form id="article-form" action="{{ isset($article) ? route('admin.articles.update', $article) : route('admin.articles.store') }}" 
          method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($article))
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ isset($article) ? 'Edit Article' : 'New Article' }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Article Title <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $article->name ?? '') }}" 
                                   placeholder="Enter article title"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control summernote @error('content') is-invalid @enderror" 
                                      id="content" 
                                      name="content">{{ old('content', $article->content ?? '') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Article Image</label>
                            <input type="file" 
                                   class="form-control @error('image') is-invalid @enderror" 
                                   id="image" 
                                   name="image"
                                   accept="image/*"
                                   onchange="previewImage(this)">
                            @if(isset($article) && $article->image)
                                <img src="{{ asset('storage/' . $article->image) }}" 
                                     alt="Current article image" 
                                     id="image-preview" 
                                     class="article-image-preview">
                            @else
                                <img src="" alt="" id="image-preview" class="article-image-preview" style="display: none;">
                            @endif
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Article Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                @foreach($types as $key => $value)
                                    <div class="col-6">
                                        <div class="card type-card p-3 text-center {{ old('type', $article->type ?? '') === $key ? 'selected' : '' }}"
                                             onclick="selectType('{{ $key }}')">
                                            @switch($key)
                                                @case('drilling')
                                                    <span class="badge bg-primary">{{ $value }}</span>
                                                    @break
                                                @case('production')
                                                    <span class="badge bg-success">{{ $value }}</span>
                                                    @break
                                                @case('reservoir')
                                                    <span class="badge bg-info">{{ $value }}</span>
                                                    @break
                                                @case('sustainability')
                                                    <span class="badge bg-warning text-dark">{{ $value }}</span>
                                                    @break
                                            @endswitch
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <input type="hidden" name="type" id="type" value="{{ old('type', $article->type ?? '') }}" required>
                            @error('type')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="author" class="form-label">Author <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('author') is-invalid @enderror" 
                                   id="author" 
                                   name="author" 
                                   value="{{ old('author', $article->author ?? '') }}" 
                                   placeholder="Enter author name"
                                   required>
                            @error('author')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $article->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                            <small class="text-muted">Inactive articles will not be displayed publicly</small>
                        </div>

                        <hr>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fe fe-save me-2"></i>{{ isset($article) ? 'Update' : 'Create' }} Article
                            </button>
                            <a href="{{ route('admin.articles.index') }}" class="btn btn-secondary">
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
    $(document).ready(function() {
        // Initialize Summernote
        $('#content').summernote({
            placeholder: 'Write your article content here...',
            tabsize: 2,
            height: 350,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'italic', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            callbacks: {
                onChange: function(contents, $editable) {
                    $('#content').val(contents);
                }
            }
        });

        // Ensure content is synced on form submit
        $('#article-form').on('submit', function() {
            if ($('#content').summernote('isEmpty')) {
                $('#content').val('');
            } else {
                $('#content').val($('#content').summernote('code'));
            }
        });
    });

    function selectType(type) {
        // Update hidden input
        document.getElementById('type').value = type;
        
        // Update visual selection
        document.querySelectorAll('.type-card').forEach(card => {
            card.classList.remove('selected');
        });
        event.currentTarget.classList.add('selected');
    }

    function previewImage(input) {
        const preview = document.getElementById('image-preview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
