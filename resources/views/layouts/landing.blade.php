<!doctype html>
<html lang="en" dir="ltr">

<head>

    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="description" content="@yield('meta_description', 'IADC Suez University Student Chapter - Your gateway to the drilling industry. Join our community of petroleum engineering students for expert insights, practical training, career opportunities, and sustainable drilling practices.')">
    <meta name="author" content="Ahmed Gomaa Eid">
    <meta name="keywords" content="@yield('meta_keywords', 'IADC, IADC Suez, IADC Suez University, Student Chapter, drilling industry, petroleum engineering, oil and gas, Suez University, drilling contractors, energy sector, sustainable drilling, petroleum students, drilling training, IADC student chapter Egypt')">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="@yield('canonical_url', route('index'))" />
    
    <!-- Open Graph Meta Tags (Facebook, LinkedIn) -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('og_url', route('index'))">
    <meta property="og:title" content="@yield('og_title', 'IADC Suez University Student Chapter | Drilling Industry Leaders')">
    <meta property="og:description" content="@yield('og_description', 'IADC Suez University Student Chapter - Your gateway to the drilling industry. Join our community for expert insights, practical training, and career opportunities in petroleum engineering.')">
    <meta property="og:image" content="@yield('og_image', route('index') . '/assets/images/brand/og-image.jpg')">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="IADC Suez University Student Chapter">
    <meta property="og:locale" content="en_US">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="@yield('twitter_url', route('index'))">
    <meta name="twitter:title" content="@yield('twitter_title', 'IADC Suez University Student Chapter')">
    <meta name="twitter:description" content="@yield('twitter_description', 'Your gateway to the drilling industry. Join our community for expert insights, practical training, and career opportunities.')">
    <meta name="twitter:image" content="@yield('twitter_image', route('index') . '/assets/images/brand/og-image.jpg')">
    
    <!-- Additional SEO Meta Tags -->
    <meta name="theme-color" content="#ab1f2e">
    <meta name="msapplication-TileColor" content="#ab1f2e">
    <meta name="geo.region" content="EG">
    <meta name="geo.placename" content="Suez, Egypt">

    <!-- FAVICON -->
    <link rel="shortcut icon" type="image/x-icon" href="{{route('index')}}/assets/images/brand/logo-2.svg" />
    <link rel="apple-touch-icon" href="{{route('index')}}/assets/images/brand/logo-2.svg">

    <!-- TITLE -->
    <title>@yield('title', 'IADC Suez University Student Chapter')</title>

    <!-- BOOTSTRAP CSS -->
    <link id="style" href="{{route('index')}}/assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />

    <!-- STYLE CSS -->
    <link href="{{route('index')}}/assets/css/style.css?v=1" rel="stylesheet" />

    <!--- FONT-ICONS CSS -->
    <link href="{{route('index')}}/assets/css/icons.css?v=1" rel="stylesheet" />

    <!-- COLOR SKIN CSS -->
    <link id="theme" rel="stylesheet" type="text/css" media="all" href="{{route('index')}}/assets/colors/color1.css" />

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css"
        />

    <script src="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js"></script>
    @yield('css')

    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "EducationalOrganization",
        "name": "IADC Suez University Student Chapter",
        "alternateName": "IADC Suez",
        "url": "{{ route('index') }}",
        "logo": "{{ route('index') }}/assets/images/brand/logo-3.png",
        "description": "IADC Suez University Student Chapter is your gateway to the drilling industry. Founded in 2024, we bridge the gap between academia and the field. We empower students through expert insights and practical training.",
        "foundingDate": "2024-01",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Suez",
            "addressCountry": "Egypt"
        },
        "parentOrganization": {
            "@type": "Organization",
            "name": "International Association of Drilling Contractors",
            "alternateName": "IADC"
        },
        "sameAs": [
            "https://www.facebook.com/iadcsuez",
            "https://www.youtube.com/@iadcsu",
            "https://www.linkedin.com/company/iadc-suez-university",
            "https://www.instagram.com/iadcsusc"
        ],
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+201094908582",
            "email": "contact@iadcsuez.org",
            "contactType": "customer service"
        }
    }
    </script>
    @yield('structured_data')
</head>

<body class="app sidebar-mini ltr landing-page horizontal">

    <style>
        /* rotate loader */
        .loader-img {
            animation: rotate 2s linear infinite;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
    </style>
    <!-- GLOBAL-LOADER -->
    <div id="global-loader">
        <img src="{{route('index')}}/assets/images/brand/logo-2.svg" class="loader-img" alt="Loader" style="width: 70px;">
    </div>
    <!-- /GLOBAL-LOADER -->

    <!-- PAGE -->
    <div class="page">
        <div class="page-main">

            <!-- app-Header -->
            <div class="app-header header">
                <div class="container-fluid main-container">
                    <div class="d-flex">
                        <a aria-label="Hide Sidebar" class="app-sidebar__toggle" data-bs-toggle="sidebar"
                            href="javascript:void(0)"></a>
                        <!-- sidebar-toggle-->
                        <a class="logo-horizontal " href="{{ route('index') }}">
                            <img src="{{route('index')}}/assets/images/brand/logo.png"
                                class="header-brand-img desktop-logo" alt="IADC Suez University Student Chapter Logo">
                            <img src="{{route('index')}}/assets/images/brand/logo-3.png"
                                class="header-brand-img light-logo1" alt="IADC Suez University Student Chapter Logo">
                        </a>
                        <!-- LOGO -->
                        <div class="d-flex order-lg-2 ms-auto header-right-icons">
                            <button class="navbar-toggler navresponsive-toggler d-lg-none ms-auto" type="button"
                                data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent-4"
                                aria-controls="navbarSupportedContent-4" aria-expanded="false"
                                aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon fe fe-more-vertical"></span>
                            </button>
                            <div class="navbar navbar-collapse responsive-navbar p-0">
                                <div class="collapse navbar-collapse bg-white px-0" id="navbarSupportedContent-4">
                                    <!-- SEARCH -->
                                    <div class="header-nav-right p-5">
                                        <a href="{{ route('login') }}" class="btn ripple btn-min w-sm btn-primary me-2"
                                            target="_blank">Login
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /app-Header -->

            <div class="landing-top-header overflow-hidden">
                <div class="top sticky overflow-hidden">
                    <!--APP-SIDEBAR-->
                    <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
                    <div class="app-sidebar bg-transparent">
                        <div class="container">
                            <div class="row">
                                <div class="main-sidemenu navbar px-0">
                                    <a class="navbar-brand ps-0 d-none d-lg-block" style="width: 128px;"
                                        href="{{ route('index') }}">
                                        <img alt="IADC Suez University Student Chapter" class="logo-2"
                                            src="{{route('index')}}/assets/images/brand/logo-3.png">
                                    </a>
                                    <div class="slide-left disabled" id="slide-left"><svg
                                            xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24"
                                            viewBox="0 0 24 24">
                                            <path
                                                d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z" />
                                        </svg></div>
                                    <ul class="side-menu">
                                        <li class="slide">
                                            <a class="side-menu__item active" data-bs-toggle="slide" href="#home"><span
                                                    class="side-menu__label">Home</span></a>
                                        </li>
                                        <li class="slide">
                                            <a class="side-menu__item" data-bs-toggle="slide" href="#About"><span
                                                    class="side-menu__label">About Us</span></a>
                                        </li>
                                        <li class="slide">
                                            <a class="side-menu__item" data-bs-toggle="slide" href="#Events"><span
                                                    class="side-menu__label">Events</span></a>
                                        </li>
                                        <li class="slide">
                                            <a class="side-menu__item" data-bs-toggle="slide" href="#Articles"><span
                                                    class="side-menu__label">Knowledge Hub</span></a>
                                        </li>
                                        <li class="slide">
                                            <a class="side-menu__item" data-bs-toggle="slide" href="#Publications"><span
                                                    class="side-menu__label">Publications</span></a>
                                        </li>
                                        <li class="slide">
                                            <a class="side-menu__item" data-bs-toggle="slide" href="#Team"><span
                                                    class="side-menu__label">Our Team</span></a>
                                        </li>
                                        <li class="slide">
                                            <a class="side-menu__item" data-bs-toggle="slide" href="#Contact"><span
                                                    class="side-menu__label">Contact</span></a>
                                        </li>
                                    </ul>
                                    <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg"
                                            fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                                            <path
                                                d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z" />
                                        </svg></div>
                                    <div class="header-nav-right d-none d-lg-block">
                                        <a href="{{ route('login') }}" class="btn ripple btn-min w-sm btn-primary me-2"
                                            target="_blank">Login
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--/APP-SIDEBAR-->
                </div>
                @yield('header')
            </div>

            <!--app-content open-->
            <div class="main-content mt-0">
                <div class="side-app">

                    <!-- CONTAINER -->
                    <div class="main-container">
                        @yield('content')
                    </div>
                    <!-- CONTAINER CLOSED-->
                </div>
            </div>
            <!--app-content closed-->
        </div>

        <!-- FOOTER OPEN -->
        <div class="demo-footer">
            <div class="container">
                <div class="row">
                    <div class="card">
                        <div class="card-body">
                            <div class="top-footer">
                                <div class="row">
                                    <div class="col-lg-5 col-sm-12 col-md-12 reveal revealleft">
                                        <h6>About</h6>
                                        <p>Founded in January 2024 and affiliated with the International Association of Drilling Contractors (IADC), our chapter is your gateway to a world of opportunities in the drilling industry. We aim to build a community of passionate students, bridge the gap between academia and industry through expert insights, practical experiences, and career opportunities, and advocate for sustainable and innovative drilling practices.
                                        </p>
                                        <p class="mb-5 mb-lg-2">
                                        </p>
                                    </div>
                                    <div class="col-lg-3 col-sm-12 col-md-5 reveal revealleft">
                                        <h6>Pages</h6>
                                        <ul class="list-unstyled mb-5 mb-lg-0">
                                            <li><a href="#Home">Home</a></li>
                                            <li><a href="#About">About Us</a></li>
                                            <li><a href="#Events">Events</a></li>
                                            <li><a href="#Articles">Knowledge Hub</a></li>
                                            <li><a href="#Publications">Publications</a></li>
                                            <li><a href="#Contact">Contact</a></li>
                                            <li><a href="{{route('privacy-policy')}}">Privacy Policy</a></li>
                                        </ul>
                                    </div>
                                    <div class="col-lg-4 col-sm-12 col-md-6 reveal revealleft">
                                        <div class="">
                                            <a href="{{ route('index') }}"><img loading="lazy" alt="IADC Suez University Student Chapter Logo" class="logo mb-3"
                                                    src="{{route('index')}}/assets/images/brand/logo-3.png"></a>
                                            <p>Join our community to stay up to date with our latest initiatives. Enter your email to ensure you never miss an update.</p>
                                            <form id="newsletterForm" class="form-group">
                                                @csrf
                                                <div class="input-group">
                                                    <input type="email" id="newsletter-email" class="form-control"
                                                        placeholder="Enter your email"
                                                        aria-label="Newsletter email"
                                                        required>
                                                    <button class="btn btn-primary" type="submit" id="newsletter-submit">
                                                        <span id="newsletter-text">Subscribe</span>
                                                        <span id="newsletter-loading" class="d-none">
                                                            <span class="spinner-border spinner-border-sm" role="status"></span>
                                                        </span>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="btn-list mt-6">
                                            <a href="https://www.facebook.com/iadcsuez" target="_blank" rel="noopener noreferrer" class="btn btn-icon rounded-pill" aria-label="Follow IADC Suez on Facebook"><i
                                                    class="fa fa-facebook"></i></a>
                                            <a href="https://www.youtube.com/@iadcsu" target="_blank" rel="noopener noreferrer" class="btn btn-icon rounded-pill" aria-label="Subscribe to IADC Suez YouTube Channel"><i
                                                    class="fa fa-youtube"></i></a>
                                            <a href="https://www.linkedin.com/company/iadc-suez-university" target="_blank" rel="noopener noreferrer" class="btn btn-icon rounded-pill" aria-label="Connect with IADC Suez on LinkedIn"><i
                                                    class="fa fa-linkedin"></i></a>
                                            <a href="https://www.instagram.com/iadcsusc" target="_blank" rel="noopener noreferrer" class="btn btn-icon rounded-pill" aria-label="Follow IADC Suez on Instagram"><i
                                                    class="fa fa-instagram"></i></a>
                                        </div>
                                        <hr>
                                    </div>
                                </div>
                            </div>
                            <footer class="main-footer px-0 pb-0 text-center d-none">
                                <div class="row ">
                                    <div class="col-md-12 col-sm-12">
                                        Copyright © <span id="year"></span> <a href="javascript:void(0)">IADC Suez</a>.
                                         All rights reserved.
                                    </div>
                                </div>
                            </footer>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- FOOTER CLOSED -->
    </div>

    <!-- BACK-TO-TOP -->
    <a href="#top" id="back-to-top"><i class="fa fa-angle-up"></i></a>

    <!-- JQUERY JS -->
    <script src="{{route('index')}}/assets/js/jquery.min.js"></script>

    <!-- BOOTSTRAP JS -->
    <script src="{{route('index')}}/assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="{{route('index')}}/assets/plugins/bootstrap/js/bootstrap.min.js"></script>

    <!-- COUNTERS JS-->
    <script src="{{route('index')}}/assets/plugins/counters/counterup.min.js"></script>
    <script src="{{route('index')}}/assets/plugins/counters/waypoints.min.js"></script>
    <script src="{{route('index')}}/assets/plugins/counters/counters-1.js"></script>

    <!-- Perfect SCROLLBAR JS-->
    <script src="{{route('index')}}/assets/plugins/owl-carousel/owl.carousel.js"></script>
    <script src="{{route('index')}}/assets/plugins/company-slider/slider.js"></script>

    <!-- SIDE-MENU JS -->
    <script src="{{route('index')}}/assets/plugins/sidemenu/sidemenu.js"></script>

    <!-- Star Rating Js-->
    <script src="{{route('index')}}/assets/plugins/rating/jquery-rate-picker.js"></script>
    <script src="{{route('index')}}/assets/plugins/rating/rating-picker.js"></script>

    <!-- Star Rating-1 Js-->
    <script src="{{route('index')}}/assets/plugins/ratings-2/jquery.star-rating.js"></script>
    <script src="{{route('index')}}/assets/plugins/ratings-2/star-rating.js"></script>

    <!-- Sticky js -->
    <script src="{{route('index')}}/assets/js/sticky.js"></script>

    <!-- CUSTOM JS -->
    <script src="{{route('index')}}/assets/js/landing.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Newsletter Form Handler -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const newsletterForm = document.getElementById('newsletterForm');
            if (newsletterForm) {
                newsletterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const emailInput = document.getElementById('newsletter-email');
                    const submitBtn = document.getElementById('newsletter-submit');
                    const submitText = document.getElementById('newsletter-text');
                    const submitLoading = document.getElementById('newsletter-loading');
                    const email = emailInput.value.trim();
                    
                    if (!email) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Email Required',
                            text: 'Please enter your email address.',
                            confirmButtonColor: '#ab1f2e'
                        });
                        return;
                    }
                    
                    // Show loading
                    submitBtn.disabled = true;
                    submitText.classList.add('d-none');
                    submitLoading.classList.remove('d-none');
                    
                    fetch('{{ route("newsletter.subscribe") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('#newsletterForm input[name="_token"]').value
                        },
                        body: JSON.stringify({ email: email })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Subscribed!',
                                text: data.message,
                                confirmButtonColor: '#ab1f2e'
                            });
                            emailInput.value = '';
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: data.message || 'Something went wrong.',
                                confirmButtonColor: '#ab1f2e'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Something went wrong. Please try again.',
                            confirmButtonColor: '#ab1f2e'
                        });
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitText.classList.remove('d-none');
                        submitLoading.classList.add('d-none');
                    });
                });
            }
        });
    </script>

    @yield('scripts')

</body>

</html>