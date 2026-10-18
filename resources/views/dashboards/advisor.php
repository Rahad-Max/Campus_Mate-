<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h2 class="fw-bold mb-1">Academic Advising Portal 📋</h2>
        <p class="text-muted mb-0">
            Advisor: <strong><?= e($advisor['first_name'] . ' ' . $advisor['last_name']) ?></strong> &bull; 
            Designation: <strong><?= e($advisor['designation'] ?? 'Advisor') ?></strong> &bull; 
            Department: <strong><?= e($advisor['department_code'] ?? 'CSE') ?></strong> &bull;
            Active Term: <strong><?= e($semester['name'] ?? 'Summer 2026') ?></strong>
        </p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <div class="d-flex justify-content-md-end gap-2">
            <a href="/advisor/requests" class="btn btn-pub">
                <i class="bi bi-inbox me-1"></i> Registration Queue (<?= count($pendingRequests) ?>)
            </a>
            <a href="/advisor/advisees" class="btn btn-outline-primary">
                <i class="bi bi-people me-1"></i> Advisee Roster
            </a>
        </div>
    </div>
</div>

<!-- Advisor Metrics -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="metric-card">
            <div class="metric-icon blue">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value"><?= count($advisees) ?></div>
                <div class="metric-label">Assigned Advisees</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="metric-card">
            <div class="metric-icon amber">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value"><?= count($pendingRequests) ?></div>
                <div class="metric-label">Pending Approval Requests</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="metric-card">
            <div class="metric-icon teal">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value"><?= ucfirst(str_replace('_', ' ', $semester['status'] ?? 'Active')) ?></div>
                <div class="metric-label">Semester Phase</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Left Column: Pending Registration Requests & Advisee Roster -->
    <div class="col-lg-8">
        <!-- Pending Registration Requests Queue -->
        <div class="card card-pub mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-inbox text-warning me-2"></i>Pending Registration Requests Awaiting Review
                </h5>
                <span class="badge bg-warning-subtle text-warning border"><?= count($pendingRequests) ?> In Queue</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($pendingRequests)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-check-circle fs-1 text-success mb-2 d-block"></i>
                        <p class="mb-0">All submitted course registrations from your advisees have been reviewed!</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Request #</th>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th>CGPA</th>
                                    <th>Credits</th>
                                    <th>Submitted On</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingRequests as $req): ?>
                                    <tr>
                                        <td>#<?= $req['id'] ?></td>
                                        <td class="fw-bold text-primary"><?= e($req['student_id_card']) ?></td>
                                        <td><?= e($req['student_first_name'] . ' ' . $req['student_last_name']) ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= number_format((float)$req['cgpa'], 2) ?></span></td>
                                        <td><strong><?= number_format((float)$req['total_credits'], 1) ?></strong> cr</td>
                                        <td><?= format_date($req['created_at']) ?></td>
                                        <td class="text-end">
                                            <a href="/advisor/requests" class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye"></i> Review
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Advisee Roster -->
        <div class="card card-pub">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-person-lines-fill text-primary me-2"></i>Assigned Advisee Roster
                </h5>
                <a href="/advisor/advisees" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($advisees)): ?>
                    <div class="text-center py-4 text-muted">
                        <p class="mb-0">No advisees currently assigned to your portfolio.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th>Batch</th>
                                    <th>CGPA</th>
                                    <th>Completed Cr</th>
                                    <th>Status</th>
                                    <th>Contact</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($advisees as $adv): ?>
                                    <tr>
                                        <td class="fw-bold text-primary"><?= e($adv['student_id_card']) ?></td>
                                        <td><?= e($adv['first_name'] . ' ' . $adv['last_name']) ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= e($adv['batch_no']) ?></span></td>
                                        <td><?= number_format((float)$adv['cgpa'], 2) ?></td>
                                        <td><?= number_format((float)$adv['completed_credits'], 1) ?> cr</td>
                                        <td><span class="status-pill completed"><?= ucfirst($adv['academic_status']) ?></span></td>
                                        <td>
                                            <a href="mailto:<?= e($adv['email']) ?>" class="btn btn-sm btn-light border" title="Email Student">
                                                <i class="bi bi-envelope"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Quick Links & Notices -->
    <div class="col-lg-4">
        <!-- Advising Quick Shortcuts -->
        <div class="card card-pub mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-lightning-charge text-warning me-2"></i>Advising Shortcuts
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/advisor/requests" class="btn btn-outline-warning text-start text-dark">
                        <i class="bi bi-inbox me-2 text-warning"></i> Review Course Registrations
                    </a>
                    <a href="/advisor/advisees" class="btn btn-outline-primary text-start">
                        <i class="bi bi-people me-2"></i> Manage Advisee Roster
                    </a>
                    <a href="/timetable" class="btn btn-outline-primary text-start">
                        <i class="bi bi-calendar-week me-2"></i> Master Department Timetable
                    </a>
                    <a href="/courses" class="btn btn-outline-primary text-start">
                        <i class="bi bi-book me-2"></i> Curriculum Catalog & Syllabi
                    </a>
                </div>
            </div>
        </div>

        <!-- Academic Notices -->
        <div class="card card-pub">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-megaphone text-warning me-2"></i>Academic Notices
                </h6>
                <a href="/notices" class="text-decoration-none small text-pub-primary">View All</a>
            </div>
            <div class="list-group list-group-flush">
                <?php if (empty($notices)): ?>
                    <div class="p-3 text-muted text-center small">No notices posted.</div>
                <?php else: ?>
                    <?php foreach ($notices as $n): ?>
                        <a href="/notices/<?= $n['id'] ?>" class="list-group-item list-group-item-action p-3">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                <span class="badge bg-light text-dark border" style="font-size:0.7rem;"><?= ucfirst($n['category']) ?></span>
                                <small class="text-muted"><?= format_date($n['publish_date']) ?></small>
                            </div>
                            <h6 class="mb-1 text-truncate fw-semibold small"><?= e($n['title']) ?></h6>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
