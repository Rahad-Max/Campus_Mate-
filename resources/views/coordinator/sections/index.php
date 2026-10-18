<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Sections</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1">Course Sections & Seat Capacities</h2>
        <p class="text-muted mb-0">Partition offerings into class sections, assign room facilities, and manage seat limits.</p>
    </div>
    <div>
        <a href="/coordinator/sections/create" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-plus-circle"></i>
            <span>Create Section</span>
        </a>
    </div>
</div>

<!-- Semester Filter Bar -->
<div class="card card-pub border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="/coordinator/sections" class="row g-2 align-items-center">
            <div class="col-md-6 col-lg-8">
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 fw-semibold text-muted text-nowrap"><i class="bi bi-filter me-1"></i>Select Term:</label>
                    <select name="semester_id" class="form-select" onchange="this.form.submit()">
                        <?php foreach ($semesters as $sem): ?>
                            <option value="<?= $sem['id'] ?>" <?= ($semesterId == $sem['id']) ? 'selected' : '' ?>>
                                <?= e($sem['name']) ?> (<?= e($sem['academic_year']) ?>) <?= !empty($sem['is_current']) ? '★ Current' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 text-end">
                <span class="text-muted small">Total <strong><?= count($sections) ?></strong> sections active</span>
            </div>
        </form>
    </div>
</div>

<!-- Sections Table -->
<div class="card card-pub border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 140px;">Course</th>
                        <th style="width: 100px;">Section</th>
                        <th>Classroom / Lab</th>
                        <th>Seat Capacity</th>
                        <th>Enrolled</th>
                        <th>Available</th>
                        <th>Status</th>
                        <th class="text-end" style="width: 160px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sections)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-layout-three-columns fs-1 d-block mb-2 text-secondary"></i>
                                <div>No sections configured for this semester yet.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sections as $sec): ?>
                            <?php
                            $capacity = (int)($sec['capacity'] ?? 0);
                            $enrolled = (int)($sec['enrolled_count'] ?? 0);
                            $available = max(0, $capacity - $enrolled);
                            $isFull = $available === 0 && $capacity > 0;
                            ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary fw-bold fs-6">
                                        <?= e($sec['course_code']) ?>
                                    </span>
                                    <div class="small text-muted text-truncate" style="max-width: 220px;"><?= e($sec['course_title']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-dark-subtle text-dark fw-bold px-3 py-1 fs-6">
                                        Sec <?= e($sec['section_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($sec['room_number'])): ?>
                                        <div class="small fw-semibold text-dark"><i class="bi bi-door-closed text-primary me-1"></i><?= e($sec['room_number']) ?></div>
                                        <div class="text-muted small"><?= e($sec['building'] ?? 'Academic Bldg') ?></div>
                                    <?php else: ?>
                                        <span class="text-muted small">TBA</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-bold"><?= $capacity ?></span> <span class="text-muted small">seats</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-secondary"><?= $enrolled ?></span>
                                </td>
                                <td>
                                    <?php if ($isFull): ?>
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1">Full</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success px-2 py-1"><?= $available ?> left</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $secBadge = match($sec['status'] ?? 'open') {
                                        'open' => 'success',
                                        'closed' => 'warning',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $secBadge ?>-subtle text-<?= $secBadge ?> px-2 py-1">
                                        <?= ucfirst($sec['status'] ?? 'open') ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/coordinator/sections/<?= $sec['id'] ?>/edit" class="btn btn-outline-primary" title="Edit Section">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="/coordinator/sections/<?= $sec['id'] ?>/toggle-status" class="d-inline" onsubmit="return confirm('Toggle status for Section <?= e($sec['section_name']) ?>?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-<?= ($sec['status'] ?? 'open') === 'open' ? 'warning' : 'success' ?>" title="Toggle Open/Close">
                                                <i class="bi bi-<?= ($sec['status'] ?? 'open') === 'open' ? 'lock-fill' : 'unlock-fill' ?>"></i>
                                            </button>
                                        </form>
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
