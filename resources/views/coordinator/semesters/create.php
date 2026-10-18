<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/coordinator/semesters">Semesters</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create Semester</li>
        </ol>
    </nav>
    <h2 class="fw-bold mb-1">Create Academic Term / Semester</h2>
    <p class="text-muted mb-0">Define an academic semester session with start/end dates and registration periods.</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-pub border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="/coordinator/semesters">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold">Semester Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Summer 2026, Fall 2026, Spring 2027" required value="<?= e(old('name')) ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Academic Year <span class="text-danger">*</span></label>
                            <input type="text" name="academic_year" class="form-control" placeholder="e.g. 2025-2026" required value="<?= e(old('academic_year', date('Y') . '-' . (date('Y') + 1))) ?>">
                        </div>
                    </div>

                    <div class="card bg-light border-0 p-3 mb-3">
                        <h6 class="fw-bold mb-3"><i class="bi bi-calendar3 text-primary me-2"></i>Semester Term Schedule</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Term Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" required value="<?= e(old('start_date')) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Term End Date <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control" required value="<?= e(old('end_date')) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="card bg-light border-0 p-3 mb-3">
                        <h6 class="fw-bold mb-3"><i class="bi bi-clock-history text-primary me-2"></i>Registration Window (Optional)</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Registration Opens</label>
                                <input type="date" name="registration_start" class="form-control" value="<?= e(old('registration_start')) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Registration Closes</label>
                                <input type="date" name="registration_end" class="form-control" value="<?= e(old('registration_end')) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Initial Status</label>
                            <select name="status" class="form-select">
                                <option value="planned" <?= old('status', 'planned') === 'planned' ? 'selected' : '' ?>>Planned (Upcoming)</option>
                                <option value="active" <?= old('status') === 'active' ? 'selected' : '' ?>>Active (In Progress)</option>
                                <option value="completed" <?= old('status') === 'completed' ? 'selected' : '' ?>>Completed (Concluded)</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-center pt-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_current" name="is_current" value="1" <?= old('is_current') ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="is_current">Set as Current Official Term</label>
                                <div class="form-text small">Will designate this term across all student and teacher dashboards.</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Save Semester
                        </button>
                        <a href="/coordinator/semesters" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
