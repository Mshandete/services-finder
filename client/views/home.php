<section class="client-hero">
    <div class="hero-content">
        <div class="hero-label">
            <i class="fa-solid fa-location-crosshairs"></i>
            <span>Find trusted professionals near you</span>
        </div>

        <div class="hero-text">
            <h1>Find the right professional for every job.</h1>
            <p>Discover trusted service providers, compare their profiles and connect with the right professional for your needs.</p>
        </div>

        <form class="hero-search" action="dashboard.php" method="GET">
            <input type="hidden" name="page" value="services">

            <div class="hero-search-input">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" name="search" placeholder="What service are you looking for?" autocomplete="off">
            </div>

            <button type="submit" class="hero-search-button">
                Search
            </button>
        </form>
    </div>

    <div class="hero-image">
        <img src="../assets/images/client-hero.jpg" alt="Find professional services">
    </div>
</section>

<section class="dashboard-section services-section">
    <div class="section-header">
        <div>
            <h2>Popular Services</h2>
            <p>Explore services available from trusted professionals.</p>
        </div>

        <a href="dashboard.php?page=services" class="section-link">
            View all
            <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <div class="services-grid">
        <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $category): ?>
                <a href="dashboard.php?page=services&category=<?= (int) $category['id']; ?>" class="service-card">
                    <div class="service-icon">
                        <?php if (!empty($category['category_icon'])): ?>
                            <i class="<?= htmlspecialchars($category['category_icon']); ?>"></i>
                        <?php else: ?>
                            <i class="fa-solid fa-layer-group"></i>
                        <?php endif; ?>
                    </div>

                    <div class="service-card-info">
                        <span><?= htmlspecialchars($category['category_name']); ?></span>
                        <small>
                            <?= (int) $category['provider_count']; ?>
                            <?= (int) $category['provider_count'] === 1 ? 'provider' : 'providers'; ?>
                        </small>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-layer-group"></i>
                <p>No services available yet.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="dashboard-section providers-section">
    <div class="section-header">
        <div>
            <h2>Recommended Providers</h2>
            <p>Professionals selected based on availability, trust and experience.</p>
        </div>

        <a href="dashboard.php?page=services" class="section-link">
            View all
            <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <div class="providers-grid">
        <?php if (!empty($recommendedProviders)): ?>
            <?php foreach ($recommendedProviders as $provider): ?>
                <?php
                $profileImage = !empty($provider['profile_image'])
                    ? $provider['profile_image']
                    : 'default.jpg';

                $coverImage = !empty($provider['cover_image'])
                    ? $provider['cover_image']
                    : 'default-cover.jpg';

                $services = !empty($provider['services'])
                    ? explode(', ', $provider['services'])
                    : [];
                ?>

                <article class="provider-card">
                    <div class="provider-cover">
                        <img src="../assets/images/providers/<?= htmlspecialchars($coverImage); ?>" alt="<?= htmlspecialchars($provider['full_name']); ?>">

                        <?php if ((int) $provider['is_verified'] === 1): ?>
                            <span class="verified-badge">
                                <i class="fa-solid fa-circle-check"></i>
                                Verified
                            </span>
                        <?php endif; ?>

                        <button type="button" class="favorite-provider" aria-label="Add <?= htmlspecialchars($provider['full_name']); ?> to favorites">
                            <i class="fa-regular fa-heart"></i>
                        </button>
                    </div>

                    <div class="provider-card-content">
                        <div class="provider-profile-row">
                            <img src="../assets/images/providers/<?= htmlspecialchars($profileImage); ?>" alt="<?= htmlspecialchars($provider['full_name']); ?>" class="provider-avatar">

                            <div>
                                <h3>
                                    <?= htmlspecialchars($provider['full_name']); ?>

                                    <?php if ((int) $provider['is_verified'] === 1): ?>
                                        <i class="fa-solid fa-circle-check"></i>
                                    <?php endif; ?>
                                </h3>

                                <p>
                                    <?= !empty($services)
                                        ? htmlspecialchars($services[0])
                                        : 'Service Provider'; ?>
                                </p>
                            </div>
                        </div>

                        <div class="provider-rating">
                            <span>
                                <i class="fa-solid fa-star"></i>
                                <?= number_format((float) $provider['average_rating'], 1); ?>
                            </span>

                            <small>
                                (<?= (int) $provider['total_reviews']; ?> reviews)
                            </small>
                        </div>

                        <div class="provider-details">
                            <?php if (!empty($provider['location_name'])): ?>
                                <span>
                                    <i class="fa-solid fa-location-dot"></i>
                                    <?= htmlspecialchars($provider['location_name']); ?>
                                </span>
                            <?php endif; ?>

                            <span>
                                <i class="fa-solid fa-briefcase"></i>
                                <?= (int) $provider['years_experience']; ?>
                                <?= (int) $provider['years_experience'] === 1 ? 'year' : 'years'; ?>
                                experience
                            </span>
                        </div>

                        <?php if (!empty($services)): ?>
                            <div class="provider-tags">
                                <?php foreach (array_slice($services, 0, 3) as $service): ?>
                                    <span><?= htmlspecialchars($service); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="provider-actions">
                            <a href="#" class="view-profile-button">
                                View Profile
                            </a>

                            <a href="#" class="hire-button">
                                Hire Now
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-users-slash"></i>
                <h3>No providers available</h3>
                <p>There are currently no active providers with services.</p>
            </div>
        <?php endif; ?>
    </div>
</section>