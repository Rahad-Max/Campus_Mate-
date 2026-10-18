<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h2 class="fw-bold mb-1">Welcome, <?= e($teacher['first_name'] . ' ' . $teacher['last_name']) ?>! 👋</h2>
        <p class="text-muted mb-0">
            Designation: <strong><?= e($teacher['designation'] ?? 'Faculty Member') ?></strong> &bull; 
            Department: <strong><?= e($teacher['department_name'] ?? 'CSE') ?></strong> &bull; 
            Office: <strong><?= e($teacher['office_room'] ?? 'TBA') ?></strong> &bull;
            Term: <strong><?= e($semester['name'] ?? 'Summer 2026') ?></strong>
        </p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <div class="d-flex justify-content-md-end gap-2">
            <a href="/timetable" class="btn btn-pub">
                <i class="bi bi-calendar-week me-1"></i> Full Teaching Routine
            </a>
            <a href="/teacher/profile" class="btn btn-outline-primary">
                <i class="bi bi-person-lines-fill me-1"></i> My Profile
            </a>
        </div>
    </div>
</div>

<!-- Teacher Metrics -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card">
            <div class="metric-icon blue">
                <i class="bi bi-journal-bookmark-fill"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value"><?= count($assignedSections) ?></div>
                <div class="metric-label">Assigned Sections</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card">
            <div class="metric-icon teal">
                <i class="bi bi-speedometer2"></i>
            </div>
            <div class="metric-info">
                <?php
                $assignedCredits = array_sum(array_map(fn($s) => (float)$s['assigned_credits'], $assignedSections));
                ?>
                <div class="metric-value"><?= number_format($assignedCredits, 1) ?></div>
                <div class="metric-label">Current Credit Load</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card">
            <div class="metric-icon amber">
                <i class="bi bi-shield-check"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value"><?= number_format((float)($teacher['max_credit_load'] ?? 15.0), 1) ?></div>
                <div class="metric-label">Max Permissible Load</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card">
            <div class="metric-icon purple">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value fs-6 text-truncate" style="max-width: 140px;">
                    <?= e($teacher['office_hours'] ?? 'By Appointment') ?>
                </div>
                <div class="metric-label">Office Hours</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Left Column: Assigned Classes, Today's Schedule, Upcoming Exams -->
    <div class="col-lg-8">
        <!-- Assigned Classes -->
        <div class="card card-pub mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-journal-check text-primary me-2"></i>My Teaching Assignments — <?= e($semester['name'] ?? 'Current Term') ?>
                </h5>
                <span class="badge bg-primary-subtle text-primary border"><?= count($assignedSections) ?> Sections</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($assignedSections)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-folder2-open fs-1 text-muted"></i>
                        <p class="text-muted mt-2">No teaching sections assigned yet for this semester.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Course Code</th>
                                    <th>Title</th>
                                    <th>Section</th>
                                    <th>Type</th>
                                    <th>Role</th>
                                    <th>Enrolled Students</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assignedSections as $sec): ?>
                                    <tr>
                                        <td class="fw-bold text-primary"><?= e($sec['course_code']) ?></td>
                                        <td><?= e($sec['course_title']) ?></td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Sec <?= e($sec['section_name']) ?></span></td>
                                        <td><span class="badge bg-light text-dark border"><?= ucfirst($sec['course_type']) ?></span></td>
                                        <td><span class="badge bg-info-subtle text-info border"><?= ucwords(str_replace('_', ' ', $sec['role'])) ?></span></td>
                                        <td>
                                            <span class="fw-semibold"><?= (int)$sec['enrolled_count'] ?></span> / <?= (int)$sec['capacity'] ?>
                                            <div class="progress mt-1" style="height: 4px;">
                                                <?php $pct = $sec['capacity'] > 0 ? min(100, round(($sec['enrolled_count'] / $sec['capacity']) * 100)) : 0; ?>
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $pct ?>%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Today's Teaching Schedule -->
        <div class="card card-pub mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-clock text-success me-2"></i>Today's Teaching Schedule (<?= date('l') ?>)
                </h5>
                <a href="/timetable" class="btn btn-sm btn-outline-success">View Week</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($todayClasses)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-cup-hot fs-3 text-secondary"></i>
                        <p class="mt-2 mb-0">No classes scheduled for today (<?= date('l') ?>). Consult your full teaching schedule for the week.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Time</th>
                                    <th>Course</th>
                                    <th>Section</th>
                                    <th>Classroom</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($todayClasses as $cls): ?>
                                    <tr>
                                        <td class="fw-bold">
                                            <i class="bi bi-clock me-1 text-muted"></i>
                                            <?= date('h:i A', strtotime($cls['start_time'])) ?> – <?= date('h:i A', strtotime($cls['end_time'])) ?>
                                        </td>
                                        <td>
                                            <strong><?= e($cls['course_code']) ?></strong>
                                            <div class="small text-muted"><?= e($cls['course_title']) ?></div>
                                        </td>
                                        <td><span class="badge bg-light text-dark border">Sec <?= e($cls['section_name']) ?></span></td>
                                        <td><span class="badge bg-info-subtle text-info border"><?= e($cls['room_number']) ?> (<?= e($cls['building']) ?>)</span></td>
                                        <td><span class="badge bg-secondary-subtle text-secondary text-uppercase"><?= e($cls['class_type']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Upcoming Examinations (Invigilation & Courses) -->
        <div class="card card-pub">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-calendar2-check text-danger me-2"></i>Upcoming Examination Duties & Course Exams
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($upcomingExams)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-calendar-x fs-3 text-secondary"></i>
                        <p class="mt-2 mb-0">No upcoming exam duties or scheduled exams assigned to your courses.</p>
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
                                    <th>Duty Role</th>
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
                                        <td>
                                            <?php if (!empty($xm['is_invigilator'])): ?>
                                                <span class="badge bg-warning-subtle text-warning border"><i class="bi bi-eye-fill me-1"></i> Invigilator</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary-subtle text-primary border">Course Instructor</span>
                                            <?php endif; ?>
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

    <!-- Right Column: Notices & Quick Links -->
    <div class="col-lg-4">
        <!-- Faculty Quick Actions -->
        <div class="card card-pub mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-lightning-charge text-warning me-2"></i>Faculty Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/teacher/classes" class="btn btn-outline-primary text-start">
                        <i class="bi bi-journal-bookmark me-2"></i> View Assigned Classes
                    </a>
                    <a href="/timetable" class="btn btn-outline-primary text-start">
                        <i class="bi bi-calendar-week me-2"></i> Weekly Timetable Grid
                    </a>
                    <a href="/teacher/profile" class="btn btn-outline-primary text-start">
                        <i class="bi bi-person-lines-fill me-2"></i> Update Faculty Profile
                    </a>
                    <a href="/helpdesk" class="btn btn-outline-primary text-start">
                        <i class="bi bi-stars me-2 text-warning"></i> AI Campus Knowledgebase
                    </a>
                </div>
            </div>
        </div>

        <!-- Latest Department Notices -->
        <div class="card card-pub">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-megaphone text-warning me-2"></i>Department Notices
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
