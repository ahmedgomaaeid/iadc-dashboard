<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title', 'IADC Suez')</title>
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
        <link rel="icon" type="image/x-icon" href="{{ asset('images/IADC Icon.png') }}">
        <style>
            .registration-container {
                max-width: 700px;
                margin: 50px auto;
                padding: 0;
            }

            .registration-card {
                background-color: #fff;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                overflow: hidden;
            }

            .card-header {
                background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
                color: white;
                padding: 40px 30px;
                text-align: center;
            }

            .card-header h1 {
                font-size: 2rem;
                font-weight: 700;
                margin-bottom: 10px;
            }

            .card-header p {
                font-size: 1rem;
                opacity: 0.9;
                margin: 0;
            }

            .card-body {
                padding: 40px 30px;
            }

            .form-label {
                font-weight: 600;
                color: #374151;
                margin-bottom: 8px;
                font-size: 0.95rem;
            }

            .form-control {
                border: 2px solid #e5e7eb;
                border-radius: 10px;
                padding: 12px 15px;
                font-size: 1rem;
                transition: all 0.3s ease;
            }

            .form-control:focus {
                border-color: var(--primary-color);
                box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            }

            .input-group {
                position: relative;
            }

            .input-icon {
                position: absolute;
                left: 15px;
                top: 50%;
                transform: translateY(-50%);
                color: #9ca3af;
                z-index: 10;
            }

            .form-control.with-icon {
                padding-left: 45px;
            }

            .btn-register {
                background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
                border: none;
                border-radius: 10px;
                padding: 15px;
                font-size: 1.1rem;
                font-weight: 600;
                color: white;
                transition: all 0.3s ease;
                margin-top: 20px;
            }

            .btn-register:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4);
            }

            .alert {
                border-radius: 10px;
                border: none;
            }

            .alert-success {
                background-color: #d1fae5;
                color: #065f46;
            }

            .alert-danger {
                background-color: #fee2e2;
                color: #991b1b;
            }

            .alert ul {
                margin-bottom: 0;
                padding-left: 20px;
            }

            @media (max-width: 768px) {
                .card-header h1 {
                    font-size: 1.5rem;
                }

                .card-body {
                    padding: 30px 20px;
                }
            }
        </style>
        @yield('styles')
    </head>
    <body>
        <header class="header">
            <nav class="container">
                @if(isset($form) && $form->id == 14)
                    <img src="{{ asset('images/logo UH.webp') }}" alt="Unconventional Highboard Logo" class="logo">
                @else
                    <img src="{{ asset('images/logo.png') }}" alt="IADC Logo" class="logo">
                @endif
            </nav>
        </header>

        <main class="container">
            <div class="registration-container">
                <div class="registration-card">
                    <div class="card-header">
                        <h1><i class="fas fa-graduation-cap"></i>@yield('subtitle', 'Registration')</h1>
                        <p>@yield('title', 'IADC Suez')</p>
                    </div>
                    @yield('form-img', '')

                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i> <strong>Please fix the following errors:</strong>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @yield('content')
                    </div>
                </div>
            </div>
        </main>

        <footer class="footer">
            <p>Explore Your Potential</p>
        </footer>
        <!-- JQUERY JS -->
    <script src="{{route('index')}}/assets/js/jquery.min.js"></script>
        <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        @if(session('registration_success'))
        <script>
            Swal.fire({
                title: '<strong>Registration Successful!</strong>',
                html: '<p style="font-size: 1.1rem; color: #666;">Thank you for registering. Join our community!</p>',
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: '<i class="fab fa-telegram"></i> Join Telegram Channel',
                cancelButtonText: '<i class="fas fa-globe"></i> Follow Us Here',
                confirmButtonColor: '#0088cc',
                cancelButtonColor: '#b4120d',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'swal-telegram-btn',
                    cancelButton: 'swal-website-btn'
                },
                buttonsStyling: true,
                width: '600px',
                padding: '2rem'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'https://t.me/IADCsu';
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    window.location.href = 'https://social.iadcsuez.org/';
                }
            });
        </script>
        <style>
            .swal-telegram-btn, .swal-website-btn {
                font-size: 1.1rem !important;
                font-weight: 600 !important;
                padding: 12px 30px !important;
                border-radius: 8px !important;
                transition: all 0.3s ease !important;
            }
            .swal-telegram-btn:hover {
                transform: translateY(-2px) !important;
                box-shadow: 0 5px 15px rgba(0, 136, 204, 0.4) !important;
            }
            .swal-website-btn:hover {
                transform: translateY(-2px) !important;
                box-shadow: 0 5px 15px rgba(180, 18, 13, 0.4) !important;
            }
        </style>
        @endif
        @yield('scripts')
    </body>
</html>
