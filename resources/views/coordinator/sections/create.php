<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/coordinator/sections">Sections</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create Section</li>
        </ol>
    </nav>
    <h2 class="fw-bold mb-1">Create Course Section</h2>
    <p class="text-muted mb-0">Establish a class group, seat limit, and default room for a scheduled offering.</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-pub border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="/coordinator/sections">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Course Offering <span class="text-danger">*</span></label>
                        <select name="offering_id" class="form-select" required>
                            <option value="">-- Choose Scheduled Offering --</option>
                            <?php foreach ($offerings as $off): ?>
                                <option value="<?= $off['id'] ?>" <?= (($_GET['offering_id'] ?? old('offering_id')) == $off['id']) ? 'selected' : '' ?>>
                                    <?= e($off['course_code']) ?> &mdash; <?= e($off['course_title']) ?> (<?= e($off['semester_name'] ?? 'Active Term') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Section Name / Code <span class="text-danger">*</span></label>
                            <input type="text" name="section_name" class="form-control text-uppercase" placeholder="e.g. A, B, C, or A1" required maxlength="10" value="<?= e(old('section_name', 'A')) ?>">
                            <div class="form-text small">Must be unique within the course offering.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Seat Capacity <span class="text-danger">*</span></label>
                            <input type="number" name="capacity" class="form-control" placeholder="e.g. 40" required min="1" max="500" value="<?= e(old('capacity', '40')) ?>">
                            <div class="form-text small">Maximum student seats available.</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Designated Room</label>
                            <select name="room_id" class="form-select">
                                <option value="">-- TBA / To Be Announced --</option>
                                <?php foreach ($rooms as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= old('room_id') == $r['id'] ? 'selected' : '' ?>>
                                        <?= e($r['room_number']) ?> &mdash; <?= e($r['building'] ?? 'Academic Complex') ?> (Cap: <?= $r['capacity'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Section Status</label>
                            <select name="status" class="form-select">
                                <option value="open" <?= old('status', 'open') === 'open' ? 'selected' : '' ?>>Open (Permits Registration)</option>
                                <option value="closed" <?= old('status') === 'closed' ? 'selected' : '' ?>>Closed</option>
                                <option value="cancelled" <?= old('status') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Save Section
                        </button>
                        <a href="/coordinator/sections" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
