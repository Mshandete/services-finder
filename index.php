<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FindPro | Find Trusted Local Professionals</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Homepage CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

</head>
<body>

<!-- =====================================================
     NAVBAR
===================================================== -->
<header class="main-header">
    <div class="navbar">
        <!-- Logo -->
        <a href="index.php" class="brand">
            <span class="brand-mark">
                <i class="fa-solid fa-location-dot"></i>
            </span>
            <span class="brand-name">
                Find<span>Pro</span>
            </span>
        </a>

        <!-- Navigation -->
        <nav class="main-nav">
            <a href="index.php" class="active">Home</a>
            <a href="#services">Services</a>
            <a href="#professionals">Professionals</a>
            <a href="about.php">About</a>
            <a href="#contact">Contact</a>
        </nav>

        <!-- Account -->
        <div class="nav-actions">
            <a href="auth/login.php" class="login-link">Login</a>
            <a href="auth/register.php" class="register-link">Register</a>
        </div>

        <!-- Mobile Menu -->
        <button class="mobile-menu" type="button" aria-label="Open menu">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>
</header>

<main>
<!-- =====================================================
     HERO
===================================================== -->
<section class="hero-section">
    <div class="hero-container">
        <!-- LEFT -->
        <div class="hero-content">
            <span class="hero-label">
                <i class="fa-solid fa-location-crosshairs"></i>
                Local services, made easier
            </span>

            <h1>Find the right <span>professional</span> for the job.</h1>

            <p class="hero-description">
                Discover trusted service providers around you, compare their services, and connect with the right professional for your needs.
            </p>

            <!-- SEARCH -->
            <div class="hero-search">
                <div class="search-field">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <div class="search-input">
                        <span>What service do you need?</span>
                        <input type="text" name="service" placeholder="e.g. Plumber, electrician...">
                    </div>
                </div>

                <div class="search-divider"></div>

                <div class="search-field">
                    <i class="fa-solid fa-location-dot"></i>
                    <div class="search-input">
                        <span>Your location</span>
                        <input type="text" name="location" placeholder="e.g. Mikocheni, Dar es Salaam">
                    </div>
                </div>

                <button type="button" class="find-button">
                    Find Services
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>

            <!-- TRUST -->
            <div class="hero-trust">
                <div class="trust-avatars">
                    <span><img src="assets/images/h.jpg" alt="User avatar"></span>
                    <span><img src="assets/images/f.jpg" alt="User avatar"></span>
                    <span><img src="assets/images/i.jpg" alt="User avatar"></span>
                </div>
                <div class="trust-text">
                    <strong>Trusted by local users</strong>
                    <small>Find professionals with confidence</small>
                </div>
            </div>
        </div>

        <!-- RIGHT -->
        <div class="hero-visual">
            <!-- Main Image -->
            <div class="hero-image-card">
                <img src="assets/images/b.jpg" alt="Find a professional">
                <div class="image-overlay"></div>

                <!-- Location Badge -->
                <div class="location-badge">
                    <span class="badge-icon">
                        <i class="fa-solid fa-location-dot"></i>
                    </span>
                    <div>
                        <small>Professionals near</small>
                        <strong>Dar es Salaam</strong>
                    </div>
                </div>

                <!-- Rating -->
                <div class="rating-badge">
                    <span class="rating-star">
                        <i class="fa-solid fa-star"></i>
                    </span>
                    <div>
                        <strong>4.9</strong>
                        <small>Average rating</small>
                    </div>
                </div>
            </div>

            <!-- Floating Service Card -->
            <div class="service-floating-card">
                <div class="service-icon">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
                <div class="service-card-text">
                    <small>Popular service</small>
                    <strong>Home Repairs</strong>
                </div>
                <span class="service-arrow">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </span>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================
     POPULAR SERVICES
===================================================== -->
<section class="services-section" id="services">
    <div class="section-container">
        <div class="section-heading">
            <div>
                <span class="section-label">Explore</span>
                <h2>What service do you need?</h2>
            </div>
            <a href="#" class="view-link">
                View all
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="service-grid">
            <a href="#" class="service-item">
                <span class="service-item-icon">
                    <i class="fa-solid fa-faucet-drip"></i>
                </span>
                <div>
                    <strong>Plumbing</strong>
                    <small>Home & repairs</small>
                </div>
                <i class="fa-solid fa-arrow-right service-item-arrow"></i>
            </a>

            <a href="#" class="service-item">
                <span class="service-item-icon">
                    <i class="fa-solid fa-bolt"></i>
                </span>
                <div>
                    <strong>Electrical</strong>
                    <small>Electrical services</small>
                </div>
                <i class="fa-solid fa-arrow-right service-item-arrow"></i>
            </a>

            <a href="#" class="service-item">
                <span class="service-item-icon">
                    <i class="fa-solid fa-broom"></i>
                </span>
                <div>
                    <strong>Cleaning</strong>
                    <small>Home & office</small>
                </div>
                <i class="fa-solid fa-arrow-right service-item-arrow"></i>
            </a>

            <a href="#" class="service-item">
                <span class="service-item-icon">
                    <i class="fa-solid fa-car"></i>
                </span>
                <div>
                    <strong>Auto Services</strong>
                    <small>Repairs & maintenance</small>
                </div>
                <i class="fa-solid fa-arrow-right service-item-arrow"></i>
            </a>

            <a href="#" class="service-item">
                <span class="service-item-icon">
                    <i class="fa-solid fa-laptop-code"></i>
                </span>
                <div>
                    <strong>Technology</strong>
                    <small>IT & digital services</small>
                </div>
                <i class="fa-solid fa-arrow-right service-item-arrow"></i>
            </a>

            <a href="#" class="service-item">
                <span class="service-item-icon">
                    <i class="fa-solid fa-house"></i>
                </span>
                <div>
                    <strong>Home Services</strong>
                    <small>More home solutions</small>
                </div>
                <i class="fa-solid fa-arrow-right service-item-arrow"></i>
            </a>
        </div>
    </div>
</section>

<!-- =====================================================
     PROFESSIONALS
===================================================== -->
<section class="professionals-section" id="professionals">
    <div class="section-container">
        <div class="section-heading">
            <div>
                <span class="section-label">Near you</span>
                <h2>Professionals people trust</h2>
            </div>
            <a href="#" class="view-link">
                Explore professionals
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="professional-grid">
            <!-- Provider 1 -->
            <a href="#" class="professional-card">
                <div class="professional-image">
                    <img src="assets/images/h.jpg" alt="Service provider">
                    <span class="verified-badge">
                        <i class="fa-solid fa-check"></i> Verified
                    </span>
                </div>
                <div class="professional-info">
                    <div class="professional-top">
                        <div>
                            <h3>Paschal Mshandete</h3>
                            <span>Programmer</span>
                        </div>
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </div>
                    <div class="professional-meta">
                        <span><i class="fa-solid fa-star"></i> 4.9</span>
                        <span><i class="fa-solid fa-location-dot"></i> 1.4 km away</span>
                    </div>
                </div>
            </a>

            <!-- Provider 2 -->
            <a href="#" class="professional-card">
                <div class="professional-image">
                    <img src="assets/images/f.jpg" alt="Service provider">
                    <span class="verified-badge">
                        <i class="fa-solid fa-check"></i> Verified
                    </span>
                </div>
                <div class="professional-info">
                    <div class="professional-top">
                        <div>
                            <h3>Niwael Mavella</h3>
                            <span>Engineer</span>
                        </div>
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </div>
                    <div class="professional-meta">
                        <span><i class="fa-solid fa-star"></i> 4.8</span>
                        <span><i class="fa-solid fa-location-dot"></i> 2.1 km away</span>
                    </div>
                </div>
            </a>

            <!-- Provider 3 -->
            <a href="#" class="professional-card">
                <div class="professional-image">
                    <img src="assets/images/i.jpg" alt="Service provider">
                    <span class="verified-badge">
                        <i class="fa-solid fa-check"></i> Verified
                    </span>
                </div>
                <div class="professional-info">
                    <div class="professional-top">
                        <div>
                            <h3>Leonard Ndaro</h3>
                            <span>Mechanic</span>
                        </div>
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </div>
                    <div class="professional-meta">
                        <span><i class="fa-solid fa-star"></i> 5.0</span>
                        <span><i class="fa-solid fa-location-dot"></i> 2.8 km away</span>
                    </div>
                </div>
            </a>

            <!-- Provider 4 -->
            <a href="#" class="professional-card">
                <div class="professional-image">
                    <img src="assets/images/mavella.jpg" alt="Service provider">
                    <span class="verified-badge">
                        <i class="fa-solid fa-check"></i> Verified
                    </span>
                </div>
                <div class="professional-info">
                    <div class="professional-top">
                        <div>
                            <h3>Godad Machumu</h3>
                            <span>Engineer</span>
                        </div>
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </div>
                    <div class="professional-meta">
                        <span><i class="fa-solid fa-star"></i> 4.8</span>
                        <span><i class="fa-solid fa-location-dot"></i> 3.2 km away</span>
                    </div>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- =====================================================
     HOW IT WORKS
===================================================== -->
<section class="how-section">
    <div class="section-container">
        <div class="how-heading">
            <span class="section-label">Simple process</span>
            <h2>Get the help you need<br>in three simple steps.</h2>
        </div>

        <div class="steps-grid">
            <div class="step-item">
                <span class="step-number">01</span>
                <div class="step-icon">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <h3>Search</h3>
                <p>Tell us what service you need and where you need it.</p>
            </div>

            <div class="step-item">
                <span class="step-number">02</span>
                <div class="step-icon">
                    <i class="fa-solid fa-users"></i>
                </div>
                <h3>Compare</h3>
                <p>Explore professionals, ratings, services and locations.</p>
            </div>

            <div class="step-item">
                <span class="step-number">03</span>
                <div class="step-icon">
                    <i class="fa-solid fa-handshake"></i>
                </div>
                <h3>Hire</h3>
                <p>Choose the professional that fits your needs and get started.</p>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================
     WHY FINDPRO
===================================================== -->
<section class="why-section">
    <div class="section-container why-container">
        <div class="why-content">
            <span class="section-label">Why FindPro</span>
            <h2>A simpler way to find people who can help.</h2>
            <p>FindPro brings customers and local professionals together in one simple place, making it easier to discover, compare and connect.</p>

            <div class="why-list">
                <div>
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Discover local professionals</span>
                </div>
                <div>
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Compare ratings and services</span>
                </div>
                <div>
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Connect directly with providers</span>
                </div>
                <div>
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Find services around your location</span>
                </div>
            </div>
        </div>

        <div class="why-card">
            <span class="why-card-icon">
                <i class="fa-solid fa-location-dot"></i>
            </span>
            <strong>Local professionals</strong>
            <p>Discover service providers close to where you are.</p>

            <div class="why-stat">
                <span>
                    <i class="fa-solid fa-user-check"></i>
                </span>
                <div>
                    <strong>Trusted providers</strong>
                    <small>Ready to serve you</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================
     PROVIDER CTA
===================================================== -->
<section class="provider-cta" id="contact">
    <div class="cta-container">
        <div>
            <span class="cta-label">For professionals</span>
            <h2>Have a skill or service to offer?</h2>
            <p>Join FindPro and let customers around you discover your work.</p>
        </div>

        <a href="auth/register.php" class="cta-button">
            Become a Provider
            <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>
</section>
</main>

<!-- =====================================================
     FOOTER
===================================================== -->
<footer class="home-footer">
    <div class="footer-container">
        <div class="footer-brand">
            <a href="index.php">Find<span>Pro</span></a>
            <p>Local services. Trusted professionals.</p>
        </div>

        <div class="footer-links">
            <h4>Explore</h4>
            <a href="index.php">Home</a>
            <a href="#services">Services</a>
            <a href="#professionals">Professionals</a>
        </div>

        <div class="footer-links">
            <h4>Company</h4>
            <a href="about.php">About</a>
            <a href="#contact">Contact</a>
            <a href="auth/login.php">Login</a>
        </div>

        <div class="footer-links">
            <h4>Join FindPro</h4>
            <a href="auth/register.php">Become a Provider</a>
            <a href="auth/register.php">Create an Account</a>
        </div>
    </div>

    <div class="footer-bottom">
        <span>© <?= date('Y'); ?> FindPro. All rights reserved.</span>
        <div>
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
        </div>
    </div>
</footer>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // 1. Weka Class za Animation Kwenye Elements
    
    // Hero Elements
    document.querySelector('.hero-content')?.classList.add('animate-item', 'anim-left');
    document.querySelector('.hero-visual')?.classList.add('animate-item', 'anim-right');

    // Section Headings
    document.querySelectorAll('.section-heading, .how-heading, .why-content').forEach(el => {
        el.classList.add('animate-item', 'anim-up');
    });

    // Grids (Tutaziwekea Mlolongo / Stagger Effect)
    const grids = document.querySelectorAll('.service-grid, .professional-grid, .steps-grid');
    
    grids.forEach(grid => {
        const items = grid.children;
        Array.from(items).forEach((item, index) => {
            item.classList.add('animate-item', 'anim-up');
            // Ongeza delay ndogo kwa kila item ili ziingie kwa zamu
            item.style.transitionDelay = `${(index % 4) * 0.12}s`;
        });
    });

    // 2. Tumia Smooth Observer BILA KUSHITUKA
    const observerOptions = {
        root: null,
        rootMargin: '0px 0px -60px 0px', // Inaanza animation kabla kidogo haijagusa kioo
        threshold: 0.1
    };

    const smoothObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Tumia requestAnimationFrame kupunguza strain kwenye graphics za simu/PC
                requestAnimationFrame(() => {
                    entry.target.classList.add('in-view');
                });
                // Acha ku-observe mara tu inapoingia ili isijirudie rudie
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Anzisha observer kwa kila element
    document.querySelectorAll('.animate-item').forEach(el => {
        smoothObserver.observe(el);
    });
});
</script>
</body>
</html>