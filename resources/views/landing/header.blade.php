<!-- app-Header -->
            <div class="app-header header">
                <div class="container-fluid main-container">
                    <div class="d-flex">
                        <a aria-label="Hide Sidebar" class="app-sidebar__toggle" data-bs-toggle="sidebar"
                            href="javascript:void(0)"></a>
                        <!-- sidebar-toggle-->
                        <a class="logo-horizontal " href="{{ route('index') }}">
                            <img src="{{route('index')}}/assets/images/brand/logo.png"
                                class="header-brand-img desktop-logo" alt="logo">
                            <img src="{{route('index')}}/assets/images/brand/logo-3.png"
                                class="header-brand-img light-logo1" alt="logo">
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
                                        <a href="register.html" class="btn ripple btn-min w-sm btn-outline-primary me-2"
                                            target="_blank">New User
                                        </a>
                                        <a href="login.html" class="btn ripple btn-min w-sm btn-primary me-2"
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
                                    <a class="navbar-brand ps-0 d-none d-lg-block" style="width: 150px;"
                                        href="{{ route('index') }}">
                                        <img alt="" class="logo-2"
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
                                            <a class="side-menu__item" data-bs-toggle="slide" href="#Features"><span
                                                    class="side-menu__label">Features</span></a>
                                        </li>
                                        <li class="slide">
                                            <a class="side-menu__item" data-bs-toggle="slide" href="#About"><span
                                                    class="side-menu__label">About</span></a>
                                        </li>
                                        <li class="slide">
                                            <a class="side-menu__item" data-bs-toggle="slide" href="#Faqs"><span
                                                    class="side-menu__label">Faq's</span></a>
                                        </li>
                                        <li class="slide">
                                            <a class="side-menu__item" data-bs-toggle="slide" href="#Blog"><span
                                                    class="side-menu__label">Blog</span></a>
                                        </li>
                                        <li class="slide">
                                            <a class="side-menu__item" data-bs-toggle="slide" href="#Clients"><span
                                                    class="side-menu__label">Clients</span></a>
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
                                        <a href="register.html" class="btn ripple btn-min w-sm btn-outline-primary me-2"
                                            target="_blank">New User
                                        </a>
                                        <a href="login.html" class="btn ripple btn-min w-sm btn-primary me-2"
                                            target="_blank">Login
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--/APP-SIDEBAR-->
                </div>
                <div class="demo-screen-headline main-demo main-demo-1 spacing-top overflow-hidden reveal" id="home">
                    <div class="container px-sm-0">
                        <div class="row">
                            <div class="col-xl-6 col-lg-6 mb-5 pb-5 animation-zidex pos-relative">
                                <h4 class="fw-semibold mt-7">Manage Your Business</h4>
                                <h1 class="text-start fw-bold">IADC Suez University Student Chapter <br><span
                                        class="text-primary" id="typewriter-text"
                                        data-text="Explore Your Potential"></span></h1>
                                <h6 class="pb-3">
                                    IADC Suez University Student Chapter is your gateway to the drilling industry.
                                    Founded in 2024, we bridge the gap between academia and the field. We empower
                                    students through expert insights and practical training, developing the technical
                                    skills and leadership mindset necessary to drive sustainable innovation in the
                                    energy sector.</h6>

                                <a href="https://social.iadcsuez.org" target="_blank"
                                    class="btn ripple btn-min w-lg mb-3 me-2 btn-primary"><i
                                        class="fe fe-play me-2"></i> Follow Us
                                </a>
                                <a href="" class="btn ripple btn-min w-lg btn-outline-primary mb-3 me-2"
                                    target="_blank"><i class="fe fe-eye me-2"></i>Discover More
                                </a>
                            </div>
                            <div class="col-xl-6 col-lg-6 my-auto">
                                <img src="{{route('index')}}/assets/images/landing/market4.png" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>