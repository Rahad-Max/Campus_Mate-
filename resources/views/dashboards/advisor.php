<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h2 class="fw-bold mb-1">Academic Advising Portal 📋</h2>
        <p class="text-muted mb-0">
            Advisor: <strong><?= e($advisor['first_name'] . ' ' . $advisor['last_name']) ?></strong> &bull; 
            Designation: <strong><?= e($advisor['designation'] ?? 'Advisor') ?></strong> &bull; 
            Department: <strong><?= e($advisor['department_code'] ?? 'CSE') ?></strong>
        </p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="/advisor/requests" class="btn btn-pub">
            <i class="bi bi-inbox me-1"></i> Review Requests Queue (<?= count($pendingRequests) ?>)
        </a>
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
                <i class="bi bi-check2-all"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value">Active</div>
                <div class="metric-label">Registration Window</div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Registration Requests Queue -->
<div class="card card-pub mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 fw-bold">
            <i class="bi bi-inbox text-warning me-2"></i>Pending Registration Requests
        </h5>
        <span class="badge bg-warning-subtle text-warning border"><?= count($pendingRequests) ?> Awaiting Action</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($pendingRequests)): ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-check-circle fs-3 text-success"></i>
                <p class="mt-2 mb-0">No pending course registration requests in your advising queue.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Request #</th>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>CGPA</th>
                            <th>Requested Credits</th>
                            <th>Submitted On</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingRequests as $req): ?>
                            <tr>
                                <td>#<?= $req['id'] ?></td>
                                <td class="fw-bold"><?= e($req['student_id_card']) ?></td>
                                <td><?= e($req['student_first_name'] . ' ' . $req['student_last_name']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= number_format((float)$req['cgpa'], 2) ?></span></td>
                                <td><?= number_format((float)$req['total_credits'], 1) ?> cr</td>
                                <td><?= format_date($req['created_at']) ?></td>
                                <td class="text-end">
                                    <form action="/advisor/request/<?= $req['id'] ?>/approve" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                    </form>
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
    <div class="card-header bg-white py-3 border-bottom">
        <h5 class="card-title mb-0 fw-bold">
            <i class="bi bi-person-lines-fill text-primary me-2"></i>Assigned Advisee Roster
        </h5>
    </div>
    <div class="card-body p-0">
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
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($advisees as $adv): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= e($adv['student_id_card']) ?></td>
                            <td><?= e($adv['first_name'] . ' ' . $adv['last_name']) ?></td>
                            <td><?= e($adv['batch_no']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= number_format((float)$adv['cgpa'], 2) ?></span></td>
                            <td><?= number_format((float)$adv['completed_credits'], 1) ?></td>
                            <td><span class="status-pill completed"><?= ucfirst($adv['academic_status']) ?></span></td>
                            <td><a href="mailto:<?= e($adv['email']) ?>" class="text-decoration-none small"><?= e($adv['email']) ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
