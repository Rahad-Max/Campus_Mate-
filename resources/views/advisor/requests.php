<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="/advisor/dashboard" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Registration Queue</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1">Registration Approval Queue</h2>
        <p class="text-muted mb-0">Review prerequisite compliance, credit workloads, and approve advisee course registrations.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <span class="badge bg-primary-subtle text-primary border px-3 py-2 fs-6">
            <i class="bi bi-inbox me-1"></i> <?= count($requests) ?> Pending Review
        </span>
    </div>
</div>

<!-- Nav Tabs for Pending vs Processed -->
<ul class="nav nav-pills mb-4" id="advisorRequestsTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active px-4 py-2 fw-medium" id="pending-tab" data-bs-toggle="pill" data-bs-target="#pending-content" type="button" role="tab">
            <i class="bi bi-hourglass-split me-1 text-warning"></i> Pending Requests (<?= count($requests) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link px-4 py-2 fw-medium" id="history-tab" data-bs-toggle="pill" data-bs-target="#history-content" type="button" role="tab">
            <i class="bi bi-clock-history me-1 text-primary"></i> Processed History (<?= count($history ?? []) ?>)
        </button>
    </li>
</ul>

<div class="tab-content" id="advisorRequestsTabContent">
    <!-- Tab 1: Pending Requests -->
    <div class="tab-pane fade show active" id="pending-content" role="tabpanel">
        <div class="card card-pub">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-list-check text-primary me-2"></i>Advisees Awaiting Registration Decision
                </h5>
                <span class="small text-muted">Showing pending submissions</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($requests)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-check-circle-fill fs-1 text-success opacity-75"></i>
                        <h5 class="fw-bold mt-2">All Caught Up!</h5>
                        <p class="small text-muted mb-0">There are no registration requests currently awaiting your approval.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Batch ID</th>
                                    <th>Student ID & Batch</th>
                                    <th>Student Name</th>
                                    <th>Standing & CGPA</th>
                                    <th>Courses</th>
                                    <th>Requested Credits</th>
                                    <th>Submitted Date</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $req): 
                                    $isProbation = ($req['academic_status'] ?? '') === 'probation' || ((float)$req['cgpa'] > 0 && (float)$req['cgpa'] < 2.50);
                                ?>
                                    <tr>
                                        <td class="fw-bold text-dark">#<?= $req['id'] ?></td>
                                        <td>
                                            <div class="fw-bold text-primary"><?= e($req['student_id_card']) ?></div>
                                            <small class="text-muted"><?= e($req['batch_no']) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= e($req['student_first_name'] . ' ' . $req['student_last_name']) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge <?= $isProbation ? 'bg-danger' : 'bg-success' ?>">
                                                CGPA <?= number_format((float)$req['cgpa'], 2) ?>
                                            </span>
                                            <?php if ($isProbation): ?>
                                                <div class="text-danger small fw-medium" style="font-size: 0.72rem;">Academic Probation</div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary">
                                                <?= $req['course_count'] ?? '3' ?> courses
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-dark fs-6"><?= number_format((float)$req['total_credits'], 1) ?></strong> <span class="text-muted small">cr</span>
                                        </td>
                                        <td>
                                            <div class="small text-dark"><?= format_date($req['created_at']) ?></div>
                                            <small class="text-muted"><?= date('H:i', strtotime($req['created_at'])) ?></small>
                                        </td>
                                        <td class="text-end">
                                            <a href="/advisor/registration-requests/<?= $req['id'] ?>" class="btn btn-sm btn-pub">
                                                <i class="bi bi-clipboard-check me-1"></i> Review & Decide
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

    <!-- Tab 2: Processed History -->
    <div class="tab-pane fade" id="history-content" role="tabpanel">
        <div class="card card-pub">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-clock-history text-primary me-2"></i>Recently Processed Registrations
                </h5>
                <span class="small text-muted">Past decisions audit log</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($history)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-folder2-open fs-1 text-secondary opacity-50"></i>
                        <h5 class="fw-bold mt-2">No Processed History</h5>
                        <p class="small text-muted mb-0">You have not processed any advisee registration requests yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Batch ID</th>
                                    <th>Student</th>
                                    <th>Semester</th>
                                    <th>Credits</th>
                                    <th>Decision</th>
                                    <th>Advisor Remarks</th>
                                    <th>Decision Date</th>
                                    <th class="text-end">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $h): 
                                    $isApproved = $h['status'] === 'advisor_approved';
                                ?>
                                    <tr>
                                        <td class="fw-bold">#<?= $h['id'] ?></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= e($h['student_first_name'] . ' ' . $h['student_last_name']) ?></div>
                                            <small class="text-muted font-monospace"><?= e($h['student_id_card']) ?></small>
                                        </td>
                                        <td><?= e($h['semester_name']) ?></td>
                                        <td><strong><?= number_format((float)$h['total_credits'], 1) ?></strong> cr</td>
                                        <td>
                                            <?php if ($isApproved): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                    <i class="bi bi-check-circle me-1"></i>Approved
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                    <i class="bi bi-x-circle me-1"></i>Rejected
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="small text-muted text-truncate" style="max-width: 250px;">
                                                <?= e($h['advisor_remarks'] ?? 'None') ?>
                                            </div>
                                        </td>
                                        <td class="small text-muted">
                                            <?= format_date($h['advisor_action_at']) ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="/advisor/registration-requests/<?= $h['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-eye"></i>
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
</div>
