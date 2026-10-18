<?php
// Coordinator Make-up Classes View
$totalMakeup = count($makeupClasses);
$scheduledCount = count(array_filter($makeupClasses, fn($m) => $m['status'] === 'scheduled'));
$completedCount = count(array_filter($makeupClasses, fn($m) => $m['status'] === 'completed'));
$cancelledCount = count(array_filter($makeupClasses, fn($m) => $m['status'] === 'cancelled'));
?>

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                <i class="bi bi-calendar-plus me-1"></i> Routine Adjustments
            </span>
            <span class="text-muted small">&bull; Semester ID: <?= e($selectedSemesterId) ?></span>
        </div>
        <h2 class="fw-bold mb-0 text-dark">Make-up Class Scheduling</h2>
        <p class="text-muted mb-0 small">Schedule replacement sessions for missed, cancelled, or holiday-interrupted classes.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?= url('/coordinator/makeup-classes/create') ?>" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3">
            <i class="bi bi-plus-circle me-1"></i> Schedule Make-up Class
        </a>
    </div>
</div>

<!-- Stats Overview -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card card-pub border-0 shadow-sm bg-white h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="p-2 bg-primary-subtle text-primary rounded-3">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Total Sessions</div>
                    <div class="h4 fw-bold mb-0 text-dark"><?= $totalMakeup ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-pub border-0 shadow-sm bg-white h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="p-2 bg-success-subtle text-success rounded-3">
                    <i class="bi bi-calendar-event fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Scheduled</div>
                    <div class="h4 fw-bold mb-0 text-success"><?= $scheduledCount ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-pub border-0 shadow-sm bg-white h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="p-2 bg-info-subtle text-info rounded-3">
                    <i class="bi bi-check2-all fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Completed</div>
                    <div class="h4 fw-bold mb-0 text-info"><?= $completedCount ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-pub border-0 shadow-sm bg-white h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="p-2 bg-danger-subtle text-danger rounded-3">
                    <i class="bi bi-x-circle fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Cancelled</div>
                    <div class="h4 fw-bold mb-0 text-danger"><?= $cancelledCount ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card card-pub border-0 shadow-sm mb-4 bg-white">
    <div class="card-body p-3">
        <form action="<?= url('/coordinator/makeup-classes') ?>" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Academic Term / Semester</label>
                <select name="semester_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php foreach ($semesters as $sem): ?>
                        <option value="<?= $sem['id'] ?>" <?= $sem['id'] == $selectedSemesterId ? 'selected' : '' ?>>
                            <?= e($sem['name']) ?> (<?= e($sem['code']) ?>) <?= !empty($sem['is_current']) ? '— Active' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="scheduled" <?= ($filters['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                    <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Faculty Member</label>
                <select name="teacher_id" class="form-select form-select-sm">
                    <option value="">All Faculty</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= ($filters['teacher_id'] ?? 0) == $t['id'] ? 'selected' : '' ?>>
                            <?= e($t['first_name'] . ' ' . $t['last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100" title="Apply Filter">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <a href="<?= url('/coordinator/makeup-classes?semester_id=' . $selectedSemesterId) ?>" class="btn btn-outline-secondary btn-sm" title="Clear Filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Make-up Classes Table -->
<div class="card card-pub border-0 shadow-sm bg-white overflow-hidden">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-calendar-plus text-primary me-2"></i>Scheduled Make-up Sessions
        </h5>
        <span class="badge bg-light text-muted border px-2 py-1 font-monospace">
            <?= count($makeupClasses) ?> sessions
        </span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($makeupClasses)): ?>
            <div class="text-center py-5">
                <div class="p-3 bg-light rounded-circle d-inline-block mb-3">
                    <i class="bi bi-calendar2-check fs-1 text-muted"></i>
                </div>
                <h5 class="fw-bold text-dark">No Make-up Classes Scheduled</h5>
                <p class="text-muted small max-w-sm mx-auto mb-3">No replacement or special class sessions are currently pending.</p>
                <a href="<?= url('/coordinator/makeup-classes/create') ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-plus-circle me-1"></i> Schedule Make-up Session
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Make-up Date & Time</th>
                            <th>Course</th>
                            <th>Section</th>
                            <th>Classroom</th>
                            <th>Instructor</th>
                            <th>Original Routine Ref</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($makeupClasses as $mc): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark"><?= format_date($mc['makeup_date']) ?></div>
                                    <span class="badge bg-light text-dark border font-monospace small">
                                        <?= format_time($mc['start_time']) ?> &ndash; <?= format_time($mc['end_time']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary"><?= e($mc['course_code']) ?></div>
                                    <div class="text-muted small text-truncate" style="max-width: 180px;"><?= e($mc['course_title']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                        Sec <?= e($mc['section_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="bi bi-geo-alt text-muted small"></i>
                                        <span class="fw-semibold text-dark"><?= e($mc['room_number']) ?></span>
                                    </div>
                                    <div class="text-muted small"><?= e($mc['building']) ?></div>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark"><?= e(($mc['teacher_first_name'] ?? '') . ' ' . ($mc['teacher_last_name'] ?? '')) ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($mc['original_day'])): ?>
                                        <span class="badge bg-light text-muted border">
                                            Replaces <?= e($mc['original_day']) ?> (<?= format_time($mc['original_start_time']) ?>)
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">Special Session</span>
                                    <?php endif; ?>
                                </td>
                                <td style="max-width: 220px;" class="text-wrap small text-dark">
                                    <?= e($mc['reason']) ?>
                                </td>
                                <td>
                                    <?php if ($mc['status'] === 'scheduled'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-calendar-check me-1"></i>Scheduled
                                        </span>
                                    <?php elseif ($mc['status'] === 'completed'): ?>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle">
                                            <i class="bi bi-check2-circle me-1"></i>Completed
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="bi bi-x-circle me-1"></i>Cancelled
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <?php if ($mc['status'] === 'scheduled'): ?>
                                        <form action="<?= url('/coordinator/makeup-classes/' . $mc['id'] . '/cancel') ?>" method="POST" class="d-inline" onsubmit="return confirm('Cancel this make-up class? All enrolled students will be notified.');">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Cancel Session">
                                                <i class="bi bi-slash-circle me-1"></i> Cancel
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
