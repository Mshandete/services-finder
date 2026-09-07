<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';


/*
|--------------------------------------------------------------------------
| AUTHORIZATION
|--------------------------------------------------------------------------
|
| Only users with the "client" role can access this dashboard.
|
*/

requireRole('client');


/*
|--------------------------------------------------------------------------
| CURRENT PAGE
|--------------------------------------------------------------------------
|
| Get the requested page from the URL.
|
| Example:
| dashboard.php?page=home
| dashboard.php?page=services
|
*/

$page = $_GET['page'] ?? 'home';


/*
|--------------------------------------------------------------------------
| ALLOWED PAGES
|--------------------------------------------------------------------------
|
| These are the only views that can be loaded.
| This prevents unauthorized file inclusion.
|
*/

$allowedPages = [

    'home',

    'services',

    'bookings',

    'favorites',

    'messages',

    'profile',

    'settings',

    'dashboard'

];


/*
|--------------------------------------------------------------------------
| VALIDATE PAGE
|--------------------------------------------------------------------------
*/

if (!in_array($page, $allowedPages, true)) {

    $page = 'home';

}


/*
|--------------------------------------------------------------------------
| PAGE TITLES
|--------------------------------------------------------------------------
*/

$pageTitles = [

    'home'      => 'Home',

    'services'  => 'Find Services',

    'bookings'  => 'My Bookings',

    'favorites' => 'Favorites',

    'messages'  => 'Messages',

    'profile'   => 'My Profile',

    'settings'  => 'Settings',

    'dashboard' => 'Dashboard'

];


/*
|--------------------------------------------------------------------------
| CURRENT PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle = $pageTitles[$page] ?? 'Client Dashboard';


/*
|--------------------------------------------------------------------------
| VIEW PATH
|--------------------------------------------------------------------------
*/

$viewPath = __DIR__ . '/views/' . $page . '.php';


/*
|--------------------------------------------------------------------------
| FALLBACK
|--------------------------------------------------------------------------
|
| If the requested view file does not exist,
| load the home page instead.
|
*/

if (!file_exists($viewPath)) {

    $page = 'home';

    $pageTitle = 'Home';

    $viewPath = __DIR__ . '/views/home.php';

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($pageTitle); ?> | FindPro
    </title>


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <!-- Client Dashboard CSS -->

    <link
        rel="stylesheet"
        href="../assets/css/client-dashboard.css"
    >

</head>


<body>


<!-- =====================================================
     CLIENT DASHBOARD
===================================================== -->

<div class="client-dashboard">


    <!-- =================================================
         SIDEBAR
    ================================================= -->

    <?php

    require_once __DIR__ . '/components/sidebar.php';

    ?>


    <!-- =================================================
         SIDEBAR OVERLAY
         
         Used on tablet and mobile devices.
    ================================================= -->

    <div
        class="sidebar-overlay"
        id="sidebarOverlay"
    ></div>



    <!-- =================================================
         MAIN AREA
    ================================================= -->

    <main class="client-main">


        <!-- =============================================
             TOPBAR
        ============================================== -->

        <?php

        require_once __DIR__ . '/components/topbar.php';

        ?>



        <!-- =============================================
             DASHBOARD CONTENT
        ============================================== -->

        <div class="dashboard-content">


            <?php

            /*
            |--------------------------------------------------------------------------
            | LOAD CURRENT VIEW
            |--------------------------------------------------------------------------
            */

            require_once $viewPath;

            ?>


        </div>


    </main>


</div>



<!-- =====================================================
     MOBILE BOTTOM NAVIGATION
===================================================== -->

<nav class="mobile-bottom-nav">


    <!-- HOME -->

    <a
        href="dashboard.php?page=home"
        class="bottom-nav-link <?= $page === 'home' ? 'active' : ''; ?>"
    >

        <i class="fa-solid fa-house"></i>

        <span>Home</span>

    </a>



    <!-- SERVICES -->

    <a
        href="dashboard.php?page=services"
        class="bottom-nav-link <?= $page === 'services' ? 'active' : ''; ?>"
    >

        <i class="fa-solid fa-briefcase"></i>

        <span>Services</span>

    </a>



    <!-- BOOKINGS -->

    <a
        href="dashboard.php?page=bookings"
        class="bottom-nav-link <?= $page === 'bookings' ? 'active' : ''; ?>"
    >

        <i class="fa-solid fa-calendar-check"></i>

        <span>Bookings</span>

    </a>



    <!-- MESSAGES -->

    <a
        href="dashboard.php?page=messages"
        class="bottom-nav-link <?= $page === 'messages' ? 'active' : ''; ?>"
    >

        <i class="fa-solid fa-comment-dots"></i>

        <span>Messages</span>

    </a>



    <!-- PROFILE -->

    <a
        href="dashboard.php?page=profile"
        class="bottom-nav-link <?= $page === 'profile' ? 'active' : ''; ?>"
    >

        <i class="fa-solid fa-user"></i>

        <span>Profile</span>

    </a>


</nav>



<!-- =====================================================
     FOOTER
===================================================== -->

<?php

require_once __DIR__ . '/components/footer.php';

?>



<!-- =====================================================
     JAVASCRIPT
===================================================== -->

<script
    src="../assets/js/client-dashboard.js"
></script>


</body>

</html>