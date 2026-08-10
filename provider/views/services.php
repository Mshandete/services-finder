<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FindPro - Services Manager</title>
    <!-- Absolute paths to load styles correctly -->
    <link rel="stylesheet" href="/services-finder/assets/css/style.css">
    <link rel="stylesheet" href="/services-finder/assets/css/provider.css">
</head>
<body>

<div class="dashboard-container">

    <!-- Sidebar -->
    <?php include __DIR__ . '../../components/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content">
        
        <!-- Topbar -->
        <?php include __DIR__ . '../../components/topbar.php'; ?>

        <!-- Content Body -->
        <div class="content-body">
            
            <!-- Header Section -->
            <div class="page-header">
                <div>
                    <h2>Services Manager</h2>
                    <p class="text-muted">Manage the services you offer to clients and update pricing.</p>
                </div>
                <button class="btn-primary" id="openAddServiceModal">+ Add New Service</button>
            </div>

            <!-- Stats Bar for Services -->
            <div class="stats-grid services-stats">
                <div class="stat-card">
                    <span class="stat-title">Total Services</span>
                    <h2>4</h2>
                </div>
                <div class="stat-card">
                    <span class="stat-title">Active Services</span>
                    <h2>3</h2>
                </div>
                <div class="stat-card">
                    <span class="stat-title">Inactive Services</span>
                    <h2>1</h2>
                </div>
                <div class="stat-card">
                    <span class="stat-title">Avg. Service Price</span>
                    <h2>TSh 85,000</h2>
                </div>
            </div>

            <!-- Services Cards Grid -->
            <div class="services-grid">
                
                <!-- Service Card 1 -->
                <div class="service-card">
                    <div class="service-card-image">
                        <img src="/services-finder/assets/images/a.jpg" alt="A/C Installation">
                        <span class="badge badge-active">Active</span>
                    </div>
                    <div class="service-card-body">
                        <small class="category-tag">Electrical & Appliances</small>
                        <h3>A/C Repair & Installation</h3>
                        <p class="service-desc">Complete diagnostic, gas refill, compressor fix, and mounting for residential A/C units.</p>
                        <div class="service-meta">
                            <div class="price">
                                <small>Starting at</small>
                                <strong>TSh 120,000</strong>
                            </div>
                            <div class="duration">
                                <small>Est. Time</small>
                                <span>2 - 3 Hours</span>
                            </div>
                        </div>
                    </div>
                    <div class="service-card-footer">
                        <button class="btn-action-outline">Edit</button>
                        <button class="btn-action-danger">Delete</button>
                    </div>
                </div>

                <!-- Service Card 2 -->
                <div class="service-card">
                    <div class="service-card-image">
                        <img src="/services-finder/assets/images/b.jpg" alt="House Wiring">
                        <span class="badge badge-active">Active</span>
                    </div>
                    <div class="service-card-body">
                        <small class="category-tag">Electrical</small>
                        <h3>House Full Wiring</h3>
                        <p class="service-desc">Full circuit layout, breaker setup, socket installations, and safety checks for new homes.</p>
                        <div class="service-meta">
                            <div class="price">
                                <small>Starting at</small>
                                <strong>TSh 250,000</strong>
                            </div>
                            <div class="duration">
                                <small>Est. Time</small>
                                <span>1 - 2 Days</span>
                            </div>
                        </div>
                    </div>
                    <div class="service-card-footer">
                        <button class="btn-action-outline">Edit</button>
                        <button class="btn-action-danger">Delete</button>
                    </div>
                </div>

                <!-- Service Card 3 -->
                <div class="service-card">
                    <div class="service-card-image">
                        <img src="/services-finder/assets/images/c.jpg" alt="Sink & Pipe Leak Fix">
                        <span class="badge badge-active">Active</span>
                    </div>
                    <div class="service-card-body">
                        <small class="category-tag">Plumbing</small>
                        <h3>Sink & Pipe Leak Repair</h3>
                        <p class="service-desc">Kitchen and bathroom plumbing repairs, pipe unblocking, and faucet replacements.</p>
                        <div class="service-meta">
                            <div class="price">
                                <small>Starting at</small>
                                <strong>TSh 45,000</strong>
                            </div>
                            <div class="duration">
                                <small>Est. Time</small>
                                <span>1 Hour</span>
                            </div>
                        </div>
                    </div>
                    <div class="service-card-footer">
                        <button class="btn-action-outline">Edit</button>
                        <button class="btn-action-danger">Delete</button>
                    </div>
                </div>

                <!-- Service Card 4 (Inactive) -->
                <div class="service-card inactive-card">
                    <div class="service-card-image">
                        <img src="/services-finder/assets/images/f.jpg" alt="Solar Panel Setup">
                        <span class="badge badge-inactive">Inactive</span>
                    </div>
                    <div class="service-card-body">
                        <small class="category-tag">Renewable Energy</small>
                        <h3>Solar Panel Mounting</h3>
                        <p class="service-desc">Rooftop solar panel installation, inverter cabling, and battery bank maintenance.</p>
                        <div class="service-meta">
                            <div class="price">
                                <small>Starting at</small>
                                <strong>TSh 180,000</strong>
                            </div>
                            <div class="duration">
                                <small>Est. Time</small>
                                <span>4 - 6 Hours</span>
                            </div>
                        </div>
                    </div>
                    <div class="service-card-footer">
                        <button class="btn-action-outline">Activate</button>
                        <button class="btn-action-danger">Delete</button>
                    </div>
                </div>

            </div>

        </div>
    </main>
</div>

<!-- Modal Form for Adding New Service -->
<div class="modal-overlay" id="addServiceModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Service</h3>
            <button class="close-modal" id="closeAddServiceModal">&times;</button>
        </div>
        <form action="#" method="POST" class="modal-body">
            <div class="form-group">
                <label>Service Title</label>
                <input type="text" placeholder="e.g. Washing Machine Repair" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Category</label>
                    <select required>
                        <option value="">Select Category</option>
                        <option value="electrical">Electrical</option>
                        <option value="plumbing">Plumbing</option>
                        <option value="cleaning">Cleaning</option>
                        <option value="appliances">Appliances Repair</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Starting Price (TSh)</label>
                    <input type="number" placeholder="50000" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Estimated Duration</label>
                    <input type="text" placeholder="e.g. 2 Hours or 1 Day">
                </div>
                <div class="form-group">
                    <label>Cover Image</label>
                    <input type="file" accept="image/*">
                </div>
            </div>

            <div class="form-group">
                <label>Service Description</label>
                <textarea rows="4" placeholder="Briefly describe what is included in this service..."></textarea>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-action-outline" id="cancelModal">Cancel</button>
                <button type="submit" class="btn-primary">Save Service</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Simple Modal Toggle JS
    const modal = document.getElementById('addServiceModal');
    const openBtn = document.getElementById('openAddServiceModal');
    const closeBtn = document.getElementById('closeAddServiceModal');
    const cancelBtn = document.getElementById('cancelModal');

    openBtn.onclick = () => modal.style.display = 'flex';
    closeBtn.onclick = () => modal.style.display = 'none';
    cancelBtn.onclick = () => modal.style.display = 'none';
    window.onclick = (e) => { if (e.target == modal) modal.style.display = 'none'; }
</script>

</body>
</html>