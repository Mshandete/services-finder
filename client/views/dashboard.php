<?php
// provider/views/dashboard.php
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provider Dashboard - FindPro</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/home.css">
    <link rel="stylesheet" href="../assets/css/provider.css">
</head>
<body>

<div class="dashboard-container">
    <!-- Include Sidebar -->
    <?php include '../components/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content">
        
        <!-- Top Bar -->
        <header class="topbar">
            <div class="search-bar">
                <input type="text" placeholder="Search for Service Requests...">
            </div>
            <div class="topbar-right">
                <div class="location-picker">
                    📍 <span>Dar es Salaam</span>
                </div>
                <div class="notifications">
                    🔔 <span class="notif-badge">3</span>
                </div>
                <div class="user-profile">
                    <img src="../../assets/images/a.jpg" alt="Paschal" class="avatar">
                    <div class="user-info">
                        <h4>Paschal - Provider</h4>
                        <small>Provider</small>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="content-body">
            
            <!-- Hero / Welcome Banner -->
            <section class="welcome-banner">
                <div class="banner-text">
                    <h1>Welcome back, Paschal!</h1>
                    <p>Set Your Availability.</p>
                </div>
                <div class="status-toggle-card">
                    <span>Status: <strong>Online (Available for Hire)</strong></span>
                    <label class="switch">
                        <input type="checkbox" checked>
                        <span class="slider round"></span>
                    </label>
                </div>
            </section>

            <!-- Main Layout Grid (Left 8 Cols, Right 4 Cols) -->
            <div class="dashboard-grid">
                
                <!-- Left Column -->
                <div class="grid-left">
                    
                    <!-- Stats Grid (4 Cards) -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <span class="stat-title">Earnings</span>
                            <h2>TSh 450,000</h2>
                            <small class="text-muted">Today/Month</small>
                        </div>
                        <div class="stat-card">
                            <span class="stat-title">Pending Requests</span>
                            <h2>3</h2>
                        </div>
                        <div class="stat-card">
                            <span class="stat-title">Upcoming Jobs</span>
                            <h2>2</h2>
                            <small class="text-muted">(confirmed)</small>
                        </div>
                        <div class="stat-card">
                            <span class="stat-title">Overall Rating</span>
                            <h2>4.9 ⭐</h2>
                            <small class="text-muted">24 Reviews</small>
                        </div>
                    </div>

                    <!-- Recent Service Requests Table -->
                    <div class="card requests-card">
                        <div class="card-header">
                            <h3>Recent Service Requests</h3>
                            <a href="requests.php" class="view-all">View all &rarr;</a>
                        </div>
                        <div class="table-responsive">
                            <table class="requests-table">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Location</th>
                                        <th>Price Offer</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="client-cell">
                                                <img src="../../assets/images/h.jpg" class="avatar-sm">
                                                <div>
                                                    <strong>Sarah J.</strong>
                                                    <small>A/C Repair</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>📍 June 10</td>
                                        <td><strong>TSh 60,000</strong></td>
                                        <td>
                                            <button class="btn-accept">Accept</button>
                                            <button class="btn-decline">Decline</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="client-cell">
                                                <img src="../../assets/images/client1.jpg" class="avatar-sm">
                                                <div>
                                                    <strong>Sarah J.</strong>
                                                    <small>Kinondoni</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>📍 June 10</td>
                                        <td><strong>TSh 60,000</strong></td>
                                        <td>
                                            <button class="btn-accept">Accept</button>
                                            <button class="btn-decline">Decline</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="client-cell">
                                                <img src="../../assets/images/t.jpg" class="avatar-sm">
                                                <div>
                                                    <strong>Sarah J.</strong>
                                                    <small>A/C Repair</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>📍 June 10</td>
                                        <td><strong>TSh 60,000</strong></td>
                                        <td>
                                            <button class="btn-accept">Accept</button>
                                            <button class="btn-decline">Decline</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- Right Column -->
                <div class="grid-right">
                    
                    <!-- My Schedule Widget -->
                    <div class="card schedule-card">
                        <div class="card-header">
                            <h3>My Schedule</h3>
                            <a href="schedule.php" class="view-all">View all &rarr;</a>
                        </div>
                        <div class="calendar-preview">
                            <!-- Mini Calendar Grid -->
                            <div class="calendar-header">
                                <span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span>
                            </div>
                            <div class="calendar-days">
                                <span>29</span><span>30</span><span>31</span><span>1</span><span>2</span><span>3</span><span>4</span>
                                <span>5</span><span>6</span><span>7</span><span>8</span><span>9</span><span>10</span><span>11</span>
                                <span>12</span><span>13</span><span>14</span><span>15</span><span class="active-day">16</span><span>17</span><span>18</span>
                            </div>
                        </div>
                    </div>

                    <!-- Earnings Graph Widget -->
                    <div class="card earnings-card">
                        <h3>Earnings Graph</h3>
                        <div class="graph-placeholder">
                            <!-- Placeholder for Chart JS or SVG -->
                            <div class="mock-chart">📈 Graph Visual</div>
                        </div>
                    </div>

                    <!-- Service Manager Preview -->
                    <div class="card service-manager-card">
                        <div class="card-header">
                            <h3>Service Manager <small>(Preview)</small></h3>
                            <a href="services.php" class="view-all">See all &rarr;</a>
                        </div>
                        <div class="service-preview-item">
                            <span>🛠️ A/C Installation</span>
                            <strong>TSh 120,000</strong>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </main>
</div>

</body>
</html>