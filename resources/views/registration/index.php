<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="/student/dashboard" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Course Registration</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1">Smart Course Registration</h2>
        <p class="text-muted mb-0">
            Term: <strong><?= e($semester['name'] ?? 'Summer 2026') ?></strong> &bull; 
            Registration Closes: <strong><?= format_date($semester['registration_end'] ?? null, 'M d, Y H:i') ?></strong>
        </p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex align-items-center justify-content-md-end gap-2">
        <a href="/student/registration/history" class="btn btn-outline-secondary">
            <i class="bi bi-clock-history me-1"></i> Registration History
        </a>
        <span class="badge bg-success-subtle text-success border px-3 py-2 fs-6">
            <i class="bi bi-calendar-check me-1"></i> Registration Open
        </span>
    </div>
</div>

<!-- Student Academic Standing Card -->
<div class="card card-pub mb-4 border-start border-4 border-primary">
    <div class="card-body p-3">
        <div class="row align-items-center g-3">
            <div class="col-md-3">
                <div class="small text-muted">Student Name & ID</div>
                <div class="fw-bold text-dark fs-6"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></div>
                <div class="small text-muted font-monospace"><?= e($student['student_id_card'] ?? '') ?> &bull; <?= e($student['batch_no'] ?? '') ?></div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Academic Standing & CGPA</div>
                <div>
                    <span class="badge <?= ($student['academic_status'] ?? '') === 'probation' ? 'bg-danger' : 'bg-success' ?>">
                        <?= ucfirst($student['academic_status'] ?? 'regular') ?>
                    </span>
                    <strong class="ms-1 text-dark">CGPA: <?= number_format((float)($student['cgpa'] ?? 0.0), 2) ?></strong>
                </div>
                <div class="small text-muted"><?= number_format((float)($student['completed_credits'] ?? 0.0), 1) ?> credits earned</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Semester Credit Rules</div>
                <div class="fw-bold text-dark">
                    <?= number_format((float)$minCredits, 1) ?> – <?= number_format((float)$maxCredits, 1) ?> Credits
                </div>
                <?php if ($isProbation): ?>
                    <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Probation Cap (Max <?= $maxCredits ?> cr)</span>
                <?php else: ?>
                    <span class="small text-muted">Standard Full-Time Policy</span>
                <?php endif; ?>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Assigned Academic Advisor</div>
                <div class="fw-bold text-dark">
                    <?= !empty($student['advisor_first_name']) ? e($student['advisor_first_name'] . ' ' . $student['advisor_last_name']) : 'Dr. Kazi Nahid' ?>
                </div>
                <div class="small text-muted"><?= e($student['advisor_designation'] ?? 'Associate Professor') ?></div>
            </div>
        </div>
    </div>
</div>

<?php if ($hasPending): ?>
    <div class="alert alert-warning border border-warning-subtle d-flex align-items-center justify-content-between p-3 mb-4 shadow-sm rounded-3">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-hourglass-split fs-2 text-warning"></i>
            <div>
                <h6 class="fw-bold mb-1">Registration Request Under Review</h6>
                <div class="small text-muted">
                    You have already submitted a course registration request for <strong><?= e($semester['name'] ?? 'Summer 2026') ?></strong>. Your Academic Advisor is currently reviewing your requested courses.
                </div>
            </div>
        </div>
        <div>
            <?php 
                $latestReq = !empty($requests) ? $requests[0] : null; 
                $reqId = $latestReq['id'] ?? 1;
            ?>
            <a href="/student/registration/<?= $reqId ?>" class="btn btn-warning btn-sm text-dark fw-bold">
                <i class="bi bi-eye me-1"></i> View Request Details
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- Already Enrolled Courses Alert (if any) -->
<?php if (!empty($enrolled)): ?>
    <div class="card card-pub mb-4 border-success-subtle">
        <div class="card-header bg-success-subtle py-2 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold text-success mb-0">
                <i class="bi bi-check-circle-fill me-2"></i>Confirmed Enrolled Courses for <?= e($semester['name'] ?? 'this semester') ?>
            </h6>
            <span class="badge bg-success"><?= count($enrolled) ?> Courses Confirmed</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light small">
                        <tr>
                            <th>Code</th>
                            <th>Course Title</th>
                            <th>Credits</th>
                            <th>Section</th>
                            <th>Instructor</th>
                            <th>Class Routine</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php foreach ($enrolled as $en): ?>
                            <tr>
                                <td class="fw-bold text-primary"><?= e($en['course_code']) ?></td>
                                <td><?= e($en['course_title']) ?></td>
                                <td><?= number_format((float)$en['credit'], 1) ?> cr</td>
                                <td><span class="badge bg-light text-dark border">Sec <?= e($en['section_name']) ?></span></td>
                                <td><?= e($en['teacher_first_name'] ? $en['teacher_first_name'] . ' ' . $en['teacher_last_name'] : 'TBA') ?></td>
                                <td>
                                    <?php if (!empty($en['schedules'])): ?>
                                        <?php foreach ($en['schedules'] as $sch): ?>
                                            <span class="badge bg-light text-dark border me-1">
                                                <?= substr($sch['day_of_week'], 0, 3) ?> <?= substr($sch['start_time'], 0, 5) ?>-<?= substr($sch['end_time'], 0, 5) ?> (<?= e($sch['room_number']) ?>)
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">Routine unassigned</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left Column: Available Course Offerings -->
    <div class="col-lg-8">
        <div class="card card-pub mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="row align-items-center g-2">
                    <div class="col-md-6">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="bi bi-collection text-primary me-2"></i>Available Courses & Sections
                        </h5>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input type="text" id="courseFilterInput" class="form-control" placeholder="Search by code, title, or teacher...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <form id="registrationForm" action="/student/registration/submit" method="POST">
                    <?= csrf_field() ?>

                    <div class="table-responsive" style="max-height: 650px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0" id="coursesTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 45px;" class="text-center">Select</th>
                                    <th>Course</th>
                                    <th>Credits</th>
                                    <th>Prerequisites</th>
                                    <th>Section & Routine</th>
                                    <th>Instructor</th>
                                    <th class="text-center">Seats</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($offerings)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-2"></i>
                                            <p class="mt-2 mb-0">No courses are currently offered for registration this semester.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($offerings as $offering): 
                                        $courseCode = $offering['course_code'];
                                        $courseTitle = $offering['course_title'];
                                        $credit = (float)$offering['credit'];
                                        $prereqPassed = $offering['prereq_status']['passed'] ?? true;
                                        $missingPrereqs = $offering['prereq_status']['missing'] ?? [];
                                        $sections = $offering['sections'] ?? [];

                                        // Check if already enrolled in this course
                                        $alreadyEnrolled = false;
                                        foreach ($enrolled as $en) {
                                            if ($en['course_code'] === $courseCode) {
                                                $alreadyEnrolled = true;
                                                break;
                                            }
                                        }
                                    ?>
                                        <?php if (empty($sections)): ?>
                                            <tr class="table-light text-muted course-row" data-search="<?= strtolower($courseCode . ' ' . $courseTitle) ?>">
                                                <td class="text-center text-muted"><i class="bi bi-dash"></i></td>
                                                <td>
                                                    <span class="fw-bold"><?= e($courseCode) ?></span>
                                                    <div class="small"><?= e($courseTitle) ?></div>
                                                </td>
                                                <td><?= number_format($credit, 1) ?> cr</td>
                                                <td colspan="4" class="text-muted fst-italic small">No sections scheduled for this offering yet.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($sections as $sec): 
                                                $secId = (int)$sec['id'];
                                                $seatsLeft = (int)$sec['available_seats'];
                                                $capacity = (int)$sec['capacity'];
                                                $isFull = $seatsLeft <= 0;
                                                $schedules = $sec['schedules'] ?? [];
                                                $leadTeacher = $sec['lead_teacher'];

                                                $disabledReason = '';
                                                if ($hasPending) {
                                                    $disabledReason = 'Pending registration under review';
                                                } elseif ($alreadyEnrolled) {
                                                    $disabledReason = 'Already enrolled this semester';
                                                } elseif (!$prereqPassed) {
                                                    $disabledReason = 'Prerequisites not met';
                                                } elseif ($isFull) {
                                                    $disabledReason = 'Section is full';
                                                }

                                                $isDisabled = !empty($disabledReason);
                                            ?>
                                                <tr class="course-row <?= $isDisabled ? 'table-light' : '' ?>" 
                                                    data-search="<?= strtolower($courseCode . ' ' . $courseTitle . ' ' . ($leadTeacher ? $leadTeacher['first_name'] . ' ' . $leadTeacher['last_name'] : '')) ?>">
                                                    <td class="text-center">
                                                        <input type="checkbox" name="sections[]" value="<?= $secId ?>"
                                                               class="form-check-input course-section-check"
                                                               data-course-id="<?= $offering['course_id'] ?>"
                                                               data-course-code="<?= e($courseCode) ?>"
                                                               data-course-title="<?= e($courseTitle) ?>"
                                                               data-credit="<?= $credit ?>"
                                                               data-section-name="<?= e($sec['section_name']) ?>"
                                                               data-section-id="<?= $secId ?>"
                                                               <?= $isDisabled ? 'disabled' : '' ?>>
                                                    </td>
                                                    <td>
                                                        <div class="fw-bold text-primary"><?= e($courseCode) ?></div>
                                                        <div class="small text-dark fw-medium"><?= e($courseTitle) ?></div>
                                                        <span class="badge bg-secondary-subtle text-secondary small" style="font-size: 0.72rem;">
                                                            <?= ucfirst($offering['course_type'] ?? 'theory') ?>
                                                        </span>
                                                        <?php if ($alreadyEnrolled): ?>
                                                            <span class="badge bg-success-subtle text-success small" style="font-size: 0.72rem;">Enrolled</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <strong><?= number_format($credit, 1) ?></strong> <span class="text-muted small">cr</span>
                                                    </td>
                                                    <td>
                                                        <?php if ($prereqPassed): ?>
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle" title="All prerequisite requirements fulfilled">
                                                                <i class="bi bi-check-circle me-1"></i>Met
                                                            </span>
                                                        <?php else: ?>
                                                            <?php 
                                                                $missNames = array_map(fn($m) => $m['course_code'], $missingPrereqs);
                                                            ?>
                                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle" 
                                                                  data-bs-toggle="tooltip" 
                                                                  title="Missing: <?= implode(', ', $missNames) ?>">
                                                                <i class="bi bi-exclamation-octagon me-1"></i>Requires <?= implode(', ', $missNames) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold mb-1">
                                                            Sec <?= e($sec['section_name']) ?>
                                                        </span>
                                                        <div class="small">
                                                            <?php if (!empty($schedules)): ?>
                                                                <?php foreach ($schedules as $sch): ?>
                                                                    <div class="text-muted" style="font-size: 0.78rem;">
                                                                        <i class="bi bi-clock me-1"></i><?= substr($sch['day_of_week'], 0, 3) ?> <?= substr($sch['start_time'], 0, 5) ?>-<?= substr($sch['end_time'], 0, 5) ?>
                                                                        <span class="text-dark">(<?= e($sch['room_number']) ?>)</span>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            <?php else: ?>
                                                                <span class="text-muted fst-italic" style="font-size: 0.75rem;">No routine posted</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td class="small">
                                                        <?php if ($leadTeacher): ?>
                                                            <div class="fw-medium text-dark"><?= e($leadTeacher['first_name'] . ' ' . $leadTeacher['last_name']) ?></div>
                                                            <div class="text-muted" style="font-size: 0.75rem;"><?= e($leadTeacher['designation']) ?></div>
                                                        <?php else: ?>
                                                            <span class="text-muted fst-italic">Faculty TBA</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($isFull): ?>
                                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                                Full (0/<?= $capacity ?>)
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                                <?= $seatsLeft ?> / <?= $capacity ?> left
                                                            </span>
                                                        <?php endif; ?>
                                                        <?php if ($disabledReason): ?>
                                                            <div class="text-muted" style="font-size: 0.68rem;"><?= e($disabledReason) ?></div>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Hidden fields & submit will be handled via the live cart sidebar or bottom summary -->
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Live Selection Cart & Validation Summary -->
    <div class="col-lg-4">
        <div class="card card-pub sticky-top" style="top: 20px; z-index: 10;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-cart3 text-primary me-2"></i>Selected Cart Summary
                </h6>
                <span id="cartCountBadge" class="badge bg-primary rounded-pill">0 Courses</span>
            </div>
            <div class="card-body p-3">
                <!-- Cart Empty State -->
                <div id="cartEmptyState" class="text-center py-4 text-muted">
                    <i class="bi bi-bag-plus fs-1 text-secondary opacity-50"></i>
                    <p class="small mt-2 mb-0">Select courses from the offerings table on the left to build your registration cart.</p>
                </div>

                <!-- Selected Courses List -->
                <div id="selectedCoursesList" class="list-group list-group-flush mb-3 d-none">
                    <!-- Dynamic Cart Items Injected via JS -->
                </div>

                <!-- Credit Progress Bar -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1 small">
                        <span>Selected Credits:</span>
                        <strong><span id="liveTotalCredits">0.0</span> / <?= number_format($maxCredits, 1) ?> cr</strong>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div id="creditProgressBar" class="progress-bar bg-primary" role="progressbar" style="width: 0%;"></div>
                    </div>
                    <div class="d-flex justify-content-between text-muted" style="font-size: 0.72rem;">
                        <span>Min: <?= number_format($minCredits, 1) ?> cr</span>
                        <span>Max: <?= number_format($maxCredits, 1) ?> cr</span>
                    </div>
                </div>

                <!-- Dynamic Server Validation Alerts -->
                <div id="validationFeedbackArea" class="mb-3"></div>

                <!-- Submission Form Container -->
                <div class="border-top pt-3">
                    <div class="mb-3">
                        <label for="advisorNotes" class="form-label small fw-bold">
                            <i class="bi bi-chat-text text-primary me-1"></i>Notes for Academic Advisor
                        </label>
                        <textarea class="form-control form-control-sm" id="advisorNotes" name="notes" rows="2" 
                                  placeholder="e.g. Taking regular 3rd semester core courses..." <?= $hasPending ? 'disabled' : '' ?>></textarea>
                    </div>

                    <?php if ($hasPending): ?>
                        <button type="button" class="btn btn-secondary w-100 disabled" disabled>
                            <i class="bi bi-lock me-1"></i> Pending Request Under Review
                        </button>
                    <?php else: ?>
                        <button type="button" id="btnSubmitCart" class="btn btn-pub w-100 py-2 fw-bold" disabled>
                            <i class="bi bi-send-check me-1"></i> Submit for Advisor Approval
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-footer bg-light p-3 small text-muted">
                <i class="bi bi-shield-check text-success me-1"></i> All requests undergo automatic prerequisite verification and advisor workload authorization.
            </div>
        </div>
    </div>
</div>

<!-- Submission Confirmation Modal -->
<div class="modal fade" id="submitConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-send-check text-primary me-2"></i>Confirm Registration Submission
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>You are about to submit your course registration request for <strong><?= e($semester['name'] ?? 'Summer 2026') ?></strong> to your Academic Advisor <strong><?= e($student['advisor_first_name'] ? $student['advisor_first_name'] . ' ' . $student['advisor_last_name'] : 'Dr. Kazi Nahid') ?></strong>.</p>
                
                <div class="bg-light p-3 rounded border mb-3">
                    <div class="d-flex justify-content-between mb-1 small">
                        <span>Total Selected Courses:</span>
                        <strong id="modalCourseCount">0</strong>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span>Total Credit Hours:</span>
                        <strong id="modalCreditCount" class="text-primary">0.0 cr</strong>
                    </div>
                </div>

                <div class="small text-muted">
                    <i class="bi bi-info-circle me-1"></i> Once submitted, your registration batch will be locked for review. You can track or withdraw your request from your Registration History.
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Modify Selection</button>
                <button type="button" id="btnFinalConfirmSubmit" class="btn btn-pub">
                    <i class="bi bi-check-lg me-1"></i> Confirm & Submit
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const minAllowedCredits = <?= json_encode((float)$minCredits) ?>;
    const maxAllowedCredits = <?= json_encode((float)$maxCredits) ?>;
    const checkboxes = document.querySelectorAll('.course-section-check');
    const coursesTable = document.getElementById('coursesTable');
    const filterInput = document.getElementById('courseFilterInput');
    const cartList = document.getElementById('selectedCoursesList');
    const cartEmpty = document.getElementById('cartEmptyState');
    const cartCountBadge = document.getElementById('cartCountBadge');
    const liveTotalEl = document.getElementById('liveTotalCredits');
    const progressBar = document.getElementById('creditProgressBar');
    const feedbackArea = document.getElementById('validationFeedbackArea');
    const btnSubmit = document.getElementById('btnSubmitCart');
    const mainForm = document.getElementById('registrationForm');
    const advisorNotes = document.getElementById('advisorNotes');

    // Search filter
    if (filterInput) {
        filterInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            const rows = coursesTable.querySelectorAll('.course-row');
            rows.forEach(row => {
                const searchData = row.getAttribute('data-search') || '';
                if (!query || searchData.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Checkbox toggle handler
    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            // Prevent selecting multiple sections of the exact same course
            const courseId = this.getAttribute('data-course-id');
            if (this.checked) {
                checkboxes.forEach(other => {
                    if (other !== this && other.getAttribute('data-course-id') === courseId) {
                        other.checked = false;
                    }
                });
            }
            updateCart();
        });
    });

    function updateCart() {
        const selected = Array.from(checkboxes).filter(cb => cb.checked);
        let totalCredits = 0.0;
        cartList.innerHTML = '';

        if (selected.length === 0) {
            cartEmpty.classList.remove('d-none');
            cartList.classList.add('d-none');
            cartCountBadge.textContent = '0 Courses';
            liveTotalEl.textContent = '0.0';
            progressBar.style.width = '0%';
            progressBar.className = 'progress-bar bg-primary';
            feedbackArea.innerHTML = '';
            if (btnSubmit) btnSubmit.disabled = true;
            return;
        }

        cartEmpty.classList.add('d-none');
        cartList.classList.remove('d-none');
        cartCountBadge.textContent = `${selected.length} Course${selected.length > 1 ? 's' : ''}`;

        selected.forEach(cb => {
            const cCode = cb.getAttribute('data-course-code');
            const cTitle = cb.getAttribute('data-course-title');
            const cCredit = parseFloat(cb.getAttribute('data-credit') || 0);
            const sName = cb.getAttribute('data-section-name');
            const sId = cb.getAttribute('data-section-id');
            totalCredits += cCredit;

            const itemDiv = document.createElement('div');
            itemDiv.className = 'list-group-item px-0 py-2 d-flex justify-content-between align-items-center';
            itemDiv.innerHTML = `
                <div>
                    <div class="fw-bold small text-primary">${cCode} <span class="badge bg-secondary-subtle text-secondary">Sec ${sName}</span></div>
                    <div class="text-truncate text-muted" style="max-width: 190px; font-size: 0.75rem;">${cTitle}</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="small fw-bold">${cCredit.toFixed(1)} cr</span>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0 border-0" data-remove-id="${sId}" title="Remove">
                        <i class="bi bi-x-circle fs-6"></i>
                    </button>
                </div>
            `;
            cartList.appendChild(itemDiv);
        });

        liveTotalEl.textContent = totalCredits.toFixed(1);
        const percent = Math.min(100, Math.round((totalCredits / maxAllowedCredits) * 100));
        progressBar.style.width = percent + '%';

        // Local credit thresholds feedback
        let localErrors = [];
        if (totalCredits < minAllowedCredits) {
            localErrors.push(`Below minimum credit threshold (${totalCredits.toFixed(1)} / ${minAllowedCredits.toFixed(1)} cr required).`);
            progressBar.className = 'progress-bar bg-warning';
        } else if (totalCredits > maxAllowedCredits) {
            localErrors.push(`Exceeds maximum allowable semester limit (${totalCredits.toFixed(1)} / ${maxAllowedCredits.toFixed(1)} cr).`);
            progressBar.className = 'progress-bar bg-danger';
        } else {
            progressBar.className = 'progress-bar bg-success';
        }

        renderFeedback(localErrors);

        // Enable or disable submit button based on local rules
        if (btnSubmit) {
            btnSubmit.disabled = (localErrors.length > 0);
        }

        // Attach remove events
        cartList.querySelectorAll('[data-remove-id]').forEach(btn => {
            btn.addEventListener('click', function() {
                const targetId = this.getAttribute('data-remove-id');
                const targetCb = document.querySelector(`.course-section-check[value="${targetId}"]`);
                if (targetCb) {
                    targetCb.checked = false;
                    updateCart();
                }
            });
        });
    }

    function renderFeedback(errors) {
        if (!feedbackArea) return;
        if (!errors || errors.length === 0) {
            feedbackArea.innerHTML = `
                <div class="alert alert-success py-2 px-3 small mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    <span>Selected credits and prerequisites meet university policy.</span>
                </div>
            `;
            return;
        }

        let html = '<div class="alert alert-danger py-2 px-3 small mb-0"><ul class="mb-0 ps-3">';
        errors.forEach(err => {
            html += `<li>${err}</li>`;
        });
        html += '</ul></div>';
        feedbackArea.innerHTML = html;
    }

    // Modal Confirmation trigger
    if (btnSubmit) {
        btnSubmit.addEventListener('click', function() {
            const selected = Array.from(checkboxes).filter(cb => cb.checked);
            if (selected.length === 0) return;

            let totalCr = 0.0;
            selected.forEach(cb => totalCr += parseFloat(cb.getAttribute('data-credit') || 0));

            document.getElementById('modalCourseCount').textContent = selected.length + ' courses';
            document.getElementById('modalCreditCount').textContent = totalCr.toFixed(1) + ' credits';

            const modal = new bootstrap.Modal(document.getElementById('submitConfirmModal'));
            modal.show();
        });
    }

    // Final form submission
    const btnFinalConfirm = document.getElementById('btnFinalConfirmSubmit');
    if (btnFinalConfirm && mainForm) {
        btnFinalConfirm.addEventListener('click', function() {
            // Append notes to main form
            const notesInput = document.createElement('input');
            notesInput.type = 'hidden';
            notesInput.name = 'notes';
            notesInput.value = advisorNotes ? advisorNotes.value : '';
            mainForm.appendChild(notesInput);

            btnFinalConfirm.disabled = true;
            btnFinalConfirm.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';
            mainForm.submit();
        });
    }
});
</script>
