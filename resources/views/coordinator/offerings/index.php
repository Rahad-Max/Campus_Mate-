<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Course Offerings</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1">Semester Course Offerings</h2>
        <p class="text-muted mb-0">Schedule catalog courses for enrollment in specific academic semesters.</p>
    </div>
    <div>
        <a href="/coordinator/course-offerings/create" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-collection-play"></i>
            <span>Offer Course</span>
        </a>
    </div>
</div>

<!-- Semester Filter Bar -->
<div class="card card-pub border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="/coordinator/course-offerings" class="row g-2 align-items-center">
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
                <span class="text-muted small">Showing <strong><?= count($offerings) ?></strong> offerings for selected term</span>
            </div>
        </form>
    </div>
</div>

<!-- Offerings Table -->
<div class="card card-pub border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 140px;">Code</th>
                        <th>Course Title</th>
                        <th>Department</th>
                        <th>Credits</th>
                        <th>Sections</th>
                        <th>Status</th>
                        <th class="text-end" style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($offerings)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-collection fs-1 d-block mb-2 text-secondary"></i>
                                <div>No courses offered for this semester yet. Click "Offer Course" to schedule one.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($offerings as $offering): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary fw-bold fs-6 px-2 py-1">
                                        <?= e($offering['course_code']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= e($offering['course_title']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= e($offering['dept_code'] ?? 'CSE') ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info fw-bold">
                                        <?= number_format((float)$offering['credit'], 1) ?> cr
                                    </span>
                                </td>
                                <td>
                                    <a href="/coordinator/sections?semester_id=<?= $semesterId ?>" class="badge bg-light text-primary border text-decoration-none">
                                        <i class="bi bi-layout-three-columns me-1"></i><?= (int)($offering['section_count'] ?? 0) ?> sections
                                    </a>
                                </td>
                                <td>
                                    <?php
                                    $statusBadge = match($offering['status'] ?? 'offered') {
                                        'offered' => 'success',
                                        'cancelled' => 'danger',
                                        'completed' => 'secondary',
                                        default => 'primary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $statusBadge ?>-subtle text-<?= $statusBadge ?> px-2 py-1">
                                        <?= ucfirst($offering['status'] ?? 'offered') ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/coordinator/sections/create?offering_id=<?= $offering['id'] ?>" class="btn btn-outline-success" title="Add Section">
                                            <i class="bi bi-plus-lg"></i> Section
                                        </a>
                                        <a href="/coordinator/course-offerings/<?= $offering['id'] ?>/edit" class="btn btn-outline-primary" title="Edit Offering">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
