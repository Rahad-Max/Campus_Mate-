<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h2 class="fw-bold mb-1">Welcome back, <?= e($student['first_name'] ?? 'Student') ?>! 👋</h2>
        <p class="text-muted mb-0">
            Student ID: <strong><?= e($student['student_id_card'] ?? '—') ?></strong> &bull; 
            Department: <strong><?= e($student['department_code'] ?? 'CSE') ?></strong> &bull; 
            Term: <strong><?= e($semester['name'] ?? 'Summer 2026') ?></strong>
        </p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="/registration" class="btn btn-pub">
            <i class="bi bi-pencil-square me-1"></i> Course Registration
        </a>
    </div>
</div>

<!-- Key Academic Metrics -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card">
            <div class="metric-icon blue">
                <i class="bi bi-mortarboard"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value"><?= number_format((float)($student['cgpa'] ?? 0.0), 2) ?></div>
                <div class="metric-label">Cumulative GPA</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card">
            <div class="metric-icon teal">
                <i class="bi bi-journal-check"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value"><?= number_format((float)($student['completed_credits'] ?? 0.0), 1) ?></div>
                <div class="metric-label">Completed Credits</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card">
            <div class="metric-icon amber">
                <i class="bi bi-collection"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value"><?= number_format((float)($totalCredits ?? 0.0), 1) ?></div>
                <div class="metric-label">Enrolled This Term</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card">
            <div class="metric-icon purple">
                <i class="bi bi-person-check"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value fs-5 text-truncate" style="max-width: 140px;">
                    <?= e($student['advisor_first_name'] ? $student['advisor_first_name'] . ' ' . $student['advisor_last_name'] : 'Not Assigned') ?>
                </div>
                <div class="metric-label">Academic Advisor</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Enrolled Courses -->
    <div class="col-lg-8">
        <div class="card card-pub h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-book text-primary me-2"></i>Current Enrolled Courses
                </h5>
                <span class="badge bg-primary-subtle text-primary border">
                    <?= count($enrolledCourses) ?> Courses Confirmed
                </span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($enrolledCourses)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-journal-x fs-1 text-muted"></i>
                        <p class="text-muted mt-2">No confirmed course registrations for Summer 2026 yet.</p>
                        <a href="/registration" class="btn btn-outline-primary btn-sm">Start Registration</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Course Title</th>
                                    <th>Credit</th>
                                    <th>Section</th>
                                    <th>Instructor</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enrolledCourses as $course): ?>
                                    <tr>
                                        <td class="fw-bold text-primary"><?= e($course['course_code']) ?></td>
                                        <td><?= e($course['course_title']) ?></td>
                                        <td><?= number_format((float)$course['credit'], 1) ?></td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Sec <?= e($course['section_name']) ?></span></td>
                                        <td><?= e($course['teacher_first_name'] ? $course['teacher_first_name'] . ' ' . $course['teacher_last_name'] : 'TBA') ?></td>
                                        <td><span class="status-pill completed"><i class="bi bi-check-circle-fill"></i> Enrolled</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Notices & AI Assistant Widget -->
    <div class="col-lg-4">
        <!-- AI Helpdesk Card -->
        <div class="card card-pub mb-4" style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); color: #ffffff;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-2 text-warning">
                    <i class="bi bi-stars fs-4"></i>
                    <h6 class="fw-bold mb-0 text-white">AI Academic Assistant</h6>
                </div>
                <p class="small text-white-50 mb-3">
                    Have questions about credit limits, add/drop policies, or syllabus requirements?
                </p>
                <a href="/helpdesk" class="btn btn-light btn-sm fw-semibold w-100 text-primary">
                    <i class="bi bi-chat-dots-fill me-1"></i> Ask AI Helpdesk
                </a>
            </div>
        </div>

        <!-- Recent Notices -->
        <div class="card card-pub">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-megaphone text-warning me-2"></i>Campus Notices
                </h6>
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
