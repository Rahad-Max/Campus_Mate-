<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="/advisor/dashboard" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/advisor/registration-requests" class="text-decoration-none">Registration Queue</a></li>
                <li class="breadcrumb-item active" aria-current="page">Review #<?= $request['id'] ?></li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1">Review Registration Request #<?= $request['id'] ?></h2>
        <p class="text-muted mb-0">
            Advisee: <strong><?= e($request['student_first_name'] . ' ' . $request['student_last_name']) ?></strong> (<?= e($request['student_id_card']) ?>) &bull; 
            Term: <strong><?= e($request['semester_name']) ?></strong>
        </p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex align-items-center justify-content-md-end gap-2">
        <a href="/advisor/registration-requests" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Queue
        </a>
        <?php 
            $status = $request['status'];
            $badgeClass = match($status) {
                'submitted' => 'bg-warning-subtle text-warning border-warning',
                'advisor_approved' => 'bg-success-subtle text-success border-success',
                'advisor_rejected' => 'bg-danger-subtle text-danger border-danger',
                default => 'bg-secondary-subtle text-secondary border-secondary'
            };
        ?>
        <span class="badge <?= $badgeClass ?> border px-3 py-2 fs-6">
            <?= ucwords(str_replace('_', ' ', $status)) ?>
        </span>
    </div>
</div>

<!-- Advisee Academic Profile Summary -->
<div class="card card-pub mb-4 border-start border-4 border-primary">
    <div class="card-body p-3">
        <div class="row align-items-center g-3">
            <div class="col-md-3">
                <div class="small text-muted">Advisee Profile</div>
                <div class="fw-bold text-dark fs-6"><?= e($request['student_first_name'] . ' ' . $request['student_last_name']) ?></div>
                <div class="small text-muted font-monospace"><?= e($request['student_id_card']) ?> &bull; <?= e($request['batch_no']) ?></div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Department & Program</div>
                <div class="fw-bold text-dark"><?= e($request['department_name']) ?></div>
                <div class="small text-muted"><?= e($request['department_code']) ?></div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Current CGPA & Standing</div>
                <div>
                    <span class="badge <?= ($request['academic_status'] ?? '') === 'probation' ? 'bg-danger' : 'bg-success' ?>">
                        <?= ucfirst($request['academic_status'] ?? 'regular') ?>
                    </span>
                    <strong class="ms-1 text-dark">CGPA: <?= number_format((float)$request['cgpa'], 2) ?></strong>
                </div>
                <div class="small text-muted"><?= number_format((float)$request['completed_credits'], 1) ?> credits completed</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Requested Workload</div>
                <div class="fw-bold text-primary fs-5">
                    <?= number_format((float)$request['total_credits'], 1) ?> Credits
                </div>
                <div class="small text-muted"><?= count($items) ?> Course Sections Selected</div>
            </div>
        </div>
    </div>
</div>

<!-- Automated Compliance & Verification Checklist -->
<div class="card card-pub mb-4">
    <div class="card-header bg-white py-3 border-bottom">
        <h6 class="card-title mb-0 fw-bold">
            <i class="bi bi-shield-check text-primary me-2"></i>Automated Advisory Pre-Audit
        </h6>
    </div>
    <div class="card-body p-3">
        <div class="row g-3">
            <!-- 1. Prerequisite Status -->
            <div class="col-md-3">
                <div class="p-3 border rounded bg-light">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-bold">Prerequisites</span>
                        <?php 
                            $allPrereqsPassed = true;
                            foreach ($auditResults as $ar) {
                                if (!($ar['prereq']['passed'] ?? true)) {
                                    $allPrereqsPassed = false;
                                    break;
                                }
                            }
                        ?>
                        <?php if ($allPrereqsPassed): ?>
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <?php else: ?>
                            <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                        <?php endif; ?>
                    </div>
                    <div class="small <?= $allPrereqsPassed ? 'text-success fw-medium' : 'text-danger fw-bold' ?>">
                        <?= $allPrereqsPassed ? 'All Prerequisites Met' : 'Unmet Prerequisites Detected' ?>
                    </div>
                </div>
            </div>

            <!-- 2. Workload Credit Limits -->
            <div class="col-md-3">
                <div class="p-3 border rounded bg-light">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-bold">Credit Policy</span>
                        <?php if ($creditAudit['valid'] ?? true): ?>
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <?php else: ?>
                            <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                        <?php endif; ?>
                    </div>
                    <div class="small <?= ($creditAudit['valid'] ?? true) ? 'text-success fw-medium' : 'text-danger fw-bold' ?>">
                        <?= ($creditAudit['valid'] ?? true) ? 'Within Allowed Limits' : ($creditAudit['message'] ?? 'Overload violation') ?>
                    </div>
                </div>
            </div>

            <!-- 3. Timetable Clashes -->
            <div class="col-md-3">
                <div class="p-3 border rounded bg-light">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-bold">Schedule Routine</span>
                        <?php if (!($conflictAudit['has_conflict'] ?? false)): ?>
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <?php else: ?>
                            <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                        <?php endif; ?>
                    </div>
                    <div class="small <?= !($conflictAudit['has_conflict'] ?? false) ? 'text-success fw-medium' : 'text-danger fw-bold' ?>">
                        <?= !($conflictAudit['has_conflict'] ?? false) ? 'No Schedule Clashes' : 'Routine Clash Detected' ?>
                    </div>
                </div>
            </div>

            <!-- 4. Section Vacancy -->
            <div class="col-md-3">
                <div class="p-3 border rounded bg-light">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-bold">Seat Availability</span>
                        <?php 
                            $allSeatsAvailable = true;
                            foreach ($auditResults as $ar) {
                                if (!($ar['seat']['available'] ?? true)) {
                                    $allSeatsAvailable = false;
                                    break;
                                }
                            }
                        ?>
                        <?php if ($allSeatsAvailable): ?>
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <?php else: ?>
                            <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                        <?php endif; ?>
                    </div>
                    <div class="small <?= $allSeatsAvailable ? 'text-success fw-medium' : 'text-danger fw-bold' ?>">
                        <?= $allSeatsAvailable ? 'Seats Vacant in All Sections' : 'One or more sections are full' ?>
                    </div>
                </div>
            </div>
        </div>
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
                <span class="badge bg-primary"><?= count($items) ?> Courses</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Course</th>
                                <th>Credit</th>
                                <th>Section & Routine</th>
                                <th>Instructor</th>
                                <th>Prerequisites</th>
                                <th>Capacity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): 
                                $sid = (int)$item['section_id'];
                                $audit = $auditResults[$sid] ?? [];
                                $prereqPassed = $audit['prereq']['passed'] ?? true;
                                $missingPrereqs = $audit['prereq']['missing'] ?? [];
                                $seatInfo = $audit['seat'] ?? [];
                                $hasSeats = $seatInfo['available'] ?? true;
                            ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-primary"><?= e($item['course_code']) ?></div>
                                        <div class="small text-dark fw-medium"><?= e($item['course_title']) ?></div>
                                        <span class="badge bg-secondary-subtle text-secondary small" style="font-size: 0.72rem;">
                                            <?= ucfirst($item['course_type']) ?>
                                        </span>
                                    </td>
                                    <td><strong><?= number_format((float)$item['credit'], 1) ?></strong> cr</td>
                                    <td>
                                        <span class="badge bg-light text-dark border mb-1">Sec <?= e($item['section_name']) ?></span>
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
                                        <?php if ($prereqPassed): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="bi bi-check-circle me-1"></i>Met
                                            </span>
                                        <?php else: ?>
                                            <?php 
                                                $missNames = array_map(fn($m) => $m['course_code'], $missingPrereqs);
                                            ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle" title="Missing: <?= implode(', ', $missNames) ?>">
                                                <i class="bi bi-x-circle me-1"></i>Requires <?= implode(', ', $missNames) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($hasSeats): ?>
                                            <span class="badge bg-success-subtle text-success">
                                                <?= $seatInfo['remaining'] ?? '?' ?> vacant / <?= $seatInfo['capacity'] ?? $item['capacity'] ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger">Full (0 seats)</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td>Total Workload:</td>
                                <td><?= number_format((float)$request['total_credits'], 1) ?> cr</td>
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Student Submission Notes (if any) -->
        <?php if (!empty($request['submission_notes'])): ?>
            <div class="card card-pub mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title mb-0 fw-bold">
                        <i class="bi bi-chat-left-text text-primary me-2"></i>Student Submission Notes
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-0 text-muted" style="white-space: pre-line;"><?= e($request['submission_notes']) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Student Academic History / Transcript Accordion -->
        <div class="card card-pub mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-mortarboard text-primary me-2"></i>Advisee Academic Transcript History
                </h6>
                <span class="small text-muted"><?= count($history) ?> Completed Courses</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                    <table class="table table-sm table-hover align-middle mb-0 small">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Semester</th>
                                <th>Course Code</th>
                                <th>Title</th>
                                <th>Credit</th>
                                <th>Grade</th>
                                <th>Grade Point</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($history)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No academic history records found for this student.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($history as $h): ?>
                                    <tr>
                                        <td><?= e($h['semester_name']) ?></td>
                                        <td class="fw-bold"><?= e($h['course_code']) ?></td>
                                        <td><?= e($h['course_title']) ?></td>
                                        <td><?= number_format((float)$h['credit'], 1) ?> cr</td>
                                        <td><span class="badge bg-light text-dark border fw-bold"><?= e($h['grade_letter']) ?></span></td>
                                        <td><?= number_format((float)$h['grade_point'], 2) ?></td>
                                        <td>
                                            <span class="badge <?= $h['status'] === 'completed' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?>">
                                                <?= ucfirst($h['status']) ?>
                                            </span>
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

    <!-- Right Column: Decision Actions Panel -->
    <div class="col-lg-4">
        <div class="card card-pub sticky-top" style="top: 20px; z-index: 10;">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Advisor Decision
                </h6>
            </div>
            <div class="card-body p-3">
                <?php if ($request['status'] === 'submitted'): ?>
                    <!-- Approval Form -->
                    <form action="/advisor/registration-requests/<?= $request['id'] ?>/approve" method="POST" class="mb-3">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="approvalRemarks" class="form-label small fw-bold">Advisor Remarks (Optional)</label>
                            <textarea class="form-control form-control-sm" id="approvalRemarks" name="remarks" rows="2" 
                                      placeholder="Prerequisites and workload verified. Approved.">Prerequisites and workload verified. Approved for registration.</textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100 py-2 fw-bold" 
                                onclick="return confirm('Confirm approval for this student registration? This will officially allocate seats and enroll the student.');">
                            <i class="bi bi-check-circle-fill me-1"></i> Approve & Enroll Courses
                        </button>
                    </form>

                    <div class="text-center my-2 text-muted small">&mdash; OR &mdash;</div>

                    <!-- Reject Trigger Button -->
                    <button type="button" class="btn btn-outline-danger w-100 py-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bi bi-x-circle me-1"></i> Reject Registration Request
                    </button>
                <?php else: ?>
                    <!-- Already Processed Notice -->
                    <div class="text-center py-4">
                        <div class="display-6 mb-2">
                            <i class="bi <?= $request['status'] === 'advisor_approved' ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' ?>"></i>
                        </div>
                        <h5 class="fw-bold mb-1">
                            <?= $request['status'] === 'advisor_approved' ? 'Request Approved' : 'Request Rejected' ?>
                        </h5>
                        <p class="small text-muted mb-3">
                            Processed on <?= format_date($request['advisor_action_at'], 'M d, Y H:i') ?>
                        </p>
                        <?php if (!empty($request['advisor_remarks'])): ?>
                            <div class="alert <?= $request['status'] === 'advisor_approved' ? 'alert-success' : 'alert-danger' ?> small text-start mb-0">
                                <strong>Remarks:</strong> <?= e($request['advisor_remarks']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-light p-3 small text-muted">
                <i class="bi bi-info-circle me-1"></i> Approving a request atomically increments section enrollment and creates confirmed registrations.
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<?php if ($request['status'] === 'submitted'): ?>
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="/advisor/registration-requests/<?= $request['id'] ?>/reject" method="POST">
                    <?= csrf_field() ?>
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold text-danger">
                            <i class="bi bi-x-circle-fill me-2"></i>Reject Registration Request
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to reject registration request #<?= $request['id'] ?> for <strong><?= e($request['student_first_name'] . ' ' . $request['student_last_name']) ?></strong>?</p>
                        
                        <div class="mb-3">
                            <label for="rejectReason" class="form-label small fw-bold text-danger">
                                Reason for Rejection <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="rejectReason" name="rejection_reason" rows="3" required
                                      placeholder="Explain the required course modifications or prerequisite deficiency..."></textarea>
                            <div class="form-text small">This explanation will be sent directly to the student's portal.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-lg me-1"></i> Confirm Rejection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
