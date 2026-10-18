<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Semesters</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1">Semester & Academic Term Management</h2>
        <p class="text-muted mb-0">Configure academic years, active sessions, and student registration deadlines.</p>
    </div>
    <div>
        <a href="/coordinator/semesters/create" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-calendar-plus"></i>
            <span>Create Semester</span>
        </a>
    </div>
</div>

<!-- Active Semester Highlight Card -->
<?php if (!empty($currentSemester)): ?>
    <div class="card card-pub border-0 shadow-sm mb-4 border-start border-4 border-success">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-success-subtle text-success p-3 fs-3">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success">Current Official Term</span>
                            <span class="badge bg-light text-dark border"><?= e($currentSemester['academic_year']) ?></span>
                        </div>
                        <h4 class="fw-bold mb-0 mt-1"><?= e($currentSemester['name']) ?></h4>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-4 text-muted small">
                    <div>
                        <div><i class="bi bi-calendar3 me-1"></i> Term Duration</div>
                        <div class="fw-semibold text-dark"><?= date('M d, Y', strtotime($currentSemester['start_date'])) ?> &mdash; <?= date('M d, Y', strtotime($currentSemester['end_date'])) ?></div>
                    </div>
                    <?php if (!empty($currentSemester['registration_start'])): ?>
                        <div>
                            <div><i class="bi bi-clock-history me-1"></i> Registration Window</div>
                            <div class="fw-semibold text-dark"><?= date('M d', strtotime($currentSemester['registration_start'])) ?> &mdash; <?= date('M d, Y', strtotime($currentSemester['registration_end'])) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Semesters List -->
<div class="card card-pub border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Semester Name</th>
                        <th>Academic Year</th>
                        <th>Term Dates</th>
                        <th>Registration Period</th>
                        <th>Status</th>
                        <th class="text-end" style="width: 220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($semesters)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary"></i>
                                <div>No semesters found in the database.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($semesters as $sem): ?>
                            <tr class="<?= !empty($sem['is_current']) ? 'table-success-subtle' : '' ?>">
                                <td>
                                    <div class="fw-bold d-flex align-items-center gap-2">
                                        <?= e($sem['name']) ?>
                                        <?php if (!empty($sem['is_current'])): ?>
                                            <span class="badge bg-success px-2 py-0.5 small">CURRENT</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= e($sem['academic_year']) ?></span></td>
                                <td>
                                    <div class="small">
                                        <?= date('M d, Y', strtotime($sem['start_date'])) ?> &mdash; <?= date('M d, Y', strtotime($sem['end_date'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($sem['registration_start']) && !empty($sem['registration_end'])): ?>
                                        <div class="small text-muted">
                                            <?= date('M d', strtotime($sem['registration_start'])) ?> &mdash; <?= date('M d, Y', strtotime($sem['registration_end'])) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">Not set</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = match($sem['status'] ?? 'planned') {
                                        'active' => 'success',
                                        'completed' => 'secondary',
                                        default => 'primary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>-subtle text-<?= $statusClass ?> px-2 py-1">
                                        <?= ucfirst($sem['status'] ?? 'planned') ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <?php if (empty($sem['is_current']) && ($sem['status'] ?? '') !== 'completed'): ?>
                                            <form method="POST" action="/coordinator/semesters/<?= $sem['id'] ?>/set-current" class="d-inline" onsubmit="return confirm('Set this semester as the official current term?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-outline-success" title="Set as Current Term">
                                                    <i class="bi bi-check-circle"></i> Activate
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <a href="/coordinator/semesters/<?= $sem['id'] ?>/edit" class="btn btn-outline-primary" title="Edit Semester">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if (($sem['status'] ?? '') !== 'completed'): ?>
                                            <form method="POST" action="/coordinator/semesters/<?= $sem['id'] ?>/close" class="d-inline" onsubmit="return confirm('Complete and archive this semester? Academic history will be protected.');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-outline-danger" title="Close Semester">
                                                    <i class="bi bi-archive"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
