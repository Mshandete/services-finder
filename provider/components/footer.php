<?php
declare(strict_types=1);
?>

<footer class="client-footer">

    <div class="client-footer-main">

        <!-- Brand -->
        <div class="footer-brand">

            <a href="dashboard.php?page=home" class="footer-logo">
                Find<span>Pro</span>
            </a>

            <p>
                Find reliable professionals and get
                the services you need with ease.
            </p>

        </div>


        <!-- Explore -->
        <div class="footer-group">

            <h4>Explore</h4>

            <a href="dashboard.php?page=home">Home</a>

            <a href="dashboard.php?page=services">Services</a>

            <a href="dashboard.php?page=bookings">Bookings</a>

        </div>


        <!-- Account -->
        <div class="footer-group">

            <h4>Account</h4>

            <a href="dashboard.php?page=favorites">Favorites</a>

            <a href="dashboard.php?page=messages">Messages</a>

            <a href="dashboard.php?page=profile">Profile</a>

        </div>


        <!-- Help -->
        <div class="footer-group">

            <h4>Help</h4>

            <a href="dashboard.php?page=settings">Settings</a>

            <a href="#">Help Center</a>

            <a href="#">Contact Us</a>

        </div>

    </div>


    <div class="client-footer-bottom">

        <span>
            © <?= date('Y'); ?> FindPro
        </span>

        <div>

            <a href="#">Privacy Policy</a>

            <a href="#">Terms of Service</a>

        </div>

    </div>

</footer>