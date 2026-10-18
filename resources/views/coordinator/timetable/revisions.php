<?php
// Master Schedule Revisions Audit View
?>

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="<?= url('/coordinator/timetable') ?>" class="text-decoration-none">Class Timetable</a></li>
                <li class="breadcrumb-item active" aria-current="page">Revision Audit Log</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-0 text-dark">Schedule Revision Audit Trail</h2>
        <p class="text-muted small mb-0">Complete historical record of all published timetable modifications and rescheduling reasons.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?= url('/coordinator/timetable?semester_id=' . $selectedSemesterId) ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-calendar3 me-1"></i> Class Timetable
        </a>
    </div>
</div>

<!-- Semester Filter Card -->
<div class="card card-pub border-0 shadow-sm mb-4 bg-white">
    <div class="card-body p-3">
        <form action="<?= url('/coordinator/timetable/revisions') ?>" method="GET" class="row g-2 align-items-center">
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
            <div class="col-12 col-md-8 text-md-end pt-3 pt-md-0">
                <span class="badge bg-light text-muted border px-3 py-2 small">
                    <i class="bi bi-shield-check text-success me-1"></i> Total Recorded Revisions: <?= count($revisions) ?>
                </span>
            </div>
        </form>
    </div>
</div>

<!-- Master Revisions Table -->
<div class="card card-pub border-0 shadow-sm bg-white overflow-hidden">
    <div class="card-header bg-white border-bottom py-3">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-clock-history text-primary me-2"></i>Audited Schedule Modifications
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($revisions)): ?>
            <div class="text-center py-5">
                <div class="p-3 bg-light rounded-circle d-inline-block mb-3">
                    <i class="bi bi-shield-check fs-1 text-success"></i>
                </div>
                <h5 class="fw-bold text-dark">No Schedule Revisions Recorded</h5>
                <p class="text-muted small max-w-sm mx-auto mb-0">No published schedules have been revised for this academic term. All routines are running on their initial published allocations.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Revision Date</th>
                            <th>Course & Section</th>
                            <th>Previous Schedule</th>
                            <th class="text-center"><i class="bi bi-arrow-right"></i></th>
                            <th>New Rescheduled Slot</th>
                            <th>Documented Reason</th>
                            <th>Effective</th>
                            <th class="pe-3">Authorized By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($revisions as $rev): ?>
                            <tr>
                                <td class="ps-3 small">
                                    <div class="fw-bold text-dark"><?= format_date($rev['created_at']) ?></div>
                                    <span class="text-muted small"><?= format_time($rev['created_at']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary"><?= e($rev['course_code']) ?></div>
                                    <div class="text-muted small text-truncate" style="max-width: 180px;"><?= e($rev['course_title']) ?></div>
                                    <span class="badge bg-secondary-subtle text-secondary small">Sec <?= e($rev['section_name']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-danger"><?= e($rev['previous_day']) ?></div>
                                    <span class="small font-monospace text-muted"><?= format_time($rev['previous_start_time']) ?> &ndash; <?= format_time($rev['previous_end_time']) ?></span>
                                    <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i><?= e($rev['prev_room_number'] ?? '—') ?></div>
                                </td>
                                <td class="text-center text-muted">
                                    <i class="bi bi-arrow-right text-primary fs-5"></i>
                                </td>
                                <td>
                                    <div class="fw-semibold text-success"><?= e($rev['new_day']) ?></div>
                                    <span class="small font-monospace text-muted"><?= format_time($rev['new_start_time']) ?> &ndash; <?= format_time($rev['new_end_time']) ?></span>
                                    <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i><?= e($rev['new_room_number'] ?? '—') ?></div>
                                </td>
                                <td style="max-width: 250px;" class="text-wrap small text-dark">
                                    <div class="p-2 bg-light rounded border-start border-3 border-info">
                                        <?= e($rev['revision_reason']) ?>
                                    </div>
                                </td>
                                <td class="small font-monospace text-muted">
                                    <?= format_date($rev['effective_date']) ?>
                                </td>
                                <td class="pe-3 small text-muted">
                                    <div class="fw-medium text-dark"><?= e(($rev['revised_first_name'] ?? '') . ' ' . ($rev['revised_last_name'] ?? '')) ?></div>
                                    <span class="badge bg-light text-muted border">Coordinator</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
