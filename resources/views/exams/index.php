<?php
// Student and Faculty Examination Routines View
?>

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                <i class="bi bi-file-earmark-text me-1"></i> Examination Schedule
            </span>
            <span class="text-muted small">&bull; <?= e($semester['name'] ?? 'Summer 2026') ?></span>
        </div>
        <h2 class="fw-bold mb-0 text-dark">Examination Routines</h2>
        <p class="text-muted mb-0 small">Official midterm, final, and practical assessment schedules and hall allocations.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Examination Admit / Routine
        </button>
    </div>
</div>

<div class="card card-pub border-0 shadow-sm bg-white overflow-hidden mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-calendar-event text-primary me-2"></i>Scheduled Assessments
        </h5>
        <span class="badge bg-light text-muted border px-2 py-1 font-monospace">
            <?= count($exams) ?> scheduled exam<?= count($exams) !== 1 ? 's' : '' ?>
        </span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($exams)): ?>
            <div class="text-center py-5">
                <div class="p-3 bg-light rounded-circle d-inline-block mb-3">
                    <i class="bi bi-file-earmark-check fs-1 text-muted"></i>
                </div>
                <h5 class="fw-bold text-dark">No Examination Routines Published</h5>
                <p class="text-muted small max-w-sm mx-auto mb-0">No official examination schedules have been published yet for your active courses in this semester.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Type</th>
                            <th>Date</th>
                            <th>Time Slot</th>
                            <th>Course</th>
                            <th>Section</th>
                            <th>Exam Hall / Room</th>
                            <th>Instructions / Role</th>
                            <th class="pe-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exams as $ex): ?>
                            <tr>
                                <td class="ps-3">
                                    <span class="badge <?= $ex['exam_type'] === 'final' ? 'bg-danger-subtle text-danger border border-danger-subtle' : ($ex['exam_type'] === 'midterm' ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle') ?> fw-bold px-2 py-1">
                                        <?= strtoupper($ex['exam_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= format_date($ex['exam_date']) ?></div>
                                    <span class="text-muted small"><?= date('l', strtotime($ex['exam_date'])) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">
                                        <?= format_time($ex['start_time']) ?> &ndash; <?= format_time($ex['end_time']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary"><?= e($ex['course_code']) ?></div>
                                    <div class="text-muted small text-truncate" style="max-width: 220px;"><?= e($ex['course_title']) ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($ex['section_name'])): ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            Sec <?= e($ex['section_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">All Sections</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="bi bi-geo-alt text-danger small"></i>
                                        <span class="fw-semibold text-dark"><?= e($ex['room_number']) ?></span>
                                    </div>
                                    <div class="text-muted small"><?= e($ex['building']) ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($ex['is_invigilator'])): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="bi bi-shield-check me-1"></i>You are Invigilator
                                        </span>
                                    <?php elseif (!empty($ex['instructions'])): ?>
                                        <span class="text-muted small text-truncate d-inline-block" style="max-width: 220px;" title="<?= e($ex['instructions']) ?>">
                                            <?= e($ex['instructions']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic">Standard Rules</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-3">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check-circle-fill me-1"></i><?= ucfirst($ex['status']) ?>
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
