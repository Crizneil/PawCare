<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Join the Pack - PawCare</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/newicon.png') }}">
    <link href="{{ asset('assets/css/flaticon.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/sass/style.css') }}" rel="stylesheet" />
    <style>
        body {
            background: #fdfaf6;
            font-family: sans-serif;
        }
    </style>
</head>

<body>
    <!-- Page Title -->
    <section class="page-title" style="padding: 100px 0; background: #fdfaf6;">
        <div class="container">
            <div class="row">
                <div class="col col-xs-12">
                    <div class="breadcumb-wrap" style="text-align: center;">
                        <h2 class="fw-bold" style="color: #2c3e50; font-size: 3rem; margin-bottom: 15px;">Join the Pack
                            🐾</h2>
                        <p style="font-size: 1.2rem; color: #666; max-width: 600px; margin: 0 auto;">To ensure the
                            highest quality of care and maintain accurate medical records, PawCare requires all new pet
                            owners to complete a quick physical onboarding process.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Onboarding Instructions -->
    <section class="onboarding-section" style="padding: 80px 0; background: #fff;">
        <div class="container">
            <div class="row justify-content-center">

                <div class="col-lg-5 col-md-12 mb-5">
                    <div class="card border-0 shadow-lg rounded-4 h-100" style="background: #fff0eb;">
                        <div class="card-body p-5">
                            <div class="icon-box mb-4 text-center">
                                <i class="fi flaticon-location" style="font-size: 4rem; color: #ff6b6b;"></i>
                            </div>
                            <h3 class="fw-bold text-center mb-4" style="color: #2c3e50;">Where to Find Us</h3>

                            <div class="info-item d-flex mb-4">
                                <i class="fi flaticon-pin fs-4 me-3 mt-1" style="color: #ff6b6b;"></i>
                                <div>
                                    <h5 class="fw-bold mb-1">Clinic Address</h5>
                                    <p class="mb-0 text-muted">McArthur Highway, Saluysoy<br>City of Meycauayan, Bulacan
                                        3020
                                    </p>
                                </div>
                            </div>

                            <div class="info-item d-flex">
                                <i class="fi flaticon-calendar-1 fs-4 me-3 mt-1" style="color: #ff6b6b;"></i>
                                <div>
                                    <h5 class="fw-bold mb-1">Office Hours</h5>
                                    <p class="mb-0 text-muted">Monday - Thursday: 8:00 AM - 5:00 PM<br>Friday, Saturday
                                        & Sunday:
                                        Closed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-md-12 offset-lg-1">
                    <div class="requirements-wrap">
                        <h3 class="fw-bold mb-4" style="color: #2c3e50;">What to Bring</h3>
                        <p class="text-muted mb-4 pb-2 border-bottom">Please bring the following for your first visit to
                            register your account and pet:</p>

                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center mb-4">
                                <div class="req-icon me-4"
                                    style="background: #eef2f5; padding: 15px; border-radius: 50%;">
                                    <img src="{{ asset('assets/images/id-card.png') }}" alt="ID" width="32"
                                        onerror="this.style.display='none'; this.parentElement.innerHTML='<span style=\'font-size:24px\'>🪪</span>';">
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1">1. Valid ID</h5>
                                    <p class="mb-0 text-muted fs-6">To verify your identity and residency.</p>
                                </div>
                            </li>

                            <li class="d-flex align-items-center mb-4">
                                <div class="req-icon me-4"
                                    style="background: #eef2f5; padding: 15px; border-radius: 50%;">
                                    <span style="font-size: 24px; line-height: 1;">📱</span>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1">2. Contact Details</h5>
                                    <p class="mb-0 text-muted fs-6">An active mobile number and email address.</p>
                                </div>
                            </li>

                            <li class="d-flex align-items-center mb-4">
                                <div class="req-icon me-4"
                                    style="background: #eef2f5; padding: 15px; border-radius: 50%;">
                                    <span style="font-size: 24px; line-height: 1;">🐕</span>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1">3. Your Pet</h5>
                                    <p class="mb-0 text-muted fs-6">Physical presence of your dog or cat for initial
                                        assessment.</p>
                                </div>
                            </li>

                            <li class="d-flex align-items-center mb-2">
                                <div class="req-icon me-4"
                                    style="background: #eef2f5; padding: 15px; border-radius: 50%;">
                                    <span style="font-size: 24px; line-height: 1;">🏥</span>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1">4. Previous Records</h5>
                                    <p class="mb-0 text-muted fs-6">A screenshot or copy of previous E-Health Cards for
                                        medical migration.</p>
                                </div>
                            </li>
                        </ul>

                        <div class="alert mt-5 border-0 shadow-sm"
                            style="background: #f8f9fa; border-left: 4px solid #ff6b6b !important;">
                            <p class="mb-0"><strong>Why the extra step?</strong> We implement a strict "One-Account
                                Policy" to protect your pet's data and ensure no duplicate or fraudulent records exist
                                in our system. Once registered by our staff, your login credentials will be emailed to
                                you instantly!</p>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('login') }}" class="btn text-white w-100 py-3 fw-bold shadow-sm"
                                style="background: #ff6b6b; border-radius: 8px; text-decoration: none; display: block; text-align: center;">Back
                                to Login</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>