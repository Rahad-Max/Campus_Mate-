<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Current Semester</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1">Current Academic Semester</h2>
        <p class="text-muted mb-0">Overview of active semester milestones, course schedules, and registration offerings.</p>
    </div>
    <div>
        <a href="/registration" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-check2-circle"></i>
            <span>Course Registration</span>
        </a>
    </div>
</div>

<!-- Semester Banner -->
<?php if (!empty($semester)): ?>
    <div class="card card-pub border-0 shadow-sm mb-4 border-start border-4 border-primary">
        <div class="card-body p-4">
            <div class="row align-items-center g-4">
                <div class="col-md-6">
                    <span class="badge bg-primary px-3 py-1 mb-2">Active Semester</span>
                    <h3 class="fw-bold mb-1"><?= e($semester['name']) ?></h3>
                    <p class="text-muted mb-0">Academic Session: <strong><?= e($semester['academic_year']) ?></strong></p>
                </div>
                <div class="col-md-6">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="card bg-light border-0 p-3">
                                <div class="text-muted small"><i class="bi bi-calendar-range text-primary me-1"></i>Term Duration</div>
                                <div class="fw-bold text-dark small mt-1">
                                    <?= date('M d, Y', strtotime($semester['start_date'])) ?><br>to <?= date('M d, Y', strtotime($semester['end_date'])) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="card bg-light border-0 p-3">
                                <div class="text-muted small"><i class="bi bi-clock-history text-primary me-1"></i>Registration Window</div>
                                <div class="fw-bold text-dark small mt-1">
                                    <?php if (!empty($semester['registration_start'])): ?>
                                        <?= date('M d', strtotime($semester['registration_start'])) ?> to <?= date('M d, Y', strtotime($semester['registration_end'])) ?>
                                    <?php else: ?>
                                        Dates announced soon
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Available Offerings for this Semester -->
<div class="card card-pub border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="card-title mb-0 fw-bold">
            <i class="bi bi-collection text-primary me-2"></i>Offered Courses in <?= e($semester['name'] ?? 'Current Term') ?> (<?= count($offerings ?? []) ?>)
        </h6>
        <a href="/courses" class="btn btn-sm btn-outline-secondary">View All Catalog</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 140px;">Code</th>
                        <th>Course Title</th>
                        <th>Credits</th>
                        <th>Type</th>
                        <th>Sections</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($offerings)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                No course offerings published for this semester yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($offerings as $off): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary fw-bold fs-6">
                                        <?= e($off['course_code']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= e($off['course_title']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info fw-bold">
                                        <?= number_format((float)$off['credit'], 1) ?> cr
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= (int)($off['section_count'] ?? 0) ?> Sections
                                    </span>
                                </td>
                                <td>
                                    <a href="/courses/<?= $off['course_id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-info-circle"></i> Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
