<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h2 class="fw-bold mb-1">Department Coordination Dashboard 🏛️</h2>
        <p class="text-muted mb-0">
            Term: <strong><?= e($semester['name'] ?? 'Summer 2026') ?></strong> &bull; 
            Department: <strong>Computer Science & Engineering (PUB)</strong>
        </p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="/coordinator/offerings" class="btn btn-pub">
            <i class="bi bi-plus-circle me-1"></i> Course Offerings
        </a>
    </div>
</div>

<!-- Department Overview Stats -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card">
            <div class="metric-icon blue">
                <i class="bi bi-people"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value"><?= (int)$stats['total_students'] ?></div>
                <div class="metric-label">Enrolled Students</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card">
            <div class="metric-icon teal">
                <i class="bi bi-person-workspace"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value"><?= (int)$stats['total_teachers'] ?></div>
                <div class="metric-label">Department Faculty</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card">
            <div class="metric-icon amber">
                <i class="bi bi-collection-play"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value"><?= (int)$stats['total_offerings'] ?></div>
                <div class="metric-label">Term Course Offerings</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card">
            <div class="metric-icon purple">
                <i class="bi bi-card-checklist"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value"><?= (int)$stats['total_registrations'] ?></div>
                <div class="metric-label">Seat Enrollments</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Active Course Offerings -->
    <div class="col-lg-8">
        <div class="card card-pub h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-collection text-primary me-2"></i>Active Term Course Offerings
                </h5>
                <a href="/coordinator/offerings" class="btn btn-outline-primary btn-sm">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Course Code</th>
                                <th>Course Title</th>
                                <th>Credit</th>
                                <th>Sections</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($offerings as $offering): ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?= e($offering['course_code']) ?></td>
                                    <td><?= e($offering['course_title']) ?></td>
                                    <td><?= number_format((float)$offering['credit'], 1) ?> cr</td>
                                    <td><span class="badge bg-secondary-subtle text-secondary"><?= $offering['section_count'] ?> Sections</span></td>
                                    <td><span class="status-pill completed"><?= ucfirst($offering['status']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Administrative Audit Activity -->
    <div class="col-lg-4">
        <div class="card card-pub h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-shield-check text-primary me-2"></i>Recent System Audit Trail
                </h6>
            </div>
            <div class="list-group list-group-flush">
                <?php if (empty($recentAudits)): ?>
                    <div class="p-4 text-center text-muted small">No recent administrative audits.</div>
                <?php else: ?>
                    <?php foreach ($recentAudits as $log): ?>
                        <div class="list-group-item p-3">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                <span class="badge bg-light text-dark border" style="font-size:0.7rem;"><?= e($log['action']) ?></span>
                                <small class="text-muted"><?= format_date($log['created_at'], 'H:i') ?></small>
                            </div>
                            <p class="mb-0 small text-muted">
                                Role: <strong><?= e($log['role']) ?></strong> &bull; Entity: <?= e($log['entity_type']) ?> #<?= $log['entity_id'] ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
