<?php
// Coordinator Exams List View
$totalExams = count($exams);
$midterms = count(array_filter($exams, fn($e) => $e['exam_type'] === 'midterm'));
$finals = count(array_filter($exams, fn($e) => $e['exam_type'] === 'final'));
$published = count(array_filter($exams, fn($e) => in_array($e['status'], ['published', 'scheduled'], true)));
?>

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                <i class="bi bi-file-earmark-text me-1"></i> Examination Control
            </span>
            <span class="text-muted small">&bull; Semester ID: <?= e($selectedSemesterId) ?></span>
        </div>
        <h2 class="fw-bold mb-0 text-dark">Examination Routines & Invigilation</h2>
        <p class="text-muted mb-0 small">Schedule midterm & final examination routines, assign invigilators, and prevent student schedule clashes.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?= url('/coordinator/exams/create') ?>" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3">
            <i class="bi bi-plus-circle me-1"></i> Schedule New Examination
        </a>
    </div>
</div>

<!-- Stats Overview -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card card-pub border-0 shadow-sm bg-white h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="p-2 bg-primary-subtle text-primary rounded-3">
                    <i class="bi bi-journal-check fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Total Exams</div>
                    <div class="h4 fw-bold mb-0 text-dark"><?= $totalExams ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-pub border-0 shadow-sm bg-white h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="p-2 bg-info-subtle text-info rounded-3">
                    <i class="bi bi-calendar-check fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Midterms</div>
                    <div class="h4 fw-bold mb-0 text-info"><?= $midterms ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-pub border-0 shadow-sm bg-white h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="p-2 bg-warning-subtle text-warning rounded-3">
                    <i class="bi bi-award fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Final Exams</div>
                    <div class="h4 fw-bold mb-0 text-warning"><?= $finals ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-pub border-0 shadow-sm bg-white h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="p-2 bg-success-subtle text-success rounded-3">
                    <i class="bi bi-send-check fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Published</div>
                    <div class="h4 fw-bold mb-0 text-success"><?= $published ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card card-pub border-0 shadow-sm mb-4 bg-white">
    <div class="card-body p-3">
        <form action="<?= url('/coordinator/exams') ?>" method="GET" class="row g-2 align-items-end">
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
                <label class="form-label small fw-semibold text-muted mb-1">Exam Type</label>
                <select name="exam_type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="midterm" <?= ($filters['exam_type'] ?? '') === 'midterm' ? 'selected' : '' ?>>Midterm</option>
                    <option value="final" <?= ($filters['exam_type'] ?? '') === 'final' ? 'selected' : '' ?>>Final Exam</option>
                    <option value="quiz" <?= ($filters['exam_type'] ?? '') === 'quiz' ? 'selected' : '' ?>>Quiz / Class Test</option>
                    <option value="practical" <?= ($filters['exam_type'] ?? '') === 'practical' ? 'selected' : '' ?>>Practical / Lab Exam</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="scheduled" <?= ($filters['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                    <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Classroom</label>
                <select name="room_id" class="form-select form-select-sm">
                    <option value="">All Examination Rooms</option>
                    <?php foreach ($rooms as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= ($filters['room_id'] ?? 0) == $r['id'] ? 'selected' : '' ?>>
                            <?= e($r['room_number']) ?> (<?= e($r['building']) ?>, Cap: <?= $r['capacity'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100" title="Apply Filter">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <a href="<?= url('/coordinator/exams?semester_id=' . $selectedSemesterId) ?>" class="btn btn-outline-secondary btn-sm" title="Clear Filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Examination Table -->
<div class="card card-pub border-0 shadow-sm bg-white overflow-hidden">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-calendar-event text-primary me-2"></i>Examination Schedules
        </h5>
        <span class="badge bg-light text-muted border px-2 py-1 font-monospace">
            <?= count($exams) ?> examinations
        </span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($exams)): ?>
            <div class="text-center py-5">
                <div class="p-3 bg-light rounded-circle d-inline-block mb-3">
                    <i class="bi bi-file-earmark-x fs-1 text-muted"></i>
                </div>
                <h5 class="fw-bold text-dark">No Examination Routines Found</h5>
                <p class="text-muted small max-w-sm mx-auto mb-3">No examinations have been scheduled for this term matching the filter.</p>
                <a href="<?= url('/coordinator/exams/create') ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-plus-circle me-1"></i> Schedule First Exam
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Type</th>
                            <th>Date & Time Slot</th>
                            <th>Course</th>
                            <th>Section</th>
                            <th>Room</th>
                            <th>Invigilator</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exams as $ex): ?>
                            <tr>
                                <td class="ps-3">
                                    <span class="badge <?= $ex['exam_type'] === 'final' ? 'bg-danger-subtle text-danger border border-danger-subtle' : ($ex['exam_type'] === 'midterm' ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle') ?> fw-bold">
                                        <?= strtoupper($ex['exam_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= format_date($ex['exam_date']) ?></div>
                                    <span class="badge bg-light text-dark border font-monospace small">
                                        <?= format_time($ex['start_time']) ?> &ndash; <?= format_time($ex['end_time']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary"><?= e($ex['course_code']) ?></div>
                                    <div class="text-muted small text-truncate" style="max-width: 200px;"><?= e($ex['course_title']) ?></div>
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
                                        <i class="bi bi-geo-alt text-muted small"></i>
                                        <span class="fw-semibold text-dark"><?= e($ex['room_number']) ?></span>
                                    </div>
                                    <div class="text-muted small"><?= e($ex['building']) ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($ex['invigilator_first_name'])): ?>
                                        <div class="fw-medium text-dark"><?= e($ex['invigilator_first_name'] . ' ' . $ex['invigilator_last_name']) ?></div>
                                        <span class="text-muted small"><i class="bi bi-person-check text-success me-1"></i>Assigned</span>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic">Not Assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($ex['status'] === 'published' || $ex['status'] === 'scheduled'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-check-circle-fill me-1"></i><?= ucfirst($ex['status']) ?>
                                        </span>
                                    <?php elseif ($ex['status'] === 'draft'): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                            <i class="bi bi-pencil me-1"></i>Draft
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="bi bi-x-circle me-1"></i><?= ucfirst($ex['status']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('/coordinator/exams/' . $ex['id'] . '/edit') ?>" class="btn btn-outline-secondary" title="Edit Examination">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <?php if ($ex['status'] === 'draft'): ?>
                                            <form action="<?= url('/coordinator/exams/' . $ex['id'] . '/publish') ?>" method="POST" class="d-inline" onsubmit="return confirm('Publish this examination schedule to students and invigilators?');">
                                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                                <button type="submit" class="btn btn-outline-success" title="Publish Examination">
                                                    <i class="bi bi-send-check"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($ex['status'] !== 'cancelled'): ?>
                                            <form action="<?= url('/coordinator/exams/' . $ex['id'] . '/cancel') ?>" method="POST" class="d-inline" onsubmit="return confirm('Cancel this examination? All students will be notified.');">
                                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                                <button type="submit" class="btn btn-outline-danger" title="Cancel Exam">
                                                    <i class="bi bi-slash-circle"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
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
