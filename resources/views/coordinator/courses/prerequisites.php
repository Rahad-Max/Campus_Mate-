<div class="mb-4">
    <a href="/coordinator/courses" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
        <i class="bi bi-arrow-left"></i> Back to Courses
    </a>
</div>

<div class="card card-pub border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-primary fs-6 px-3 py-1"><?= e($course['course_code']) ?></span>
                    <span class="badge bg-light text-dark border"><?= ucfirst($course['course_type']) ?></span>
                </div>
                <h3 class="fw-bold mb-1"><?= e($course['title']) ?></h3>
                <p class="text-muted mb-0"><?= number_format((float)$course['credit'], 1) ?> Credit Hours &bull; Prerequisite Dependency Graph</p>
            </div>
            <div>
                <a href="/coordinator/courses/<?= $course['id'] ?>/edit" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i> Edit Course
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Active Prerequisites List -->
    <div class="col-lg-7">
        <div class="card card-pub border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-arrow-return-right text-primary me-2"></i>Required Prerequisites (<?= count($prerequisites) ?>)
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Prerequisite Code</th>
                                <th>Course Title</th>
                                <th>Min. Grade</th>
                                <th class="text-end" style="width: 100px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($prerequisites)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-check2-all fs-2 text-success d-block mb-2"></i>
                                        <div>No prerequisites required for this course (Direct Admission).</div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($prerequisites as $pr): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-warning-subtle text-warning fw-bold fs-6">
                                                <?= e($pr['course_code']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= e($pr['title']) ?></div>
                                            <div class="small text-muted"><?= number_format((float)$pr['credit'], 1) ?> cr</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><?= e($pr['min_grade'] ?? 'D') ?></span>
                                        </td>
                                        <td class="text-end">
                                            <form method="POST" action="/coordinator/courses/<?= $course['id'] ?>/remove-prerequisite/<?= $pr['id'] ?>" class="d-inline" onsubmit="return confirm('Remove prerequisite requirement <?= e($pr['course_code']) ?>?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove Prerequisite">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Prerequisite Card -->
    <div class="col-lg-5">
        <div class="card card-pub border-0 shadow-sm sticky-top" style="top: 20px;">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold"><i class="bi bi-plus-circle text-primary me-2"></i>Add Prerequisite Requirement</h6>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-info small mb-3">
                    <i class="bi bi-shield-check me-1"></i> <strong>Cycle Detection Guard:</strong> The system automatically evaluates circular dependency graphs and rejects circular prerequisite loops (e.g., A &rarr; B &rarr; A).
                </div>

                <?php if (empty($availableCourses)): ?>
                    <div class="text-muted small">No other courses available to link as prerequisites.</div>
                <?php else: ?>
                    <form method="POST" action="/coordinator/courses/<?= $course['id'] ?>/add-prerequisite">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Select Prerequisite Course <span class="text-danger">*</span></label>
                            <select name="prerequisite_course_id" class="form-select" required>
                                <option value="">-- Choose Course --</option>
                                <?php foreach ($availableCourses as $ac): ?>
                                    <option value="<?= $ac['id'] ?>">
                                        <?= e($ac['course_code']) ?> - <?= e($ac['title']) ?> (<?= number_format((float)$ac['credit'], 1) ?> cr)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Minimum Passing Grade</label>
                            <select name="min_grade" class="form-select">
                                <option value="D">D (Passing / 2.00)</option>
                                <option value="C" selected>C (Standard / 2.25)</option>
                                <option value="C+">C+ (Fair / 2.50)</option>
                                <option value="B-">B- (Satisfactory / 2.75)</option>
                                <option value="B">B (Good / 3.00)</option>
                                <option value="B+">B+ (Very Good / 3.25)</option>
                                <option value="A-">A- (Excellent / 3.50)</option>
                                <option value="A">A (Superior / 3.75)</option>
                                <option value="A+">A+ (Outstanding / 4.00)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-link-45deg"></i> Link Prerequisite
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
