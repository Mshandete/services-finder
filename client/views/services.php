<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

/*
|--------------------------------------------------------------------------
| Current Client
|--------------------------------------------------------------------------
*/

$currentClientId = (int)($_SESSION['user_id'] ?? 0);

/*
|--------------------------------------------------------------------------
| Provider Profile View
|--------------------------------------------------------------------------
|
| dashboard.php?page=services&provider_id=XX
|
*/

$providerId = isset($_GET['provider_id'])
    ? (int)$_GET['provider_id']
    : 0;

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('clientServiceImage')) {
    function clientServiceImage(?string $image): ?string
    {
        if (!$image) {
            return null;
        }

        $image = basename($image);

        $path = __DIR__ . '/../../assets/images/services/' . $image;

        return file_exists($path)
            ? '../assets/images/services/' . $image
            : null;
    }
}

if (!function_exists('providerProfileImage')) {
    function providerProfileImage(?string $image): ?string
    {
        if (!$image) {
            return null;
        }

        $image = basename($image);

        $path = __DIR__ . '/../../assets/images/providers/' . $image;

        return file_exists($path)
            ? '../assets/images/providers/' . $image
            : null;
    }
}

if (!function_exists('providerCoverImage')) {
    function providerCoverImage(?string $image): ?string
    {
        if (!$image) {
            return null;
        }

        $image = basename($image);

        $path = __DIR__ . '/../../assets/images/providers/covers/' . $image;

        return file_exists($path)
            ? '../assets/images/providers/covers/' . $image
            : null;
    }
}

/*
|--------------------------------------------------------------------------
| PROVIDER PROFILE
|--------------------------------------------------------------------------
*/

if ($providerId > 0) {

    $providerStmt = $pdo->prepare("
        SELECT
            u.id,
            u.full_name,
            u.phone,
            pp.bio,
            pp.profile_image,
            pp.cover_image,
            pp.years_experience,
            pp.location_name,
            pp.availability,
            pp.is_verified,
            pp.average_rating,
            pp.total_reviews
        FROM users u
        INNER JOIN provider_profiles pp
            ON pp.user_id = u.id
        WHERE u.id = ?
          AND u.role = 'provider'
          AND u.account_status = 'active'
        LIMIT 1
    ");

    $providerStmt->execute([$providerId]);

    $provider = $providerStmt->fetch(PDO::FETCH_ASSOC);

    if (!$provider) {
        ?>

        <section class="client-bookings">
            <div class="empty-bookings">

                <div class="empty-icon">
                    <i class="fa-solid fa-user-slash"></i>
                </div>

                <h2>Provider not found</h2>

                <p>
                    This provider is no longer available or the profile
                    could not be found.
                </p>

                <a
                    href="dashboard.php?page=services"
                    class="find-service-button"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to services
                </a>

            </div>
        </section>

        <?php
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Provider Services
    |--------------------------------------------------------------------------
    */

    $servicesStmt = $pdo->prepare("
        SELECT
            ps.id,
            ps.provider_user_id,
            ps.service_name,
            ps.description,
            ps.price,
            ps.price_type,
            ps.service_image,
            ps.estimated_duration,
            ps.status
        FROM provider_services ps
        WHERE ps.provider_user_id = ?
          AND ps.status = 'active'
        ORDER BY ps.created_at DESC
    ");

    $servicesStmt->execute([$providerId]);

    $providerServices = $servicesStmt->fetchAll(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | Provider Images
    |--------------------------------------------------------------------------
    */

    $profileImage = providerProfileImage(
        $provider['profile_image'] ?? null
    );

    $coverImage = providerCoverImage(
        $provider['cover_image'] ?? null
    );

    /*
    |--------------------------------------------------------------------------
    | Availability
    |--------------------------------------------------------------------------
    */

    $availability = $provider['availability'] ?? 'available';

    $availabilityLabel = match ($availability) {
        'available' => 'Available',
        'busy' => 'Busy',
        'offline' => 'Offline',
        default => ucfirst($availability)
    };

    ?>

    <section class="provider-profile-page">

        <!-- Back -->
        <div class="provider-profile-topbar">

            <a
                href="dashboard.php?page=services"
                class="provider-back-button"
            >
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to services</span>
            </a>

        </div>


        <!-- =====================================================
             PROVIDER HERO
             ===================================================== -->

        <div class="provider-profile-hero">

            <div class="provider-cover">

                <?php if ($coverImage): ?>

                    <img
                        src="<?= htmlspecialchars($coverImage) ?>"
                        alt="<?= htmlspecialchars($provider['full_name']) ?>"
                    >

                <?php else: ?>

                    <div class="provider-cover-placeholder"></div>

                <?php endif; ?>

            </div>


            <div class="provider-profile-main">

                <div class="provider-avatar">

                    <?php if ($profileImage): ?>

                        <img
                            src="<?= htmlspecialchars($profileImage) ?>"
                            alt="<?= htmlspecialchars($provider['full_name']) ?>"
                        >

                    <?php else: ?>

                        <div class="provider-avatar-placeholder">
                            <i class="fa-solid fa-user"></i>
                        </div>

                    <?php endif; ?>

                </div>


                <div class="provider-identity">

                    <div class="provider-name-row">

                        <h1>
                            <?= htmlspecialchars($provider['full_name']) ?>
                        </h1>

                        <?php if ((int)$provider['is_verified'] === 1): ?>

                            <span
                                class="provider-verified"
                                title="Verified provider"
                            >
                                <i class="fa-solid fa-circle-check"></i>
                            </span>

                        <?php endif; ?>

                    </div>


                    <div class="provider-rating">

                        <i class="fa-solid fa-star"></i>

                        <strong>
                            <?= number_format(
                                (float)$provider['average_rating'],
                                1
                            ) ?>
                        </strong>

                        <span>
                            (<?= (int)$provider['total_reviews'] ?> reviews)
                        </span>

                    </div>


                    <div class="provider-location">

                        <i class="fa-solid fa-location-dot"></i>

                        <span>
                            <?= htmlspecialchars(
                                $provider['location_name'] ?: 'Location not provided'
                            ) ?>
                        </span>

                    </div>

                </div>


                <div class="provider-availability availability-<?= htmlspecialchars($availability) ?>">

                    <span class="availability-dot"></span>

                    <?= htmlspecialchars($availabilityLabel) ?>

                </div>

            </div>

        </div>


        <!-- =====================================================
             PROVIDER INFORMATION
             ===================================================== -->

        <div class="provider-profile-content">

            <div class="provider-about">

                <div class="provider-section-heading">

                    <div>
                        <span class="section-label">
                            About provider
                        </span>

                        <h2>
                            <?= htmlspecialchars($provider['full_name']) ?>
                        </h2>
                    </div>

                </div>


                <?php if (!empty($provider['bio'])): ?>

                    <p class="provider-bio">
                        <?= nl2br(
                            htmlspecialchars($provider['bio'])
                        ) ?>
                    </p>

                <?php else: ?>

                    <p class="provider-bio provider-bio-empty">
                        This provider has not added an introduction yet.
                    </p>

                <?php endif; ?>


                <div class="provider-facts">

                    <div class="provider-fact">

                        <i class="fa-solid fa-briefcase"></i>

                        <div>
                            <span>Experience</span>

                            <strong>
                                <?= (int)$provider['years_experience'] ?>
                                <?= (int)$provider['years_experience'] === 1
                                    ? 'year'
                                    : 'years' ?>
                            </strong>
                        </div>

                    </div>


                    <div class="provider-fact">

                        <i class="fa-solid fa-star"></i>

                        <div>
                            <span>Rating</span>

                            <strong>
                                <?= number_format(
                                    (float)$provider['average_rating'],
                                    1
                                ) ?>
                            </strong>
                        </div>

                    </div>


                    <div class="provider-fact">

                        <i class="fa-solid fa-location-dot"></i>

                        <div>
                            <span>Location</span>

                            <strong>
                                <?= htmlspecialchars(
                                    $provider['location_name'] ?: 'Not provided'
                                ) ?>
                            </strong>
                        </div>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 PROVIDER SERVICES
                 ================================================= -->

            <div class="provider-services-section">

                <div class="provider-section-heading">

                    <div>

                        <span class="section-label">
                            What they offer
                        </span>

                        <h2>
                            Services
                        </h2>

                    </div>

                    <span class="provider-service-count">
                        <?= count($providerServices) ?>
                        <?= count($providerServices) === 1
                            ? 'service'
                            : 'services' ?>
                    </span>

                </div>


                <?php if (!$providerServices): ?>

                    <div class="provider-empty-services">

                        <i class="fa-solid fa-briefcase"></i>

                        <h3>
                            No services available
                        </h3>

                        <p>
                            This provider currently has no active services.
                        </p>

                    </div>

                <?php else: ?>

                    <div class="provider-services-list">

                        <?php foreach ($providerServices as $service): ?>

                            <?php
                            $serviceImage = clientServiceImage(
                                $service['service_image'] ?? null
                            );
                            ?>

                            <article class="provider-service-item">

                                <div class="provider-service-image">

                                    <?php if ($serviceImage): ?>

                                        <img
                                            src="<?= htmlspecialchars($serviceImage) ?>"
                                            alt="<?= htmlspecialchars($service['service_name']) ?>"
                                        >

                                    <?php else: ?>

                                        <div class="provider-service-placeholder">
                                            <i class="fa-solid fa-briefcase"></i>
                                        </div>

                                    <?php endif; ?>

                                </div>


                                <div class="provider-service-info">

                                    <h3>
                                        <?= htmlspecialchars(
                                            $service['service_name']
                                        ) ?>
                                    </h3>


                                    <?php if (!empty($service['description'])): ?>

                                        <p>
                                            <?= htmlspecialchars(
                                                mb_strimwidth(
                                                    $service['description'],
                                                    0,
                                                    150,
                                                    '...'
                                                )
                                            ) ?>
                                        </p>

                                    <?php endif; ?>


                                    <div class="provider-service-meta">

                                        <?php if ($service['price'] !== null): ?>

                                            <span class="provider-service-price">

                                                <?= number_format(
                                                    (float)$service['price'],
                                                    0
                                                ) ?>
                                                TZS

                                                <?php if (
                                                    $service['price_type'] === 'negotiable'
                                                ): ?>

                                                    <small>
                                                        Negotiable
                                                    </small>

                                                <?php endif; ?>

                                            </span>

                                        <?php else: ?>

                                            <span class="provider-service-price">
                                                Price on request
                                            </span>

                                        <?php endif; ?>


                                        <?php if (!empty($service['estimated_duration'])): ?>

                                            <span>
                                                <i class="fa-regular fa-clock"></i>

                                                <?= htmlspecialchars(
                                                    $service['estimated_duration']
                                                ) ?>
                                            </span>

                                        <?php endif; ?>

                                    </div>

                                </div>


                                <div class="provider-service-action">

                                    <a
                                        href="dashboard.php?page=bookings&action=create&service_id=<?= (int)$service['id'] ?>"
                                        class="provider-hire-button"
                                    >
                                        Hire
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </a>

                                </div>

                            </article>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>


            <!-- =================================================
                 OTHER ITEMS / PRODUCTS
                 ================================================= -->

            <div class="provider-items-section">

                <div class="provider-section-heading">

                    <div>

                        <span class="section-label">
                            More from this provider
                        </span>

                        <h2>
                            Items & Products
                        </h2>

                    </div>

                </div>


                <div class="provider-items-coming-soon">

                    <div class="provider-items-icon">
                        <i class="fa-solid fa-box-open"></i>
                    </div>

                    <div>

                        <h3>
                            More items coming soon
                        </h3>

                        <p>
                            Providers will be able to showcase other
                            products and items they offer here.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </section>

    <?php

    return;
}

/*
|--------------------------------------------------------------------------
| EXPLORE SERVICES
|--------------------------------------------------------------------------
*/

$search = trim(
    (string)($_GET['search'] ?? '')
);

$params = [];

$sql = "
    SELECT
        ps.id,
        ps.provider_user_id,
        ps.service_name,
        ps.description,
        ps.price,
        ps.price_type,
        ps.service_image,
        ps.estimated_duration,
        ps.category_id,

        u.full_name AS provider_name,

        pp.profile_image,
        pp.location_name,
        pp.availability,
        pp.is_verified,
        pp.average_rating,
        pp.total_reviews

    FROM provider_services ps

    INNER JOIN users u
        ON u.id = ps.provider_user_id

    LEFT JOIN provider_profiles pp
        ON pp.user_id = ps.provider_user_id

    WHERE ps.status = 'active'
      AND u.role = 'provider'
      AND u.account_status = 'active'
";

if ($search !== '') {

    $sql .= "
        AND (
            ps.service_name LIKE ?
            OR ps.description LIKE ?
            OR u.full_name LIKE ?
            OR pp.location_name LIKE ?
        )
    ";

    $searchValue = '%' . $search . '%';

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
}

$sql .= "
    ORDER BY
        pp.is_verified DESC,
        pp.average_rating DESC,
        ps.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<section class="client-services-page">

    <!-- =====================================================
         HEADER
         ===================================================== -->

    <div class="services-page-header">

        <div>

            <span class="section-label">
                FindPro
            </span>

            <h1>
                Explore Services
            </h1>

            <p>
                Find a provider and request the service you need.
            </p>

        </div>

    </div>


    <!-- =====================================================
         SEARCH
         ===================================================== -->

    <form
        method="GET"
        action="dashboard.php"
        class="services-search-form"
    >

        <input
            type="hidden"
            name="page"
            value="services"
        >

        <div class="services-search-box">

            <i class="fa-solid fa-magnifying-glass"></i>

            <input
                type="text"
                name="search"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Search for a service..."
                autocomplete="off"
            >

            <?php if ($search !== ''): ?>

                <a
                    href="dashboard.php?page=services"
                    class="services-search-clear"
                    aria-label="Clear search"
                >
                    <i class="fa-solid fa-xmark"></i>
                </a>

            <?php endif; ?>

        </div>

        <button
            type="submit"
            class="services-search-button"
        >
            Search
        </button>

    </form>


    <!-- =====================================================
         RESULTS
         ===================================================== -->

    <?php if (!$services): ?>

        <div class="services-empty-state">

            <div class="services-empty-icon">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>

            <h2>
                No services found
            </h2>

            <p>
                Try searching with a different service name or keyword.
            </p>

            <?php if ($search !== ''): ?>

                <a
                    href="dashboard.php?page=services"
                    class="find-service-button"
                >
                    View all services
                </a>

            <?php endif; ?>

        </div>

    <?php else: ?>

        <div class="services-results-heading">

            <span>
                <?= count($services) ?>
                <?= count($services) === 1
                    ? 'service'
                    : 'services' ?>
                available
            </span>

        </div>


        <div class="services-results-grid">

            <?php foreach ($services as $service): ?>

                <?php
                $serviceImage = clientServiceImage(
                    $service['service_image'] ?? null
                );
                ?>

                <article class="client-service-card">

                    <div class="client-service-image">

                        <?php if ($serviceImage): ?>

                            <img
                                src="<?= htmlspecialchars($serviceImage) ?>"
                                alt="<?= htmlspecialchars($service['service_name']) ?>"
                            >

                        <?php else: ?>

                            <div class="client-service-image-placeholder">
                                <i class="fa-solid fa-briefcase"></i>
                            </div>

                        <?php endif; ?>

                    </div>


                    <div class="client-service-content">

                        <div class="client-service-title-row">

                            <h2>
                                <?= htmlspecialchars(
                                    $service['service_name']
                                ) ?>
                            </h2>

                            <?php if ((int)$service['is_verified'] === 1): ?>

                                <i
                                    class="fa-solid fa-circle-check client-service-verified"
                                    title="Verified provider"
                                ></i>

                            <?php endif; ?>

                        </div>


                        <div class="client-service-provider">

                            <i class="fa-solid fa-user"></i>

                            <span>
                                <?= htmlspecialchars(
                                    $service['provider_name']
                                ) ?>
                            </span>

                        </div>


                        <div class="client-service-rating">

                            <i class="fa-solid fa-star"></i>

                            <strong>
                                <?= number_format(
                                    (float)$service['average_rating'],
                                    1
                                ) ?>
                            </strong>

                            <span>
                                (<?= (int)$service['total_reviews'] ?>)
                            </span>

                        </div>


                        <div class="client-service-location">

                            <i class="fa-solid fa-location-dot"></i>

                            <span>
                                <?= htmlspecialchars(
                                    $service['location_name']
                                    ?: 'Location not provided'
                                ) ?>
                            </span>

                        </div>


                        <div class="client-service-bottom">

                            <?php if ($service['price'] !== null): ?>

                                <div class="client-service-price">

                                    <strong>
                                        <?= number_format(
                                            (float)$service['price'],
                                            0
                                        ) ?>
                                        TZS
                                    </strong>

                                    <?php if (
                                        $service['price_type'] === 'negotiable'
                                    ): ?>

                                        <span>
                                            Negotiable
                                        </span>

                                    <?php endif; ?>

                                </div>

                            <?php else: ?>

                                <div class="client-service-price">

                                    <strong>
                                        Price on request
                                    </strong>

                                </div>

                            <?php endif; ?>


                            <?php if (!empty($service['estimated_duration'])): ?>

                                <span class="client-service-duration">

                                    <i class="fa-regular fa-clock"></i>

                                    <?= htmlspecialchars(
                                        $service['estimated_duration']
                                    ) ?>

                                </span>

                            <?php endif; ?>

                        </div>


                        <div class="client-service-actions">

                            <!-- VIEW PROFILE -->

                            <a
                                href="dashboard.php?page=services&provider_id=<?= (int)$service['provider_user_id'] ?>"
                                class="service-view-profile"
                            >
                                View Profile
                            </a>


                            <!-- HIRE -->

                            <a
                                href="dashboard.php?page=bookings&action=create&service_id=<?= (int)$service['id'] ?>"
                                class="service-hire-button"
                            >
                                Hire
                            </a>

                        </div>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>