<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/coordinator/course-offerings">Course Offerings</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Offering</li>
        </ol>
    </nav>
    <h2 class="fw-bold mb-1">Edit Course Offering</h2>
    <p class="text-muted mb-0">Modify term or status for this offering.</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-pub border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="/coordinator/course-offerings/<?= $offering['id'] ?>/edit">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Academic Semester <span class="text-danger">*</span></label>
                        <select name="semester_id" class="form-select" required>
                            <?php foreach ($semesters as $sem): ?>
                                <option value="<?= $sem['id'] ?>" <?= old('semester_id', $offering['semester_id']) == $sem['id'] ? 'selected' : '' ?>>
                                    <?= e($sem['name']) ?> (<?= e($sem['academic_year']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Course <span class="text-danger">*</span></label>
                        <select name="course_id" class="form-select" required>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= old('course_id', $offering['course_id']) == $c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['course_code']) ?> &mdash; <?= e($c['title']) ?> (<?= number_format((float)$c['credit'], 1) ?> cr)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Offering Status</label>
                        <select name="status" class="form-select">
                            <option value="offered" <?= old('status', $offering['status']) === 'offered' ? 'selected' : '' ?>>Offered</option>
                            <option value="cancelled" <?= old('status', $offering['status']) === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            <option value="completed" <?= old('status', $offering['status']) === 'completed' ? 'selected' : '' ?>>Completed</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2-circle me-1"></i> Update Offering
                        </button>
                        <a href="/coordinator/course-offerings" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
