<?php
declare(strict_types=1);

if ($providerId <= 0) {
    throw new RuntimeException(
        'Provider session not found.'
    );
}

$providerId = (int)($_SESSION['user_id'] ?? 0);

if (!isset($_SESSION['services_csrf_token'])) {
    $_SESSION['services_csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['services_csrf_token'];
$message = '';
$messageType = '';

function cleanServiceValue(string $value): string {
    return trim($value);
}

function uploadServiceImage(array $file, ?string $oldImage = null): ?string {
    // No new image uploaded
    if (empty($file) || !isset($file['error'])) {
        return $oldImage;
    }

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return $oldImage;
    }

    // Handle upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {

        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded image exceeds the server upload limit.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded image exceeds the allowed size.',
            UPLOAD_ERR_PARTIAL => 'The image was only partially uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Temporary upload folder is missing.',
            UPLOAD_ERR_CANT_WRITE => 'Server failed to write the uploaded image.',
            UPLOAD_ERR_EXTENSION => 'Image upload was stopped by a PHP extension.'
        ];

        $errorMessage = $uploadErrors[$file['error']]
            ?? 'Image upload failed.';

        throw new RuntimeException($errorMessage);
    }

    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Invalid uploaded image.');
    }

    // Maximum size: 5MB
    $maxSize = 5 * 1024 * 1024;

    if ($file['size'] > $maxSize) {
        throw new RuntimeException(
            'Image must not exceed 5MB.'
        );
    }

    // Check real MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);

    $mimeType = $finfo->file($file['tmp_name']);

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!array_key_exists($mimeType, $allowedTypes)) {
        throw new RuntimeException(
            'Only JPG, PNG and WEBP images are allowed.'
        );
    }

    // Get project root safely
    $projectRoot = dirname(__DIR__, 2);

    $uploadDirectory =
        $projectRoot . '/assets/images/services/';

    // Create folder if it doesn't exist
    if (!is_dir($uploadDirectory)) {

        if (!mkdir($uploadDirectory, 0775, true)) {
            throw new RuntimeException(
                'Unable to create service image directory.'
            );
        }
    }

    // Check if directory is writable
    if (!is_writable($uploadDirectory)) {
        throw new RuntimeException(
            'Service image directory is not writable. Please check folder permissions.'
        );
    }

    // Generate safe filename
    $extension = $allowedTypes[$mimeType];

    $fileName =
        'service_' .
        bin2hex(random_bytes(8)) .
        '_' .
        time() .
        '.' .
        $extension;

    $destination =
        $uploadDirectory . $fileName;

    // Save uploaded file
    if (!move_uploaded_file(
        $file['tmp_name'],
        $destination
    )) {
        throw new RuntimeException(
            'Unable to save uploaded image. Please check folder permissions.'
        );
    }

    // Delete old image only after new image is successfully uploaded
    if (!empty($oldImage)) {

        $oldImagePath =
            $uploadDirectory .
            basename($oldImage);

        if (is_file($oldImagePath)) {
            @unlink($oldImagePath);
        }
    }

    return $fileName;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $submittedToken = $_POST['csrf_token'] ?? '';

        if (!hash_equals($csrfToken, $submittedToken)) {
            throw new RuntimeException('Invalid request. Please try again.');
        }

        $action = $_POST['action'] ?? '';

        if ($action === 'add_service') {
            $serviceName = cleanServiceValue($_POST['service_name'] ?? '');
            $description = cleanServiceValue($_POST['description'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $priceType = $_POST['price_type'] ?? 'fixed';
            $duration = cleanServiceValue($_POST['estimated_duration'] ?? '');
            $status = $_POST['status'] ?? 'active';

            if ($serviceName === '') {
                throw new RuntimeException('Service name is required.');
            }

            if ($description === '') {
                throw new RuntimeException('Service description is required.');
            }

            if ($price < 0) {
                throw new RuntimeException('Price cannot be negative.');
            }

            if (!in_array($priceType, ['fixed', 'hourly', 'negotiable'], true)) {
                $priceType = 'fixed';
            }

            if (!in_array($status, ['active', 'inactive'], true)) {
                $status = 'active';
            }

            $imageName = uploadServiceImage($_FILES['service_image'] ?? []);

            $stmt = $pdo->prepare("
                INSERT INTO provider_services (
                    provider_user_id,
                    service_name,
                    description,
                    price,
                    price_type,
                    service_image,
                    estimated_duration,
                    status,
                    created_at,
                    updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");

            $stmt->execute([
                $providerId,
                $serviceName,
                $description,
                $price,
                $priceType,
                $imageName,
                $duration !== '' ? $duration : null,
                $status
            ]);

            $_SESSION['services_message'] = 'Service added successfully.';
            $_SESSION['services_message_type'] = 'success';

            header('Location: dashboard.php?page=services');
            exit();
        }

        if ($action === 'edit_service') {
            $serviceId = (int)($_POST['service_id'] ?? 0);
            $serviceName = cleanServiceValue($_POST['service_name'] ?? '');
            $description = cleanServiceValue($_POST['description'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $priceType = $_POST['price_type'] ?? 'fixed';
            $duration = cleanServiceValue($_POST['estimated_duration'] ?? '');
            $status = $_POST['status'] ?? 'active';

            if ($serviceId <= 0) {
                throw new RuntimeException('Invalid service.');
            }

            if ($serviceName === '') {
                throw new RuntimeException('Service name is required.');
            }

            if ($description === '') {
                throw new RuntimeException('Service description is required.');
            }

            if ($price < 0) {
                throw new RuntimeException('Price cannot be negative.');
            }

            $serviceStmt = $pdo->prepare("
                SELECT service_image
                FROM provider_services
                WHERE id = ?
                AND provider_user_id = ?
                LIMIT 1
            ");

            $serviceStmt->execute([$serviceId, $providerId]);
            $existingService = $serviceStmt->fetch(PDO::FETCH_ASSOC);

            if (!$existingService) {
                throw new RuntimeException('Service not found.');
            }

            if (!in_array($priceType, ['fixed', 'hourly', 'negotiable'], true)) {
                $priceType = 'fixed';
            }

            if (!in_array($status, ['active', 'inactive'], true)) {
                $status = 'active';
            }

            $imageName = uploadServiceImage(
                $_FILES['service_image'] ?? [],
                $existingService['service_image'] ?? null
            );

            $stmt = $pdo->prepare("
                UPDATE provider_services
                SET service_name = ?,
                    description = ?,
                    price = ?,
                    price_type = ?,
                    service_image = ?,
                    estimated_duration = ?,
                    status = ?,
                    updated_at = NOW()
                WHERE id = ?
                AND provider_user_id = ?
            ");

            $stmt->execute([
                $serviceName,
                $description,
                $price,
                $priceType,
                $imageName,
                $duration !== '' ? $duration : null,
                $status,
                $serviceId,
                $providerId
            ]);

            $_SESSION['services_message'] = 'Service updated successfully.';
            $_SESSION['services_message_type'] = 'success';

            header('Location: dashboard.php?page=services');
            exit();
        }

        if ($action === 'toggle_service') {
            $serviceId = (int)($_POST['service_id'] ?? 0);

            if ($serviceId <= 0) {
                throw new RuntimeException('Invalid service.');
            }

            $stmt = $pdo->prepare("
                UPDATE provider_services
                SET status = CASE
                    WHEN status = 'active' THEN 'inactive'
                    ELSE 'active'
                END,
                updated_at = NOW()
                WHERE id = ?
                AND provider_user_id = ?
            ");

            $stmt->execute([$serviceId, $providerId]);

            if ($stmt->rowCount() === 0) {
                throw new RuntimeException('Service not found.');
            }

            $_SESSION['services_message'] = 'Service status updated successfully.';
            $_SESSION['services_message_type'] = 'success';

            header('Location: dashboard.php?page=services');
            exit();
        }

        if ($action === 'delete_service') {
            $serviceId = (int)($_POST['service_id'] ?? 0);

            if ($serviceId <= 0) {
                throw new RuntimeException('Invalid service.');
            }

            $imageStmt = $pdo->prepare("
                SELECT service_image
                FROM provider_services
                WHERE id = ?
                AND provider_user_id = ?
                LIMIT 1
            ");

            $imageStmt->execute([$serviceId, $providerId]);
            $service = $imageStmt->fetch(PDO::FETCH_ASSOC);

            if (!$service) {
                throw new RuntimeException('Service not found.');
            }

            $stmt = $pdo->prepare("
                DELETE FROM provider_services
                WHERE id = ?
                AND provider_user_id = ?
            ");

            $stmt->execute([$serviceId, $providerId]);

            if (!empty($service['service_image'])) {
                $imagePath = __DIR__ . '/../../assets/images/services/' . basename($service['service_image']);

                if (is_file($imagePath)) {
                    @unlink($imagePath);
                }
            }

            $_SESSION['services_message'] = 'Service deleted successfully.';
            $_SESSION['services_message_type'] = 'success';

            header('Location: dashboard.php?page=services');
            exit();
        }

    } catch (Throwable $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

if (isset($_SESSION['services_message'])) {
    $message = $_SESSION['services_message'];
    $messageType = $_SESSION['services_message_type'] ?? 'success';

    unset(
        $_SESSION['services_message'],
        $_SESSION['services_message_type']
    );
}

$stmt = $pdo->prepare("
    SELECT *
    FROM provider_services
    WHERE provider_user_id = ?
    ORDER BY created_at DESC
");

$stmt->execute([$providerId]);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="services-page">

    <div class="services-header">
        <div class="services-heading">
            <div class="breadcrumb">
                <span>Dashboard</span>
                <i class="fa-solid fa-chevron-right"></i>
                <span>My Services</span>
            </div>

            <h1>My Services</h1>
            <p>Manage the services you offer to your clients.</p>
        </div>

        <button type="button" class="add-service-btn" id="openAddService">
            <i class="fa-solid fa-plus"></i>
            Add Service
        </button>
    </div>

    <?php if ($message !== ''): ?>
        <div class="services-alert <?= $messageType === 'success' ? 'alert-success' : 'alert-error' ?>">
            <span><?= htmlspecialchars($message) ?></span>
            <button type="button" class="close-alert">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    <?php endif; ?>

    <div class="services-toolbar">
        <div class="service-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="serviceSearch" placeholder="Search services...">
        </div>

        <div class="service-filter">
            <select id="serviceStatusFilter">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>

    <div class="services-grid" id="servicesGrid">

        <?php if (empty($services)): ?>

            <div class="empty-services">
                <div class="empty-icon">
                    <i class="fa-solid fa-briefcase"></i>
                </div>

                <h2>No Services Yet</h2>

                <p>
                    You haven't added any services yet.
                    Start by adding your first service.
                </p>

                <button type="button" class="add-first-service" id="openAddServiceEmpty">
                    <i class="fa-solid fa-plus"></i>
                    Add Your First Service
                </button>
            </div>

        <?php else: ?>

            <?php foreach ($services as $service): ?>

                <?php
                $serviceId = (int)$service['id'];
                $serviceName = $service['service_name'] ?? 'Unnamed Service';
                $description = $service['description'] ?? 'No description available.';
                $price = (float)($service['price'] ?? 0);
                $priceType = $service['price_type'] ?? 'fixed';
                $duration = $service['estimated_duration'] ?? 'Not specified';
                $status = strtolower($service['status'] ?? 'active');
                $image = $service['service_image'] ?? '';
                ?>

                <div class="service-card"
                    data-name="<?= htmlspecialchars(strtolower($serviceName)) ?>"
                    data-status="<?= htmlspecialchars($status) ?>">

                    <div class="service-image">

                        <?php if (!empty($image)): ?>

                            <img
                                src="../assets/images/services/<?= htmlspecialchars($image) ?>"
                                alt="<?= htmlspecialchars($serviceName) ?>">

                        <?php else: ?>

                            <div class="service-image-placeholder">
                                <i class="fa-solid fa-image"></i>
                            </div>

                        <?php endif; ?>

                        <span class="service-status <?= $status === 'active' ? 'status-active' : 'status-inactive' ?>">
                            <?= htmlspecialchars(ucfirst($status)) ?>
                        </span>

                    </div>

                    <div class="service-content">

                        <h3><?= htmlspecialchars($serviceName) ?></h3>

                        <p class="service-description">
                            <?= htmlspecialchars($description) ?>
                        </p>

                        <div class="service-price">
                            <span class="price-label">Price</span>

                            <strong>
                                <?php if ($priceType === 'negotiable'): ?>
                                    Negotiable
                                <?php else: ?>
                                    TZS <?= number_format($price) ?>
                                <?php endif; ?>
                            </strong>
                        </div>

                        <div class="service-meta">
                            <span>
                                <i class="fa-regular fa-clock"></i>
                                <?= htmlspecialchars($duration) ?>
                            </span>

                            <span>
                                <i class="fa-solid fa-tag"></i>
                                <?= htmlspecialchars(ucfirst($priceType)) ?>
                            </span>
                        </div>

                        <div class="service-actions">

                            <button
                                type="button"
                                class="service-action edit-service"
                                data-service-id="<?= $serviceId ?>"
                                data-service-name="<?= htmlspecialchars($serviceName, ENT_QUOTES) ?>"
                                data-description="<?= htmlspecialchars($description, ENT_QUOTES) ?>"
                                data-price="<?= htmlspecialchars((string)$price) ?>"
                                data-price-type="<?= htmlspecialchars($priceType) ?>"
                                data-duration="<?= htmlspecialchars($duration, ENT_QUOTES) ?>"
                                data-status="<?= htmlspecialchars($status) ?>">

                                <i class="fa-solid fa-pen"></i>
                                Edit

                            </button>

                            <form method="POST" class="inline-service-form">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <input type="hidden" name="action" value="toggle_service">
                                <input type="hidden" name="service_id" value="<?= $serviceId ?>">

                                <button type="submit" class="service-action toggle-service">
                                    <i class="fa-solid fa-toggle-on"></i>
                                    Toggle
                                </button>
                            </form>

                            <button
                                type="button"
                                class="service-action delete-service"
                                data-service-id="<?= $serviceId ?>"
                                data-service-name="<?= htmlspecialchars($serviceName, ENT_QUOTES) ?>">

                                <i class="fa-solid fa-trash"></i>
                                Delete

                            </button>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</div>


<!-- ADD SERVICE MODAL -->

<div class="service-modal" id="addServiceModal">

    <div class="service-modal-backdrop"></div>

    <div class="service-modal-content">

        <div class="service-modal-header">
            <div>
                <h2>Add New Service</h2>
                <p>Add a service that clients can find and request.</p>
            </div>

            <button type="button" class="close-service-modal" data-close-modal="addServiceModal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" enctype="multipart/form-data" class="service-form">

            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="action" value="add_service">

            <div class="service-form-grid">

                <div class="service-form-group full-width">
                    <label for="serviceName">Service Name <span>*</span></label>

                    <input type="text" id="serviceName"
                        name="service_name" placeholder="Example: Electrical Installation" required>
                </div>

                <div class="service-form-group full-width">
                    <label for="serviceDescription">Description <span>*</span></label>
                    <textarea id="serviceDescription" name="description" placeholder="Describe your service..." required></textarea>
                </div>

                <div class="service-form-group">
                    <label for="servicePrice">Price (TZS) <span>*</span></label>
                    <input type="number" id="servicePrice" name="price" min="0" step="0.01" placeholder="Example: 50000" required>
                </div>

                <div class="service-form-group">
                    <label for="servicePriceType">Price Type <span>*</span></label>

                    <select id="servicePriceType" name="price_type" required>
                        <option value="fixed">Fixed</option>
                        <option value="hourly">Hourly</option>
                        <option value="negotiable">Negotiable</option>
                    </select>
                </div>

                <div class="service-form-group">
                    <label for="serviceDuration">Estimated Duration</label>
                    <input type="text" id="serviceDuration" name="estimated_duration" placeholder="Example: 2 Hours">
                </div>

                <div class="service-form-group">
                    <label for="serviceStatus">Status <span>*</span></label>
                    <select id="serviceStatus" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="service-form-group full-width">
                    <label for="serviceImage">Service Image</label>
                    <input type="file" id="serviceImage" name="service_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                </div>

            </div>

            <div class="service-modal-footer">
                <button type="button" class="modal-cancel-btn" data-close-modal="addServiceModal">
                    Cancel
                </button>

                <button type="submit" class="modal-submit-btn">
                    <i class="fa-solid fa-plus"></i>
                    Add Service
                </button>
            </div>

        </form>

    </div>

</div>


<!-- EDIT SERVICE MODAL -->

<div class="service-modal" id="editServiceModal">

    <div class="service-modal-backdrop"></div>

    <div class="service-modal-content">

        <div class="service-modal-header">
            <div>
                <h2>Edit Service</h2>
                <p>Update your service information.</p>
            </div>

            <button type="button" class="close-service-modal" data-close-modal="editServiceModal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" enctype="multipart/form-data" class="service-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="action" value="edit_service">
            <input type="hidden" name="service_id" id="editServiceId">

            <div class="service-form-grid">
                <div class="service-form-group full-width">
                    <label for="editServiceName">Service Name <span>*</span></label>
                    <input type="text" id="editServiceName" name="service_name"required>
                </div>

                <div class="service-form-group full-width">
                    <label for="editServiceDescription">Description <span>*</span></label>
                    <textarea id="editServiceDescription" name="description" required></textarea>
                </div>

                <div class="service-form-group">
                    <label for="editServicePrice">Price (TZS) <span>*</span></label>
                    <input type="number" id="editServicePrice" name="price" min="0" step="0.01" required>
                </div>

                <div class="service-form-group">
                    <label for="editServicePriceType">Price Type <span>*</span></label>

                    <select id="editServicePriceType" name="price_type" required>
                        <option value="fixed">Fixed</option>
                        <option value="hourly">Hourly</option>
                        <option value="negotiable">Negotiable</option>
                    </select>
                </div>

                <div class="service-form-group">
                    <label for="editServiceDuration">Estimated Duration</label>
                    <input type="text" id="editServiceDuration" name="estimated_duration">
                </div>

                <div class="service-form-group">
                    <label for="editServiceStatus">Status <span>*</span></label>
                    <select id="editServiceStatus" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="service-form-group full-width">
                    <label for="editServiceImage">Change Service Image</label>
                    <input type="file" id="editServiceImage" name="service_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                </div>

            </div>

            <div class="service-modal-footer">
                <button type="button" class="modal-cancel-btn" data-close-modal="editServiceModal">
                    Cancel
                </button>
                <button type="submit" class="modal-submit-btn">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Save Changes
                </button>
            </div>
        </form>

    </div>

</div>


<!-- DELETE CONFIRMATION MODAL -->

<div class="service-modal" id="deleteServiceModal">

    <div class="service-modal-backdrop"></div>

    <div class="service-modal-content delete-modal-content">

        <div class="delete-service-icon">
            <i class="fa-solid fa-trash"></i>
        </div>

        <h2>Delete Service?</h2>

        <p>
            Are you sure you want to delete
            <strong id="deleteServiceName"></strong>?
            This action cannot be undone.
        </p>

        <form method="POST" class="delete-service-form">

            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="action" value="delete_service">
            <input type="hidden" name="service_id" id="deleteServiceId">

            <div class="service-modal-footer">
                <button type="button" class="modal-cancel-btn" data-close-modal="deleteServiceModal">
                    Cancel
                </button>

                <button type="submit" class="modal-delete-btn">
                    <i class="fa-solid fa-trash"></i>
                    Delete Service
                </button>

            </div>

        </form>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const addModal = document.getElementById('addServiceModal');
    const editModal = document.getElementById('editServiceModal');
    const deleteModal = document.getElementById('deleteServiceModal');

    const openAddService = document.getElementById('openAddService');
    const openAddServiceEmpty = document.getElementById('openAddServiceEmpty');

    const searchInput = document.getElementById('serviceSearch');
    const statusFilter = document.getElementById('serviceStatusFilter');
    const serviceCards = document.querySelectorAll('.service-card');

    function openModal(modal) {
        if (!modal) return;
        modal.classList.add('is-open');
        document.body.classList.add('service-modal-open');
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.classList.remove('is-open');

        if (!document.querySelector('.service-modal.is-open')) {
            document.body.classList.remove('service-modal-open');
        }
    }

    if (openAddService) {
        openAddService.addEventListener('click', function () {
            openModal(addModal);
        });
    }

    if (openAddServiceEmpty) {
        openAddServiceEmpty.addEventListener('click', function () {
            openModal(addModal);
        });
    }

    document.querySelectorAll('[data-close-modal]').forEach(function (button) {
        button.addEventListener('click', function () {
            const modalId = this.dataset.closeModal;
            closeModal(document.getElementById(modalId));
        });
    });

    document.querySelectorAll('.service-modal-backdrop').forEach(function (backdrop) {
        backdrop.addEventListener('click', function () {
            closeModal(this.closest('.service-modal'));
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.service-modal.is-open').forEach(function (modal) {
                closeModal(modal);
            });
        }
    });

    document.querySelectorAll('.edit-service').forEach(function (button) {
        button.addEventListener('click', function () {

            document.getElementById('editServiceId').value = button.dataset.serviceId;
            document.getElementById('editServiceName').value = button.dataset.serviceName;
            document.getElementById('editServiceDescription').value = button.dataset.description;
            document.getElementById('editServicePrice').value = button.dataset.price;
            document.getElementById('editServicePriceType').value = button.dataset.priceType;
            document.getElementById('editServiceDuration').value = button.dataset.duration;
            document.getElementById('editServiceStatus').value = button.dataset.status;

            openModal(editModal);
        });
    });

    document.querySelectorAll('.delete-service').forEach(function (button) {
        button.addEventListener('click', function () {

            document.getElementById('deleteServiceId').value = button.dataset.serviceId;
            document.getElementById('deleteServiceName').textContent = button.dataset.serviceName;

            openModal(deleteModal);
        });
    });

    function filterServices() {

        const searchValue = searchInput
            ? searchInput.value.toLowerCase().trim()
            : '';

        const selectedStatus = statusFilter
            ? statusFilter.value
            : 'all';

        serviceCards.forEach(function (card) {

            const serviceName = card.dataset.name || '';
            const serviceStatus = card.dataset.status || '';

            const matchesSearch = serviceName.includes(searchValue);

            const matchesStatus =
                selectedStatus === 'all' ||
                serviceStatus === selectedStatus;

            card.style.display =
                matchesSearch && matchesStatus
                    ? ''
                    : 'none';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterServices);
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', filterServices);
    }

    document.querySelectorAll('.close-alert').forEach(function (button) {
        button.addEventListener('click', function () {
            const alert = this.closest('.services-alert');

            if (alert) {
                alert.remove();
            }
        });
    });

});
</script>                                                                                        
 