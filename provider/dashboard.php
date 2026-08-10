<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FindPro - Provider Dashboard</title>
    <!-- Absolute paths to prevent CSS breakdown -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/provider.css">
</head>
<body>

<div class="dashboard-container">

    <!-- Sidebar -->
    <?php include __DIR__ . '/components/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content">
        
        <!-- Topbar -->
        <?php include __DIR__ . '/components/topbar.php'; ?>

        <!-- Dashboard Body Content -->
        <div class="content-body">
            
            <!-- Hero Status Banner -->
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

            <!-- Dashboard Main Grid -->
            <div class="dashboard-grid">
                
                <!-- Left Column (Stats & Recent Requests) -->
                <div class="grid-left">
                    
                    <!-- Stats Grid -->
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
                            <a href="/services-finder/provider/views/requests.php" class="view-all">View all &rarr;</a>
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
                                                <img src="../assets/images/b.jpg" class="avatar-sm">
                                                <div>
                                                    <strong>Sarah J.</strong>
                                                    <small>A/C Repair</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>📍 Kinondoni, DSM</td>
                                        <td><strong>TSh 60,000</strong></td>
                                        <td>
                                            <button class="btn-accept">Accept</button>
                                            <button class="btn-decline">Decline</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="client-cell">
                                                <img src="../assets/images/c.jpg" class="avatar-sm">
                                                <div>
                                                    <strong>Sarah J.</strong>
                                                    <small>Plumbing</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>📍 Ubungo, DSM</td>
                                        <td><strong>TSh 60,000</strong></td>
                                        <td>
                                            <button class="btn-accept">Accept</button>
                                            <button class="btn-decline">Decline</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="client-cell">
                                                <img src="../assets/images/f.jpg" class="avatar-sm">
                                                <div>
                                                    <strong>Sarah J.</strong>
                                                    <small>Electrical</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>📍 Mbezi, DSM</td>
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

                <!-- Right Column (Schedule, Graph, Service Preview) -->
                <div class="grid-right">
                    
                    <!-- Calendar / Schedule Widget -->
                    <div class="card schedule-card">
                        <div class="card-header">
                            <h3>My Schedule</h3>
                            <a href="/services-finder/provider/views/schedule.php" class="view-all">View all &rarr;</a>
                        </div>
                        <div class="calendar-preview">
                            <div class="calendar-days-header">
                                <span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span>
                            </div>
                            <div class="calendar-days-grid">
                                <span>29</span><span>30</span><span>31</span><span>1</span><span>2</span><span>3</span><span>4</span>
                                <span>5</span><span>6</span><span>7</span><span>8</span><span>9</span><span>10</span><span>11</span>
                                <span>12</span><span>13</span><span>14</span><span>15</span><span class="active-day">16</span><span>17</span><span>18</span>
                            </div>
                        </div>
                    </div>

                    <!-- Earnings Graph Widget -->
                    <div class="card earnings-card">
                        <div class="card-header">
                            <h3>Earnings Graph</h3>
                        </div>
                        <div class="graph-placeholder">
                            <div class="line-chart-sim">
                                <span style="height: 40%"></span>
                                <span style="height: 65%"></span>
                                <span style="height: 50%"></span>
                                <span style="height: 85%"></span>
                                <span style="height: 70%"></span>
                                <span style="height: 95%"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Service Manager Preview -->
                    <div class="card service-manager-card">
                        <div class="card-header">
                            <h3>Service Manager <small>(Preview)</small></h3>
                            <a href="/services-finder/provider/views/services.php" class="view-all">See all &rarr;</a>
                        </div>
                        <div class="service-preview-item">
                            <div class="service-info">
                                <span>🛠️ A/C Installation</span>
                            </div>
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