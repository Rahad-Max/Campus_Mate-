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
        <div class="d-flex justify-content-md-end gap-2">
            <a href="/registration" class="btn btn-pub">
                <i class="bi bi-pencil-square me-1"></i> Course Registration
            </a>
            <a href="/timetable" class="btn btn-outline-primary">
                <i class="bi bi-calendar-week me-1"></i> My Timetable
            </a>
        </div>
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
                <div class="metric-value fs-6 text-truncate" style="max-width: 140px;">
                    <?= e(!empty($student['advisor_first_name']) ? $student['advisor_first_name'] . ' ' . $student['advisor_last_name'] : 'Not Assigned') ?>
                </div>
                <div class="metric-label">Academic Advisor</div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Shortcuts -->
<div class="row g-2 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
        <a href="/registration" class="card card-pub text-decoration-none text-center p-3 h-100 hover-lift">
            <i class="bi bi-check2-circle text-primary fs-3 mb-1"></i>
            <span class="d-block small fw-bold text-dark">Registration</span>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="/timetable" class="card card-pub text-decoration-none text-center p-3 h-100 hover-lift">
            <i class="bi bi-calendar-week text-success fs-3 mb-1"></i>
            <span class="d-block small fw-bold text-dark">Timetable</span>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="/exams" class="card card-pub text-decoration-none text-center p-3 h-100 hover-lift">
            <i class="bi bi-journal-text text-danger fs-3 mb-1"></i>
            <span class="d-block small fw-bold text-dark">Exam Routine</span>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="/student/history" class="card card-pub text-decoration-none text-center p-3 h-100 hover-lift">
            <i class="bi bi-award text-warning fs-3 mb-1"></i>
            <span class="d-block small fw-bold text-dark">History</span>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="/faculty" class="card card-pub text-decoration-none text-center p-3 h-100 hover-lift">
            <i class="bi bi-person-lines-fill text-info fs-3 mb-1"></i>
            <span class="d-block small fw-bold text-dark">Faculty</span>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="/helpdesk" class="card card-pub text-decoration-none text-center p-3 h-100 hover-lift">
            <i class="bi bi-stars text-accent fs-3 mb-1"></i>
            <span class="d-block small fw-bold text-dark">AI Helpdesk</span>
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Main Left Column: Enrolled Courses & Today's Schedule -->
    <div class="col-lg-8">
        <!-- Current Enrolled Courses -->
        <div class="card card-pub mb-4">
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
                        <p class="text-muted mt-2">No confirmed course registrations for <?= e($semester['name'] ?? 'Summer 2026') ?> yet.</p>
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

        <!-- Today's Scheduled Classes -->
        <div class="card card-pub mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-clock-history text-success me-2"></i>Upcoming Class Routine (<?= date('l') ?>)
                </h5>
                <a href="/timetable" class="btn btn-sm btn-outline-success">Full Timetable</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($todayClasses)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-cup-hot fs-3 text-secondary"></i>
                        <p class="mt-2 mb-0">No classes scheduled for today (<?= date('l') ?>). Review your weekly timetable for other days.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Time</th>
                                    <th>Course</th>
                                    <th>Section</th>
                                    <th>Room</th>
                                    <th>Teacher</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($todayClasses as $cls): ?>
                                    <tr>
                                        <td class="fw-bold text-dark">
                                            <i class="bi bi-clock me-1 text-muted"></i>
                                            <?= date('h:i A', strtotime($cls['start_time'])) ?> – <?= date('h:i A', strtotime($cls['end_time'])) ?>
                                        </td>
                                        <td>
                                            <strong><?= e($cls['course_code']) ?></strong>
                                            <div class="small text-muted"><?= e($cls['course_title']) ?></div>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?= e($cls['section_name']) ?></span></td>
                                        <td><span class="badge bg-info-subtle text-info border"><?= e($cls['room_number']) ?> (<?= e($cls['building']) ?>)</span></td>
                                        <td><?= e($cls['teacher_first_name'] . ' ' . $cls['teacher_last_name']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Upcoming Examinations -->
        <div class="card card-pub">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-calendar-event text-danger me-2"></i>Upcoming Examinations
                </h5>
                <a href="/exams" class="btn btn-sm btn-outline-danger">All Exams</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($upcomingExams)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-calendar-check fs-3 text-secondary"></i>
                        <p class="mt-2 mb-0">No examinations published yet for your enrolled courses.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Type</th>
                                    <th>Course</th>
                                    <th>Room</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcomingExams as $xm): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?= format_date($xm['exam_date']) ?></div>
                                            <small class="text-muted"><?= date('h:i A', strtotime($xm['start_time'])) ?> – <?= date('h:i A', strtotime($xm['end_time'])) ?></small>
                                        </td>
                                        <td><span class="badge bg-danger-subtle text-danger text-uppercase border"><?= e($xm['exam_type']) ?></span></td>
                                        <td>
                                            <strong><?= e($xm['course_code']) ?></strong>
                                            <div class="small text-muted"><?= e($xm['course_title']) ?></div>
                                        </td>
                                        <td><?= e($xm['room_number']) ?> (<?= e($xm['building']) ?>)</td>
                                        <td><span class="status-pill scheduled"><?= ucfirst($xm['status']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Notifications & Campus Notices -->
    <div class="col-lg-4">
        <!-- AI Helpdesk Card -->
        <div class="card card-pub mb-4" style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); color: #ffffff;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-2 text-warning">
                    <i class="bi bi-stars fs-4"></i>
                    <h6 class="fw-bold mb-0 text-white">AI Academic Assistant</h6>
                </div>
                <p class="small text-white-50 mb-3">
                    Have questions about credit limits, add/drop policies, or syllabus requirements? Ask our RAG-powered campus companion.
                </p>
                <a href="/helpdesk" class="btn btn-light btn-sm fw-semibold w-100 text-primary">
                    <i class="bi bi-chat-dots-fill me-1"></i> Ask AI Helpdesk
                </a>
            </div>
        </div>

        <!-- Recent Notifications -->
        <div class="card card-pub mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-bell text-primary me-2"></i>Recent Notifications
                </h6>
                <a href="/notifications" class="text-decoration-none small text-pub-primary">View All</a>
            </div>
            <div class="list-group list-group-flush">
                <?php if (empty($notifications)): ?>
                    <div class="p-3 text-muted text-center small">No new notifications.</div>
                <?php else: ?>
                    <?php foreach ($notifications as $notif): ?>
                        <a href="<?= e($notif['action_url'] ?: '/notifications') ?>" class="list-group-item list-group-item-action p-3 <?= !(int)$notif['is_read'] ? 'bg-light' : '' ?>">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                <span class="badge bg-secondary-subtle text-secondary" style="font-size:0.65rem;"><?= ucfirst($notif['type']) ?></span>
                                <small class="text-muted" style="font-size:0.75rem;"><?= format_date($notif['created_at'], 'M d, H:i') ?></small>
                            </div>
                            <h6 class="mb-1 text-truncate fw-semibold small <?= !(int)$notif['is_read'] ? 'text-primary' : '' ?>">
                                <?= !(int)$notif['is_read'] ? '<i class="bi bi-circle-fill text-primary me-1" style="font-size:0.5rem;"></i>' : '' ?>
                                <?= e($notif['title']) ?>
                            </h6>
                            <p class="mb-0 text-muted small text-truncate"><?= e($notif['message']) ?></p>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Campus Notices -->
        <div class="card card-pub">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-megaphone text-warning me-2"></i>Campus Notices
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
