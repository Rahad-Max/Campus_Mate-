<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h2 class="fw-bold mb-1">Welcome, <?= e($teacher['first_name'] . ' ' . $teacher['last_name']) ?>! 👋</h2>
        <p class="text-muted mb-0">
            Designation: <strong><?= e($teacher['designation'] ?? 'Faculty Member') ?></strong> &bull; 
            Department: <strong><?= e($teacher['department_name'] ?? 'CSE') ?></strong> &bull; 
            Office: <strong><?= e($teacher['office_room'] ?? 'TBA') ?></strong>
        </p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="/timetable" class="btn btn-pub">
            <i class="bi bi-calendar-week me-1"></i> Full Teaching Routine
        </a>
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

<!-- Assigned Classes -->
<div class="card card-pub mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 fw-bold">
            <i class="bi bi-calendar3 text-primary me-2"></i>My Teaching Assignments — <?= e($semester['name'] ?? 'Current Term') ?>
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($assignedSections)): ?>
            <div class="text-center py-5">
                <p class="text-muted">No teaching sections assigned yet for this semester.</p>
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
                                <td><?= (int)$sec['enrolled_count'] ?> / <?= (int)$sec['capacity'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
