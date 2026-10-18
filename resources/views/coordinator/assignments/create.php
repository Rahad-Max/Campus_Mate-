<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/coordinator/teacher-assignments">Teacher Assignments</a></li>
            <li class="breadcrumb-item active" aria-current="page">Assign Teacher</li>
        </ol>
    </nav>
    <h2 class="fw-bold mb-1">Assign Teacher to Course Section</h2>
    <p class="text-muted mb-0">Designate primary or co-instructors for scheduled sections and prevent faculty overload.</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-pub border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="/coordinator/teacher-assignments">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Academic Semester <span class="text-danger">*</span></label>
                        <select name="semester_id" class="form-select" required>
                            <?php foreach ($semesters as $sem): ?>
                                <option value="<?= $sem['id'] ?>" <?= (!empty($sem['is_current']) || old('semester_id') == $sem['id']) ? 'selected' : '' ?>>
                                    <?= e($sem['name']) ?> (<?= e($sem['academic_year']) ?>) <?= !empty($sem['is_current']) ? '★ Active Term' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Faculty Member <span class="text-danger">*</span></label>
                        <select name="teacher_id" class="form-select" required>
                            <option value="">-- Select Instructor --</option>
                            <?php foreach ($teachers as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= old('teacher_id') == $t['id'] ? 'selected' : '' ?>>
                                    <?= e(($t['first_name'] ?? '') . ' ' . ($t['last_name'] ?? '')) ?> &mdash; <?= e($t['designation'] ?? 'Lecturer') ?> (Max Load: <?= number_format((float)($t['max_credit_load'] ?? 12.0), 1) ?> cr)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text small">Workload enforcement ensures no faculty member is assigned beyond their maximum allowed credit capacity.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Course Section <span class="text-danger">*</span></label>
                        <select name="section_id" class="form-select" required>
                            <option value="">-- Select Course Section --</option>
                            <?php foreach ($sections as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= old('section_id') == $s['id'] ? 'selected' : '' ?>>
                                    <?= e($s['course_code']) ?> (<?= number_format((float)($s['credit'] ?? 3.0), 1) ?> cr) &mdash; <?= e($s['course_title']) ?> &bull; Section <?= e($s['section_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Instruction Role</label>
                        <select name="role" class="form-select">
                            <option value="primary" <?= old('role', 'primary') === 'primary' ? 'selected' : '' ?>>Primary Course Teacher / Course Lead</option>
                            <option value="assistant" <?= old('role') === 'assistant' ? 'selected' : '' ?>>Co-Teacher / Teaching Assistant</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-person-check me-1"></i> Save Assignment
                        </button>
                        <a href="/coordinator/teacher-assignments" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
