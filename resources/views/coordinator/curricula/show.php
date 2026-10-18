<div class="mb-4">
    <a href="/coordinator/curricula" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
        <i class="bi bi-arrow-left"></i> Back to Curricula
    </a>
</div>

<!-- Curriculum Summary Header -->
<div class="card card-pub border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-primary fs-6 px-3 py-1"><?= e($curriculum['dept_code'] ?? 'CSE') ?></span>
                    <span class="badge bg-light text-dark border">Version <?= e($curriculum['version'] ?? '1.0') ?></span>
                    <span class="badge bg-<?= ($curriculum['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>-subtle text-<?= ($curriculum['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?> px-2 py-1">
                        <?= ucfirst($curriculum['status'] ?? 'active') ?>
                    </span>
                </div>
                <h3 class="fw-bold mb-1"><?= e($curriculum['name']) ?></h3>
                <p class="text-muted mb-0"><?= e($curriculum['department_name'] ?? 'Pundra University Department') ?></p>
            </div>
            <div class="text-end">
                <div class="text-muted small">Total Matrix Credits</div>
                <div class="fs-3 fw-bold text-primary"><?= number_format((float)($totalCredits ?? $curriculum['total_credits_required']), 1) ?> <span class="fs-6 text-muted">/ <?= number_format((float)$curriculum['total_credits_required'], 1) ?> req</span></div>
                <a href="/coordinator/curricula/<?= $curriculum['id'] ?>/edit" class="btn btn-sm btn-outline-primary mt-2">
                    <i class="bi bi-pencil me-1"></i> Edit Metadata
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Course Matrix Section -->
    <div class="col-lg-8">
        <div class="card card-pub border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0 fw-bold"><i class="bi bi-journal-code text-primary me-2"></i>Curriculum Course Matrix (<?= count($courses) ?> Courses)</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Term</th>
                                <th>Code</th>
                                <th>Course Title</th>
                                <th>Credits</th>
                                <th>Type</th>
                                <th>Group</th>
                                <th class="text-end" style="width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($courses)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        No courses attached to this curriculum yet. Use the form on the right to add courses.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($courses as $c): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary">Sem <?= $c['recommended_semester'] ?></span>
                                        </td>
                                        <td class="fw-bold text-primary"><?= e($c['course_code']) ?></td>
                                        <td>
                                            <div class="fw-semibold"><?= e($c['title']) ?></div>
                                        </td>
                                        <td><?= number_format((float)$c['credit'], 1) ?> cr</td>
                                        <td><span class="badge bg-light text-dark border"><?= ucfirst($c['course_type']) ?></span></td>
                                        <td>
                                            <?php if (!empty($c['is_elective'])): ?>
                                                <span class="badge bg-warning-subtle text-warning border"><?= e($c['elective_group'] ?: 'Elective') ?></span>
                                            <?php else: ?>
                                                <span class="text-muted small">Core</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <form method="POST" action="/coordinator/curricula/<?= $curriculum['id'] ?>/remove-course/<?= $c['id'] ?>" class="d-inline" onsubmit="return confirm('Remove course <?= e($c['course_code']) ?> from this curriculum matrix?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove Course">
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

    <!-- Add Course to Matrix Form -->
    <div class="col-lg-4">
        <div class="card card-pub border-0 shadow-sm sticky-top" style="top: 20px;">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold"><i class="bi bi-plus-circle text-primary me-2"></i>Add Course to Matrix</h6>
            </div>
            <div class="card-body p-3">
                <?php if (empty($availableCourses)): ?>
                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-1"></i> All available department courses are already added to this curriculum.
                    </div>
                <?php else: ?>
                    <form method="POST" action="/coordinator/curricula/<?= $curriculum['id'] ?>/add-course">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Select Course <span class="text-danger">*</span></label>
                            <select name="course_id" class="form-select" required>
                                <option value="">-- Choose Course --</option>
                                <?php foreach ($availableCourses as $ac): ?>
                                    <option value="<?= $ac['id'] ?>">
                                        <?= e($ac['course_code']) ?> - <?= e($ac['title']) ?> (<?= number_format((float)$ac['credit'], 1) ?> cr)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Recommended Semester <span class="text-danger">*</span></label>
                            <select name="recommended_semester" class="form-select" required>
                                <?php for ($s = 1; $s <= 8; $s++): ?>
                                    <option value="<?= $s ?>">Semester <?= $s ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_elective" value="1" id="isElective">
                                <label class="form-check-label small fw-semibold" for="isElective">Is Elective Course</label>
                            </div>
                        </div>

                        <div class="mb-3" id="electiveGroupWrap" style="display: none;">
                            <label class="form-label small fw-semibold">Elective Group</label>
                            <input type="text" name="elective_group" class="form-control form-control-sm" placeholder="e.g. Elective-I, Elective-II">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-1">
                            <i class="bi bi-plus-lg"></i> Add to Curriculum
                        </button>
                    </form>

                    <script>
                        document.getElementById('isElective')?.addEventListener('change', function() {
                            document.getElementById('electiveGroupWrap').style.display = this.checked ? 'block' : 'none';
                        });
                    </script>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
