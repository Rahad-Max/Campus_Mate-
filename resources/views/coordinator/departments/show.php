<div class="mb-4">
    <a href="/coordinator/departments" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
        <i class="bi bi-arrow-left"></i> Back to Departments
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card card-pub border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="badge bg-primary fs-6 px-3 py-1"><?= e($department['code']) ?></span>
                    <span class="badge bg-<?= ($department['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>-subtle text-<?= ($department['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?> px-3 py-1">
                        <?= ucfirst($department['status'] ?? 'active') ?>
                    </span>
                </div>
                <h3 class="fw-bold mb-3"><?= e($department['name']) ?></h3>
                
                <div class="row g-3 py-3 border-top border-bottom mb-4">
                    <div class="col-sm-6">
                        <div class="text-muted small">Campus Location / Office</div>
                        <div class="fw-semibold"><i class="bi bi-geo-alt text-primary me-1"></i><?= e($department['building'] ?? 'Main Academic Building') ?></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">Official Contact Email</div>
                        <div class="fw-semibold"><i class="bi bi-envelope text-primary me-1"></i><?= e($department['contact_email'] ?? 'cse@pub.ac.bd') ?></div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="/coordinator/departments/<?= $department['id'] ?>/edit" class="btn btn-outline-primary d-inline-flex align-items-center gap-1">
                        <i class="bi bi-pencil"></i> Edit Department Details
                    </a>
                </div>
            </div>
        </div>

        <!-- Associated Department Courses -->
        <div class="card card-pub border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0 fw-bold">Active Courses in <?= e($department['code']) ?></h6>
                <a href="/coordinator/courses/create" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-circle me-1"></i>New Course</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Title</th>
                                <th>Credits</th>
                                <th>Type</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($courses)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No courses currently associated with this department.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($courses as $c): ?>
                                    <tr>
                                        <td class="fw-bold text-primary"><?= e($c['course_code']) ?></td>
                                        <td><?= e($c['title']) ?></td>
                                        <td><?= number_format((float)$c['credit'], 1) ?> cr</td>
                                        <td><span class="badge bg-light text-dark border"><?= ucfirst($c['course_type']) ?></span></td>
                                        <td class="text-end">
                                            <a href="/coordinator/courses/<?= $c['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-pencil"></i>
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
    </div>

    <!-- Sidebar Stats & Faculty -->
    <div class="col-lg-4">
        <div class="card card-pub border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold">Department Metrics</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <span class="text-muted"><i class="bi bi-book me-2 text-primary"></i>Total Courses</span>
                    <span class="fw-bold fs-6"><?= $stats['course_count'] ?? count($courses ?? []) ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <span class="text-muted"><i class="bi bi-person-video3 me-2 text-primary"></i>Faculty Members</span>
                    <span class="fw-bold fs-6"><?= $stats['teacher_count'] ?? count($teachers ?? []) ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <span class="text-muted"><i class="bi bi-mortarboard me-2 text-primary"></i>Active Students</span>
                    <span class="fw-bold fs-6"><?= $stats['student_count'] ?? 0 ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted"><i class="bi bi-diagram-3 me-2 text-primary"></i>Curricula</span>
                    <span class="fw-bold fs-6"><?= $stats['curriculum_count'] ?? 1 ?></span>
                </div>
            </div>
        </div>

        <div class="card card-pub border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold">Department Faculty</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if (empty($teachers)): ?>
                        <li class="list-group-item text-center text-muted py-3">No faculty assigned to this department.</li>
                    <?php else: ?>
                        <?php foreach ($teachers as $t): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <div>
                                    <div class="fw-semibold small"><?= e(($t['first_name'] ?? '') . ' ' . ($t['last_name'] ?? '')) ?></div>
                                    <div class="text-muted small"><?= e($t['designation'] ?? 'Lecturer') ?></div>
                                </div>
                                <span class="badge bg-light text-dark border small"><?= e($t['teacher_id'] ?? 'T-ID') ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
