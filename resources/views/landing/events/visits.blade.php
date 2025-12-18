<div class="tab-pane pb-0" id="visits">
    <div class="row d-flex align-items-center justify-content-center">
        <div class="swiper pagination-dynamic text-start swiper-initialized swiper-horizontal swiper-pointer-events">
            <div class="swiper-wrapper">
                @foreach ($visits as $event)
                    <div class="swiper-slide">
                    <div class="card  testimonial-card">
                        
                        <div class="d-flex align-items-center mb-3">
                            @if ($event->attendees_number)
                                <span class="badge bg-primary position-absolute top-0 start-0 m-3">
                                    <i class="fa fa-user fs-12 text-white"></i> +{{ $event->attendees_number }}
                                </span>
                            @endif
                            <img src="{{ asset('storage/'.$event->image) }}" class="w-100">
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <span class="text-muted"> {{  $event->name }} </span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center"> 
                                    <span class="text-muted">Date :
                                    </span> 
                                    <span class="text-primary d-block ms-1">{{ \Carbon\Carbon::parse($event->date_from)->format('d/m/Y') }}</span>
                                </div>
                                <div class="float-end fs-12 fw-semibold text-muted text-end"> 
                                    <a href="{{ route('eventPreview', $event->id) }}" target="_blank"
                                        class="btn ripple btn-min w-lg mb-3 me-2 btn-primary"><i
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