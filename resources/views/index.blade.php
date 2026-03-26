<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

    <style>
        /* Modern Text Reveal Animation */
        .hero-reveal-1,
        .hero-reveal-2,
        .hero-reveal-3,
        .hero-reveal-btn {
            opacity: 0;
            transform: translateY(30px);
            transition: all 1.2s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: transform, opacity;
        }

        .hero-section.start-animation .hero-reveal-1 {
            opacity: 1;
            transform: translateY(0);
            transition-delay: 0.1s;
        }

        .hero-section.start-animation .hero-reveal-2 {
            opacity: 1;
            transform: translateY(0);
            transition-delay: 0.3s;
        }

        .hero-section.start-animation .hero-reveal-3 {
            opacity: 1;
            transform: translateY(0);
            transition-delay: 0.5s;
        }

        .hero-section.start-animation .hero-reveal-btn {
            opacity: 1;
            transform: translateY(0);
            transition-delay: 0.7s;
        }
    </style>
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
                                <div class="header-right"
                                    style="display: flex; align-items: center; justify-content: flex-end; gap: 15px;">
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

        <!-- start of hero-section -->
        <section class="hero-section">
            <div class="hero-inner-slider">
                <div class="hero-inner">
                    <h2 class="hero-reveal-1">Trusted Pet Care Service</h2>
                    <h3 class="hero-reveal-2">Trusted Veterinary Care for Healthy, Happy Pets.</h3>
                    <p class="hero-reveal-3">Providing safe vaccinations and digital record management you can rely on.</p>
                    <a href="{{ route('login') }}" class="theme-btn-s2 hero-reveal-btn">Update your Pet's Records</a>
                </div>
            </div>
            <div class="hero-image">
                <div class="top-image">
                    <div class="item">
                        <img src="assets/images/slider/slide-1.png" alt="">
                    </div>
                    <div class="item">
                        <img src="assets/images/slider/slide-2.png" alt="">
                    </div>
                    <div class="item">
                        <img src="assets/images/slider/slide-3.png" alt="">
                    </div>
                    <div class="item">
                        <img src="assets/images/slider/slide-4.png" alt="">
                    </div>
                </div>
                <div class="buttom-image">
                    <div class="item">
                        <img src="assets/images/slider/1.png" alt="">
                    </div>
                    <div class="item">
                        <img src="assets/images/slider/2.png" alt="">
                    </div>
                    <div class="item">
                        <img src="assets/images/slider/3.png" alt="">
                    </div>
                    <div class="item">
                        <img src="assets/images/slider/4.png" alt="">
                    </div>
                </div>
            </div>
            <div class="paw-1">
                <img src="assets/images/slider/shape-1.svg" alt="">
            </div>
            <div class="paw-2">
                <img src="assets/images/slider/shape-2.svg" alt="">
            </div>
            <div class="paw-3">
                <img src="assets/images/slider/shape-3.svg" alt="">
            </div>
            <div class="paw-4">
                <img src="assets/images/slider/shape-4.svg" alt="">
            </div>
        </section>

        <!-- start of service-section -->
        <section class="service-section section-padding">
            <div class="wraper" style="justify-content: center; gap: 80px;">
                <div class="col">
                    <div class="item" style="text-align: center; margin: 0 auto;">
                        <div class="icon" style="margin: 0 auto;">
                            <img src="assets/images/service/2.svg" alt="Pet Monitoring">
                        </div>
                        <div class="content">
                            <h2><a href="service-single.html">Pet Health Records</a></h2>
                            <p>Manage and access your pet’s vaccination and medical history anytime. </p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="item" style="text-align: center; margin: 0 auto;">
                        <div class="icon" style="margin: 0 auto;">
                            <img src="assets/images/service/4.svg" alt="Vaccination">
                        </div>
                        <div class="content">
                            <h2><a href="service-single.html">Vaccination</a></h2>
                            <p>Safe and reliable vaccination services to keep your pet healthy and protected. </p>
                        </div>
                    </div>
                </div>
                <div class="top-img-1" style="left: 50%; transform: translateX(-50%); margin-left: -150px;">
                    <img src="assets/images/service/top-img-1.png" alt="">
                </div>
                <div class="top-img-2" style="left: 50%; transform: translateX(-50%); margin-left: 150px;">
                    <img src="assets/images/service/top-img-2.png" alt="">
                </div>
            </div>
        </section>

        <!-- start of about-section -->
        <section class="about-section section-padding pt-0">
            <div class="wraper">
                <div class="left">
                    <div class="image">
                        <img src="assets/images/about.png" alt="">
                        <div class="shape">
                            <svg width="793" height="786" viewBox="0 0 793 786" fill="none">
                                <path
                                    d="M84.9007 505.664C-181.681 609.802 245.585 843.801 512.633 772.246C713.751 718.356 833.104 511.631 779.214 310.513C725.325 109.395 552.6 -41.9576 351.482 11.9319C150.364 65.8214 351.482 401.526 84.9007 505.664Z"
                                    fill="#FFEFEB" />
                            </svg>
                        </div>
                    </div>
                    <div class="about-video">
                        <div class="icon">
                            <img src="assets/images/service/3.svg" alt="">
                        </div>
                        <h2>How To Take Care Of Your Favorite Pet. </h2>
                        <p>Through preventive care and continuous health monitoring, PawCare is committed to helping you
                            give your furry companions a safer,
                            longer, and more comfortable life.</p>
                    </div>
                </div>
                <div class="right">
                    <span>About Us</span>
                    <h2>At PawCare, every vet visit counts</h2>
                    <p>PawCare, is a student-developed veterinary system designed to support pet vaccination management
                        and health monitoring. It aims to help veterinary clinics and pet owners ensure that pets
                        receive
                        timely vaccinations and proper health tracking through and organized and digital approach.
                    </p>

                    <div class="about-btn">
                        <a href="about.html" class="theme-btn-s2">Discover More</a>
                    </div>
                </div>
            </div>
            <div class="shape">
                <img src="assets/images/paws-6.png" alt="">
            </div>
            <div class="shape-2">
                <img src="assets/images/paws-7.png" alt="">
            </div>
        </section>

        <!-- start of funfact-section -->
        <section class="funfact-section">
            <div class="section-top-image">
                <img src="assets/images/funfact/top-image.jpg" alt="">
            </div>
            <div class="container">
                <div class="row">
                    <div class="col col-lg-4 col-md-6 col-12">
                        <div class="item">
                            <div class="icon">
                                <img src="assets/images/funfact/scanner.png" alt="">
                            </div>
                            <h3>Simple Scan</h3>
                            <p class="hover-text">Instant access to pet records.</p>
                        </div>
                    </div>
                    <div class="col col-lg-4 col-md-6 col-12">
                        <div class="item">
                            <div class="icon">
                                <img src="assets/images/funfact/3.svg" alt="">
                            </div>
                            <h3>Quick Access to the Vet</h3>
                            <p class="hover-text">Faster response, better care.</p>
                        </div>
                    </div>
                    <div class="col col-lg-4 col-md-6 col-12">
                        <div class="item">
                            <div class="icon">
                                <img src="assets/images/funfact/4.svg" alt="">
                            </div>
                            <h3>Personal Privacy</h3>
                            <p class="hover-text">Secure and protected personal data.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- start of testimonial-section -->
        <section class="testimonial-section section-padding">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-6 col-12">
                        <div class="section-title">
                            <h3>Testimonials</h3>
                            <h2>What People Say About PawCare</h2>
                        </div>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-lg-6 col-12">
                        <div class="testimonial-image">
                            <img src="assets/images/testimonial.png" alt="">
                            <div class="shape">
                                <svg viewBox="0 0 932 866" fill="none">
                                    <path
                                        d="M809.592 217.287C836.009 315.879 817.135 440.087 766.796 546.355C716.445 652.649 634.907 740.4 536.578 766.747C339.293 819.609 136.508 702.532 83.6458 505.246C57.2312 406.666 77.0482 320.935 128.016 253.209C179.036 185.413 261.413 135.488 360.331 108.983C459.684 82.3613 559.022 60.3667 640.086 69.6483C680.555 74.2818 716.32 86.6978 745.237 110.108C774.137 133.505 796.397 168.045 809.592 217.287Z"
                                        stroke="#FABE3D" stroke-width="5" />
                                    <path
                                        d="M772.667 241.253C819.838 417.297 705.161 683.804 529.117 730.975C353.074 778.146 172.122 673.674 124.952 497.63C77.7809 321.586 195.712 190.863 371.756 143.692C547.799 96.5215 725.496 65.2093 772.667 241.253Z"
                                        fill="#FFF7E5" />
                                    <mask id="mask0_1346_138" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="114"
                                        y="106" width="670" height="637">
                                        <path
                                            d="M772.667 241.253C819.838 417.297 705.161 683.804 529.117 730.975C353.074 778.146 172.122 673.674 124.952 497.63C77.7809 321.586 195.712 190.863 371.756 143.692C547.799 96.5215 725.496 65.2093 772.667 241.253Z"
                                            fill="#FFF7E5" />
                                    </mask>
                                    <g mask="url(#mask0_1346_138)">
                                    </g>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-12">
                        <div class="testimonial-slider owl-carousel">
                            <div class="item">
                                <div class="icon">
                                    <img src="assets/images/testimonial-icon.svg" alt="">
                                </div>
                                <h3>“PawCare made managing our pet vaccination records so much easier.”</h3>
                                <p>“With just a quick scan, we can instantly view a pet’s vaccination history. It saves
                                    time during consultations and helps us provide better care.”</p>
                                <div class="client-wrap">
                                    <div class="image">
                                    </div>
                                    <div class="text">
                                        <h4>Veterinary Clinic Staff</h4>
                                        <span>Clinic Team</span>
                                    </div>
                                </div>
                            </div>
                            <div class="item">
                                <div class="icon">
                                    <img src="assets/images/testimonial-icon.svg" alt="">
                                </div>
                                <h3>“Simple, organized, and very helpful for our clinic.”</h3>
                                <p>“The system keeps all pet records in one place. Tracking vaccinations and monitoring
                                    pet health has become more efficient and reliable.”</p>
                                <div class="client-wrap">
                                    <div class="image">
                                    </div>
                                    <div class="text">
                                        <h4>Clinic Administrator</h4>
                                        <span>Operations</span>
                                    </div>
                                </div>
                            </div>
                            <div class="item">
                                <div class="icon">
                                    <img src="assets/images/testimonial-icon.svg" alt="">
                                </div>
                                <h3>“I feel more confident knowing my pet’s records are secure.”</h3>
                                <p>“PawCare protects our personal information while giving the vet fast access to
                                    important health details. It gives us peace of mind.”</p>
                                <div class="client-wrap">
                                    <div class="image">
                                    </div>
                                    <div class="text">
                                        <h4>Pet Owner</h4>
                                        <span>Client</span>
                                    </div>
                                </div>
                            </div>
                            <div class="item">
                                <div class="icon">
                                    <img src="assets/images/testimonial-icon.svg" alt="">
                                </div>
                                <h3>“A smart solution for modern veterinary care.”</h3>
                                <p>“The digital monitoring and scanning feature really helps, especially during busy
                                    clinic hours. It’s practical and easy to use.”</p>
                                <div class="client-wrap">
                                    <div class="image">
                                    </div>
                                    <div class="text">
                                        <h4>Veterinarian</h4>
                                        <span>Doctor</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="shape-1">
                <img src="assets/images/paws-7.png" alt="">
            </div>
            <div class="shape-2">
                <img src="assets/images/paws-6.png" alt="">
            </div>
        </section>

        <!-- start of blog-section -->
        <section class="blog-section section-padding">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-6 col-12">
                        <div class="section-title">
                            <h3>Our Blog</h3>
                            <h2>Prevention, Monitoring & Better Care</h2>
                        </div>
                    </div>
                </div>
                <div class="row blog-wrap">
                    <div class="col col-lg-4 col-md-6 col-12">
                        <div class="blog-card">
                            <div class="image">
                                <img src="assets/images/blog/vaccine.jpg" alt="">
                            </div>
                            <div class="content">
                                <ul class="date">
                                    <li>By PawCare </li>
                                    <li>Jan 2026</li>
                                </ul>
                                <h2><a href="blog.html">Protection Starts with Prevention</a></h2>
                                <p>Timely vaccinations help protect pets from preventable diseases.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col col-lg-4 col-md-6 col-12">
                        <div class="blog-card">
                            <div class="image">
                                <img src="assets/images/blog/signs.jpg" alt="">
                            </div>
                            <div class="content">
                                <ul class="date">
                                    <li>By PawCare </li>
                                    <li>Jan 2026</li>
                                </ul>
                                <h2><a href="blog.html">Monitoring Health, One Visit at a Time</a></h2>
                                <p>Consistent tracking helps detect health issues early.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col col-lg-4 col-md-6 col-12">
                        <div class="blog-card">
                            <div class="image">
                                <img src="assets/images/blog/scan.jpg" alt="">
                            </div>
                            <div class="content">
                                <ul class="date">
                                    <li>By PawCare </li>
                                    <li>Jan 2026</li>
                                </ul>
                                <h2><a href="blog.html">Quick Access, Better Care</a></h2>
                                <p>Simple scanning provides instant access to vital pet information.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

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
