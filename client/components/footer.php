<?php
declare(strict_types=1);
?>

<footer class="client-footer">

    <div class="client-footer-inner">

        <!-- Brand -->
        <div class="footer-brand">

            <a href="dashboard.php?page=home" class="footer-logo">
                Find<span>Pro</span>
            </a>

            <p>
                Find trusted local professionals<br>
                for the services you need.
            </p>

        </div>


        <!-- Navigation -->
        <nav class="footer-links">

            <a href="dashboard.php?page=home">
                Home
            </a>

            <a href="dashboard.php?page=services">
                Services
            </a>

            <a href="dashboard.php?page=bookings">
                Bookings
            </a>

            <a href="dashboard.php?page=favorites">
                Favorites
            </a>

            <a href="dashboard.php?page=messages">
                Messages
            </a>

        </nav>


        <!-- Account -->
        <div class="footer-account">

            <span>Account</span>

            <a href="dashboard.php?page=profile">
                Profile
            </a>

            <a href="dashboard.php?page=settings">
                Settings
            </a>

        </div>


        <!-- Social / Contact -->
        <div class="footer-connect">

            <span>Connect</span>

            <div class="footer-socials">

                <a href="#" aria-label="Facebook">
                    <i class="fa-brands fa-facebook-f"></i>
                </a>

                <a href="#" aria-label="Instagram">
                    <i class="fa-brands fa-instagram"></i>
                </a>

                <a href="#" aria-label="WhatsApp">
                    <i class="fa-brands fa-whatsapp"></i>
                </a>

            </div>

        </div>

    </div>


    <!-- Bottom -->
    <div class="footer-bottom">

        <span>
            © <?= date('Y'); ?> FindPro. All rights reserved.
        </span>

        <div>

            <a href="#">
                Privacy
            </a>

            <a href="#">
                Terms
            </a>

        </div>

    </div>

</footer>