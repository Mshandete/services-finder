<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services Finder</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <header>
        <div class="logo">
            <i class="fa-solid fa-location-dot"></i>
            <span>Services Finder</span>
        </div>

        <nav>
            <ul>
                <li><a href="#">Home</a></li>
                <li><a href="#">Services</a></li>
                <li><a href="#">Providers</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </nav>

        <div class="buttons">
            <a href="#" class="login">Login</a>
            <a href="auth/register.php" class="register">Register</a>
        </div>

        <div class="menu-btn">
            <i class="fa-solid fa-bars"></i>
        </div>
    </header>

    <section class="hero">
        <div class="overlay"></div>

        <div class="hero-content">
            <h1>Find Trusted Professionals Near Your Location</h1>
            <p>Search verified electricians, mechanics, plumbers, cleaners, tutors, painters, photographers, and many more.</p>

            <div class="search-card">
                <form>
                    <div class="input-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="What service are you looking for?">
                    </div>

                    <div class="input-box">
                        <i class="fa-solid fa-location-dot"></i>
                        <input type="text" placeholder="Enter your location">
                    </div>
                    <button type="submit">Find Services</button>
                </form>
            </div>

            <div class="statistics">
                <div class="stat">
                    <i class="fa-solid fa-user-check"></i>
                    <h2>2,500+</h2>
                    <p>Verified Providers</p>
                </div>

                <div class="stat">
                    <i class="fa-solid fa-briefcase"></i>
                    <h2>5,000+</h2>
                    <p>Completed Jobs</p>
                </div>

                <div class="stat">
                    <i class="fa-solid fa-star"></i>
                    <h2>4.9</h2>
                    <p>Average Rating</p>
                </div>

                <div class="stat">
                    <i class="fa-solid fa-location-dot"></i>
                    <h2>100+</h2>
                    <p>Cities Covered</p>
                </div>
            </div>

            <div class="scroll-down">
                <i class="fa-solid fa-angles-down"></i>
            </div>
        </div>
    </section> <section class="featured reveal">
        <div class="section-title">
            <h2>Featured Service Providers</h2>
            <p>Discover trusted professionals recommended for you.</p>
        </div>

        <div class="provider-grid">
            <a href="provider.php?id=1" class="provider-card">
                <div class="provider-image">
                    <img src="assets/images/msagambegu.jpg" alt="Provider">
                    <span class="verified">
                        <i class="fa-solid fa-circle-check"></i> Verified
                    </span>
                </div>
                <div class="provider-info">
                    <h3>Ndete Mwana</h3>
                    <span class="profession">Programmer</span>
                    <div class="rating">
                        <i class="fa-solid fa-star"></i> 4.9
                    </div>
                </div>
            </a>

            <a href="provider.php?id=2" class="provider-card">
                <div class="provider-image">
                    <img src="assets/images/t.jpg" alt="Provider">
                    <span class="verified">
                        <i class="fa-solid fa-circle-check"></i> Verified
                    </span>
                </div>
                <div class="provider-info">
                    <h3>Sarah John</h3>
                    <span class="profession">Plumber</span>
                    <div class="rating">
                        <i class="fa-solid fa-star"></i> 4.8
                    </div>
                </div>
            </a>

            <a href="provider.php?id=3" class="provider-card">
                <div class="provider-image">
                    <img src="assets/images/q.jpg" alt="Provider">
                    <span class="verified">
                        <i class="fa-solid fa-circle-check"></i> Verified
                    </span>
                </div>
                <div class="provider-info">
                    <h3>David Peter</h3>
                    <span class="profession">Mechanic</span>
                    <div class="rating">
                        <i class="fa-solid fa-star"></i> 5.0
                    </div>
                </div>
            </a>
        </div>
    </section>

    <script src="assets/js/main.js"></script>
</body>
</html>