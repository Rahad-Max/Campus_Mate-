<?php
// Coordinator Timetable Edit & Revision Form
$isPublished = ($schedule['status'] === 'published');
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="<?= url('/coordinator/timetable') ?>" class="text-decoration-none">Class Timetable</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Routine #<?= $schedule['id'] ?></li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-0 text-dark">Edit Schedule Routine Slot</h2>
        <p class="text-muted small mb-0">Modify routine parameters or record an audited schedule revision.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?= url('/coordinator/timetable') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Routines
        </a>
    </div>
</div>

<?php if ($isPublished): ?>
    <div class="alert alert-warning border-warning-subtle shadow-sm d-flex align-items-start gap-3 mb-4 rounded-3">
        <i class="bi bi-shield-exclamation fs-4 text-warning flex-shrink-0 mt-1"></i>
        <div>
            <h6 class="fw-bold mb-1 text-dark">Active Published Routine</h6>
            <div class="small text-muted">
                This schedule slot is currently <strong>Published</strong> to students and faculty. Modifying the Day, Time Slot, or Classroom requires documenting a <strong>Revision Reason</strong>. An official entry will be recorded in the Revision Audit Log, and all affected participants will receive an immediate notification.
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card card-pub border-0 shadow-sm bg-white mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Update Routine Slot
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= url('/coordinator/timetable/' . $schedule['id'] . '/edit') ?>" method="POST" id="formEditSchedule">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <div class="row g-3">
                        <!-- Course & Section Display -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-between border">
                                <div>
                                    <span class="text-muted small">Course & Academic Term:</span>
                                    <div class="fw-bold text-dark fs-6">
                                        <?= e($schedule['course_code']) ?> &bull; <?= e($schedule['course_title']) ?> 
                                        <span class="badge bg-secondary-subtle text-secondary ms-2">Sec <?= e($schedule['section_name']) ?></span>
                                    </div>
                                    <div class="text-muted small mt-1">
                                        Term: <?= e($schedule['semester_name'] ?? 'Summer 2026') ?> &bull; Enrolled: <?= (int)$schedule['enrolled_count'] ?> / <?= (int)$schedule['capacity'] ?>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge <?= $schedule['status'] === 'published' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' ?> px-2 py-1">
                                        <?= ucfirst($schedule['status']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Section Select -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Section <span class="text-danger">*</span></label>
                            <select name="section_id" class="form-select form-select-sm" required>
                                <?php foreach ($sections as $sec): ?>
                                    <option value="<?= $sec['id'] ?>" <?= $sec['id'] == $schedule['section_id'] ? 'selected' : '' ?>>
                                        <?= e($sec['course_code']) ?> - Sec <?= e($sec['section_name']) ?> (Cap: <?= $sec['capacity'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Faculty Member -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Faculty Member <span class="text-danger">*</span></label>
                            <select name="teacher_id" class="form-select form-select-sm" required>
                                <?php foreach ($teachers as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= $t['id'] == $schedule['teacher_id'] ? 'selected' : '' ?>>
                                        <?= e($t['first_name'] . ' ' . $t['last_name']) ?> (<?= e($t['designation']) ?> &bull; <?= e($t['department_code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Classroom -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Classroom / Facility <span class="text-danger">*</span></label>
                            <select name="room_id" class="form-select form-select-sm" required>
                                <?php foreach ($rooms as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= $r['id'] == $schedule['room_id'] ? 'selected' : '' ?>>
                                        <?= e($r['room_number']) ?> &bull; <?= e($r['building']) ?> (<?= ucfirst($r['room_type']) ?>, Cap: <?= $r['capacity'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Day of Week -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Day of Week <span class="text-danger">*</span></label>
                            <select name="day_of_week" class="form-select form-select-sm" required>
                                <?php foreach ($days as $day): ?>
                                    <option value="<?= $day ?>" <?= $day === $schedule['day_of_week'] ? 'selected' : '' ?>><?= $day ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Start Time -->
                        <div class="col-6 col-md-6">
                            <label class="form-label small fw-bold">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control form-control-sm" required value="<?= substr($schedule['start_time'], 0, 5) ?>">
                        </div>

                        <!-- End Time -->
                        <div class="col-6 col-md-6">
                            <label class="form-label small fw-bold">End Time <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control form-control-sm" required value="<?= substr($schedule['end_time'], 0, 5) ?>">
                        </div>

                        <!-- Class Type -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Class Routine Type</label>
                            <select name="class_type" class="form-select form-select-sm">
                                <option value="lecture" <?= $schedule['class_type'] === 'lecture' ? 'selected' : '' ?>>Theory / Lecture</option>
                                <option value="lab" <?= $schedule['class_type'] === 'lab' ? 'selected' : '' ?>>Laboratory / Practical</option>
                                <option value="tutorial" <?= $schedule['class_type'] === 'tutorial' ? 'selected' : '' ?>>Tutorial / Sessional</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="published" <?= $schedule['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                                <option value="draft" <?= $schedule['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                <option value="revised" <?= $schedule['status'] === 'revised' ? 'selected' : '' ?>>Revised</option>
                                <option value="cancelled" <?= $schedule['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>

                        <!-- Revision Reason (Mandatory if Published) -->
                        <div class="col-12">
                            <label class="form-label small fw-bold">
                                Revision Reason <?= $isPublished ? '<span class="text-danger">* (Required if changing routine timings or room)</span>' : '<span class="text-muted">(Optional for draft)</span>' ?>
                            </label>
                            <textarea name="revision_reason" class="form-control form-control-sm" rows="3" placeholder="Explain the rationale for rescheduling this class routine..."></textarea>
                            <div class="form-text small">Will be permanently logged into schedule revisions and broadcasted to enrolled students.</div>
                        </div>

                        <div class="col-12 pt-3 border-top d-flex align-items-center justify-content-end gap-2">
                            <a href="<?= url('/coordinator/timetable') ?>" class="btn btn-light btn-sm px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">
                                <i class="bi bi-save me-1"></i> Update Routine
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Side Information -->
    <div class="col-12 col-lg-4">
        <div class="card card-pub border-0 shadow-sm bg-white mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-info-circle text-primary me-2"></i>Current Schedule Overview
                </h6>
            </div>
            <div class="card-body p-3 small text-muted">
                <div class="mb-2"><strong>Day:</strong> <?= e($schedule['day_of_week']) ?></div>
                <div class="mb-2"><strong>Time:</strong> <?= format_time($schedule['start_time']) ?> &ndash; <?= format_time($schedule['end_time']) ?></div>
                <div class="mb-2"><strong>Room:</strong> <?= e($schedule['room_number']) ?> (<?= e($schedule['building']) ?>)</div>
                <div class="mb-2"><strong>Instructor:</strong> <?= e($schedule['teacher_first_name'] . ' ' . $schedule['teacher_last_name']) ?></div>
                <div><strong>Created:</strong> <?= format_date($schedule['created_at']) ?></div>
            </div>
        </div>

        <!-- Quick Action: Make-up Class -->
        <div class="card card-pub border-0 shadow-sm bg-light">
            <div class="card-body p-3">
                <h6 class="fw-bold mb-1 text-dark">Missed a class?</h6>
                <p class="small text-muted mb-3">If this session was cancelled or postponed, you can schedule an official make-up class.</p>
                <a href="<?= url('/coordinator/makeup-classes/create?schedule_id=' . $schedule['id']) ?>" class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-calendar-plus me-1"></i> Schedule Make-up Class
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Revision History Table -->
<div class="card card-pub border-0 shadow-sm bg-white mt-2 mb-4" id="revisions">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-clock-history text-primary me-2"></i>Revision Audit History for this Slot
        </h5>
        <span class="badge bg-light text-muted border"><?= count($revisions) ?> revisions recorded</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($revisions)): ?>
            <div class="text-center py-4 text-muted small">
                <i class="bi bi-journal-check fs-2 text-muted mb-2 d-block"></i>
                No revisions have been recorded for this schedule slot yet. It is running on its original schedule.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Date & Time</th>
                            <th>Previous Schedule</th>
                            <th></th>
                            <th>New Schedule</th>
                            <th>Revision Reason</th>
                            <th class="pe-3">Revised By</th>
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
                                    <div class="fw-semibold text-danger"><?= e($rev['previous_day']) ?></div>
                                    <span class="small font-monospace text-muted"><?= format_time($rev['previous_start_time']) ?> - <?= format_time($rev['previous_end_time']) ?></span>
                                    <div class="text-muted small">Room <?= e($rev['prev_room_number'] ?? '—') ?></div>
                                </td>
                                <td class="text-center text-muted">
                                    <i class="bi bi-arrow-right fs-5 text-primary"></i>
                                </td>
                                <td>
                                    <div class="fw-semibold text-success"><?= e($rev['new_day']) ?></div>
                                    <span class="small font-monospace text-muted"><?= format_time($rev['new_start_time']) ?> - <?= format_time($rev['new_end_time']) ?></span>
                                    <div class="text-muted small">Room <?= e($rev['new_room_number'] ?? '—') ?></div>
                                </td>
                                <td style="max-width: 250px;" class="text-wrap small text-dark">
                                    <?= e($rev['revision_reason']) ?>
                                </td>
                                <td class="pe-3 small text-muted">
                                    <?= e(($rev['revised_first_name'] ?? '') . ' ' . ($rev['revised_last_name'] ?? '')) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
