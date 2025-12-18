<div class="section" id="Publications">
    <div class="container">
        <div class="row">
            <h4 class="text-center fw-semibold">Publications</h4>
            <span class="landing-title"></span>
            <h2 class="text-center fw-semibold">Our <span class="text-primary">Magazines</span></h2>
        </div>
        
        @if($magazines->count() > 0)
            <div class="magazine-slider-wrapper mt-5 reveal">
                <div class="swiper magazineSwiper">
                    <div class="swiper-wrapper">
                        @foreach($magazines as $magazine)
                            <div class="swiper-slide">
                                <div class="magazine-showcase-item">
                                    <div class="row align-items-center justify-content-center g-5">
                                        <!-- Magazine Cover -->
                                        <div class="col-lg-5 col-md-6">
                                            <div class="magazine-cover-container">
                                                <div class="magazine-book">
                                                    <div class="magazine-spine"></div>
                                                    @if($magazine->image)
                                                        <img src="{{ asset('storage/' . $magazine->image) }}" 
                                                             alt="{{ $magazine->name }}" 
                                                             class="magazine-cover-img">
                                                    @else
                                                        <div class="magazine-cover-placeholder">
                                                            <i class="fe fe-book-open"></i>
                                                            <span>Magazine</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Magazine Info -->
                                        <div class="col-lg-5 col-md-6">
                                            <div class="magazine-details">
                                                <!-- <span class="magazine-edition">Edition {{ $loop->iteration }}</span> -->
                                                <h3 class="magazine-main-title">{{ $magazine->name }}</h3>
                                                <div class="magazine-meta">
                                                    <!-- <div class="meta-item">
                                                        <i class="fe fe-calendar"></i>
                                                        <span>{{ $magazine->created_at->format('F Y') }}</span>
                                                    </div> -->
                                                    <div class="meta-item">
                                                        <i class="fe fe-file-text"></i>
                                                        <span>PDF Format</span>
                                                    </div>
                                                </div>
                                                <!-- <p class="magazine-desc">
                                                    Explore the latest insights, research findings, and industry updates in petroleum engineering.
                                                </p> -->
                                                <div class="magazine-actions">
                                                    <a href="{{ asset('storage/' . $magazine->pdf_file) }}" 
                                                       target="_blank" 
                                                       class="btn btn-primary btn-lg">
                                                        <i class="fe fe-eye me-2"></i>Read Online
                                                    </a>
                                                    <a href="{{ asset('storage/' . $magazine->pdf_file) }}" 
                                                       download
                                                       class="btn btn-outline-primary btn-lg">
                                                        <i class="fe fe-download me-2"></i>Download
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Navigation -->
                    <div class="swiper-button-prev magazine-nav-btn"></div>
                    <div class="swiper-button-next magazine-nav-btn"></div>
                    
                    <!-- Pagination -->
                    <div class="swiper-pagination magazine-pagination"></div>
                </div>
            </div>
        @else
            <div class="row justify-content-center mt-5">
                <div class="col-lg-6 text-center">
                    <div class="empty-state py-5">
                        <div class="empty-icon mb-4">
                            <i class="fe fe-book-open"></i>
                        </div>
                        <h5 class="fw-semibold">Coming Soon</h5>
                        <p class="text-muted mb-0">Our first magazine edition will be available soon.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    #Publications {
        padding: 80px 0;
        background: linear-gradient(180deg, #f8f9fc 0%, #ffffff 100%);
        overflow: hidden;
    }
    
    .magazine-slider-wrapper {
        max-width: 1100px;
        margin: 0 auto;
        padding: 20px 60px;
        position: relative;
    }
    
    .magazineSwiper {
        padding: 0px 0 60px;
    }
    
    .magazine-showcase-item {
        padding: 20px;
    }
    
    /* Magazine Book Effect */
    .magazine-cover-container {
        display: flex;
        justify-content: center;
        perspective: 1000px;
    }
    
    .magazine-book {
        position: relative;
        transform-style: preserve-3d;
        transform: rotateY(-5deg);
        transition: transform 0.5s ease;
    }
    
    .magazine-book:hover {
        transform: rotateY(0deg) scale(1.02);
    }
    
    .magazine-spine {
        position: absolute;
        left: -15px;
        top: 0;
        width: 15px;
        height: 100%;
        background: linear-gradient(90deg, #2a2a2a 0%, #4a4a4a 50%, #3a3a3a 100%);
        transform: rotateY(-90deg) translateX(-7.5px);
        transform-origin: right;
        border-radius: 2px 0 0 2px;
    }
    
    .magazine-cover-img {
        width: 320px;
        height: 440px;
        object-fit: cover;
        border-radius: 0 6px 6px 0;
        box-shadow: 
            0 30px 60px rgba(0,0,0,0.25),
            0 15px 30px rgba(0,0,0,0.15),
            inset 0 0 0 1px rgba(255,255,255,0.1);
    }
    
    .magazine-cover-placeholder {
        width: 320px;
        height: 440px;
        background: linear-gradient(145deg, #667eea 0%, #764ba2 100%);
        border-radius: 0 6px 6px 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 80px;
        box-shadow: 
            0 30px 60px rgba(0,0,0,0.25),
            0 15px 30px rgba(0,0,0,0.15);
    }
    
    .magazine-cover-placeholder span {
        font-size: 18px;
        font-weight: 600;
        margin-top: 10px;
        text-transform: uppercase;
        letter-spacing: 3px;
    }
    
    /* Magazine Details */
    .magazine-details {
        padding: 20px 0;
    }
    
    .magazine-edition {
        display: inline-block;
        background: linear-gradient(135deg, var(--primary-bg-color) 0%, #764ba2 100%);
        color: #fff;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 15px;
    }
    
    .magazine-main-title {
        font-size: 32px;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 20px;
        line-height: 1.3;
    }
    
    .magazine-meta {
        display: flex;
        gap: 25px;
        margin-bottom: 20px;
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #666;
        font-size: 14px;
    }
    
    .meta-item i {
        color: var(--primary-bg-color);
    }
    
    .magazine-desc {
        color: #555;
        font-size: 15px;
        line-height: 1.7;
        margin-bottom: 30px;
    }
    
    .magazine-actions {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .magazine-actions .btn {
        padding: 12px 28px;
        font-weight: 600;
        border-radius: 8px;
    }
    
    .magazine-actions .btn-primary {
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.35);
    }
    
    .magazine-actions .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(102, 126, 234, 0.45);
    }
    
    /* Swiper Navigation */
    .magazine-nav-btn {
        width: 50px;
        height: 50px;
        background: #fff;
        border-radius: 50%;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        color: #333;
    }
    
    .magazine-nav-btn:after {
        font-size: 18px;
        font-weight: 700;
        color: #333;
    }
    
    .magazine-nav-btn:hover {
        background: var(--primary-bg-color);
    }
    
    .magazine-nav-btn:hover:after {
        color: #fff;
    }
    
    .swiper-button-prev.magazine-nav-btn {
        left: 0;
    }
    .swiper-button-next svg, .swiper-button-prev svg{
        height: 60% !important;
    }
    
    .swiper-button-next.magazine-nav-btn {
        right: 0;
    }
    .swiper-button-next, .swiper-button-prev{
        margin: 20px;
    }
    
    /* Swiper Pagination */
    .magazine-pagination {
        bottom: 0 !important;
    }
    
    .magazine-pagination .swiper-pagination-bullet {
        width: 10px;
        height: 10px;
        background: #ccc !important;
        opacity: 1 !important;
        transition: all 0.3s ease;
    }
    
    .magazine-pagination .swiper-pagination-bullet-active {
        width: 30px;
        border-radius: 5px;
        background: var(--primary-bg-color) !important;
    }
    
    /* Empty State */
    .empty-state {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        padding: 60px 40px;
    }
    
    .empty-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-bg-color) 0%, #764ba2 100%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        color: #fff;
    }
    
    /* Responsive */
    @media (max-width: 991px) {
        .magazine-cover-img,
        .magazine-cover-placeholder {
            width: 260px;
            height: 360px;
        }
        
        .magazine-main-title {
            font-size: 26px;
        }
        
        .magazine-slider-wrapper {
            padding: 0px 50px;
        }
    }
    
    @media (max-width: 768px) {
        .magazine-cover-img,
        .magazine-cover-placeholder {
            width: 220px;
            height: 300px;
        }
        
        .magazine-cover-placeholder {
            font-size: 60px;
        }
        
        .magazine-main-title {
            font-size: 22px;
        }
        
        .magazine-meta {
            flex-direction: column;
            gap: 10px;
        }
        
        .magazine-actions {
            flex-direction: column;
        }
        
        .magazine-actions .btn {
            width: 100%;
            justify-content: center;
        }
        
        .magazine-slider-wrapper {
            padding: 20px 40px;
        }
        
        .magazine-nav-btn {
            width: 40px;
            height: 40px;
        }
        
        .magazine-nav-btn:after {
            font-size: 14px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        new Swiper('.magazineSwiper', {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: true,
            speed: 600,
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.magazine-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });
    });
</script>