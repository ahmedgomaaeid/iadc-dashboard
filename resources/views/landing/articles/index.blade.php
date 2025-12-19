@extends('layouts.landing')

@section('header')
<div class="jumps-prevent" style="padding-top: 67.5px;"></div>
@endsection

@section('title', 'IADC Suez - Technical Articles')

@section('content')
<style>
    .articles-hero {
        background: linear-gradient(135deg, var(--primary-bg-color) 0%, #764ba2 100%);
        padding: 50px 0;
        margin-bottom: 30px;
    }
    .article-card {
        transition: all 0.3s ease;
        border: none;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.12) !important;
    }
    .article-card:hover {
        transform: translateY(-5px);
    }
    .article-img {
        height: 200px;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .article-card:hover .article-img {
        transform: scale(1.08);
    }
    .article-title {
        text-decoration: none;
        transition: color 0.3s ease;
        display: block;
        line-height: 1.4;
    }
    .article-title:hover {
        color: var(--primary-bg-color) !important;
    }
    .author-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        /* background: linear-gradient(135deg, var(--primary-bg-color) 0%, #764ba2 100%); */
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
        font-size: 14px;
    }
    .filter-btn {
        border-radius: 50px;
        padding: 8px 20px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .filter-btn:hover {
        transform: translateY(-2px);
    }
    .z-1 {
        z-index: 1;
    }
</style>

<!-- Hero Section -->
<div class="articles-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('landing') }}" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item text-white active" aria-current="page">Articles</li>
                    </ol>
                </nav>
                <h1 class="text-white fw-bold mb-2">
                    <i class="fe fe-book-open me-2"></i>Technical Articles
                </h1>
                <p class="text-white-50 mb-0">
                    Explore our comprehensive collection of technical articles covering key topics in petroleum engineering.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <!-- Filter Buttons -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="{{ route('articlesList') }}" class="btn filter-btn {{ !request('type') ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="fe fe-grid me-1"></i> All
                </a>
                @foreach($types as $key => $value)
                    <a href="{{ route('articlesList', ['type' => $key]) }}" 
                       class="btn filter-btn {{ request('type') == $key ? 'btn-primary' : 'btn-outline-primary' }}">
                        @switch($key)
                            @case('drilling')
                                <i class="fa fa-cog me-1"></i>
                                @break
                            @case('production')
                                <i class="fa fa-industry me-1"></i>
                                @break
                            @case('reservoir')
                                <i class="fe fe-layers me-1"></i>
                                @break
                            @case('sustainability')
                                <i class="fa fa-leaf me-1"></i>
                                @break
                        @endswitch
                        {{ $value }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Articles Grid -->
    <div class="row">
        @forelse($articles as $article)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card testimonial-card h-100 article-card">
                    <div class="position-relative overflow-hidden">
                        <span class="badge bg-{{ $article->type == 'drilling' ? 'primary' : ($article->type == 'production' ? 'success' : ($article->type == 'reservoir' ? 'info' : 'warning')) }} position-absolute top-0 start-0 m-3 z-1">
                            @switch($article->type)
                                @case('drilling')
                                    <i class="fa fa-cog me-1"></i> Drilling
                                    @break
                                @case('production')
                                    <i class="fa fa-industry me-1"></i> Production
                                    @break
                                @case('reservoir')
                                    <i class="fe fe-layers me-1"></i> Reservoir
                                    @break
                                @case('sustainability')
                                    <i class="fa fa-leaf me-1"></i> Sustainability
                                    @break
                            @endswitch
                        </span>
                        <a href="{{ route('articlePreview', $article->id) }}">
                            <img class="w-100 article-img" 
                                 src="{{ $article->image ? asset('storage/' . $article->image) : asset('assets/images/media/12.jpg') }}" 
                                 alt="{{ $article->name }}">
                        </a>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="fw-semibold mb-2">
                            <a href="{{ route('articlePreview', $article->id) }}" class="text-dark article-title">
                                {{ Str::limit($article->name, 60) }}
                            </a>
                        </h5>
                        <p class="text-muted fs-13 mb-3">
                            {{ Str::limit(strip_tags($article->content), 100) }}
                        </p>
                        <div class="mt-auto pt-3 border-top">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="author-avatar me-2 bg-success-gradient">
                                        {{ strtoupper(substr($article->author, 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-semibold fs-12 d-block">{{ Str::limit($article->author, 15) }}</span>
                                        <small class="text-muted fs-11">{{ $article->created_at->format('M d, Y') }}</small>
                                    </div>
                                </div>
                                <a href="{{ route('articlePreview', $article->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fe fe-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card testimonial-card text-center py-5">
                    <div class="card-body">
                        <i class="fe fe-file-text text-muted mb-3" style="font-size: 64px;"></i>
                        <h4 class="text-muted">No Articles Found</h4>
                        <p class="text-muted mb-3">We haven't published any articles in this category yet.</p>
                        <a href="{{ route('articlesList') }}" class="btn btn-primary">
                            <i class="fe fe-arrow-left me-2"></i>View All Articles
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($articles->hasPages())
        <div class="row mt-4">
            <div class="col-12 d-flex justify-content-center">
                {{ $articles->appends(request()->query())->links() }}
            </div>
        </div>
    @endif

    <!-- Back Button -->
    <div class="text-center mt-4">
        <a href="{{ route('landing') }}#Blog" class="btn btn-outline-primary">
            <i class="fe fe-arrow-left me-2"></i>Back to Home
        </a>
    </div>
</div>
@endsection
