<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-1">Campus Notices & Circulars</h2>
        <p class="text-muted mb-0">Official academic circulars, exam schedules, and department announcements.</p>
    </div>
</div>

<div class="row g-3">
    <?php if (empty($notices)): ?>
        <div class="col-12 text-center py-5 text-muted">
            <i class="bi bi-bell-slash fs-1"></i>
            <p class="mt-2">No active notices found.</p>
        </div>
    <?php else: ?>
        <?php foreach ($notices as $n): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-pub h-100 <?= $n['is_pinned'] ? 'border-primary' : '' ?>">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-light text-dark border"><?= ucfirst($n['category']) ?></span>
                            <?php if ($n['is_pinned']): ?>
                                <span class="badge bg-primary-subtle text-primary"><i class="bi bi-pin-angle-fill"></i> Pinned</span>
                            <?php endif; ?>
                        </div>
                        <h5 class="fw-bold mb-2">
                            <a href="/notices/<?= $n['id'] ?>" class="text-decoration-none text-dark stretched-link">
                                <?= e($n['title']) ?>
                            </a>
                        </h5>
                        <p class="text-muted small flex-grow-1">
                            <?= e(mb_substr($n['content'], 0, 120)) ?>...
                        </p>
                        <div class="border-top pt-2 mt-auto d-flex justify-content-between align-items-center small text-muted">
                            <span><i class="bi bi-calendar3 me-1"></i><?= format_date($n['publish_date']) ?></span>
                            <span><?= e($n['author_first_name'] ?? 'Admin') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
