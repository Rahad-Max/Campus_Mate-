<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Curriculum Explorer</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1">Academic Curriculum & Course Explorer</h2>
        <p class="text-muted mb-0">Browse university curricula, graduation requirement matrices, and prerequisite dependencies to advise students.</p>
    </div>
    <div>
        <a href="/advisor/advisees" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-people"></i>
            <span>My Advisees</span>
        </a>
    </div>
</div>

<!-- Search & Curriculum Explorer Bar -->
<div class="card card-pub border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="/advisor/courses" class="row g-2 align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by course code or title..." value="<?= e($search ?? '') ?>">
                </div>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter Catalog</button>
                <?php if (!empty($search)): ?>
                    <a href="/advisor/courses" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    <!-- Courses Table -->
    <div class="col-lg-8">
        <div class="card card-pub border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-journal-code text-primary me-2"></i>Academic Courses & Credit Allocations (<?= count($courses) ?>)
                </h6>
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
                                <th class="text-end" style="width: 100px;">Syllabus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($courses)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        No courses found matching search.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($courses as $c): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary fw-bold fs-6">
                                                <?= e($c['course_code']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= e($c['title']) ?></div>
                                            <div class="small text-muted text-truncate" style="max-width: 320px;"><?= e($c['description'] ?: 'Standard departmental syllabus.') ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info fw-bold">
                                                <?= number_format((float)$c['credit'], 1) ?> cr
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?= ucfirst($c['course_type']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="/courses/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
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
    </div>

    <!-- Active Curricula Sidebar -->
    <div class="col-lg-4">
        <div class="card card-pub border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-diagram-3 text-primary me-2"></i>Approved Curricula
                </h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if (empty($curricula)): ?>
                        <li class="list-group-item text-muted text-center py-3">No active curricula.</li>
                    <?php else: ?>
                        <?php foreach ($curricula as $cur): ?>
                            <li class="list-group-item p-3">
                                <div class="fw-bold text-dark"><?= e($cur['name']) ?></div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span class="badge bg-primary-subtle text-primary"><?= e($cur['dept_code'] ?? 'CSE') ?></span>
                                    <span class="text-muted small fw-semibold"><?= number_format((float)($cur['total_credits_required'] ?? 148), 1) ?> Credits Required</span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
