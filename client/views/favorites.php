<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Client Favorites
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$clientId = $_SESSION['user_id'] ?? null;


/*
|--------------------------------------------------------------------------
| Demo Favorites
|--------------------------------------------------------------------------
| Later this data will come from the favorites table.
|--------------------------------------------------------------------------
*/

$favorites = [
    [
        'id' => 1,
        'name' => 'John Plumbing Services',
        'category' => 'Plumbing',
        'service' => 'Plumbing & Pipe Repair',
        'location' => 'Mabibo, Dar es Salaam',
        'rating' => 4.9,
        'reviews' => 34,
        'price' => 'From TZS 25,000',
        'available' => true,
        'image' => '../assets/images/provider-default.jpg'
    ],

    [
        'id' => 2,
        'name' => 'Mshana Electrical Services',
        'category' => 'Electrical',
        'service' => 'Electrical Installation',
        'location' => 'Sinza, Dar es Salaam',
        'rating' => 4.8,
        'reviews' => 27,
        'price' => 'From TZS 30,000',
        'available' => true,
        'image' => '../assets/images/provider-default.jpg'
    ],

    [
        'id' => 3,
        'name' => 'CleanPro Tanzania',
        'category' => 'Cleaning',
        'service' => 'Home & Office Cleaning',
        'location' => 'Kinondoni, Dar es Salaam',
        'rating' => 4.7,
        'reviews' => 19,
        'price' => 'From TZS 40,000',
        'available' => false,
        'image' => '../assets/images/provider-default.jpg'
    ]
];

?>

<div class="favorites-page">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="favorites-header">

        <div>

            <span class="favorites-label">
                <i class="fa-regular fa-heart"></i>
                Saved Providers
            </span>

            <h1>Favorites</h1>

            <p>
                Keep the service providers you trust close at hand.
            </p>

        </div>

        <a
            href="dashboard.php?page=services"
            class="find-provider-link"
        >
            <i class="fa-solid fa-plus"></i>
            Find Providers
        </a>

    </div>


    <!-- =====================================================
         FAVORITES COUNT
    ====================================================== -->

    <div class="favorites-intro">

        <div>

            <strong>
                <?= count($favorites); ?>
            </strong>

            <span>
                <?= count($favorites) === 1 ? 'saved provider' : 'saved providers'; ?>
            </span>

        </div>

        <div class="favorites-note">

            <i class="fa-regular fa-bookmark"></i>

            Your saved providers appear here.

        </div>

    </div>


    <!-- =====================================================
         FAVORITES LIST
    ====================================================== -->

    <?php if (empty($favorites)): ?>

        <div class="favorites-empty">

            <div class="favorites-empty-icon">
                <i class="fa-regular fa-heart"></i>
            </div>

            <h2>No favorites yet</h2>

            <p>
                When you find a provider you like, save them here
                so you can easily come back later.
            </p>

            <a href="dashboard.php?page=services">
                Explore Services
                <i class="fa-solid fa-arrow-right"></i>
            </a>

        </div>

    <?php else: ?>

        <div class="favorites-list">

            <?php foreach ($favorites as $favorite): ?>

                <article
                    class="favorite-item"
                    data-favorite-id="<?= (int) $favorite['id']; ?>"
                >


                    <!-- PROVIDER IMAGE -->

                    <div class="favorite-provider">

                        <div class="favorite-image">

                            <img
                                src="<?= htmlspecialchars($favorite['image']); ?>"
                                alt="<?= htmlspecialchars($favorite['name']); ?>"
                                onerror="this.src='../assets/images/default-provider.png';"
                            >

                            <?php if ($favorite['available']): ?>

                                <span
                                    class="availability-dot"
                                    title="Available"
                                ></span>

                            <?php endif; ?>

                        </div>


                        <div class="favorite-provider-info">

                            <span class="favorite-category">
                                <?= htmlspecialchars($favorite['category']); ?>
                            </span>

                            <h3>
                                <?= htmlspecialchars($favorite['name']); ?>
                            </h3>

                            <p>
                                <?= htmlspecialchars($favorite['service']); ?>
                            </p>

                        </div>

                    </div>


                    <!-- PROVIDER DETAILS -->

                    <div class="favorite-details">

                        <div class="favorite-location">

                            <i class="fa-solid fa-location-dot"></i>

                            <span>
                                <?= htmlspecialchars($favorite['location']); ?>
                            </span>

                        </div>


                        <div class="favorite-rating">

                            <span class="rating-star">
                                <i class="fa-solid fa-star"></i>
                            </span>

                            <strong>
                                <?= htmlspecialchars((string) $favorite['rating']); ?>
                            </strong>

                            <span>
                                (<?= (int) $favorite['reviews']; ?>)
                            </span>

                        </div>


                        <span class="favorite-price">
                            <?= htmlspecialchars($favorite['price']); ?>
                        </span>

                    </div>


                    <!-- ACTIONS -->

                    <div class="favorite-actions">

                        <button
                            type="button"
                            class="remove-favorite"
                            data-id="<?= (int) $favorite['id']; ?>"
                            aria-label="Remove from favorites"
                            title="Remove from favorites"
                        >
                            <i class="fa-solid fa-heart"></i>
                        </button>


                        <a
                            href="dashboard.php?page=services&provider=<?= (int) $favorite['id']; ?>"
                            class="view-provider"
                        >
                            View
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         SMALL FOOT NOTE
    ====================================================== -->

    <?php if (!empty($favorites)): ?>

        <div class="favorites-footer-note">

            <i class="fa-regular fa-lightbulb"></i>

            <span>
                Tip: Save providers you may want to hire again.
            </span>

        </div>

    <?php endif; ?>


</div>


<script>

document.addEventListener("DOMContentLoaded", function () {

    /*
    |--------------------------------------------------------------------------
    | Remove favorite
    |--------------------------------------------------------------------------
    */

    const removeButtons =
        document.querySelectorAll(".remove-favorite");


    removeButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            const favoriteItem =
                this.closest(".favorite-item");

            if (!favoriteItem) {
                return;
            }


            /*
            | Temporary UI behaviour.
            | Database delete will be connected later.
            */

            favoriteItem.classList.add("favorite-removing");


            setTimeout(function () {

                favoriteItem.remove();


                const remaining =
                    document.querySelectorAll(".favorite-item").length;


                const countElement =
                    document.querySelector(".favorites-intro strong");

                const countText =
                    document.querySelector(".favorites-intro > div:first-child span");


                if (countElement) {
                    countElement.textContent = remaining;
                }


                if (countText) {

                    countText.textContent =
                        remaining === 1
                            ? "saved provider"
                            : "saved providers";

                }


                /*
                |--------------------------------------------------------------------------
                | Show empty state when everything is removed
                |--------------------------------------------------------------------------
                */

                if (remaining === 0) {

                    const list =
                        document.querySelector(".favorites-list");

                    if (list) {
                        list.remove();
                    }

                    const empty =
                        document.querySelector(".favorites-empty");

                    if (empty) {
                        empty.style.display = "block";
                    }

                }

            }, 250);

        });

    });

});

</script>