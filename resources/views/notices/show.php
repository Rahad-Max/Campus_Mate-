<div class="mb-4">
    <a href="/notices" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Back to Notices
    </a>
</div>

<div class="card card-pub">
    <div class="card-body p-4 p-md-5">
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="badge bg-primary"><?= ucfirst($notice['category']) ?></span>
            <span class="text-muted small">&bull; Published on <?= format_date($notice['publish_date'], 'F d, Y') ?></span>
        </div>

        <h2 class="fw-bold mb-4"><?= e($notice['title']) ?></h2>

        <div class="notice-content text-secondary leading-relaxed mb-4" style="font-size: 1.05rem; white-space: pre-line;">
            <?= e($notice['content']) ?>
        </div>

        <?php if (!empty($notice['attachment_url'])): ?>
            <div class="p-3 bg-light rounded border d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-pdf fs-3 text-danger"></i>
                    <div>
                        <div class="fw-bold small">Official Notice Attachment</div>
                        <div class="text-muted" style="font-size: 0.75rem;"><?= e(basename($notice['attachment_url'])) ?></div>
                    </div>
                </div>
                <a href="/<?= e($notice['attachment_url']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                    <i class="bi bi-download me-1"></i> Download
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
