<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="author" content="wpOceans" />
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/newicon.png') }}">
    <link href="{{ asset('assets/css/themify-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/flaticon.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/animate.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/owl.carousel.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/owl.theme.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/slick.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/slick-theme.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/swiper.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/owl.transitions.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/jquery.fancybox.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/odometer-theme-default.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/sass/style.css') }}" rel="stylesheet" />
</head>

<body class="pawcare-login">
    <div class="bg"></div>

    <div class="floating-paws">
        <img src="{{ asset('assets/images/paws-6.png') }}" alt="" class="paw" />
        <img src="{{ asset('assets/images/paws-7.png') }}" alt="" class="paw" />
        <img src="{{ asset('assets/images/paws-8.png') }}" alt="" class="paw" />
        <img src="{{ asset('assets/images/paws-9.png') }}" alt="" class="paw" />
        <img src="{{ asset('assets/images/paws-10.png') }}" alt="" class="paw" />
        <img src="{{ asset('assets/images/paws-11.png') }}" alt="" class="paw" />
    </div>

    <div class="wrapper">
        <div class="card">
            <div class="login-view" id="emailView">
                <header class="logo-wrap">
                    <img src="{{ asset('assets/images/newlogo.png') }}" alt="PawCare" />
                </header>
                <h1 class="title-main">LOG IN</h1>
                <p class="subtitle">Sign in to continue cuddles, walks, and wagging tails.</p>

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form id="loginForm" action="{{ route('login.post') }}" method="POST">
                    @csrf
                    <div class="field floating">
                        <input type="text" id="email" name="email" placeholder=" " value="{{ old('email') }}"
                            required />
                        <label for="email">Email or phone</label>
                    </div>

                    <div class="field floating">
                        <input type="password" id="password" name="password" placeholder=" " required />
                        <label for="password">Password</label>
                    </div>

                    <div class="extras">
                        <label class="show-password">
                            <input type="checkbox" id="showPassword" />
                            <span>Show password</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="link">Forgot password?</a>
                    </div>

                    <div class="submit-row">
                        <button type="submit" class="btn-primary">
                            <span>Log in</span>
                            <span class="paw-emoji">🐾</span>
                        </button>
                    </div>

                    <p class="cta-text-center">
                        New here? <a href="{{ route('join') }}" class="link"><strong>Join the pack</strong></a> and
                        track.
                    </p>

                    <div class="text-center mt-3">
                        <a href="{{ url('/') }}" class="text-muted small text-decoration-none">
                            <i class="bi bi-house-door me-1"></i> Back to Home
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script>
        // Elements
        const loginPassword = document.getElementById("password");
        const showPasswordCheckbox = document.getElementById("showPassword");

        // 2. Show Password Toggle (Login Form)
        if (showPasswordCheckbox) {
            showPasswordCheckbox.addEventListener("change", () => {
                loginPassword.type = showPasswordCheckbox.checked ? "text" : "password";
            });
        }

        // 3. Simple Login Validation
        document.getElementById('loginForm').addEventListener("submit", function (e) {
            const email = document.getElementById("email").value.trim();
            const pass = loginPassword.value.trim();

            if (!email || !pass) {
                e.preventDefault();
                alert("Please fill in both fields.");
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
