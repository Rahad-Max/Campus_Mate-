<?php
// Coordinator Exam Create Form
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="<?= url('/coordinator/exams') ?>" class="text-decoration-none">Examinations</a></li>
                <li class="breadcrumb-item active" aria-current="page">Schedule Exam</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-0 text-dark">Schedule New Examination</h2>
        <p class="text-muted small mb-0">Define assessment routine, reserve examination hall, and designate invigilator.</p>
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
                    <i class="bi bi-calendar-event text-primary me-2"></i>Examination Parameters
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= url('/coordinator/exams') ?>" method="POST" id="formCreateExam">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <div class="row g-3">
                        <!-- Term Information -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-between border">
                                <div>
                                    <span class="text-muted small">Academic Semester:</span>
                                    <div class="fw-bold text-dark fs-6"><?= e($currentSemester['name'] ?? 'Summer 2026') ?> (<?= e($currentSemester['code'] ?? 'SUMMER-2026') ?>)</div>
                                </div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Active Term</span>
                            </div>
                        </div>

                        <!-- Course Offering -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Course Offering <span class="text-danger">*</span></label>
                            <select name="course_offering_id" id="selectExamOffering" class="form-select form-select-sm" required>
                                <option value="">-- Choose Course Offering --</option>
                                <?php foreach ($offerings as $off): ?>
                                    <option value="<?= $off['id'] ?>">
                                        <?= e($off['course_code']) ?> &bull; <?= e($off['course_title']) ?> (<?= e($off['department_code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Section (Optional, applies to all if null) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Section <span class="text-muted small">(Leave blank for all sections)</span></label>
                            <select name="section_id" id="selectExamSection" class="form-select form-select-sm" disabled>
                                <option value="">-- Entire Course (All Sections) --</option>
                            </select>
                        </div>

                        <!-- Exam Type -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Examination Type <span class="text-danger">*</span></label>
                            <select name="exam_type" class="form-select form-select-sm" required>
                                <option value="midterm">Midterm Examination</option>
                                <option value="final" selected>Final Examination</option>
                                <option value="quiz">Class Test / Quiz</option>
                                <option value="practical">Practical / Lab Examination</option>
                            </select>
                        </div>

                        <!-- Exam Date -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Examination Date <span class="text-danger">*</span></label>
                            <input type="date" name="exam_date" class="form-control form-control-sm" required value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                        </div>

                        <!-- Start Time -->
                        <div class="col-6 col-md-6">
                            <label class="form-label small fw-bold">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control form-control-sm" required value="10:00">
                        </div>

                        <!-- End Time -->
                        <div class="col-6 col-md-6">
                            <label class="form-label small fw-bold">End Time <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control form-control-sm" required value="12:00">
                        </div>

                        <!-- Classroom / Hall -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Examination Hall / Room <span class="text-danger">*</span></label>
                            <select name="room_id" class="form-select form-select-sm" required>
                                <option value="">-- Select Room --</option>
                                <?php foreach ($rooms as $r): ?>
                                    <option value="<?= $r['id'] ?>">
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
                                    <option value="<?= $t['id'] ?>">
                                        <?= e($t['first_name'] . ' ' . $t['last_name']) ?> (<?= e($t['designation']) ?> &bull; <?= e($t['department_code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Initial Status -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="published" selected>Published (Notify Students & Invigilator)</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="draft">Draft (Visible only to coordinator)</option>
                            </select>
                        </div>

                        <!-- Instructions -->
                        <div class="col-12">
                            <label class="form-label small fw-bold">Special Exam Instructions</label>
                            <textarea name="instructions" class="form-control form-control-sm" rows="3" placeholder="e.g. Non-programmable scientific calculators permitted. Students must bring university ID card. No electronic devices."></textarea>
                        </div>

                        <div class="col-12 pt-3 border-top d-flex align-items-center justify-content-end gap-2">
                            <a href="<?= url('/coordinator/exams') ?>" class="btn btn-light btn-sm px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">
                                <i class="bi bi-calendar-check me-1"></i> Save Examination Routine
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Conflict Guidance -->
    <div class="col-12 col-lg-4">
        <div class="card card-pub border-0 shadow-sm bg-white mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-shield-check text-success me-2"></i>Exam Conflict Checks
                </h6>
            </div>
            <div class="card-body p-3 small text-muted">
                <p class="mb-2">Before committing the exam routine, the system verifies:</p>
                <ul class="ps-3 mb-0">
                    <li class="mb-2"><strong>Room Conflict:</strong> Checks that the room is not already booked for another examination on this date and time.</li>
                    <li class="mb-2"><strong>Invigilator Duty:</strong> Checks that the designated faculty member is not invigilating another exam at the same time.</li>
                    <li><strong>Student Enrollment Clash:</strong> Ensures no student enrolled in this course has another exam scheduled at the same time.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const offeringSelect = document.getElementById('selectExamOffering');
    const sectionSelect = document.getElementById('selectExamSection');

    offeringSelect.addEventListener('change', function() {
        const offeringId = this.value;
        if (!offeringId) {
            sectionSelect.innerHTML = '<option value="">-- Entire Course (All Sections) --</option>';
            sectionSelect.disabled = true;
            return;
        }

        sectionSelect.disabled = true;
        sectionSelect.innerHTML = '<option value="">Loading sections...</option>';

        fetch('<?= url("/coordinator/api/offering-data") ?>?offering_id=' + offeringId)
            .then(res => res.json())
            .then(data => {
                sectionSelect.innerHTML = '<option value="">-- Entire Course (All Sections) --</option>';
                if (data.sections && data.sections.length > 0) {
                    data.sections.forEach(sec => {
                        const opt = document.createElement('option');
                        opt.value = sec.id;
                        opt.textContent = 'Section ' + sec.section_name + ' (Enrolled: ' + sec.enrolled_count + ')';
                        sectionSelect.appendChild(opt);
                    });
                    sectionSelect.disabled = false;
                } else {
                    sectionSelect.disabled = false;
                }
            })
            .catch(() => {
                sectionSelect.innerHTML = '<option value="">-- Entire Course (All Sections) --</option>';
                sectionSelect.disabled = false;
            });
    });
});
</script>
