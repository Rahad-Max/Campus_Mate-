<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/coordinator/departments">Departments</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create Department</li>
        </ol>
    </nav>
    <h2 class="fw-bold mb-1">Create Academic Department</h2>
    <p class="text-muted mb-0">Register a new academic department or faculty entity in the university system.</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-pub border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="/coordinator/departments">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Department Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control text-uppercase" placeholder="e.g. CSE, EEE, BBA" required maxlength="10" value="<?= e(old('code')) ?>">
                            <div class="form-text">Unique uppercase institutional code.</div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Department Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Computer Science and Engineering" required maxlength="100" value="<?= e(old('name')) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Campus Building / Location</label>
                            <input type="text" name="building" class="form-control" placeholder="e.g. Academic Complex - Floor 3" value="<?= e(old('building')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Official Contact Email</label>
                            <input type="email" name="contact_email" class="form-control" placeholder="e.g. cse@pub.ac.bd" value="<?= e(old('contact_email')) ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Operational Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= old('status', 'active') === 'active' ? 'selected' : '' ?>>Active (Accepting programs & courses)</option>
                            <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>Inactive (Archived or unlaunched)</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Save Department
                        </button>
                        <a href="/coordinator/departments" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
