<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Assigned Courses</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1">My Teaching Courses & Syllabus</h2>
        <p class="text-muted mb-0">Courses and academic subjects allotted to you for instructional delivery in <?= e($currentSemester['name'] ?? 'Active Semester') ?>.</p>
    </div>
    <div>
        <a href="/teacher/assignments" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-person-workspace"></i>
            <span>Workload Details</span>
        </a>
    </div>
</div>

<div class="card card-pub border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 140px;">Course Code</th>
                        <th>Course Title</th>
                        <th>Credits</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th class="text-end" style="width: 120px;">Syllabus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($courses)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-book-half fs-1 d-block mb-2 text-secondary"></i>
                                <div>No courses currently assigned for instruction in this semester.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($courses as $c): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary fw-bold fs-6">
                                        <?= e($c['course_code']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= e($c['title']) ?></div>
                                    <div class="small text-muted text-truncate" style="max-width: 350px;"><?= e($c['description'] ?: 'Standard departmental syllabus.') ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info fw-bold">
                                        <?= number_format((float)$c['credit'], 1) ?> cr
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= ucfirst($c['course_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success px-2 py-1">Active</span>
                                </td>
                                <td class="text-end">
                                    <a href="/courses/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
