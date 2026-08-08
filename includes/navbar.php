<header class="navbar">

    <div class="logo">
        <span class="find">Find</span><span class="pro">Pro</span>
    </div>

    <nav class="nav-links" id="navLinks">
        <a href="index.php">Home</a>
        <a href="services.php">Services</a>
        <a href="about.php">How it works</a>
        <a href="providers.php">Providers</a>
        
        <!-- Action Buttons kwa ajili ya Skrini ndogo (Mobile) -->
        <div class="mobile-actions">
            <button class="btn-outline" onclick="window.location.href='auth/login.php'">Login</button>
            <button class="btn-primary" onclick="window.location.href='auth/register.php'">Register</button>
        </div>
    </nav>

    <!-- MOBILE MENU ICON -->
    <div class="menu-icon" onclick="toggleMenu()">
        <i class="fa fa-bars"></i>
    </div>

</header>