<?php
$userName = $_SESSION['full_name'] ?? 'Client';
?>

<!-- HERO SECTION -->
<section class="hero-section">
    <div class="hero-container">
        <div class="hero-left">
            <div class="hero-badge">
                <i class="fa-solid fa-shield-halved"></i>
                <span>VERIFIED LOCAL PROFESSIONALS</span>
            </div>

            <h1>What service are you looking for today?</h1>
            <p>
                Find trusted professionals near your location. Search, compare profiles, and hire with confidence.
            </p>

            <form class="hero-search-form" action="#" method="GET">
                <div class="search-input-group">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" name="query" placeholder="Search electrician, plumber, tutor...">
                </div>
                <button type="submit" class="btn-search">Search</button>
            </form>

            <div class="popular-tags">
                <span class="tag-title">Popular:</span>
                <a href="#" class="tag-chip">Electrician</a>
                <a href="#" class="tag-chip">Plumber</a>
                <a href="#" class="tag-chip">Mechanic</a>
                <a href="#" class="tag-chip">Cleaner</a>
            </div>
        </div>

        <div class="hero-right">
            <div class="provider-preview-card">
                <div class="status-badge online">
                    <span class="status-dot"></span> Available Today
                </div>

                <div class="preview-avatar">
                    <img src="../../assets/images/mshandete.webp" alt="John Michael" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=John+Michael&background=10b981&color=fff';">
                </div>

                <h3>John Michael</h3>
                <p class="profession-subtitle">Professional Electrician</p>

                <div class="preview-meta">
                    <span class="meta-item rating">
                        <i class="fa-solid fa-star"></i> 4.9
                    </span>
                    <span class="meta-item location">
                        <i class="fa-solid fa-location-dot"></i> 1.2 km
                    </span>
                </div>

                <a href="#" class="btn-preview-profile">View Profile</a>
            </div>
        </div>
    </div>
</section>

<!-- QUICK CATEGORIES SECTION -->
<section class="categories-section">
    <div class="section-header">
        <h2>Browse Categories</h2>
        <p>Explore the most requested services.</p>
    </div>

    <div class="categories-grid">
        <a href="#" class="category-card">
            <div class="icon-wrapper"><i class="fa-solid fa-bolt"></i></div>
            <span>Electrician</span>
        </a>
        <a href="#" class="category-card">
            <div class="icon-wrapper"><i class="fa-solid fa-faucet-drip"></i></div>
            <span>Plumber</span>
        </a>
        <a href="#" class="category-card">
            <div class="icon-wrapper"><i class="fa-solid fa-car"></i></div>
            <span>Mechanic</span>
        </a>
        <a href="#" class="category-card">
            <div class="icon-wrapper"><i class="fa-solid fa-paint-roller"></i></div>
            <span>Painter</span>
        </a>
        <a href="#" class="category-card">
            <div class="icon-wrapper"><i class="fa-solid fa-laptop-code"></i></div>
            <span>IT Support</span>
        </a>
        <a href="#" class="category-card">
            <div class="icon-wrapper"><i class="fa-solid fa-broom"></i></div>
            <span>Cleaner</span>
        </a>
        <a href="#" class="category-card">
            <div class="icon-wrapper"><i class="fa-solid fa-scissors"></i></div>
            <span>Salon</span>
        </a>
        <a href="#" class="category-card">
            <div class="icon-wrapper"><i class="fa-solid fa-graduation-cap"></i></div>
            <span>Tutor</span>
        </a>
    </div>
</section>

<!-- FEATURED PROFESSIONALS SECTION -->
<section class="featured-section">
    <div class="section-header flex-header">
        <div>
            <h2>Featured Professionals</h2>
            <p>Highly rated professionals available today.</p>
        </div>
        <a href="#" class="link-view-all">View All <i class="fa-solid fa-arrow-right"></i></a>
    </div>

    <div class="featured-grid">
        <?php for($i = 1; $i <= 6; $i++): ?>
        <article class="pro-card">
            <div class="pro-avatar-wrapper">
                <!-- Hapa tulirekebisha 'rc' kuwa 'src' -->
                <img src="assets/images/mshandete.webp" alt="John Michael" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=John+Michael&background=10b981&color=fff';">
                <span class="verified-badge" title="Verified Professional">
                    <i class="fa-solid fa-circle-check"></i>
                </span>
            </div>

            <div class="pro-details">
                <h3>John Michael</h3>
                <span class="pro-badge">Electrician</span>

                <div class="pro-meta-info">
                    <span class="rating-info">
                        <i class="fa-solid fa-star"></i> 4.9
                    </span>
                    <span class="location-info">
                        <i class="fa-solid fa-location-dot"></i> Dar es Salaam
                    </span>
                </div>

                <a href="#" class="btn-card-action">View Profile</a>
            </div>
        </article>
        <?php endfor; ?>
    </div>
</section>