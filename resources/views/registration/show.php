<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="/student/dashboard" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/student/registration" class="text-decoration-none">Registration</a></li>
                <li class="breadcrumb-item"><a href="/student/registration/history" class="text-decoration-none">History</a></li>
                <li class="breadcrumb-item active" aria-current="page">Request #<?= $request['id'] ?></li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1">Registration Request #<?= $request['id'] ?></h2>
        <p class="text-muted mb-0">
            Semester: <strong><?= e($request['semester_name']) ?></strong> &bull; 
            Submitted on <strong><?= format_date($request['created_at'], 'M d, Y H:i') ?></strong>
        </p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex align-items-center justify-content-md-end gap-2">
        <?php if ($request['status'] === 'submitted'): ?>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelRequestModal">
                <i class="bi bi-x-circle me-1"></i> Cancel Request
            </button>
        <?php endif; ?>
        <a href="/student/registration/history" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to History
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Requested Courses Table -->
    <div class="col-lg-8">
        <div class="card card-pub mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-journals text-primary me-2"></i>Requested Courses & Sections
                </h5>
                <span class="badge bg-primary rounded-pill"><?= count($items) ?> Courses (<?= number_format((float)$request['total_credits'], 1) ?> cr)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Course Code</th>
                                <th>Course Title</th>
                                <th>Credits</th>
                                <th>Section & Routine</th>
                                <th>Instructor</th>
                                <th>Item Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): 
                                $itemStatus = $item['status'];
                                $badge = match($itemStatus) {
                                    'approved' => 'bg-success text-white',
                                    'rejected' => 'bg-danger text-white',
                                    default => 'bg-warning text-dark'
                                };
                            ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?= e($item['course_code']) ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= e($item['course_title']) ?></div>
                                        <small class="text-muted"><?= ucfirst($item['course_type']) ?></small>
                                    </td>
                                    <td><strong><?= number_format((float)$item['credit'], 1) ?></strong> cr</td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary mb-1">
                                            Sec <?= e($item['section_name']) ?>
                                        </span>
                                        <div class="small">
                                            <?php if (!empty($item['schedules'])): ?>
                                                <?php foreach ($item['schedules'] as $sch): ?>
                                                    <div class="text-muted" style="font-size: 0.75rem;">
                                                        <?= substr($sch['day_of_week'], 0, 3) ?> <?= substr($sch['start_time'], 0, 5) ?>-<?= substr($sch['end_time'], 0, 5) ?> (<?= e($sch['room_number']) ?>)
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-muted fst-italic" style="font-size: 0.75rem;">Routine unassigned</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="small">
                                        <?= e($item['teacher_first_name'] ? $item['teacher_first_name'] . ' ' . $item['teacher_last_name'] : 'TBA') ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $badge ?>">
                                            <?= ucfirst($itemStatus) ?>
                                        </span>
                                        <?php if (!empty($item['rejection_reason'])): ?>
                                            <div class="text-danger small mt-1" style="font-size: 0.75rem;">
                                                <?= e($item['rejection_reason']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="2" class="text-end">Total Requested Credits:</td>
                                <td><?= number_format((float)$request['total_credits'], 1) ?> cr</td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <?php if (!empty($request['submission_notes'])): ?>
            <div class="card card-pub mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title mb-0 fw-bold text-dark">
                        <i class="bi bi-chat-left-text text-primary me-2"></i>My Submission Notes
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-0 text-muted" style="white-space: pre-line;"><?= e($request['submission_notes']) ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Column: Status & Advisor Decision Card -->
    <div class="col-lg-4">
        <div class="card card-pub mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-info-circle text-primary me-2"></i>Review & Decision Status
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center py-3 border-bottom mb-3">
                    <?php 
                        $status = $request['status'];
                        $badgeClass = match($status) {
                            'submitted' => 'bg-warning-subtle text-warning border-warning',
                            'advisor_approved' => 'bg-success-subtle text-success border-success',
                            'advisor_rejected' => 'bg-danger-subtle text-danger border-danger',
                            'cancelled' => 'bg-secondary-subtle text-secondary border-secondary',
                            default => 'bg-light text-dark'
                        };
                        $icon = match($status) {
                            'submitted' => 'bi-hourglass-split',
                            'advisor_approved' => 'bi-check-circle-fill',
                            'advisor_rejected' => 'bi-x-circle-fill',
                            'cancelled' => 'bi-slash-circle',
                            default => 'bi-circle'
                        };
                    ?>
                    <div class="display-6 mb-2">
                        <i class="bi <?= $icon ?>"></i>
                    </div>
                    <span class="badge <?= $badgeClass ?> border px-3 py-2 fs-6">
                        <?= ucwords(str_replace('_', ' ', $status)) ?>
                    </span>
                    <div class="small text-muted mt-2">
                        Request ID: #<?= $request['id'] ?>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="small text-muted mb-1">Academic Advisor</div>
                    <div class="fw-bold text-dark">
                        <?= !empty($request['advisor_first_name']) ? e($request['advisor_first_name'] . ' ' . $request['advisor_last_name']) : 'Dr. Kazi Nahid' ?>
                    </div>
                </div>

                <?php if (!empty($request['advisor_action_at'])): ?>
                    <div class="mb-3">
                        <div class="small text-muted mb-1">Advisor Action Date</div>
                        <div class="fw-bold text-dark"><?= format_date($request['advisor_action_at'], 'M d, Y H:i') ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($request['advisor_remarks'])): ?>
                    <div class="mb-3">
                        <div class="small text-muted mb-1">Advisor Remarks / Reason</div>
                        <div class="alert <?= $status === 'advisor_approved' ? 'alert-success' : 'alert-danger' ?> py-2 px-3 small mb-0">
                            <?= e($request['advisor_remarks']) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="pt-2 border-top small text-muted">
                    <i class="bi bi-clock me-1"></i> Created on <?= format_date($request['created_at']) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Request Modal -->
<?php if ($request['status'] === 'submitted'): ?>
    <div class="modal fade" id="cancelRequestModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="/student/registration/<?= $request['id'] ?>/cancel" method="POST">
                    <?= csrf_field() ?>
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold text-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>Withdraw Registration Request
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to withdraw this registration request (Batch #<?= $request['id'] ?>)?</p>
                        <p class="small text-muted">This will cancel your pending review. You will be able to select courses again and submit a new request if the registration window is open.</p>
                        
                        <div class="mb-3">
                            <label for="cancelReason" class="form-label small fw-bold">Reason for Cancellation (Optional)</label>
                            <input type="text" class="form-control form-control-sm" id="cancelReason" name="reason" placeholder="e.g. Changed elective preference">
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Keep Request</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> Yes, Withdraw Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
