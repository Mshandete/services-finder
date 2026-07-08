<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Providers - Services Finder</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/providers.css">
</head>

<body>

<!-- ================= NAVBAR ================= -->

<header>
    <div class="logo">
        <i class="fa-solid fa-location-dot"></i>
        <span>Services Finder</span>
    </div>

    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="#">Services</a></li>
            <li><a href="providers.php" class="active">Providers</a></li>
            <li><a href="#">About</a></li>
            <li><a href="#">Contact</a></li>
        </ul>
    </nav>

    <div class="buttons">
        <a href="#" class="login">Login</a>
        <a href="#" class="register">Register</a>
    </div>
</header>

<!-- ================= HERO ================= -->

<section class="providers-hero">

    <div class="overlay"></div>

    <div class="providers-hero-content">

        <h1>Find Trusted Service Providers</h1>

        <p>Search and connect with verified professionals near you</p>

    </div>

</section>

<!-- ================= FILTER SECTION ================= -->

<section class="filter-section">

    <div class="filter-box">

        <input type="text" placeholder="Search service (e.g Electrician)">

        <select>
            <option>All Categories</option>
            <option>Electrician</option>
            <option>Plumber</option>
            <option>Mechanic</option>
            <option>Cleaner</option>
        </select>

        <input type="text" placeholder="Location">

        <button><i class="fa-solid fa-magnifying-glass"></i> Search</button>

    </div>

</section>

<!-- ================= PROVIDERS GRID ================= -->

<section class="providers-section">

    <div class="providers-grid">

        <!-- CARD 1 -->
        <a href="provider.php?id=1" class="provider-card">

            <div class="card-img">
                <img src="assets/images/y.jpg">
                <span class="badge">Verified</span>
            </div>

            <div class="card-body">
                <h3>John Michael</h3>
                <p class="job">Electrician</p>

                <div class="rating">
                    <i class="fa-solid fa-star"></i> 4.9
                </div>
            </div>

        </a>

        <!-- CARD 2 -->
        <a href="provider.php?id=2" class="provider-card">

            <div class="card-img">
                <img src="assets/images/t.jpg">
                <span class="badge">Verified</span>
            </div>

            <div class="card-body">
                <h3>Sarah John</h3>
                <p class="job">Plumber</p>

                <div class="rating">
                    <i class="fa-solid fa-star"></i> 4.8
                </div>
            </div>

        </a>

        <!-- CARD 3 -->
        <a href="provider.php?id=3" class="provider-card">

            <div class="card-img">
                <img src="assets/images/q.jpg">
                <span class="badge">Verified</span>
            </div>

            <div class="card-body">
                <h3>David Peter</h3>
                <p class="job">Mechanic</p>

                <div class="rating">
                    <i class="fa-solid fa-star"></i> 5.0
                </div>
            </div>

        </a>

    </div>

</section>

<!-- ================= PAGINATION ================= -->

<div class="pagination">

    <span class="page active">1</span>
    <span class="page">2</span>
    <span class="page">3</span>

</div>

<script src="assets/js/script.js"></script>

</body>
</html>