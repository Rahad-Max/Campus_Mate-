<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Departments</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1">Department Management</h2>
        <p class="text-muted mb-0">Oversee academic departments, department codes, and organizational units.</p>
    </div>
    <div>
        <a href="/coordinator/departments/create" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-plus-circle"></i>
            <span>Add Department</span>
        </a>
    </div>
</div>

<!-- Stats Bar -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-4">
        <div class="card card-pub border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary-subtle text-primary p-3 fs-4">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Departments</div>
                    <div class="fs-4 fw-bold"><?= count($departments) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4">
        <div class="card card-pub border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success-subtle text-success p-3 fs-4">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <div class="text-muted small">Active Departments</div>
                    <div class="fs-4 fw-bold">
                        <?= count(array_filter($departments, fn($d) => ($d['status'] ?? 'active') === 'active')) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card card-pub border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="/coordinator/departments" class="row g-2 align-items-center">
            <div class="col-md-6 col-lg-8">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by department code or name..." value="<?= e($search ?? '') ?>">
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 col-lg-1 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                <?php if (!empty($search) || !empty($status)): ?>
                    <a href="/coordinator/departments" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Departments Table -->
<div class="card card-pub border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 120px;">Code</th>
                        <th>Department Name</th>
                        <th>Location</th>
                        <th>Contact Email</th>
                        <th>Status</th>
                        <th class="text-end" style="width: 200px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($departments)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-building-exclamation fs-1 d-block mb-2 text-secondary"></i>
                                <div>No departments found matching your criteria.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($departments as $dept): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1 fs-6">
                                        <?= e($dept['code']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/coordinator/departments/<?= $dept['id'] ?>" class="text-decoration-none fw-semibold text-dark hover-primary">
                                        <?= e($dept['name']) ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="text-muted small">
                                        <i class="bi bi-geo-alt me-1"></i><?= e($dept['building'] ?? 'Main Academic Bldg') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted small">
                                        <?= e($dept['contact_email'] ?? '—') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (($dept['status'] ?? 'active') === 'active'): ?>
                                        <span class="badge bg-success-subtle text-success px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-1"><i class="bi bi-pause-circle-fill me-1"></i>Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/coordinator/departments/<?= $dept['id'] ?>" class="btn btn-outline-secondary" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="/coordinator/departments/<?= $dept['id'] ?>/edit" class="btn btn-outline-primary" title="Edit Department">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="/coordinator/departments/<?= $dept['id'] ?>/toggle-status" class="d-inline" onsubmit="return confirm('Change status for this department?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-<?= ($dept['status'] ?? 'active') === 'active' ? 'warning' : 'success' ?>" title="Toggle Status">
                                                <i class="bi bi-<?= ($dept['status'] ?? 'active') === 'active' ? 'pause-fill' : 'play-fill' ?>"></i>
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
