<div class="section" id="Blog">
    <div class="container">
        <div class="row">
            <h4 class="text-center fw-semibold">Knowledge Hub</h4>
            <span class="landing-title"></span>
            <h2 class="text-center fw-semibold">Explore Our <span class="text-primary">Technical Articles</span></h2>
            <div class="row justify-content-center">
                <p class="col-xl-9 wow fadeInUp text-default sub-text mb-7 text-center" data-wow-delay="0s">
                    Dive deep into petroleum engineering with our comprehensive articles on drilling, production, reservoir engineering, and sustainability.
                </p>
            </div>
        </div>
        
        <div class="row">
            @forelse($articles as $article)
                <div class="col-lg-6 col-xl-3 col-md-6 mb-4 reveal">
                    <div class="card testimonial-card h-100 article-card">
                        <div class="position-relative overflow-hidden">
                            @if($article->image)
                                <span class="badge bg-{{ $article->type == 'drilling' ? 'primary' : ($article->type == 'production' ? 'success' : ($article->type == 'reservoir' ? 'info' : 'warning')) }} position-absolute top-0 start-0 m-3 z-1">
                                    @switch($article->type)
                                        @case('drilling')
                                            <i class="fa fa-cog me-1"></i> Drilling
                                            @break
                                        @case('production')
                                            <i class="fa fa-industry me-1"></i> Production
                                            @break
                                        @case('reservoir')
                                            <i class="fa fa-water me-1"></i> Reservoir
                                            @break
                                        @case('sustainability')
                                            <i class="fa fa-leaf me-1"></i> Sustainability
                                            @break
                                    @endswitch
                                </span>
                            @endif
                            <a href="{{ route('articlePreview', $article->id) }}">
                                <img class="w-100 article-img" 
                                     src="{{ $article->image ? asset('storage/' . $article->image) : asset('assets/images/media/12.jpg') }}" 
                                     alt="{{ $article->name }}">
                            </a>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="mb-2">
                                <h5 class="fw-semibold mb-2">
                                    <a href="{{ route('articlePreview', $article->id) }}" class="text-dark article-title">
                                        {{ Str::limit($article->name, 45) }}
                                    </a>
                                </h5>
                                <p class="text-muted fs-13 mb-0">
                                    {{ Str::limit(strip_tags($article->content), 80) }}
                                </p>
                            </div>
                            <div class="mt-auto pt-3 border-top">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="author-avatar me-2">
                                            {{ strtoupper(substr($article->author, 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="fw-semibold fs-12">{{ Str::limit($article->author, 12) }}</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('articlePreview', $article->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fe fe-book-open me-1"></i> Read
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-lg-6 col-xl-3 col-md-6 mb-4 reveal">
                    <div class="card testimonial-card h-100">
                        <div class="card-body text-center py-5">
                            <i class="fe fe-file-text text-muted mb-3" style="font-size: 48px;"></i>
                            <h5 class="text-muted">Coming Soon</h5>
                            <p class="text-muted fs-13">Technical articles will be published soon.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-xl-3 col-md-6 mb-4 reveal">
                    <div class="card testimonial-card h-100">
                        <div class="card-body text-center py-5">
                            <i class="fa fa-cog text-primary mb-3" style="font-size: 48px;"></i>
                            <h5 class="text-muted">Drilling</h5>
                            <p class="text-muted fs-13">Explore drilling techniques and innovations.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-xl-3 col-md-6 mb-4 reveal">
                    <div class="card testimonial-card h-100">
                        <div class="card-body text-center py-5">
                            <i class="fa fa-industry text-success mb-3" style="font-size: 48px;"></i>
                            <h5 class="text-muted">Production</h5>
                            <p class="text-muted fs-13">Learn about production optimization.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-xl-3 col-md-6 mb-4 reveal">
                    <div class="card testimonial-card h-100">
                        <div class="card-body text-center py-5">
                            <i class="fa fa-leaf text-warning mb-3" style="font-size: 48px;"></i>
                            <h5 class="text-muted">Sustainability</h5>
                            <p class="text-muted fs-13">Discover sustainable energy practices.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
        
        <div class="text-center mt-4">
            <a href="{{ route('articlesList') }}" class="btn ripple btn-primary btn-lg px-5">
                <i class="fe fe-arrow-right me-2"></i> Discover More
            </a>
        </div>
    </div>
</div>

<style>
    .article-card {
        transition: all 0.3s ease;
        border: none;
        overflow: hidden;
    }
    .article-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    }
    .article-img {
        height: 180px;
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
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-bg-color) 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
        font-size: 12px;
    }
    .z-1 {
        z-index: 1;
    }
</style>