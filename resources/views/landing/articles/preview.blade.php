@extends('layouts.landing')

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@endsection

@section('header')
<div class="jumps-prevent" style="padding-top: 67.5px;"></div>
@endsection

@section('title', 'IADC Suez - ' . $article->name)

@section('content')
<style>
    body {
        background-color: #f0f0f5 !important;
    }
    .article-hero {
        background: linear-gradient(135deg, var(--primary-bg-color) 0%, #764ba2 100%);
        padding: 50px 0 80px;
    }
    .article-content-card {
        margin-top: -50px;
        border-radius: 15px;
        border: none;
        box-shadow: 0 5px 30px rgba(0,0,0,0.1);
    }
    .article-body {
        font-size: 16px;
        line-height: 1.8;
        color: #555;
    }
    .article-body h1, .article-body h2, .article-body h3, .article-body h4 {
        color: #333;
        margin-top: 25px;
        margin-bottom: 15px;
        font-weight: 600;
    }
    .article-body p {
        margin-bottom: 18px;
    }
    .article-body img {
        max-width: 100%;
        border-radius: 10px;
        margin: 15px 0;
    }
    .article-body ul, .article-body ol {
        margin-bottom: 18px;
        padding-left: 20px;
    }
    .article-body li {
        margin-bottom: 8px;
    }
    .author-avatar-lg {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        /* background: linear-gradient(135deg, var(--primary-bg-color) 0%, #764ba2 100%); */
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
        font-size: 20px;
    }
    .share-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        font-size: 16px;
    }
    .share-btn:hover {
        transform: translateY(-3px);
    }
    .related-card {
        transition: all 0.3s ease;
        border: none;
    }
    .related-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    .related-img {
        height: 120px;
        object-fit: cover;
        border-radius: 8px;
    }
    .category-badge {
        display: inline-flex;
        align-items: center;
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 500;
    }
</style>

<!-- Hero Section -->
<div class="article-hero">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('landing') }}" class="text-white-50">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('articlesList') }}" class="text-white-50">Articles</a></li>
                <li class="breadcrumb-item text-white active" aria-current="page">{{ Str::limit($article->name, 30) }}</li>
            </ol>
        </nav>
        
        <div class="row align-items-center">
            <div class="col-lg-8">
                @switch($article->type)
                    @case('drilling')
                        <span class="category-badge bg-primary text-white mb-3">
                            <i class="fa fa-cog me-2"></i> Drilling
                        </span>
                        @break
                    @case('production')
                        <span class="category-badge bg-success text-white mb-3">
                            <i class="fa fa-industry me-2"></i> Production
                        </span>
                        @break
                    @case('reservoir')
                        <span class="category-badge bg-info text-white mb-3">
                            <i class="fa fa-water me-2"></i> Reservoir
                        </span>
                        @break
                    @case('sustainability')
                        <span class="category-badge bg-warning text-dark mb-3">
                            <i class="fa fa-leaf me-2"></i> Sustainability
                        </span>
                        @break
                @endswitch
                
                <h1 class="text-white fw-bold mb-3">{{ $article->name }}</h1>
                
                <div class="d-flex align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center">
                        <div class="author-avatar-lg bg-success-gradient me-2">
                            {{ strtoupper(substr($article->author, 0, 1)) }}
                        </div>
                        <div>
                            <span class="text-white fw-semibold d-block">{{ $article->author }}</span>
                            <small class="text-white-50">Author</small>
                        </div>
                    </div>
                    <span class="text-white-50 d-none d-md-inline">|</span>
                    <div class="d-flex align-items-center text-white-50">
                        <i class="fe fe-calendar me-2"></i>
                        <span>{{ $article->created_at->format('F d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card article-content-card">
                @if($article->image)
                    <img class="card-img-top" 
                         src="{{ asset('storage/' . $article->image) }}" 
                         alt="{{ $article->name }}"
                         style="object-fit: cover; border-radius: 15px 15px 0 0;">
                @endif
                <div class="card-body p-4 p-md-5">
                    <div class="article-body">
                        {!! $article->content !!}
                    </div>
                    
                    <!-- Share Section -->
                    <hr class="my-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <span class="fw-semibold me-3">Share:</span>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" 
                               target="_blank" 
                               class="share-btn btn btn-primary me-1 d-inline-flex align-items-center">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($article->name) }}" 
                               target="_blank" 
                               class="share-btn btn btn-info me-1 d-inline-flex align-items-center">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(request()->url()) }}&title={{ urlencode($article->name) }}" 
                               target="_blank" 
                               class="share-btn btn btn-primary me-1 d-inline-flex align-items-center">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="https://wa.me/?text={{ urlencode($article->name . ' - ' . request()->url()) }}" 
                               target="_blank" 
                               class="share-btn btn btn-success d-inline-flex align-items-center">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                        <a href="{{ route('articlesList') }}" class="btn btn-outline-primary">
                            <i class="fe fe-arrow-left me-2"></i> All Articles
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Categories -->
            <div class="card mb-4" style="margin-top: -50px;">
                <div class="card-header bg-transparent">
                    <h6 class="fw-bold mb-0">
                        <i class="fe fe-tag text-primary me-2"></i>Categories
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('articlesList', ['type' => 'drilling']) }}" class="btn btn-sm {{ $article->type == 'drilling' ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="fa fa-cog me-1"></i> Drilling
                        </a>
                        <a href="{{ route('articlesList', ['type' => 'production']) }}" class="btn btn-sm {{ $article->type == 'production' ? 'btn-success' : 'btn-outline-success' }}">
                            <i class="fa fa-industry me-1"></i> Production
                        </a>
                        <a href="{{ route('articlesList', ['type' => 'reservoir']) }}" class="btn btn-sm {{ $article->type == 'reservoir' ? 'btn-info' : 'btn-outline-info' }}">
                            <i class="fa fa-water me-1"></i> Reservoir
                        </a>
                        <a href="{{ route('articlesList', ['type' => 'sustainability']) }}" class="btn btn-sm {{ $article->type == 'sustainability' ? 'btn-warning' : 'btn-outline-warning' }}">
                            <i class="fa fa-leaf me-1"></i> Sustainability
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Related Articles -->
            @if($relatedArticles->count() > 0)
                <div class="card">
                    <div class="card-header bg-transparent">
                        <h6 class="fw-bold mb-0">
                            <i class="fe fe-bookmark text-primary me-2"></i>Related Articles
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($relatedArticles as $related)
                            <div class="related-card card mb-3">
                                <div class="card-body p-2">
                                    <div class="d-flex">
                                        <img class="related-img me-3" 
                                             src="{{ $related->image ? asset('storage/' . $related->image) : asset('assets/images/media/12.jpg') }}" 
                                             alt="{{ $related->name }}"
                                             style="width: 80px;">
                                        <div class="flex-grow-1">
                                            <h6 class="fw-semibold mb-1 fs-13">
                                                <a href="{{ route('articlePreview', $related->id) }}" class="text-dark text-decoration-none">
                                                    {{ Str::limit($related->name, 40) }}
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fe fe-calendar me-1"></i>{{ $related->created_at->format('M d') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <!-- Back to Home -->
            <div class="text-center mt-4">
                <a href="{{ route('landing') }}#Blog" class="btn btn-primary w-100">
                    <i class="fe fe-home me-2"></i>Back to Home
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
