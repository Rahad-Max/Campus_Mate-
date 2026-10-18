<?php
// Coordinator Exam Edit Form
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="<?= url('/coordinator/exams') ?>" class="text-decoration-none">Examinations</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Exam #<?= $exam['id'] ?></li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-0 text-dark">Edit Examination Routine</h2>
        <p class="text-muted small mb-0">Update assessment schedule, room, or assigned invigilator.</p>
    </div>
    <a href="<?= url('/coordinator/exams') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Back to Exams
    </a>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card card-pub border-0 shadow-sm bg-white">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Update Examination Details
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= url('/coordinator/exams/' . $exam['id'] . '/edit') ?>" method="POST" id="formEditExam">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <div class="row g-3">
                        <!-- Course Display -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-between border">
                                <div>
                                    <span class="text-muted small">Course:</span>
                                    <div class="fw-bold text-dark fs-6">
                                        <?= e($exam['course_code']) ?> &bull; <?= e($exam['course_title']) ?>
                                        <?php if (!empty($exam['section_name'])): ?>
                                            <span class="badge bg-secondary-subtle text-secondary ms-2">Sec <?= e($exam['section_name']) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border ms-2">All Sections</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                        <?= strtoupper($exam['exam_type']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Course Offering (Hidden or Select) -->
                        <input type="hidden" name="course_offering_id" value="<?= e($exam['course_offering_id']) ?>">

                        <!-- Section Select -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Section</label>
                            <select name="section_id" class="form-select form-select-sm">
                                <option value="">-- Entire Course (All Sections) --</option>
                                <?php foreach ($sections as $sec): ?>
                                    <option value="<?= $sec['id'] ?>" <?= $sec['id'] == $exam['section_id'] ? 'selected' : '' ?>>
                                        Section <?= e($sec['section_name']) ?> (Enrolled: <?= $sec['enrolled_count'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Exam Type -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Examination Type <span class="text-danger">*</span></label>
                            <select name="exam_type" class="form-select form-select-sm" required>
                                <option value="midterm" <?= $exam['exam_type'] === 'midterm' ? 'selected' : '' ?>>Midterm Examination</option>
                                <option value="final" <?= $exam['exam_type'] === 'final' ? 'selected' : '' ?>>Final Examination</option>
                                <option value="quiz" <?= $exam['exam_type'] === 'quiz' ? 'selected' : '' ?>>Class Test / Quiz</option>
                                <option value="practical" <?= $exam['exam_type'] === 'practical' ? 'selected' : '' ?>>Practical / Lab Examination</option>
                            </select>
                        </div>

                        <!-- Exam Date -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Examination Date <span class="text-danger">*</span></label>
                            <input type="date" name="exam_date" class="form-control form-control-sm" required value="<?= e($exam['exam_date']) ?>">
                        </div>

                        <!-- Start Time -->
                        <div class="col-6 col-md-6">
                            <label class="form-label small fw-bold">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control form-control-sm" required value="<?= substr($exam['start_time'], 0, 5) ?>">
                        </div>

                        <!-- End Time -->
                        <div class="col-6 col-md-6">
                            <label class="form-label small fw-bold">End Time <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control form-control-sm" required value="<?= substr($exam['end_time'], 0, 5) ?>">
                        </div>

                        <!-- Classroom / Hall -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Examination Hall / Room <span class="text-danger">*</span></label>
                            <select name="room_id" class="form-select form-select-sm" required>
                                <?php foreach ($rooms as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= $r['id'] == $exam['room_id'] ? 'selected' : '' ?>>
                                        <?= e($r['room_number']) ?> &bull; <?= e($r['building']) ?> (Capacity: <?= $r['capacity'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Invigilator Faculty -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Designated Invigilator</label>
                            <select name="invigilator_teacher_id" class="form-select form-select-sm">
                                <option value="">-- Assign Invigilator Later --</option>
                                <?php foreach ($teachers as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= $t['id'] == $exam['invigilator_teacher_id'] ? 'selected' : '' ?>>
                                        <?= e($t['first_name'] . ' ' . $t['last_name']) ?> (<?= e($t['designation']) ?> &bull; <?= e($t['department_code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="published" <?= $exam['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                                <option value="scheduled" <?= $exam['status'] === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                <option value="draft" <?= $exam['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                <option value="cancelled" <?= $exam['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>

                        <!-- Instructions -->
                        <div class="col-12">
                            <label class="form-label small fw-bold">Special Exam Instructions</label>
                            <textarea name="instructions" class="form-control form-control-sm" rows="3"><?= e($exam['instructions'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12 pt-3 border-top d-flex align-items-center justify-content-end gap-2">
                            <a href="<?= url('/coordinator/exams') ?>" class="btn btn-light btn-sm px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">
                                <i class="bi bi-save me-1"></i> Update Routine
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Side Info -->
    <div class="col-12 col-lg-4">
        <div class="card card-pub border-0 shadow-sm bg-white mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-info-circle text-primary me-2"></i>Routine Meta
                </h6>
            </div>
            <div class="card-body p-3 small text-muted">
                <div class="mb-2"><strong>Exam ID:</strong> #<?= $exam['id'] ?></div>
                <div class="mb-2"><strong>Room:</strong> <?= e($exam['room_number']) ?> (<?= e($exam['building']) ?>)</div>
                <div class="mb-2"><strong>Invigilator:</strong> <?= e($exam['invigilator_first_name'] ? $exam['invigilator_first_name'] . ' ' . $exam['invigilator_last_name'] : 'None') ?></div>
                <div class="mb-2"><strong>Created:</strong> <?= format_date($exam['created_at']) ?></div>
                <div><strong>Updated:</strong> <?= format_date($exam['updated_at']) ?></div>
            </div>
        </div>
    </div>
</div>
