<?php
// Coordinator Timetable Management View
$totalSlots = count($schedules);
$publishedCount = count(array_filter($schedules, fn($s) => $s['status'] === 'published'));
$draftCount = count(array_filter($schedules, fn($s) => $s['status'] === 'draft'));
$revisedCount = count(array_filter($schedules, fn($s) => ($s['revision_count'] ?? 0) > 0));
?>

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                <i class="bi bi-calendar3 me-1"></i> Academic Planning
            </span>
            <span class="text-muted small">&bull; Semester ID: <?= e($selectedSemesterId) ?></span>
        </div>
        <h2 class="fw-bold mb-0 text-dark">Class Timetable Management</h2>
        <p class="text-muted mb-0 small">Schedule, coordinate, validate conflicts, and publish course routines.</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="<?= url('/coordinator/timetable/revisions?semester_id=' . $selectedSemesterId) ?>" class="btn btn-outline-secondary btn-sm shadow-sm rounded-pill px-3">
            <i class="bi bi-clock-history me-1"></i> Revision Log
        </a>
        <a href="<?= url('/coordinator/timetable/create') ?>" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3">
            <i class="bi bi-plus-circle me-1"></i> Schedule Class Routine
        </a>
    </div>
</div>

<!-- Quick Statistics Bar -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card card-pub border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="p-2 bg-primary-subtle text-primary rounded-3">
                    <i class="bi bi-calendar-range fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Total Slots</div>
                    <div class="h4 fw-bold mb-0 text-dark"><?= $totalSlots ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-pub border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="p-2 bg-success-subtle text-success rounded-3">
                    <i class="bi bi-check2-circle fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Published</div>
                    <div class="h4 fw-bold mb-0 text-success"><?= $publishedCount ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-pub border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="p-2 bg-warning-subtle text-warning rounded-3">
                    <i class="bi bi-pencil-square fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Draft Routines</div>
                    <div class="h4 fw-bold mb-0 text-warning"><?= $draftCount ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-pub border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="p-2 bg-info-subtle text-info rounded-3">
                    <i class="bi bi-arrow-repeat fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Revised Slots</div>
                    <div class="h4 fw-bold mb-0 text-info"><?= $revisedCount ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card card-pub border-0 shadow-sm mb-4 bg-white">
    <div class="card-body p-3">
        <form action="<?= url('/coordinator/timetable') ?>" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Academic Term / Semester</label>
                <select name="semester_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php foreach ($semesters as $sem): ?>
                        <option value="<?= $sem['id'] ?>" <?= $sem['id'] == $selectedSemesterId ? 'selected' : '' ?>>
                            <?= e($sem['name']) ?> (<?= e($sem['code']) ?>) <?= !empty($sem['is_current']) ? '— Active' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Day of Week</label>
                <select name="day_of_week" class="form-select form-select-sm">
                    <option value="">All Days</option>
                    <?php foreach ($days as $day): ?>
                        <option value="<?= $day ?>" <?= ($filters['day_of_week'] ?? '') === $day ? 'selected' : '' ?>><?= $day ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Classroom</label>
                <select name="room_id" class="form-select form-select-sm">
                    <option value="">All Rooms</option>
                    <?php foreach ($rooms as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= ($filters['room_id'] ?? 0) == $r['id'] ? 'selected' : '' ?>>
                            <?= e($r['room_number']) ?> (<?= e($r['building']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Instructor</label>
                <select name="teacher_id" class="form-select form-select-sm">
                    <option value="">All Faculty</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= ($filters['teacher_id'] ?? 0) == $t['id'] ? 'selected' : '' ?>>
                            <?= e($t['first_name'] . ' ' . $t['last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="revised" <?= ($filters['status'] ?? '') === 'revised' ? 'selected' : '' ?>>Revised</option>
                    <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-12 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100" title="Apply Filter">
                    <i class="bi bi-funnel"></i>
                </button>
                <a href="<?= url('/coordinator/timetable?semester_id=' . $selectedSemesterId) ?>" class="btn btn-outline-secondary btn-sm" title="Clear Filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Timetable Routines Table Card -->
<div class="card card-pub border-0 shadow-sm bg-white overflow-hidden">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-table me-2 text-primary"></i>Scheduled Routine Slots
        </h5>
        <span class="badge bg-light text-muted border px-2 py-1 font-monospace">
            Showing <?= count($schedules) ?> routines
        </span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($schedules)): ?>
            <div class="text-center py-5">
                <div class="p-3 bg-light rounded-circle d-inline-block mb-3">
                    <i class="bi bi-calendar-x fs-1 text-muted"></i>
                </div>
                <h5 class="fw-bold text-dark">No Class Routine Slots Found</h5>
                <p class="text-muted small max-w-sm mx-auto mb-3">No schedule entries match your filter criteria for this academic semester.</p>
                <a href="<?= url('/coordinator/timetable/create') ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-plus-circle me-1"></i> Schedule New Slot
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 14%;">Day & Time</th>
                            <th>Course</th>
                            <th>Section</th>
                            <th>Room</th>
                            <th>Instructor</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $slot): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark"><?= e($slot['day_of_week']) ?></div>
                                    <span class="badge bg-light text-dark border font-monospace small">
                                        <?= format_time($slot['start_time']) ?> - <?= format_time($slot['end_time']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary"><?= e($slot['course_code']) ?></div>
                                    <div class="text-muted small text-truncate" style="max-width: 220px;"><?= e($slot['course_title']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                        Sec <?= e($slot['section_name']) ?>
                                    </span>
                                    <div class="text-muted small mt-1 font-monospace">
                                        <?= (int)$slot['enrolled_count'] ?> / <?= (int)$slot['capacity'] ?> enrolled
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="bi bi-geo-alt text-muted small"></i>
                                        <span class="fw-semibold text-dark"><?= e($slot['room_number']) ?></span>
                                    </div>
                                    <div class="text-muted small"><?= e($slot['building']) ?></div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                            <?= strtoupper(substr($slot['teacher_first_name'] ?? 'T', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-medium text-dark"><?= e(($slot['teacher_first_name'] ?? '') . ' ' . ($slot['teacher_last_name'] ?? '')) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?= $slot['class_type'] === 'lab' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' ?>">
                                        <?= ucfirst($slot['class_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($slot['status'] === 'published'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-check-circle-fill me-1"></i>Published
                                        </span>
                                    <?php elseif ($slot['status'] === 'draft'): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                            <i class="bi bi-pencil-fill me-1"></i>Draft
                                        </span>
                                    <?php elseif ($slot['status'] === 'revised'): ?>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle">
                                            <i class="bi bi-arrow-repeat me-1"></i>Revised
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="bi bi-x-circle me-1"></i>Cancelled
                                        </span>
                                    <?php endif; ?>

                                    <?php if (!empty($slot['revision_count']) && (int)$slot['revision_count'] > 0): ?>
                                        <a href="<?= url('/coordinator/timetable/' . $slot['id'] . '/edit#revisions') ?>" class="badge bg-light text-muted border text-decoration-none ms-1" title="View <?= $slot['revision_count'] ?> revisions">
                                            <?= $slot['revision_count'] ?> rev
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('/coordinator/timetable/' . $slot['id'] . '/edit') ?>" class="btn btn-outline-secondary" title="Edit Schedule Slot">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <?php if ($slot['status'] === 'draft'): ?>
                                            <form action="<?= url('/coordinator/timetable/' . $slot['id'] . '/publish') ?>" method="POST" class="d-inline" onsubmit="return confirm('Publish this class schedule to students and instructors?');">
                                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                                <button type="submit" class="btn btn-outline-success" title="Publish Routine">
                                                    <i class="bi bi-send-check"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <a href="<?= url('/coordinator/makeup-classes/create?schedule_id=' . $slot['id']) ?>" class="btn btn-outline-info" title="Schedule Make-up Class">
                                            <i class="bi bi-calendar-plus"></i>
                                        </a>

                                        <?php if ($slot['status'] !== 'cancelled'): ?>
                                            <button type="button" class="btn btn-outline-danger" title="Cancel Routine" data-bs-toggle="modal" data-bs-target="#cancelModal<?= $slot['id'] ?>">
                                                <i class="bi bi-slash-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Cancellation Confirmation Modal -->
                                    <div class="modal fade" id="cancelModal<?= $slot['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content text-start">
                                                <form action="<?= url('/coordinator/timetable/' . $slot['id'] . '/cancel') ?>" method="POST">
                                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                                    <div class="modal-header border-bottom">
                                                        <h5 class="modal-title text-danger fw-bold">
                                                            <i class="bi bi-exclamation-triangle me-2"></i>Cancel Class Routine Slot
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="small text-muted mb-3">
                                                            You are about to cancel this routine for <strong><?= e($slot['course_code']) ?> (Sec <?= e($slot['section_name']) ?>)</strong> on <strong><?= e($slot['day_of_week']) ?></strong>.
                                                            All registered students and faculty will receive an immediate notification.
                                                        </p>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold">Cancellation Reason</label>
                                                            <textarea name="cancel_reason" class="form-control form-control-sm" rows="3" placeholder="Provide reason for class routine cancellation..." required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top">
                                                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Keep Schedule</button>
                                                        <button type="submit" class="btn btn-danger btn-sm">Confirm Cancellation</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
