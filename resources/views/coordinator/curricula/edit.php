<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/coordinator/curricula">Curricula</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Curriculum</li>
        </ol>
    </nav>
    <h2 class="fw-bold mb-1">Edit Curriculum: <?= e($curriculum['name']) ?></h2>
    <p class="text-muted mb-0">Update curriculum metadata, department assignment, and target credit requirements.</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-pub border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="/coordinator/curricula/<?= $curriculum['id'] ?>/edit">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Academic Department <span class="text-danger">*</span></label>
                        <select name="department_id" class="form-select" required>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>" <?= old('department_id', $curriculum['department_id']) == $dept['id'] ? 'selected' : '' ?>>
                                    <?= e($dept['code']) ?> - <?= e($dept['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Curriculum Title <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required value="<?= e(old('name', $curriculum['name'])) ?>">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Curriculum Version <span class="text-danger">*</span></label>
                            <input type="text" name="version" class="form-control" required value="<?= e(old('version', $curriculum['version'])) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Total Credits Required <span class="text-danger">*</span></label>
                            <input type="number" step="0.5" min="1" max="300" name="total_credits_required" class="form-control" required value="<?= e(old('total_credits_required', $curriculum['total_credits_required'])) ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Status</label>
                        <?php $curStatus = $curriculum['status'] ?? (!empty($curriculum['is_active']) ? 'active' : 'inactive'); ?>
                        <select name="status" class="form-select">
                            <option value="active" <?= old('status', $curStatus) === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= old('status', $curStatus) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2-circle me-1"></i> Update Curriculum
                        </button>
                        <a href="/coordinator/curricula" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
