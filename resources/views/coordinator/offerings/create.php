<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/coordinator/course-offerings">Course Offerings</a></li>
            <li class="breadcrumb-item active" aria-current="page">Offer Course</li>
        </ol>
    </nav>
    <h2 class="fw-bold mb-1">Schedule Course Offering</h2>
    <p class="text-muted mb-0">Open an academic course for student enrollment and section assignment in a semester.</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-pub border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="/coordinator/course-offerings">
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
                        <label class="form-label fw-semibold">Course to Offer <span class="text-danger">*</span></label>
                        <select name="course_id" class="form-select" required>
                            <option value="">-- Choose Course from Catalog --</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= old('course_id') == $c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['course_code']) ?> &mdash; <?= e($c['title']) ?> (<?= number_format((float)$c['credit'], 1) ?> cr)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Offering Status</label>
                        <select name="status" class="form-select">
                            <option value="offered" <?= old('status', 'offered') === 'offered' ? 'selected' : '' ?>>Offered (Open for sections and registration)</option>
                            <option value="cancelled" <?= old('status') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            <option value="completed" <?= old('status') === 'completed' ? 'selected' : '' ?>>Completed</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Offer Course
                        </button>
                        <a href="/coordinator/course-offerings" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
