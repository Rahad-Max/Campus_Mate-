<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Curricula</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1">Academic Curricula & Degree Programs</h2>
        <p class="text-muted mb-0">Manage degree requirements, syllabi, and semester-by-semester course roadmaps.</p>
    </div>
    <div>
        <a href="/coordinator/curricula/create" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-diagram-3"></i>
            <span>Create Curriculum</span>
        </a>
    </div>
</div>

<div class="card card-pub border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Curriculum Name</th>
                        <th>Department</th>
                        <th>Version</th>
                        <th>Credits Required</th>
                        <th>Status</th>
                        <th class="text-end" style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($curricula)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-diagram-2 fs-1 d-block mb-2 text-secondary"></i>
                                <div>No curricula registered yet.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($curricula as $cur): ?>
                            <tr>
                                <td>
                                    <a href="/coordinator/curricula/<?= $cur['id'] ?>" class="fw-bold text-dark text-decoration-none hover-primary">
                                        <?= e($cur['name']) ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        <?= e($cur['dept_code'] ?? $cur['department_name'] ?? 'CSE') ?>
                                    </span>
                                </td>
                                <td><span class="badge bg-light text-dark border">v<?= e($cur['version'] ?? '1.0') ?></span></td>
                                <td>
                                    <span class="fw-semibold"><?= number_format((float)($cur['total_credits_required'] ?? 148), 1) ?> credits</span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= ($cur['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>-subtle text-<?= ($cur['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?> px-2 py-1">
                                        <?= ucfirst($cur['status'] ?? 'active') ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/coordinator/curricula/<?= $cur['id'] ?>" class="btn btn-outline-secondary" title="View & Configure Course Matrix">
                                            <i class="bi bi-diagram-3"></i> Matrix
                                        </a>
                                        <a href="/coordinator/curricula/<?= $cur['id'] ?>/edit" class="btn btn-outline-primary" title="Edit Metadata">
                                            <i class="bi bi-pencil"></i>
                                        </a>
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
