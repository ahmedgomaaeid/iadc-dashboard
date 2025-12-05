<!doctype html>
<html lang="en" dir="ltr">

<head>

    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Sash – Bootstrap 5  Admin & Dashboard Template">
    <meta name="author" content="Spruko Technologies Private Limited">
    <meta name="keywords"
        content="admin,admin dashboard,admin panel,admin template,bootstrap,clean,dashboard,flat,jquery,modern,responsive,premium admin templates,responsive admin,ui,ui kit.">

    <!-- FAVICON -->
    <link rel="shortcut icon" type="image/x-icon" href="{{route('index')}}/assets/images/brand/logo-2.svg" />

    <!-- TITLE -->
    <title>@yield('title', 'Dashboard')</title>

    <!-- BOOTSTRAP CSS -->
    <link id="style" href="{{route('index')}}/assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />

    <!-- STYLE CSS -->
    <link href="{{route('index')}}/assets/style.css" rel="stylesheet" />
    <link href="{{route('index')}}/assets/css/style.css" rel="stylesheet" />
    <link href="{{route('index')}}/assets/css/dark-style.css" rel="stylesheet" />
    <link href="{{route('index')}}/assets/css/transparent-style.css" rel="stylesheet">
    <link href="{{route('index')}}/assets/css/skin-modes.css" rel="stylesheet" />

    <!--- FONT-ICONS CSS -->
    <link href="{{route('index')}}/assets/css/icons.css" rel="stylesheet" />

    <!-- COLOR SKIN CSS -->
    <link id="theme" rel="stylesheet" type="text/css" media="all" href="{{route('index')}}/assets/colors/color1.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @yield('css')
</head>

<body class="app sidebar-mini ltr light-mode">

    <!-- GLOBAL-LOADER -->
    <div id="global-loader">
        <img src="{{route('index')}}/assets/images/loader.svg" class="loader-img" alt="Loader">
    </div>
    <!-- /GLOBAL-LOADER -->

    <!-- PAGE -->
    <div class="page">
        <div class="page-main">

            <!-- app-Header -->
            <div class="app-header header sticky">
                <div class="container-fluid main-container">
                    <div class="d-flex">
                        <a aria-label="Hide Sidebar" class="app-sidebar__toggle" data-bs-toggle="sidebar" href="javascript:void(0)"></a>
                        <!-- sidebar-toggle-->
                        <a class="logo-horizontal " href="{{route('admin.dashboard')}}">
                            <img src="{{asset('assets/images/brand/logo.png')}}" class="header-brand-img desktop-logo" alt="logo" style="height: 60px;">
                            <img src="{{asset('assets/images/brand/logo-3.png')}}" class="header-brand-img light-logo1"
                                alt="logo" style="height: 60px;">
                        </a>

                        <div class="d-flex order-lg-2 ms-auto header-right-icons">

                            <!-- SEARCH -->
                            <button class="navbar-toggler navresponsive-toggler d-lg-none ms-auto" type="button"
                                data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent-4"
                                aria-controls="navbarSupportedContent-4" aria-expanded="false"
                                aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon fe fe-more-vertical"></span>
                            </button>
                            <div class="navbar navbar-collapse responsive-navbar p-0">
                                <div class="collapse navbar-collapse" id="navbarSupportedContent-4">
                                    <div class="d-flex order-lg-2">

                                        <div class="d-flex country">
                                            <a class="nav-link icon theme-layout nav-link-bg layout-setting">
                                                <span class="dark-layout"><i class="fe fe-moon"></i></span>
                                                <span class="light-layout"><i class="fe fe-sun"></i></span>
                                            </a>
                                        </div>
                                        <!-- Theme-Layout -->
                                        <div class="dropdown  d-flex shopping-cart" style="display:none !important;">
                                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">

                                                <div class="header-dropdown-list message-menu">

                                                </div>

                                            </div>
                                        </div>

                                        <!-- FULL-SCREEN -->
                                        <div class="dropdown d-flex">
                                            <a class="nav-link icon full-screen-link nav-link-bg">
                                                <i class="fe fe-minimize fullscreen-button"></i>
                                            </a>
                                        </div>


                                        <!-- SIDE-MENU -->
                                        <div class="dropdown d-flex profile-1">
                                            <a href="javascript:void(0)" data-bs-toggle="dropdown" class="nav-link leading-none d-flex">
                                                @if(Auth::guard('admin')->user()->image)
                                                    <img src="{{ asset('storage/' . Auth::guard('admin')->user()->image) }}" alt="profile-user"
                                                        class="avatar profile-user brround cover-image">
                                                @else
                                                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::guard('admin')->user()->name) }}&size=200&background=random" alt="profile-user"
                                                        class="avatar profile-user brround cover-image" onerror="this.onerror=null; this.src='{{ asset('assets/images/users/user.jpg') }}';">
                                                @endif
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                                <div class="drop-heading">
                                                    <div class="text-center">
                                                        <h5 class="text-dark mb-0 fs-14 fw-semibold">{{ Auth::guard('admin')->user()->name }}</h5>
                                                        <small class="text-muted">Administrator</small>
                                                    </div>
                                                </div>
                                                <div class="dropdown-divider m-0"></div>
                                                <a class="dropdown-item" href="{{ route('admin.profile.edit') }}">
                                                    <i class="dropdown-icon fe fe-user"></i> Edit Profile
                                                </a>
                                                <a class="dropdown-item" href="{{route('admin.logout')}}">
                                                    <i class="dropdown-icon fe fe-alert-circle"></i> Sign out
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /app-Header -->

            <!--APP-SIDEBAR-->

                <div class="sticky">
                    <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
                    <div class="app-sidebar">
                        <div class="side-header">
                            <a class="header-brand1" href="{{route('admin.dashboard')}}" style="width: 150px;">
                                <img src="{{ asset('assets/images/brand/logo.png') }}" class="header-brand-img desktop-logo" alt="logo">
                                <img src="{{ asset('assets/images/brand/logo-1.png') }}" class="header-brand-img toggle-logo"
                                    alt="logo">
                                <img src="{{ asset('assets/images/brand/logo-2.svg') }}" class="header-brand-img light-logo" alt="logo">
                                <img src="{{ asset('assets/images/brand/logo-3.png') }}" class="header-brand-img light-logo1"
                                    alt="logo">
                            </a>
                            <!-- LOGO -->
                        </div>

                            <div class="main-sidemenu">
                                <div class="slide-left disabled" id="slide-left"><svg xmlns="http://www.w3.org/2000/svg"
                                        fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                                        <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z" />
                                    </svg></div>
                                <ul class="side-menu">
                                    <li class="sub-category">
                                        <h3>Home</h3>
                                    </li>
                                    <li class="slide">
                                        <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{route('admin.dashboard')}}"><i
                                                class="side-menu__icon fe fe-home"></i><span
                                                class="side-menu__label">Home</span></a>
                                    </li>

                                    <li class="sub-category">
                                        <h3>Committees</h3>
                                    </li>
                                    <li class="slide">
                                        <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('admin.fields.index') }}"><i
                                                class="side-menu__icon fe fe-layers"></i><span
                                                class="side-menu__label">Fields</span></a>
                                    </li>
                                    <li class="slide">
                                        <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('admin.committees.index') }}"><i
                                                class="side-menu__icon fe fe-briefcase"></i><span
                                                class="side-menu__label">Committees</span></a>
                                    </li>
                                    <li class="sub-category">
                                        <h3>Members</h3>
                                    </li>
                                    <li class="slide">
                                        <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('admin.highboards.index') }}"><i
                                                class="side-menu__icon fe fe-user-check"></i><span
                                                class="side-menu__label">Highboard</span></a>
                                    </li>
                                    <li class="slide">
                                        <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('admin.boards.index') }}"><i
                                                class="side-menu__icon fe fe-users"></i><span
                                                class="side-menu__label">Board</span></a>
                                    </li>
                                    <li class="slide">
                                        <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('admin.members.index') }}"><i
                                                class="side-menu__icon fe fe-user"></i><span
                                                class="side-menu__label">Members</span></a>
                                    </li>
                                    <li class="sub-category">
                                        <h3>Content</h3>
                                    </li>
                                    <li class="slide">
                                        <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('admin.quizzes.index') }}"><i
                                                class="side-menu__icon fe fe-award"></i><span
                                                class="side-menu__label">Quizzes</span></a>
                                    </li>
                                    <li class="slide">
                                        <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('admin.dynamic-forms.index') }}"><i
                                                class="side-menu__icon fe fe-file-text"></i><span
                                                class="side-menu__label">Dynamic Forms</span></a>
                                    </li>
                                    <li class="sub-category">
                                        <h3>Logout</h3>
                                    </li>
                                    <li class="slide">
                                        <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{route('admin.logout')}}"><i
                                                class="side-menu__icon icon icon-logout"></i><span
                                                class="side-menu__label">Logout</span></a>
                                    </li>
                                </ul>
                                <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                                        width="24" height="24" viewBox="0 0 24 24">
                                        <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z" />
                                    </svg></div>
                            </div>

                    </div>
                    <!--/APP-SIDEBAR-->
                </div>
                <!--app-content open-->
                <div class="main-content app-content mt-0">
                    <div class="side-app">

                        <!-- CONTAINER -->
                        <div class="main-container container-fluid">
                            @yield('content')

                        </div>
                        <!-- CONTAINER END -->
                    </div>
                </div>
                <!--app-content close-->


        </div>



        

    </div>

    <!-- BACK-TO-TOP -->
    <a href="#top" id="back-to-top"><i class="fa fa-angle-up"></i></a>

    <!-- JQUERY JS -->
    <script src="{{route('index')}}/assets/js/jquery.min.js"></script>

    <!-- BOOTSTRAP JS -->
    <script src="{{route('index')}}/assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="{{route('index')}}/assets/plugins/bootstrap/js/bootstrap.min.js"></script>

    <!-- SPARKLINE JS-->
    <script src="{{route('index')}}/assets/js/jquery.sparkline.min.js"></script>

    <!-- Sticky js -->
    <script src="{{route('index')}}/assets/js/sticky.js"></script>




    <!-- SIDEBAR JS -->
    <script src="{{route('index')}}/assets/plugins/sidebar/sidebar.js"></script>

    <!-- Perfect SCROLLBAR JS-->
    <script src="{{route('index')}}/assets/plugins/p-scroll/perfect-scrollbar.js"></script>
    <script src="{{route('index')}}/assets/plugins/p-scroll/pscroll.js"></script>
    <script src="{{route('index')}}/assets/plugins/p-scroll/pscroll-1.js"></script>

    <!-- SIDE-MENU JS-->
    <script src="{{route('index')}}/assets/plugins/sidemenu/sidemenu.js"></script>

    <!-- INTERNAL INDEX JS -->
    <script src="{{route('index')}}/assets/js/index1.js"></script>

    <!-- Color Theme js -->
    <script src="{{route('index')}}/assets/js/themeColors.js"></script>

    <!-- CUSTOM JS -->
    <script src="{{route('index')}}/assets/js/custom.js"></script>
    @yield('scripts')


</body>

</html>
