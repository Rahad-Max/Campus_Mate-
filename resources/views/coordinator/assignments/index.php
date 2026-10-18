<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Teacher Assignments</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1">Teacher Course Assignments & Workload</h2>
        <p class="text-muted mb-0">Assign faculty instructors to course sections and monitor departmental teaching loads.</p>
    </div>
    <div>
        <a href="/coordinator/teacher-assignments/create" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-person-plus"></i>
            <span>Assign Teacher</span>
        </a>
    </div>
</div>

<!-- Semester Filter Bar -->
<div class="card card-pub border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="/coordinator/teacher-assignments" class="row g-2 align-items-center">
            <div class="col-md-6 col-lg-8">
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 fw-semibold text-muted text-nowrap"><i class="bi bi-filter me-1"></i>Select Term:</label>
                    <select name="semester_id" class="form-select" onchange="this.form.submit()">
                        <?php foreach ($semesters as $sem): ?>
                            <option value="<?= $sem['id'] ?>" <?= ($semesterId == $sem['id']) ? 'selected' : '' ?>>
                                <?= e($sem['name']) ?> (<?= e($sem['academic_year']) ?>) <?= !empty($sem['is_current']) ? '★ Current' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 text-end">
                <span class="text-muted small">Total <strong><?= count($assignments) ?></strong> active teaching allotments</span>
            </div>
        </form>
    </div>
</div>

<!-- Assignments Table -->
<div class="card card-pub border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Faculty Instructor</th>
                        <th>Assigned Course</th>
                        <th>Section</th>
                        <th>Credit Load</th>
                        <th>Assignment Role</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assignments)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-person-workspace fs-1 d-block mb-2 text-secondary"></i>
                                <div>No teacher assignments created for this semester yet.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($assignments as $a): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">
                                        <?= e(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? '')) ?>
                                    </div>
                                    <div class="small text-muted">
                                        <?= e($a['designation'] ?? 'Faculty') ?> &bull; <span class="badge bg-light text-dark border"><?= e($a['teacher_id'] ?? 'T-ID') ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary fw-bold fs-6">
                                        <?= e($a['course_code']) ?>
                                    </span>
                                    <div class="small text-dark fw-semibold"><?= e($a['course_title']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-dark-subtle text-dark fw-bold px-3 py-1">
                                        Sec <?= e($a['section_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-info"><?= number_format((float)($a['credit'] ?? 3.0), 1) ?> cr</span>
                                </td>
                                <td>
                                    <?php if (($a['role'] ?? 'primary') === 'primary'): ?>
                                        <span class="badge bg-success-subtle text-success px-2 py-1"><i class="bi bi-star-fill me-1"></i>Lead Teacher</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-1">Co-Instructor</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="/coordinator/teacher-assignments/<?= $a['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Remove this faculty assignment?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove Assignment">
                                            <i class="bi bi-trash"></i> Remove
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
