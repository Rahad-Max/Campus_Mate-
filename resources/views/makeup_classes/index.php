<?php
// Student and Teacher Make-up Classes View
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-1">Make-up Class Routines</h2>
        <p class="text-muted mb-0">Upcoming compensatory and rescheduled class sessions for <strong><?= e($semester['name'] ?? 'Summer 2026') ?></strong>.</p>
    </div>
</div>

<div class="card card-pub border-0 shadow-sm bg-white overflow-hidden">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-calendar-plus text-primary me-2"></i>Upcoming Make-up Sessions
        </h5>
        <span class="badge bg-light text-muted border px-2 py-1 font-monospace">
            <?= count($makeupClasses) ?> upcoming sessions
        </span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($makeupClasses)): ?>
            <div class="text-center py-5">
                <div class="p-3 bg-light rounded-circle d-inline-block mb-3">
                    <i class="bi bi-calendar-check fs-1 text-success"></i>
                </div>
                <h5 class="fw-bold text-dark">No Make-up Classes Scheduled</h5>
                <p class="text-muted small max-w-sm mx-auto mb-0">You have no upcoming make-up or rescheduled class sessions for your courses. All courses are proceeding on their standard published timetables.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Date</th>
                            <th>Time Slot</th>
                            <th>Course</th>
                            <th>Section</th>
                            <th>Classroom</th>
                            <th>Instructor</th>
                            <th>Reason</th>
                            <th class="pe-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($makeupClasses as $mc): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-dark">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="p-1 bg-primary-subtle text-primary rounded">
                                            <i class="bi bi-calendar-event"></i>
                                        </div>
                                        <span><?= format_date($mc['makeup_date']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">
                                        <?= format_time($mc['start_time']) ?> &ndash; <?= format_time($mc['end_time']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary"><?= e($mc['course_code']) ?></div>
                                    <div class="text-muted small text-truncate" style="max-width: 200px;"><?= e($mc['course_title']) ?></div>
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
                                <td style="max-width: 250px;" class="text-wrap small text-muted">
                                    <div class="p-2 bg-light rounded border-start border-2 border-primary">
                                        <?= e($mc['reason']) ?>
                                    </div>
                                </td>
                                <td class="pe-3">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check-circle me-1"></i>Scheduled
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
