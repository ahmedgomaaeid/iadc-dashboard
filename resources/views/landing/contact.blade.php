<div class="bg-image-landing section pb-0" id="Contact">
    <div class="container">
        <div class="">
            <div class="card card-shadow reveal">
                <h4 class="text-center fw-semibold mt-7">Contact</h4>
                <span class="landing-title"></span>
                <h2 class="text-center fw-semibold mb-0 px-2">Get in Touch with <span
                        class="text-primary">US.</span></h2>
                <div class="card-body p-5 pb-6 text-dark">
                    <div class="statistics-info p-4">
                        <div class="row justify-content-center">
                            <div class="col-lg-9">
                                <div class="mt-3">
                                    <div class="text-dark">
                                        <div class="services-statistics reveal my-5">
                                            <div class="row text-center justify-content-center">
                                                <div class="col-xl-3 col-md-6 col-lg-6">
                                                    <a href="https://maps.app.goo.gl/ZC9Gewysqv4Y2w3z9" target="_blank" class="text-dark">
                                                        <div class="card">
                                                            <div class="card-body p-0">
                                                                <div class="counter-status">
                                                                    <div
                                                                        class="counter-icon bg-primary-transparent box-shadow-primary">
                                                                        <i
                                                                            class="fe fe-map-pin text-primary fs-23"></i>
                                                                    </div>
                                                                    <h4
                                                                        class="mb-2 fw-semibold">
                                                                        Location</h4>
                                                                    <p>Suez University, EG </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                                <div class="col-xl-3 col-md-6 col-lg-6">
                                                    <a href="tel:+201094908582" class="text-dark">
                                                        <div class="card">
                                                            <div class="card-body p-0">
                                                                <div class="counter-status">
                                                                    <div
                                                                        class="counter-icon bg-secondary-transparent box-shadow-secondary">
                                                                        <i
                                                                            class="fe fe-headphones text-secondary fs-23"></i>
                                                                    </div>
                                                                    <h4
                                                                        class="mb-2 fw-semibold">
                                                                        Phone</h4>
                                                                    <p class="mb-0"> +201094908582 </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                                <div class="col-xl-3 col-md-6 col-lg-6">
                                                    <a href="mailto:contact@iadcsuez.org" class="text-dark">
                                                        <div class="card">
                                                            <div class="card-body p-0">
                                                                <div class="counter-statuss">
                                                                    <div
                                                                        class="counter-icon bg-success-transparent box-shadow-success">
                                                                        <i
                                                                            class="fe fe-mail text-success fs-23"></i>
                                                                    </div>
                                                                    <h4
                                                                        class="mb-2 fw-semibold">
                                                                        Contact</h4>
                                                                    <p class="mb-0">
                                                                        contact@iadcsuez.org</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-9">
                                <div class="justify-content-center">
                                    <form id="contactForm" class="form-horizontal reveal revealrotate m-t-20">
                                        @csrf
                                        <div class="form-group">
                                            <div class="col-xs-12">
                                                <input class="form-control" type="text"
                                                    required="" placeholder="Name*" name="name" id="contact-name">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-xs-12">
                                                <input class="form-control" type="email"
                                                    required="" placeholder="Email*" name="email" id="contact-email">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-xs-12">
                                                <textarea class="form-control"
                                                    rows="5" name="message" id="contact-message" placeholder="Your Message*" required></textarea>
                                            </div>
                                        </div>
                                        <div class="">
                                            <button type="submit" id="contact-submit"
                                                class="btn btn-primary btn-rounded waves-effect waves-light">
                                                <span id="submit-text">Send Message</span>
                                                <span id="submit-loading" class="d-none">
                                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                    Sending...
                                                </span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>