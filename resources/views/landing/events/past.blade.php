<div class="tab-pane active show" id="past">
    <div class="row d-flex align-items-center justify-content-center">
        <div class="swiper pagination-dynamic text-start swiper-initialized swiper-horizontal swiper-pointer-events">
            <div class="swiper-wrapper">
                @foreach ($past_events as $event)
                    <div class="swiper-slide mb-4">
                    <div class="card  testimonial-card h-100 article-card">
                        
                        <div class="d-flex align-items-center mb-3">
                            @if ($event->attendees_number)
                                <span class="badge bg-primary position-absolute top-0 start-0 m-3">
                                    <i class="fa fa-user fs-12 text-white"></i> +{{ $event->attendees_number }}
                                </span>
                            @endif
                            <a href="{{ route('eventPreview', $event->id) }}" class="w-100">
                                <img class="w-100 article-img" 
                                     src="{{ $event->image ? asset('storage/' . $event->image) : asset('assets/images/media/12.jpg') }}" 
                                     alt="{{ $event->name }}">
                            </a>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="mb-2">
                                <h5 class="fw-semibold mb-2">
                                    <a href="{{ route('eventPreview', $event->id) }}" class="text-dark article-title">
                                        {{ Str::limit($event->name, 45) }}
                                    </a>
                                </h5>
                                <p class="text-muted fs-13 mb-0">
                                    {{ Str::limit(strip_tags($event->description), 80) }}
                                </p>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center"> 
                                    <span class="text-muted">Date :
                                    </span> 
                                    <span class="text-primary d-block ms-1">{{ \Carbon\Carbon::parse($event->date_from)->format('d/m/Y') }}</span>
                                </div>
                                <div class="float-end fs-12 fw-semibold text-muted text-end"> 
                                    <a href="{{ route('eventPreview', $event->id) }}" target="_blank"
                                        class="btn ripple btn-min mb-3 me-2 btn-primary"><i
                                            class="fe fe-info me-2"></i> Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</div>