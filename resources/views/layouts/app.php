<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="description" content="PUB CampusMate — Smart Campus Companion with Academic Planning, Course Registration, and AI Helpdesk for Pundra University of Science & Technology.">
    <title><?= e($title ?? 'PUB CampusMate — Smart Campus Companion') ?></title>

    <!-- Bootstrap 5 & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.min.css">

    <!-- Application Theme Styles -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/timetable.css">
    <link rel="stylesheet" href="/assets/css/responsive.css">
</head>
<body>
    <div class="app-wrapper">
        <!-- Sidebar -->
        <?php require __DIR__ . '/sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="main-content">
            <!-- Navbar -->
            <?php require __DIR__ . '/navbar.php'; ?>

            <!-- Flash Messages -->
            <div class="content-body">
                <div class="flash-container">
                    <?php if (has_flash('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show flash-alert" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i><?= e(flash('success')) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (has_flash('danger')): ?>
                        <div class="alert alert-danger alert-dismissible fade show flash-alert" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= e(flash('danger')) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (has_flash('warning')): ?>
                        <div class="alert alert-warning alert-dismissible fade show flash-alert" role="alert">
                            <i class="bi bi-exclamation-circle-fill me-2"></i><?= e(flash('warning')) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (has_flash('info')): ?>
                        <div class="alert alert-info alert-dismissible fade show flash-alert" role="alert">
                            <i class="bi bi-info-circle-fill me-2"></i><?= e(flash('info')) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Page Content -->
                <?= $content ?>
            </div>

            <!-- Footer -->
            <?php require __DIR__ . '/footer.php'; ?>
        </div>
    </div>

    <!-- Vendor Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    <!-- Application Scripts -->
    <script src="/assets/js/app.js"></script>
    <script src="/assets/js/dashboard.js"></script>
    <script src="/assets/js/registration.js"></script>
    <script src="/assets/js/timetable.js"></script>
    <script src="/assets/js/notifications.js"></script>
    <script src="/assets/js/helpdesk.js"></script>
</body>
</html>
