<?php
$user = auth_user();
$unreadNotifCount = 0;
if ($user) {
    $notifModel = new \App\Models\Notification();
    $unreadNotifCount = $notifModel->getUnreadCount((int)$user['id']);
}
$semModel = new \App\Models\Semester();
$activeSem = $semModel->getCurrent();
$activeSemName = $activeSem ? ($activeSem['name'] . ' Active') : 'Academic Session';
?>
<nav class="app-navbar">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-light d-lg-none border" id="sidebarToggleBtn" type="button" aria-label="Toggle navigation">
            <i class="bi bi-list fs-5"></i>
        </button>
        <span class="badge bg-light text-primary border px-2 py-1 d-none d-md-inline-block">
            <i class="bi bi-calendar3 me-1"></i> <?= e($activeSemName) ?>
        </span>
    </div>

    <div class="d-flex align-items-center gap-3">
        <?php if ($user): ?>
            <!-- Notifications Icon -->
            <a href="<?= url('notifications') ?>" class="btn btn-light rounded-circle position-relative border" title="Notifications">
                <i class="bi bi-bell"></i>
                <?php if ($unreadNotifCount > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                        <?= $unreadNotifCount ?>
                    </span>
                <?php endif; ?>
            </a>

            <!-- User Dropdown -->
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 border px-3 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="avatar-circle bg-pub-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.85rem;">
                        <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
                    </span>
                    <span class="d-none d-sm-inline-block text-start">
                        <span class="d-block fw-semibold small lh-1"><?= e($user['first_name'] . ' ' . $user['last_name']) ?></span>
                        <span class="text-muted" style="font-size: 0.75rem;"><?= ucwords(str_replace('_', ' ', $user['role'])) ?></span>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border">
                    <li><h6 class="dropdown-header"><?= e($user['email']) ?></h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= url('dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                    <li><a class="dropdown-item" href="<?= url('helpdesk') ?>"><i class="bi bi-robot me-2"></i>AI Helpdesk</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
                </ul>
            </div>
        <?php else: ?>
            <a href="<?= url('login') ?>" class="btn btn-pub btn-sm px-3">Sign In</a>
        <?php endif; ?>
    </div>
</nav>
