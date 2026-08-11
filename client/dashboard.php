<?php
declare(strict_types=1);

require_once "../includes/auth.php";
requireRole("client");

/*=========================================
    AVAILABLE PAGES
=========================================*/

$page=$_GET['page']??'home';

$allowedPages=[
'home',
'services',
'bookings',
'favorites',
'messages',
'profile',
'settings'
];

if(!in_array($page,$allowedPages)){
$page='home';
}

$viewFile=__DIR__."/views/{$page}.php";

if(!file_exists($viewFile)){
$page='home';
$viewFile=__DIR__."/views/home.php";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Client Dashboard | FindPro</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/home.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="../assets/css/bookings.css">
    <link rel="stylesheet" href="../assets/css/favorites.css">
    <link rel="stylesheet" href="../assets/css/messages.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>

<div class="wrapper">

    <?php include __DIR__.'/components/sidebar.php'; ?>

    <main class="main">

        <?php include __DIR__.'/components/topbar.php'; ?>


        <section class="content">

            <?php include $viewFile; ?>

        </section>

    </main>



</div>

<?php include __DIR__.'/components/footer.php'; ?>

<script src="../assets/js/dashboard.js"></script>

</body>

</html>