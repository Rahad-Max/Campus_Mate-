<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Teaching Assignments</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1">Teaching Workload & Section Allotments</h2>
        <p class="text-muted mb-0">Review teaching credit distribution, assigned class sections, and classroom venues for <?= e($currentSemester['name'] ?? 'Active Semester') ?>.</p>
    </div>
    <div>
        <a href="/timetable" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-calendar-week"></i>
            <span>Weekly Schedule</span>
        </a>
    </div>
</div>

<!-- Faculty Workload Progress Card -->
<?php
$maxLoad = (float)($teacher['max_credit_load'] ?? 12.0);
$currentLoad = (float)($totalCredits ?? 0.0);
$loadPct = min(100, ($maxLoad > 0 ? ($currentLoad / $maxLoad) * 100 : 0));
?>
<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-4">
        <div class="card card-pub border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="text-muted small mb-1">Current Assigned Credits</div>
                <div class="fs-2 fw-bold text-primary mb-2">
                    <?= number_format($currentLoad, 1) ?> <span class="fs-6 text-muted">/ <?= number_format($maxLoad, 1) ?> max</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-<?= $loadPct > 90 ? 'danger' : ($loadPct > 70 ? 'warning' : 'primary') ?>" style="width: <?= $loadPct ?>%"></div>
                </div>
                <div class="small text-muted mt-2">
                    <?= number_format(max(0, $maxLoad - $currentLoad), 1) ?> credit capacity remaining
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-8">
        <div class="card card-pub border-0 shadow-sm h-100">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary-subtle text-primary p-3 fs-3">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-5 text-dark"><?= e(($teacher['first_name'] ?? '') . ' ' . ($teacher['last_name'] ?? '')) ?></div>
                        <div class="text-muted small"><?= e($teacher['designation'] ?? 'Faculty Member') ?> &bull; <?= e($teacher['department_name'] ?? 'Computer Science and Engineering') ?></div>
                        <div class="text-muted small mt-1">Faculty ID: <code><?= e($teacher['teacher_id'] ?? '') ?></code></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assigned Sections Table -->
<div class="card card-pub border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom">
        <h6 class="card-title mb-0 fw-bold">
            <i class="bi bi-layout-three-columns text-primary me-2"></i>Assigned Class Sections (<?= count($assignments ?? []) ?>)
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 140px;">Course Code</th>
                        <th>Course Title</th>
                        <th>Section</th>
                        <th>Venue Room</th>
                        <th>Credits</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assignments)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                No sections assigned for instruction in this semester yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($assignments as $a): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary fw-bold fs-6">
                                        <?= e($a['course_code']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= e($a['course_title']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-dark-subtle text-dark fw-bold px-3 py-1">
                                        Sec <?= e($a['section_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($a['room_number'])): ?>
                                        <div class="small fw-semibold text-dark"><i class="bi bi-door-closed text-primary me-1"></i><?= e($a['room_number']) ?></div>
                                        <div class="text-muted small"><?= e($a['building'] ?? 'Academic Bldg') ?></div>
                                    <?php else: ?>
                                        <span class="text-muted small">TBA</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-bold text-info"><?= number_format((float)($a['credit'] ?? 3.0), 1) ?> cr</span>
                                </td>
                                <td>
                                    <?php if (($a['role'] ?? 'primary') === 'primary'): ?>
                                        <span class="badge bg-success-subtle text-success px-2 py-1"><i class="bi bi-star-fill me-1"></i>Lead Teacher</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-1">Co-Instructor</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
