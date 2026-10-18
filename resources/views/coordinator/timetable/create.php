<?php
// Coordinator Timetable Creation Form
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="<?= url('/coordinator/timetable') ?>" class="text-decoration-none">Class Timetable</a></li>
                <li class="breadcrumb-item active" aria-current="page">New Schedule Routine</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-0 text-dark">Schedule New Class Routine</h2>
        <p class="text-muted small mb-0">Create a routine slot with automatic validation for instructor, room, section, and student clashes.</p>
    </div>
    <a href="<?= url('/coordinator/timetable') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Back to Routines
    </a>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card card-pub border-0 shadow-sm bg-white">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-calendar-plus text-primary me-2"></i>Routine Slot Parameters
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= url('/coordinator/timetable') ?>" method="POST" id="formCreateSchedule">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="semester_id" value="<?= e($currentSemester['id'] ?? 2) ?>">

                    <div class="row g-3">
                        <!-- Semester Indicator -->
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
                            <select name="course_offering_id" id="selectOffering" class="form-select form-select-sm" required>
                                <option value="">-- Choose Course Offering --</option>
                                <?php foreach ($offerings as $off): ?>
                                    <option value="<?= $off['id'] ?>">
                                        <?= e($off['course_code']) ?> &bull; <?= e($off['course_title']) ?> (<?= e($off['department_code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text small">Select active course offering in current semester.</div>
                        </div>

                        <!-- Section -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Section <span class="text-danger">*</span></label>
                            <select name="section_id" id="selectSection" class="form-select form-select-sm" required disabled>
                                <option value="">-- Select Offering First --</option>
                            </select>
                            <div class="form-text small" id="sectionCapacityNote">Section and seat capacity will appear here.</div>
                        </div>

                        <!-- Assigned Instructor -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Assigned Faculty Member <span class="text-danger">*</span></label>
                            <select name="teacher_id" id="selectTeacher" class="form-select form-select-sm" required>
                                <option value="">-- Select Faculty Member --</option>
                                <?php foreach ($teachers as $t): ?>
                                    <option value="<?= $t['id'] ?>">
                                        <?= e($t['first_name'] . ' ' . $t['last_name']) ?> (<?= e($t['designation']) ?> &bull; <?= e($t['department_code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text small">Instructor must be officially assigned to this section.</div>
                        </div>

                        <!-- Classroom -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Classroom / Facility <span class="text-danger">*</span></label>
                            <select name="room_id" class="form-select form-select-sm" required>
                                <option value="">-- Select Classroom --</option>
                                <?php foreach ($rooms as $r): ?>
                                    <option value="<?= $r['id'] ?>">
                                        <?= e($r['room_number']) ?> &bull; <?= e($r['building']) ?> (<?= ucfirst($r['room_type']) ?>, Cap: <?= $r['capacity'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Day of Week -->
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">Day of Week <span class="text-danger">*</span></label>
                            <select name="day_of_week" class="form-select form-select-sm" required>
                                <option value="">-- Choose Day --</option>
                                <?php foreach ($days as $day): ?>
                                    <option value="<?= $day ?>"><?= $day ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Start Time -->
                        <div class="col-6 col-md-4">
                            <label class="form-label small fw-bold">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control form-control-sm" required value="09:00">
                        </div>

                        <!-- End Time -->
                        <div class="col-6 col-md-4">
                            <label class="form-label small fw-bold">End Time <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control form-control-sm" required value="10:30">
                        </div>

                        <!-- Class Type -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Class Routine Type</label>
                            <select name="class_type" class="form-select form-select-sm">
                                <option value="lecture" selected>Theory / Lecture</option>
                                <option value="lab">Laboratory / Practical</option>
                                <option value="tutorial">Tutorial / Sessional</option>
                            </select>
                        </div>

                        <!-- Publication Status -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Initial Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="published" selected>Published (Visible to students & faculty)</option>
                                <option value="draft">Draft (Visible only to coordinator)</option>
                            </select>
                        </div>

                        <div class="col-12 pt-3 border-top d-flex align-items-center justify-content-end gap-2">
                            <a href="<?= url('/coordinator/timetable') ?>" class="btn btn-light btn-sm px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> Save Schedule Routine
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Help & Conflict Rules Card -->
    <div class="col-12 col-lg-4">
        <div class="card card-pub border-0 shadow-sm bg-white mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-shield-check text-success me-2"></i>Conflict Rules & Logic
                </h6>
            </div>
            <div class="card-body p-3 small text-muted">
                <p class="mb-2">The system enforces 5 automatic checks before saving:</p>
                <ul class="ps-3 mb-0">
                    <li class="mb-2"><strong>Time Range:</strong> Start time must be strictly before end time (min 30m, max 5h).</li>
                    <li class="mb-2"><strong>Teacher Clash:</strong> Faculty member cannot have overlapping classes on the same day.</li>
                    <li class="mb-2"><strong>Room Clash:</strong> Classroom cannot be assigned to overlapping sessions simultaneously.</li>
                    <li class="mb-2"><strong>Section Clash:</strong> A section cannot have two classes scheduled at the same time.</li>
                    <li><strong>Student Clash:</strong> Enrolled students cannot have overlapping timetables.</li>
                </ul>
            </div>
        </div>

        <div class="card card-pub border-0 shadow-sm bg-light">
            <div class="card-body p-3 small text-muted">
                <div class="d-flex align-items-center gap-2 mb-2 text-primary fw-semibold">
                    <i class="bi bi-info-circle"></i>
                    <span>Revision Policy</span>
                </div>
                Once a schedule is marked <strong>Published</strong>, any future modification to its day, time, or room will automatically create an audit record in the revision log and alert registered students.
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const offeringSelect = document.getElementById('selectOffering');
    const sectionSelect = document.getElementById('selectSection');
    const teacherSelect = document.getElementById('selectTeacher');
    const capacityNote = document.getElementById('sectionCapacityNote');

    offeringSelect.addEventListener('change', function() {
        const offeringId = this.value;
        if (!offeringId) {
            sectionSelect.innerHTML = '<option value="">-- Select Offering First --</option>';
            sectionSelect.disabled = true;
            return;
        }

        sectionSelect.disabled = true;
        sectionSelect.innerHTML = '<option value="">Loading sections...</option>';

        fetch('<?= url("/coordinator/api/offering-data") ?>?offering_id=' + offeringId)
            .then(res => res.json())
            .then(data => {
                sectionSelect.innerHTML = '<option value="">-- Choose Section --</option>';
                if (data.sections && data.sections.length > 0) {
                    data.sections.forEach(sec => {
                        const opt = document.createElement('option');
                        opt.value = sec.id;
                        opt.textContent = 'Section ' + sec.section_name + ' (Cap: ' + sec.capacity + ', Enrolled: ' + sec.enrolled_count + ')';
                        opt.dataset.instructors = JSON.stringify(sec.instructors || []);
                        sectionSelect.appendChild(opt);
                    });
                    sectionSelect.disabled = false;
                } else {
                    sectionSelect.innerHTML = '<option value="">No sections found for offering</option>';
                }
            })
            .catch(() => {
                sectionSelect.innerHTML = '<option value="">Error loading sections</option>';
            });
    });

    sectionSelect.addEventListener('change', function() {
        const selectedOpt = this.options[this.selectedIndex];
        if (!selectedOpt || !selectedOpt.dataset.instructors) return;

        const instructors = JSON.parse(selectedOpt.dataset.instructors);
        if (instructors.length > 0) {
            teacherSelect.value = instructors[0].teacher_id;
            capacityNote.innerHTML = '<span class="text-success fw-medium">Assigned instructor automatically selected: ' + instructors[0].first_name + ' ' + instructors[0].last_name + '</span>';
        } else {
            capacityNote.innerHTML = '<span class="text-warning fw-medium">Note: No teacher assignment currently found for this section. Please assign teacher first if required.</span>';
        }
    });
});
</script>
