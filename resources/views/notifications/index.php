<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-1">Notifications & Alerts</h2>
        <p class="text-muted mb-0">System updates, registration approvals, and routine revisions.</p>
    </div>
    <form action="/notifications/mark-all-read" method="POST">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-check2-all me-1"></i> Mark All as Read
        </button>
    </form>
</div>

<div class="card card-pub">
    <div class="card-body p-0">
        <?php if (empty($notifications)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-bell-slash fs-1"></i>
                <p class="mt-2">No notifications found.</p>
            </div>
        <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($notifications as $notif): ?>
                    <div class="list-group-item p-3 d-flex align-items-center justify-content-between notification-item <?= !$notif['is_read'] ? 'bg-light unread' : '' ?>">
                        <div class="d-flex align-items-center gap-3">
                            <span class="rounded-circle p-2 bg-<?= $notif['type'] ?? 'primary' ?>-subtle text-<?= $notif['type'] ?? 'primary' ?>">
                                <i class="bi bi-bell-fill"></i>
                            </span>
                            <div>
                                <h6 class="mb-1 fw-bold"><?= e($notif['title']) ?></h6>
                                <p class="mb-0 text-secondary small"><?= e($notif['message']) ?></p>
                                <small class="text-muted" style="font-size: 0.75rem;"><?= format_date($notif['created_at'], 'M d, Y H:i') ?></small>
                            </div>
                        </div>
                        <?php if (!$notif['is_read']): ?>
                            <a href="/notifications/<?= $notif['id'] ?>/read" class="btn btn-sm btn-light border btn-mark-read" data-id="<?= $notif['id'] ?>">
                                <i class="bi bi-check-lg"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
