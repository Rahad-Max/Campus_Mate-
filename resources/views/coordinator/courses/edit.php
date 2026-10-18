<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/coordinator/courses">Courses</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Course</li>
        </ol>
    </nav>
    <h2 class="fw-bold mb-1">Edit Course: <?= e($course['course_code']) ?></h2>
    <p class="text-muted mb-0"><?= e($course['title']) ?></p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-pub border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="/coordinator/courses/<?= $course['id'] ?>/edit">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Course Code <span class="text-danger">*</span></label>
                            <input type="text" name="course_code" class="form-control text-uppercase" required maxlength="20" value="<?= e(old('course_code', $course['course_code'])) ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Course Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required maxlength="150" value="<?= e(old('title', $course['title'])) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Academic Department <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-select" required>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>" <?= old('department_id', $course['department_id']) == $dept['id'] ? 'selected' : '' ?>>
                                        <?= e($dept['code']) ?> - <?= e($dept['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Credits <span class="text-danger">*</span></label>
                            <input type="number" step="0.5" min="0.5" max="12.0" name="credit" class="form-control" required value="<?= e(old('credit', $course['credit'])) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Course Type</label>
                            <select name="course_type" class="form-select">
                                <option value="theory" <?= old('course_type', $course['course_type']) === 'theory' ? 'selected' : '' ?>>Theory</option>
                                <option value="lab" <?= old('course_type', $course['course_type']) === 'lab' ? 'selected' : '' ?>>Lab / Sessional</option>
                                <option value="project" <?= old('course_type', $course['course_type']) === 'project' ? 'selected' : '' ?>>Project</option>
                                <option value="thesis" <?= old('course_type', $course['course_type']) === 'thesis' ? 'selected' : '' ?>>Thesis</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Course Description & Syllabus Objectives</label>
                        <textarea name="description" class="form-control" rows="4"><?= e(old('description', $course['description'])) ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= old('status', $course['status']) === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= old('status', $course['status']) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2-circle me-1"></i> Update Course
                        </button>
                        <a href="/coordinator/courses/<?= $course['id'] ?>/prerequisites" class="btn btn-outline-warning">
                            <i class="bi bi-diagram-2 me-1"></i> Prerequisites
                        </a>
                        <a href="/coordinator/courses" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
