<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="wpOceans">
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/newicon.png') }}">
    <title>Paw Care | Pet Care & Veterinary HTML5 Template</title>
    <link href="assets/css/themify-icons.css" rel="stylesheet">
    <link href="assets/css/flaticon.css" rel="stylesheet">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/animate.css" rel="stylesheet">
    <link href="assets/css/owl.carousel.css" rel="stylesheet">
    <link href="assets/css/owl.theme.css" rel="stylesheet">
    <link href="assets/css/slick.css" rel="stylesheet">
    <link href="assets/css/slick-theme.css" rel="stylesheet">
    <link href="assets/css/swiper.min.css" rel="stylesheet">
    <link href="assets/css/owl.transitions.css" rel="stylesheet">
    <link href="assets/css/jquery.fancybox.css" rel="stylesheet">
    <link href="assets/css/odometer-theme-default.css" rel="stylesheet">
    <link href="assets/sass/style.css" rel="stylesheet">
</head>

<body>

    <!-- start page-wrapper -->
    <div class="page-wrapper">
        <!-- start preloader -->
        <div class="preloader">
            <div class="vertical-centered-box">
                <div class="content">
                    <div class="loader-circle"></div>
                    <div class="loader-line-mask">
                        <div class="loader-line"></div>
                    </div>
                    <img src="assets/images/newicon.png" alt="">
                </div>
            </div>
        </div>
        <!-- end preloader -->

        <!-- Start header -->
        <header id="header">
            <div class="wpo-site-header">
                <nav class="navigation navbar navbar-expand-lg navbar-light">
                    <div class="container-fluid">
                        <div class="row align-items-center">

                            <div class="col-lg-3 col-md-3 col-3 d-lg-none dl-block order-1">
                                <div class="mobail-menu">
                                    <button type="button" class="navbar-toggler open-btn">
                                        <span class="sr-only">Toggle navigation</span>
                                        <span class="icon-bar first-angle"></span>
                                        <span class="icon-bar middle-angle"></span>
                                        <span class="icon-bar last-angle"></span>
                                    </button>
                                </div>
                            </div>

                            <div class="col-lg-2 col-md-4 col-4 order-2">
                                <div class="navbar-header">
                                    <a class="navbar-brand" href="{{ url('/') }}">
                                        <img src="assets/images/newlogo.png" alt="Logo">
                                    </a>
                                </div>
                            </div>

                            <div class="col-lg-7 col-md-1 col-1 order-lg-3">
                                <div id="navbar" class="collapse navbar-collapse navigation-holder">
                                    <button class="menu-close"><i class="ti-close"></i></button>
                                    <ul class="nav navbar-nav mb-2 mb-lg-0">
                                        <li class="menu-item-has-children"><a href="index.html">Home</a></li>
                                        <li class="menu-item-has-children">
                                            <a href="#">Page</a>
                                            <ul class="sub-menu">
                                                <li><a href="about.html">About</a></li>
                                                <li><a href="faq.html">Faq</a></li>
                                                <li><a href="contact.html">Contact Us</a></li>
                                            </ul>
                                        </li>
                                        <li class="menu-item-has-children">
                                            <a href="#">Services</a>
                                            <ul class="sub-menu">
                                                <li><a href="service.html">Services</a></li>
                                                <li><a href="service-single.html">Service Single</a></li>
                                            </ul>
                                        </li>
                                        <li><a href="blog.html">Blog</a></li>

                                        <li class="d-lg-none"><a href="{{ route('login') }}">Login</a></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-4 col-4 order-3 order-lg-4">
                                <div class="header-right" style="display: flex; align-items: center; justify-content: flex-end; gap: 15px;">
                                    <div class="header-search-form-wrapper">
                                    </div>

                                    <div class="header-login-btn d-none d-lg-block">
                                        <a class="custom-login-btn" href="{{ route('login') }}">Login</a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </nav>
            </div>
        </header>
        <!-- end of header -->

        <!-- start blog-hero -->
        <section class="blog-hero" style="background-image: url('assets/images/blog/img-1.jpg');">
            <div class="container">
                <div class="row">
                    <div class="col col-lg-8 col-md-10 col-12">
                        <div class="blog-hero-content">
                            <span class="blog-hero-eyebrow">PawCare Blog</span>
                            <h1>Caring for Pets Through Prevention and Monitoring</h1>
                            <p>Modern solutions for healthier, happier pets.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- end blog-hero -->

        <!-- start blog-layout-section -->
        <section class="blog-layout-section section-padding">
            <div class="container">
                <div class="blog-layout-item">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-6 col-12">
                            <div class="blog-layout-image">
                                <img src="assets/images/blog/vaccine.jpg" alt="Timely pet vaccination">
                            </div>
                        </div>
                        <div class="col-lg-6 col-12">
                            <div class="blog-layout-content">
                                <span class="blog-layout-kicker">The Importance of Timely Vaccination</span>
                                <h2>Protection Starts with Prevention</h2>
                                <p>Timely vaccinations help protect pets from preventable diseases.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="blog-layout-item">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-6 col-12 order-lg-2">
                            <div class="blog-layout-image">
                                <img src="assets/images/blog/signs.jpg" alt="Pet health monitoring in clinic">
                            </div>
                        </div>
                        <div class="col-lg-6 col-12 order-lg-1">
                            <div class="blog-layout-content">
                                <span class="blog-layout-kicker">How Vaccination Tracking Improves Pet Health</span>
                                <h2>Protecting Your Pet, One Visit at a Time</h2>
                                <p>Tracking vaccinations and appointments helps ensure your pet receives timely care and stays protected from preventable diseases.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="blog-layout-item">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-6 col-12">
                            <div class="blog-layout-image">
                                <img src="assets/images/blog/scan.jpg" alt="Digital pet records and quick access">
                            </div>
                        </div>
                        <div class="col-lg-6 col-12">
                            <div class="blog-layout-content">
                                <span class="blog-layout-kicker">Faster Care Through Digital Records</span>
                                <h2>Quick Access, Better Care</h2>
                                <p>Simple scanning provides instant access to vital pet information.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="blog-layout-item">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-6 col-12 order-lg-2">
                            <div class="blog-layout-image">
                                <img src="assets/images/blog/secure.jpg" alt="Secure and private pet healthcare data">
                            </div>
                        </div>
                        <div class="col-lg-6 col-12 order-lg-1">
                            <div class="blog-layout-content">
                                <span class="blog-layout-kicker">Privacy and Trust in Pet Healthcare</span>
                                <h2>Your Data, Protected</h2>
                                <p>Pet and owner information is kept secure and private.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="blog-layout-item">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-6 col-12">
                            <div class="blog-layout-image">
                                <img src="assets/images/blog/student.png"
                                    alt="Technology supporting better veterinary care">
                            </div>
                        </div>
                        <div class="col-lg-6 col-12">
                            <div class="blog-layout-content">
                                <span class="blog-layout-kicker">Supporting Better Veterinary Care with Technology</span>
                                <h2>Technology with Purpose</h2>
                                <p>Student innovation supporting real-world veterinary care.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="blog-cta">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-6 col-12">
                            <div class="blog-cta-image">
                                <img src="assets/images/blog/happy.jpg" alt="Happy pet after a vet visit">
                            </div>
                        </div>
                        <div class="col-lg-6 col-12">
                            <div class="blog-cta-content">
                                <h2>Because Every Vet Visit Counts</h2>
                                <p>Learn how PawCare supports vaccination and pet health management.</p>
                                <a href="about.html" class="theme-btn-s2">Learn More</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- end blog-layout-section -->


        <!-- start of wpo-site-footer-section -->
        <footer class="wpo-site-footer">
            <div class="wpo-upper-footer">
                <div class="container">
                    <div class="row">
                        <div class="col col-lg-6 col-md-6 col-sm-12 col-12">
                            <div class="widget about-widget">
                                <div class="widget-title">
                                    <h2>Trusted Pet Care Vet For
                                        Your Favorite Pets</h2>
                                </div>
                                <div class="social-widget">
                                    <ul>
                                        <li><a href="#"><i class="ti-facebook"></i></a></li>
                                        <li><a href="#"><i class="ti-twitter-alt"></i></a></li>
                                        <li><a href="#"><i class="ti-linkedin"></i></a></li>
                                        <li><a href="#"><i class="ti-instagram"></i></a></li>
                                    </ul>
                                </div>

                            </div>
                        </div>
                        <div class="col col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="widget contact-widget">
                                <div class="widget-title">
                                    <h3>Contact</h3>
                                </div>
                                <ul>
                                    <li>pawcare@gmail.com</li>
                                    <li>(044) 919-8020</li>
                                    <li>MacArthur Hwy <br> Meycauayan City, Bulacan</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="widget link-widget">
                                <div class="widget-title">
                                    <h3>Quick Link</h3>
                                </div>
                                <ul>
                                    <li><a href="about.html">About Us</a></li>
                                    <li><a href="service.html">Services</a></li>
                                    <li><a href="blog.html">Blog</a></li>
                                    <li><a href="contact.html">Contact Us</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div> <!-- end container -->
            </div>
            <div class="wpo-lower-footer">
                <div class="container-fluid">
                    <div class="row g-0">
                        <div class="col col-lg-6 col-12">
                        </div>
                        <div class="col col-lg-6 col-12">
                            <ul class="right">
                                <li><a href="privace.html"><span class="rolling-text">Privace & Policy</span> </a></li>
                                <li><a href="terms.html"><span class="rolling-text">Terms</span></a></li>
                                <li><a href="about.html"><span class="rolling-text">About us</span></a></li>
                                <li><a href="faq.html"><span class="rolling-text">FAQ</span></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        <!-- end of wpo-site-footer-section -->


    </div>
    <!-- end of page-wrapper -->

    <!-- All JavaScript files
    ================================================== -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <!-- Plugins for this template -->
    <script src="assets/js/modernizr.custom.js"></script>
    <script src="assets/js/jquery-plugin-collection.js"></script>
    <!-- Custom script for this template -->
    <script src="assets/js/script.js"></script>
</body>


</html>
