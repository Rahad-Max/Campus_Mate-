<?php
// Coordinator Make-up Class Create Form
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="<?= url('/coordinator/makeup-classes') ?>" class="text-decoration-none">Make-up Classes</a></li>
                <li class="breadcrumb-item active" aria-current="page">Schedule Make-up Session</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-0 text-dark">Schedule Make-up Class</h2>
        <p class="text-muted small mb-0">Create a replacement class session without overwriting the master routine.</p>
    </div>
    <a href="<?= url('/coordinator/makeup-classes') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Back to Make-up Classes
    </a>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card card-pub border-0 shadow-sm bg-white">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-calendar-plus text-primary me-2"></i>Make-up Class Parameters
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= url('/coordinator/makeup-classes') ?>" method="POST" id="formCreateMakeup">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <?php if ($originalSchedule): ?>
                        <input type="hidden" name="class_schedule_id" value="<?= e($originalSchedule['id']) ?>">
                        <input type="hidden" name="section_id" value="<?= e($originalSchedule['section_id']) ?>">
                    <?php endif; ?>

                    <div class="row g-3">
                        <!-- Term Indicator -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-between border">
                                <div>
                                    <span class="text-muted small">Academic Semester:</span>
                                    <div class="fw-bold text-dark fs-6"><?= e($currentSemester['name'] ?? 'Summer 2026') ?> (<?= e($currentSemester['code'] ?? 'SUMMER-2026') ?>)</div>
                                </div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Active Term</span>
                            </div>
                        </div>

                        <?php if ($originalSchedule): ?>
                            <!-- Linked Original Routine Information -->
                            <div class="col-12">
                                <div class="p-3 bg-info-subtle rounded-3 border border-info-subtle">
                                    <div class="d-flex align-items-center gap-2 mb-1 text-info-emphasis fw-bold">
                                        <i class="bi bi-link-45deg fs-5"></i>
                                        <span>Linked to Master Routine #<?= $originalSchedule['id'] ?></span>
                                    </div>
                                    <div class="text-dark fw-bold">
                                        <?= e($originalSchedule['course_code']) ?> &bull; <?= e($originalSchedule['course_title']) ?>
                                        <span class="badge bg-secondary-subtle text-secondary ms-2">Sec <?= e($originalSchedule['section_name']) ?></span>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        Original Routine: Regular <?= e($originalSchedule['day_of_week']) ?>s at <?= format_time($originalSchedule['start_time']) ?> &ndash; <?= format_time($originalSchedule['end_time']) ?> in Room <?= e($originalSchedule['room_number']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Course Section Selector -->
                            <div class="col-12">
                                <label class="form-label small fw-bold">Target Course & Section <span class="text-danger">*</span></label>
                                <select name="section_id" class="form-select form-select-sm" required>
                                    <option value="">-- Select Section --</option>
                                    <?php foreach ($sections as $sec): ?>
                                        <option value="<?= $sec['id'] ?>">
                                            <?= e($sec['course_code']) ?> &bull; <?= e($sec['course_title']) ?> &mdash; Sec <?= e($sec['section_name']) ?> (Enrolled: <?= $sec['enrolled_count'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <!-- Assigned Instructor -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Instructor for Make-up Session <span class="text-danger">*</span></label>
                            <select name="teacher_id" class="form-select form-select-sm" required>
                                <option value="">-- Select Faculty Member --</option>
                                <?php foreach ($teachers as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= ($originalSchedule && $originalSchedule['teacher_id'] == $t['id']) ? 'selected' : '' ?>>
                                        <?= e($t['first_name'] . ' ' . $t['last_name']) ?> (<?= e($t['designation']) ?> &bull; <?= e($t['department_code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Classroom -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Classroom / Facility <span class="text-danger">*</span></label>
                            <select name="room_id" class="form-select form-select-sm" required>
                                <option value="">-- Select Classroom --</option>
                                <?php foreach ($rooms as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= ($originalSchedule && $originalSchedule['room_id'] == $r['id']) ? 'selected' : '' ?>>
                                        <?= e($r['room_number']) ?> &bull; <?= e($r['building']) ?> (Capacity: <?= $r['capacity'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Make-up Date -->
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">Replacement Date <span class="text-danger">*</span></label>
                            <input type="date" name="makeup_date" class="form-control form-control-sm" required value="<?= date('Y-m-d', strtotime('+2 days')) ?>">
                        </div>

                        <!-- Start Time -->
                        <div class="col-6 col-md-4">
                            <label class="form-label small fw-bold">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control form-control-sm" required value="14:00">
                        </div>

                        <!-- End Time -->
                        <div class="col-6 col-md-4">
                            <label class="form-label small fw-bold">End Time <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control form-control-sm" required value="15:30">
                        </div>

                        <!-- Reason -->
                        <div class="col-12">
                            <label class="form-label small fw-bold">Reason for Make-up Session <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control form-control-sm" rows="3" placeholder="e.g. Compensatory class for national holiday on Sunday, or instructor official conference leave..." required></textarea>
                            <div class="form-text small">This reason will be provided to all enrolled students and faculty in their notifications.</div>
                        </div>

                        <div class="col-12 pt-3 border-top d-flex align-items-center justify-content-end gap-2">
                            <a href="<?= url('/coordinator/makeup-classes') ?>" class="btn btn-light btn-sm px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">
                                <i class="bi bi-calendar-check me-1"></i> Confirm & Notify Students
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Policy Information -->
    <div class="col-12 col-lg-4">
        <div class="card card-pub border-0 shadow-sm bg-white mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-shield-check text-success me-2"></i>Make-up Class Policy
                </h6>
            </div>
            <div class="card-body p-3 small text-muted">
                <p class="mb-2"><strong>Non-Destructive Scheduling:</strong></p>
                <p class="mb-3">Creating a make-up class does <strong>not</strong> delete or overwrite the regular class schedule. It links to the original routine for full auditability.</p>
                <p class="mb-2"><strong>Automatic Collision Detection:</strong></p>
                <ul class="ps-3 mb-0">
                    <li class="mb-2">Ensures classroom is not in use by another regular class, exam, or make-up session.</li>
                    <li class="mb-2">Checks that instructor has no conflicting academic commitments.</li>
                    <li>Guarantees enrolled students do not have overlapping classes or examinations.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
