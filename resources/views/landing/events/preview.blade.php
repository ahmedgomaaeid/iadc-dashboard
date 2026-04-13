@extends('layouts.landing')

@section('header')
<div class="jumps-prevent" style="padding-top: 67.5px;"></div>
@endsection

@section('title', 'IADC Suez - '.$event->name)

@section('css')
<style>
    body {
        background-color: #f0f0f5 !important;
    }
    .top {
        background-color: white !important;
    }
    /* Gallery Carousel Styles */
    .event-gallery-swiper {
        width: 100%;
        border-radius: 12px 12px 0 0;
        overflow: hidden;
        position: relative;
    }
    .event-gallery-swiper .swiper-slide img {
        width: 100%;
        height: 500px;
        object-fit: cover;
    }
    @media (max-width: 768px) {
        .event-gallery-swiper .swiper-slide img {
            height: 300px;
        }
    }
    
    /* Navigation Arrows - Glassmorphism & Hover Reveal */
    .event-gallery-swiper .swiper-button-next,
    .event-gallery-swiper .swiper-button-prev {
        color: #fff;
        background: rgba(0, 0, 0, 0.25);
        backdrop-filter: blur(4px);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        transition: all 0.3s ease;
        opacity: 0; /* Hidden by default for clean look */
        transform: scale(0.9);
    }

    /* Show on hover */
    .event-gallery-swiper:hover .swiper-button-next,
    .event-gallery-swiper:hover .swiper-button-prev {
        opacity: 1;
        transform: scale(1);
    }
    
    .event-gallery-swiper .swiper-button-next:hover,
    .event-gallery-swiper .swiper-button-prev:hover {
        background: rgba(180, 18, 13, 0.9); /* Brand color */
        box-shadow: 0 0 15px rgba(180, 18, 13, 0.4);
    }
    
    .event-gallery-swiper .swiper-button-next::after,
    .event-gallery-swiper .swiper-button-prev::after {
        font-size: 16px;
        font-weight: bold;
    }
    
    /* Clean Pagination */
    .event-gallery-swiper .swiper-pagination {
        bottom: 20px !important;
        z-index: 20;
    }
    .event-gallery-swiper .swiper-pagination-bullet {
        width: 8px;
        height: 8px;
        background: rgba(255, 255, 255, 0.8);
        opacity: 0.5;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        margin: 0 5px !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .event-gallery-swiper .swiper-pagination-bullet-active {
        opacity: 1;
        background: #fff; /* White looks cleaner on dark overlay */
        width: 24px;
        border-radius: 4px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }

    /* Gradient Overlay for Bottom Contrast */
    .event-gallery-swiper::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 100px;
        background: linear-gradient(to top, rgba(0,0,0,0.4), transparent);
        pointer-events: none;
        z-index: 10;
    }

    .single-image-card {
        border-radius: 12px 12px 0 0;
        overflow: hidden;
    }
    .single-image-card img {
        width: 100%;
        height: auto;
        max-height: 500px;
        object-fit: cover;
    }
    .swiper-button-next svg, .swiper-button-prev svg{
        width: 60%;
        height: 60%;
    }
</style>
@endsection

@section('content')
    <div class="container container-fluid my-5">

        <div class="row">
            <div class="col-xl-{{ ($event->partners->count() > 0 || $event->communityPartners->count() > 0) ? '8' : '12' }}">
                <div class="card">
                    {{-- Gallery Carousel or Single Image --}}
                    @if($event->images->count() > 0)
                        <div class="event-gallery-swiper swiper" id="eventGallery">
                            <div class="swiper-wrapper">
                                @foreach($event->images as $image)
                                    <div class="swiper-slide">
                                        <img src="{{ asset('storage/' . $image->image) }}" alt="{{ $event->name }}">
                                    </div>
                                @endforeach
                            </div>
                            @if($event->images->count() > 0)
                                <div class="swiper-button-next"></div>
                                <div class="swiper-button-prev"></div>
                            @endif
                            <div class="swiper-pagination"></div>
                        </div>
                    @elseif($event->image)
                        <img class="card-img-top " src="{{ asset('storage/' . $event->image) }}" alt="Card image cap">

                    @endif
                    
                    <div class="card-body">
                        <div class="d-md-flex">
                            <a href="javascript:void(0);" class="d-flex me-2 mb-2"><i class="fe fe-calendar fs-16 me-1 p-3 bg-primary-transparent text-primary bradius"></i>
                                <div class="mt-0 mt-3 ms-1 text-muted font-weight-semibold">{{ \Carbon\Carbon::parse($event->date_from)->format('d-m-Y') }} @if($event->date_to) to {{ \Carbon\Carbon::parse($event->date_to)->format('d-m-Y') }} @endif</div>
                            </a>
                            <a href="javascript:void(0);" class="d-flex me-2 mb-2"><i class="fa fa-location-arrow fs-16 me-1 p-3 bg-primary-transparent text-primary bradius"></i>
                                <div class="mt-0 mt-3 ms-1 text-muted font-weight-semibold">{{ $event->place }} </div>
                            </a>
                            @if($event->attendees_number)
                                <a href="javascript:void(0);" class="d-flex me-2 mb-2"><i class="fa fa-users fs-16 me-1 p-3 bg-primary-transparent text-primary bradius"></i>
                                    <div class="mt-0 mt-3 ms-1 text-muted font-weight-semibold">+{{ $event->attendees_number }}</div>
                                </a>
                            @endif
                            @if($event->register_active && $event->register_link)
                                <div class="ms-auto">
                                    <a href="{{ $event->register_link }}" class="btn btn-primary">
                                        <i class="fe fe-user-plus"></i> Register Now
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <h3><a href="javascript:void(0)">{{ $event->name }}</a></h3>
                        <p class="card-text">{!! $event->description !!}</p>

                        <!-- Links -->
                        @if($event->links->count() > 0)
                            <div class="mt-4 mb-3">
                                <h5 class="fw-bold mb-3" style="color: #333; font-size: 1rem;"><i class="fe fe-link me-2 text-primary"></i>Links</h5>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($event->links as $link)
                                        <a href="{{ $link->url }}" target="_blank" class="btn btn-outline-light text-dark border shadow-sm rounded-pill px-3 py-2 d-flex align-items-center event-link-badge" style="transition: all 0.2s ease;">
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2" style="width: 20px; height: 20px;">
                                                <i class="fe fe-external-link text-primary" style="font-size: 10px;"></i>
                                            </div>
                                            <span class="fw-semibold" style="font-size: 0.9rem;">{{ $link->name }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                @if($event->communityPartners->count() > 0)
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title fw-bold">
                                        <i class="fe fe-users me-2 text-primary"></i>Community Partners
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-wrap align-items-center justify-content-center gap-4 py-2">
                                        @foreach($event->communityPartners as $partner)
                                            <div class="text-center" style="transition: transform 0.2s ease;">
                                                <img src="{{ asset('storage/' . $partner->image) }}"
                                                    alt="Community Partner"
                                                    style="height: 150px; max-width: 160px; object-fit: contain; filter: grayscale(20%); transition: filter 0.3s ease;"
                                                    onmouseover="this.style.filter='grayscale(0%)'; this.parentElement.style.transform='translateY(-3px)'"
                                                    onmouseout="this.style.filter='grayscale(20%)'; this.parentElement.style.transform='translateY(0)'">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @if($event->partners->count() > 0)
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Sponsors</div>
                        </div>
                        <div class="card-body">
                            <div class="">
                                @foreach ($event->partners as $partner)
                                <div class="d-flex overflow-visible mb-2">
                                    <img class="bradius me-3" style="height: 64px;" src="{{ asset('storage/' . $partner->image) }}" alt="avatar-img">
                                    <div class="media-body valign-middle" style="padding-top: 18px;">
                                        <span class="fw-semibold badge bg-success-transparent rounded-pill text-success p-2 px-3">{{ $partner->type }} Sponsor</a>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    @php
        $totalSlides = ($event->image ? 1 : 0) + $event->images->count();
    @endphp
    
    @if($totalSlides > 1)
        // Initialize main gallery swiper with navigation
        var eventGallery = new Swiper("#eventGallery", {
            spaceBetween: 0,
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            effect: "slide",
            fadeEffect: {
                crossFade: true
            },
        });
    @elseif($totalSlides == 1)
        // Single image, just initialize for consistency
        var eventGallery = new Swiper("#eventGallery", {
            loop: false,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
        });
    @endif
</script>
@endsection