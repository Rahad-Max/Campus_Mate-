<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="/student/dashboard" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/student/registration" class="text-decoration-none">Registration</a></li>
                <li class="breadcrumb-item active" aria-current="page">History</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1">Registration History</h2>
        <p class="text-muted mb-0">Record of course registration batches submitted for Academic Advisor review.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <a href="/student/registration" class="btn btn-pub">
            <i class="bi bi-plus-circle me-1"></i> Register Courses
        </a>
    </div>
</div>

<div class="card card-pub">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 fw-bold">
            <i class="bi bi-clock-history text-primary me-2"></i>My Registration Requests
        </h5>
        <span class="badge bg-light text-dark border"><?= count($requests) ?> Total Batches</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($requests)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 text-secondary opacity-50"></i>
                <h5 class="fw-bold mt-2">No Registration History</h5>
                <p class="text-muted small">You haven't submitted any course registration requests yet.</p>
                <a href="/student/registration" class="btn btn-pub btn-sm">
                    <i class="bi bi-check2-circle me-1"></i> Open Course Registration
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Batch ID</th>
                            <th>Semester</th>
                            <th>Type</th>
                            <th>Credits</th>
                            <th>Status</th>
                            <th>Submission Date</th>
                            <th>Advisor Decision</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $req): 
                            $status = $req['status'];
                            $badgeClass = match($status) {
                                'submitted' => 'bg-warning text-dark',
                                'advisor_approved' => 'bg-success text-white',
                                'advisor_rejected' => 'bg-danger text-white',
                                'coordinator_approved' => 'bg-primary text-white',
                                'cancelled' => 'bg-secondary text-white',
                                default => 'bg-light text-dark'
                            };
                            $icon = match($status) {
                                'submitted' => 'bi-hourglass-split',
                                'advisor_approved' => 'bi-check-circle-fill',
                                'advisor_rejected' => 'bi-x-circle-fill',
                                'coordinator_approved' => 'bi-award-fill',
                                'cancelled' => 'bi-slash-circle',
                                default => 'bi-circle'
                            };
                        ?>
                            <tr>
                                <td class="fw-bold">#<?= $req['id'] ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= e($req['semester_name']) ?></div>
                                    <small class="text-muted"><?= e($req['semester_code']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= ucfirst(str_replace('_', ' ', $req['request_type'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= number_format((float)$req['total_credits'], 1) ?></strong> cr
                                </td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>">
                                        <i class="bi <?= $icon ?> me-1"></i><?= ucwords(str_replace('_', ' ', $status)) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="small text-dark"><?= format_date($req['created_at']) ?></div>
                                    <small class="text-muted"><?= date('H:i', strtotime($req['created_at'])) ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($req['advisor_remarks'])): ?>
                                        <div class="small text-dark text-truncate" style="max-width: 250px;" title="<?= e($req['advisor_remarks']) ?>">
                                            <?= e($req['advisor_remarks']) ?>
                                        </div>
                                        <?php if (!empty($req['advisor_action_at'])): ?>
                                            <small class="text-muted"><?= format_date($req['advisor_action_at']) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="small text-muted fst-italic">Pending decision</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="/student/registration/<?= $req['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i> View Details
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
