<?php

declare(strict_types=1);
require_once __DIR__ . '/../../includes/auth.php';
requireRole('client');

require_once __DIR__ . '/../../config/database.php';

/*
|--------------------------------------------------------------------------
| Load Active Services
|--------------------------------------------------------------------------
*/

$services = [];

try {

    $serviceQuery = $pdo->query("
        SELECT
            ps.id,
            ps.provider_user_id,
            ps.service_name,
            ps.description,
            ps.price,
            ps.price_type,
            ps.service_image,
            ps.estimated_duration,

            u.full_name,
            pp.location_name,
            pp.latitude,
            pp.longitude,
            pp.average_rating,
            pp.total_reviews,
            pp.years_experience,
            pp.availability,
            pp.is_verified

        FROM provider_services ps

        INNER JOIN users u
            ON u.id = ps.provider_user_id

        INNER JOIN provider_profiles pp
            ON pp.user_id = ps.provider_user_id

        WHERE
            ps.status = 'active'
            AND u.role = 'provider'
            AND u.account_status = 'active'

        ORDER BY
            pp.is_verified DESC,
            pp.average_rating DESC,
            pp.total_reviews DESC,
            ps.created_at DESC

        LIMIT 12
    ");

    $services = $serviceQuery->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $services = [];

}

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
*/

function clientHomeImage(string|null $image, string $default): string
{
    if (
        !empty($image) &&
        filter_var($image, FILTER_VALIDATE_URL)
    ) {
        return $image;
    }

    if (!empty($image)) {

        $image = ltrim($image, '/');

        /*
         * Images stored inside assets/images/...
         */
        if (str_starts_with($image, 'assets/')) {
            return '../' . $image;
        }

        /*
         * Images stored as filename only.
         */
        return '../assets/images/' . $image;
    }

    return $default;
}

function formatServicePrice(
    mixed $price,
    string|null $priceType
): string {

    if ($priceType === 'negotiable') {
        return 'Negotiable';
    }

    if ($price === null || $price === '') {
        return 'Price on request';
    }

    return 'TZS ' . number_format(
        (float) $price,
        0
    );
}

function serviceRating(mixed $rating): string
{
    if ($rating === null || $rating === '') {
        return 'New';
    }

    return number_format(
        (float) $rating,
        1
    );
}

?>

<!-- =========================================================
     CLIENT HOME
========================================================= -->

<section class="client-home">

    <section class="client-hero">

        <div class="hero-content">

            <span class="hero-eyebrow">
                <i class="fa-solid fa-location-dot"></i>
                Find trusted local professionals
            </span>
            <h1> Find the right service<span>near you.</span></h1>
            <p>
                Discover trusted providers, compare services,
                and hire the right professional for your needs.
            </p>

            <form class="hero-search" action="dashboard.php" method="GET">
                <input type="hidden" name="page" value="services">

                <div class="search-input-wrapper">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="q" placeholder="What service are you looking for?" autocomplete="off">
                </div>

                <button type="submit" class="hero-search-btn"> Search</button>
            </form>
        </div>

        <div class="hero-image">
            <img src="../assets/images/client-hero.jpg" alt="FindPro services">
        </div>
    </section>


    <!-- =====================================================
         EXPLORE SERVICES
    ====================================================== -->

    <section class="dashboard-section services-section">
        <div class="section-header">
            <div>
                <span class="section-eyebrow"> DISCOVER </span>
                <h2>Explore Services</h2>
                <p>Find services from trusted providers near you.</p>
            </div>

            <a href="dashboard.php?page=services" class="section-link">
                View all
                <i class="fa-solid fa-arrow-right"></i>
            </a>

        </div>


        <?php if (!empty($services)): ?>

            <div class="services-grid">

                <?php foreach ($services as $service): ?>

                    <?php

                    $serviceName = trim(
                        (string) ($service['service_name'] ?? '')
                    );

                    $providerName = trim(
                        (string) ($service['full_name'] ?? 'Provider')
                    );

                    $location = trim(
                        (string) ($service['location_name'] ?? '')
                    );

                    $rating = serviceRating(
                        $service['average_rating'] ?? null
                    );

                    $price = formatServicePrice(
                        $service['price'] ?? null,
                        $service['price_type'] ?? null
                    );

$serviceImageName = trim(
    (string) ($service['service_image'] ?? '')
);

if ($serviceImageName !== '') {
    $serviceImage = '../assets/images/services/' . basename($serviceImageName);
} else {
    $serviceImage = '../assets/images/service-placeholder.jpg';
}

                    ?>

                    <!-- =====================================
                         SERVICE CARD
                    ====================================== -->

                    <article
                        class="service-card service-card-large"
                        data-service-id="<?= (int) $service['id']; ?>"
                    >

                        <!-- Service Image -->

                        <div class="service-card-image">

                            <img
                                src="<?= htmlspecialchars($serviceImage); ?>"
                                alt="<?= htmlspecialchars($serviceName); ?>"
                                loading="lazy"
                                onerror="this.src='../assets/images/service-placeholder.jpg';"
                            >

                            <?php if (
                                !empty($service['is_verified']) &&
                                (int) $service['is_verified'] === 1
                            ): ?>

                                <span class="service-verified">
                                    <i class="fa-solid fa-circle-check"></i>
                                    Verified
                                </span>

                            <?php endif; ?>

                        </div>


                        <!-- Service Content -->

                        <div class="service-card-body">

                            <div class="service-card-top">

                                <h3>
                                    <?= htmlspecialchars($serviceName); ?>
                                </h3>

                                <span class="service-price">
                                    <?= htmlspecialchars($price); ?>
                                </span>

                            </div>

                            <!-- Location -->

                            <?php if ($location !== ''): ?>
                                <div class="service-location">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <span>
                                        <?= htmlspecialchars($location); ?>
                                    </span>
                                </div>
                            <?php endif; ?>


                            <!-- Duration -->

                            <?php if (
                                !empty($service['estimated_duration'])
                            ): ?>

                                <div class="service-duration">
                                    <i class="fa-regular fa-clock"></i>
                                    <span>
                                        <?= htmlspecialchars(
                                            (string) $service['estimated_duration']
                                        ); ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <!-- Description -->
                            <?php if (
                                !empty($service['description'])
                            ): ?>
                                <p class="service-description">
                                    <?= htmlspecialchars(
                                        (string) $service['description']
                                    ); ?>
                                </p>
                            <?php endif; ?>


                            <!-- Actions -->
                            <div class="service-card-actions">
                                <a href="#" class="service-view-btn" data-provider-id="<?= (int) $service['provider_user_id']; ?>">
                                    View Profile
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>

                                <a href="#" class="service-hire-btn" data-service-id="<?= (int) $service['id']; ?>"
                                    data-provider-id="<?= (int) $service['provider_user_id']; ?>">
                                    <i class="fa-solid fa-briefcase"></i>
                                    Hire
                                </a>
                            </div>
                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <!-- =================================================
                 EMPTY STATE
            ================================================== -->

            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fa-solid fa-briefcase"></i>
                </div>

                <h3> No services available yet</h3>
                <p>New services from local providers will appear here.</p>
                <a href="dashboard.php?page=services" class="empty-state-btn">
                    Explore Services
                    <i class="fa-solid fa-arrow-right"></i>
                </a>

            </div>

        <?php endif; ?>

    </section>


    <!-- =====================================================
         QUICK DISCOVERY
    ====================================================== -->

    <section class="dashboard-section home-discovery-section">
        <div class="discovery-content">
            <div class="discovery-icon">
                <i class="fa-solid fa-compass"></i>
            </div>

            <div>
                <h2>Looking for something specific?</h2>
                <p>
                    Search through all available services and
                    find the professional that matches your needs.
                </p>

            </div>

            <a href="dashboard.php?page=services" class="discovery-btn">
                Find a Service
                <i class="fa-solid fa-arrow-right"></i>
            </a>

        </div>

    </section>

</section>