<div class="mb-4">
    <a href="/courses" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Back to Course Catalog
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card card-pub mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge bg-primary fs-6 px-3 py-1"><?= e($course['course_code']) ?></span>
                    <span class="badge bg-light text-dark border"><?= ucfirst($course['course_type']) ?></span>
                </div>
                <h3 class="fw-bold mb-3"><?= e($course['title']) ?></h3>
                <h6 class="fw-bold text-muted mb-2">Course Description & Objectives</h6>
                <p class="text-secondary leading-relaxed mb-4">
                    <?= e($course['description'] ?? 'No syllabus description provided.') ?>
                </p>

                <h6 class="fw-bold text-muted mb-2">Academic Prerequisite Requirements</h6>
                <?php if (empty($prerequisites)): ?>
                    <p class="text-muted small"><i class="bi bi-info-circle me-1"></i> None (This course has no prerequisites).</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($prerequisites as $pr): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold text-primary"><?= e($pr['course_code']) ?></span> &bull; <?= e($pr['title']) ?>
                                </div>
                                <span class="badge bg-warning-subtle text-warning border">Min Grade: <?= e($pr['min_grade']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-pub">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold">Course Specifications</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="small text-muted">Credits</div>
                    <div class="fw-bold fs-5"><?= number_format((float)$course['credit'], 1) ?> Credit Hours</div>
                </div>
                <div class="mb-3">
                    <div class="small text-muted">Course Type</div>
                    <div class="fw-semibold"><?= ucfirst($course['course_type']) ?></div>
                </div>
                <div class="mb-3">
                    <div class="small text-muted">Catalog Status</div>
                    <span class="status-pill completed"><?= ucfirst($course['status']) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
