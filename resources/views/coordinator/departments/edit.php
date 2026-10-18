<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/coordinator/departments">Departments</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Department</li>
        </ol>
    </nav>
    <h2 class="fw-bold mb-1">Edit Department: <?= e($department['name']) ?></h2>
    <p class="text-muted mb-0">Update department profile, location details, and operational status.</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-pub border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="/coordinator/departments/<?= $department['id'] ?>/edit">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Department Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control text-uppercase" required maxlength="10" value="<?= e(old('code', $department['code'])) ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Department Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required maxlength="100" value="<?= e(old('name', $department['name'])) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Campus Building / Location</label>
                            <input type="text" name="building" class="form-control" value="<?= e(old('building', $department['building'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Official Contact Email</label>
                            <input type="email" name="contact_email" class="form-control" value="<?= e(old('contact_email', $department['contact_email'] ?? '')) ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Operational Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= old('status', $department['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= old('status', $department['status'] ?? 'active') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2-circle me-1"></i> Update Department
                        </button>
                        <a href="/coordinator/departments" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
