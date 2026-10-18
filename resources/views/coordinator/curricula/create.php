<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/coordinator/curricula">Curricula</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create Curriculum</li>
        </ol>
    </nav>
    <h2 class="fw-bold mb-1">Create Degree Curriculum</h2>
    <p class="text-muted mb-0">Establish an academic curriculum syllabus and total credit framework.</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-pub border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="/coordinator/curricula">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Academic Department <span class="text-danger">*</span></label>
                        <select name="department_id" class="form-select" required>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>" <?= old('department_id') == $dept['id'] ? 'selected' : '' ?>>
                                    <?= e($dept['code']) ?> - <?= e($dept['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Curriculum Title <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. B.Sc. in Computer Science and Engineering (OBE Curriculum)" required value="<?= e(old('name')) ?>">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Curriculum Version <span class="text-danger">*</span></label>
                            <input type="text" name="version" class="form-control" placeholder="e.g. 2024.1 or 2025" required value="<?= e(old('version', '2024.1')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Total Credits Required <span class="text-danger">*</span></label>
                            <input type="number" step="0.5" min="1" max="300" name="total_credits_required" class="form-control" placeholder="e.g. 148.0" required value="<?= e(old('total_credits_required', '148.0')) ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= old('status', 'active') === 'active' ? 'selected' : '' ?>>Active (Currently adopted by batches)</option>
                            <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>Inactive (Archived)</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Save Curriculum
                        </button>
                        <a href="/coordinator/curricula" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
