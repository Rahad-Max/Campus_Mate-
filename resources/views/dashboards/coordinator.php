<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h2 class="fw-bold mb-1">Department Coordination Dashboard 🏛️</h2>
        <p class="text-muted mb-0">
            Term: <strong><?= e($semester['name'] ?? 'Summer 2026') ?></strong> &bull; 
            Department: <strong>Computer Science & Engineering (PUB)</strong> &bull;
            Status: <strong><span class="badge bg-success-subtle text-success"><?= ucfirst(str_replace('_', ' ', $semester['status'] ?? 'Active')) ?></span></strong>
        </p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <div class="d-flex justify-content-md-end gap-2">
            <a href="/coordinator/offerings" class="btn btn-pub">
                <i class="bi bi-collection me-1"></i> Course Offerings
            </a>
            <a href="/coordinator/curriculum" class="btn btn-outline-primary">
                <i class="bi bi-diagram-3 me-1"></i> Curriculum Matrix
            </a>
        </div>
    </div>
</div>

<!-- Department Overview Stats -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-2">
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
    <div class="col-sm-6 col-xl-2">
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
    <div class="col-sm-6 col-xl-2">
        <div class="metric-card">
            <div class="metric-icon amber">
                <i class="bi bi-collection-play"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value"><?= (int)$stats['total_offerings'] ?></div>
                <div class="metric-label">Course Offerings</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2">
        <div class="metric-card">
            <div class="metric-icon purple">
                <i class="bi bi-grid-3x3"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value"><?= (int)$stats['total_sections'] ?></div>
                <div class="metric-label">Active Sections</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2">
        <div class="metric-card">
            <div class="metric-icon teal">
                <i class="bi bi-card-checklist"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value"><?= (int)$stats['total_registrations'] ?></div>
                <div class="metric-label">Seat Enrollments</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2">
        <div class="metric-card">
            <div class="metric-icon <?= $stats['pending_tasks'] > 0 ? 'amber' : 'blue' ?>">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value"><?= (int)$stats['pending_tasks'] ?></div>
                <div class="metric-label">Pending Approval</div>
            </div>
        </div>
    </div>
</div>

<!-- Coordinator Quick Links Navigation -->
<div class="row g-2 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
        <a href="/coordinator/offerings" class="card card-pub text-decoration-none text-center p-3 h-100 hover-lift">
            <i class="bi bi-collection text-primary fs-3 mb-1"></i>
            <span class="d-block small fw-bold text-dark">Course Offerings</span>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="/coordinator/curriculum" class="card card-pub text-decoration-none text-center p-3 h-100 hover-lift">
            <i class="bi bi-diagram-3 text-success fs-3 mb-1"></i>
            <span class="d-block small fw-bold text-dark">Curriculum</span>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="/timetable" class="card card-pub text-decoration-none text-center p-3 h-100 hover-lift">
            <i class="bi bi-calendar3 text-danger fs-3 mb-1"></i>
            <span class="d-block small fw-bold text-dark">Timetable</span>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="/faculty" class="card card-pub text-decoration-none text-center p-3 h-100 hover-lift">
            <i class="bi bi-person-video3 text-warning fs-3 mb-1"></i>
            <span class="d-block small fw-bold text-dark">Faculty Roster</span>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="/notices" class="card card-pub text-decoration-none text-center p-3 h-100 hover-lift">
            <i class="bi bi-megaphone text-info fs-3 mb-1"></i>
            <span class="d-block small fw-bold text-dark">Post Notice</span>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="/helpdesk" class="card card-pub text-decoration-none text-center p-3 h-100 hover-lift">
            <i class="bi bi-file-earmark-arrow-up text-accent fs-3 mb-1"></i>
            <span class="d-block small fw-bold text-dark">Documents & AI</span>
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Active Course Offerings -->
    <div class="col-lg-7">
        <div class="card card-pub h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-collection text-primary me-2"></i>Active Term Course Offerings
                </h5>
                <a href="/coordinator/offerings" class="btn btn-outline-primary btn-sm">Manage All</a>
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

    <!-- Recent Schedule Updates & Audit Logs -->
    <div class="col-lg-5">
        <!-- Schedule Updates / Revisions -->
        <div class="card card-pub mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-calendar-range text-warning me-2"></i>Recent Schedule Revisions
                </h6>
                <a href="/timetable" class="text-decoration-none small text-pub-primary">Schedule View</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentRevisions)): ?>
                    <div class="p-3 text-center text-muted small">No schedule revisions recorded this term.</div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentRevisions as $rev): ?>
                            <div class="list-group-item p-3">
                                <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-primary small"><?= e($rev['course_code']) ?> (Sec <?= e($rev['section_name']) ?>)</span>
                                    <small class="text-muted"><?= format_date($rev['effective_date']) ?></small>
                                </div>
                                <div class="small text-muted mb-1">
                                    Changed from <span class="text-danger"><?= e($rev['previous_day']) ?> <?= date('H:i', strtotime($rev['previous_start_time'])) ?></span>
                                    to <span class="text-success"><?= e($rev['new_day']) ?> <?= date('H:i', strtotime($rev['new_start_time'])) ?></span>
                                </div>
                                <div class="small fst-italic text-secondary">"<?= e($rev['revision_reason']) ?>"</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Administrative Audit Trail -->
        <div class="card card-pub">
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
                                <small class="text-muted"><?= format_date($log['created_at'], 'H:i:s') ?></small>
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
