<section class="dashboard-home">

    <!-- PAGE TITLE -->

    <div class="dashboard-heading">
        <div>
            <h1>Dashboard</h1>
            <p>Manage your services, bookings and grow your business.</p>
        </div>
    </div>

    <!-- PROVIDER PROFILE CARD -->
    <section class="provider-overview-card">
        <div class="provider-info">
            <div class="provider-avatar">
                <img src="../assets/images/msagambegu.jpg" alt="Provider">
            </div>

            <div class="provider-details">
                <div class="provider-name-row">
                    <h2>Paschal Mshandete</h2>
                    <span class="verified-badge">
                        <i class="fa-solid fa-circle-check"></i>
                        Verified Provider
                    </span>
                </div>

                <p class="provider-service">Plumbing Services</p>
                <p class="provider-location">
                    <i class="fa-solid fa-location-dot"></i>
                    Dar es Salaam, Tanzania
                </p>

                <p class="provider-rating">
                    <i class="fa-solid fa-star"></i>
                    4.8
                    <span>(128 reviews)</span>
                </p>
            </div>
        </div>

        <div class="profile-progress">
            <div class="progress-info">
                <div>
                    <span>Profile Status</span>
                    <strong>100% Completed</strong>
                </div>
            </div>


            <div class="progress-bar">
                <div class="progress-fill" style="width: 100%;"></div>
            </div>

        </div>


        <a href="dashboard.php?page=profile" class="edit-profile-btn">
            <i class="fa-solid fa-pen"></i>
            Edit Profile
        </a>
    </section>


    <!-- STATISTICS -->

    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon bookings-icon">
                <i class="fa-regular fa-calendar"></i>
            </div>

            <div>
                <span class="stat-label">Total Bookings</span>
                <h3>12</h3>

                <small class="positive">
                    +3 from last month
                </small>
            </div>
        </div>



        <div class="stat-card">
            <div class="stat-icon pending-icon">
                <i class="fa-regular fa-clock"></i>
            </div>

            <div>
                <span class="stat-label">Pending Requests</span>
                <h3>3</h3>

                <small class="warning">
                    +1 from last week
                </small>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon completed-icon">
                <i class="fa-solid fa-check"></i>
            </div>

            <div>
                <span class="stat-label">Completed Jobs</span>
                <h3>28</h3>

                <small class="positive">
                    +5 from last month
                </small>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon rating-icon">
                <i class="fa-solid fa-star"></i>
            </div>

            <div>
                <span class="stat-label">Rating</span>
                <h3>4.8</h3>

                <small>
                    From 128 reviews
                </small>
            </div>
        </div>
    </section>

    <!-- RECENT BOOKINGS -->
    <section class="recent-bookings">
        <div class="section-header">
            <h2>Recent Booking Requests</h2>
            <a href="dashboard.php?page=bookings">View All</a>
        </div>

        <div class="booking-list">
            <div class="booking-row">
                <div class="client-info">
                    <img src="../assets/images/default-user.png" alt="Client">

                    <div>
                        <strong>John Mwangi</strong>
                        <span>Plumbing Repair</span>
                    </div>
                </div>

                <span class="booking-date">24 May 2026</span>
                <span class="status pending"> Pending</span>

                <button class="view-btn">View
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>

            <div class="booking-row">
                <div class="client-info">
                    <img src="../assets/images/p.jpg" alt="Client">
                    <div>
                        <strong>Anna Smith</strong>
                        <span>Bathroom Cleaning</span>
                    </div>
                </div>

                <span class="booking-date">23 May 2026</span>
                <span class="status accepted">Accepted</span>

                <button class="view-btn">View
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>

        </div>
    </section>
</section>