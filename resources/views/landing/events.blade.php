<style>
.swiper-pagination-bullet {
  width: 1.25rem !important;
  height: 0.25rem !important;
  border-radius: 7px !important;
  background: #fff !important;
}

.swiper-pagination-bullet-active {
  background: #fff !important;
}
.swiper-pagination-bullet {
  background: var(--primary-bg-color) !important;
}
.swiper-pagination-bullet:active {
  background: var(--primary-bg-color) !important;
}

</style>
<div class="bg-landing section bg-image-style">
    <div class="container">
        <div class="row">
            <h4 class="text-center fw-semibold">Events & Activities </h4>
            <span class="landing-title"></span>
            <h2 class="text-center fw-semibold">Explor Our <span class="text-primary">Events & Activities</span></h2>
            <div class="pricing-tabs">
                <div class="pri-tabs-heading text-center">
                    <ul class="nav nav-price">
                        <li><a class="active show" data-bs-toggle="tab" href="#upcoming">Upcoming Events</a></li>
                        <li><a data-bs-toggle="tab" href="#past">Past Events </a></li>
                        <li><a data-bs-toggle="tab" href="#visits">Visits</a></li>
                    </ul>
                </div>
                <div class="tab-content">
                    @include('landing.events.upcoming')

                    @include('landing.events.past')

                    @include('landing.events.visits')
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var swiper = new Swiper(".pagination-dynamic", {
        pagination: {
            el: ".swiper-pagination",
            dynamicBullets: true,
            clickable: true,
        },
        slidesPerView: 1,
        loop: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false
        },
        breakpoints: {
            768: {
                slidesPerView: 2,
                spaceBetween: 40,
            },
            1024: {
                slidesPerView: 2,
                spaceBetween: 50,
            },
            1400: {
                slidesPerView: 3,
                spaceBetween: 50,
            },
        },
    });
</script>