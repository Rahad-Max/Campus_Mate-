<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/coordinator/sections">Sections</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Section</li>
        </ol>
    </nav>
    <h2 class="fw-bold mb-1">Edit Section: <?= e($section['course_code']) ?> &mdash; Sec <?= e($section['section_name']) ?></h2>
    <p class="text-muted mb-0">Adjust room assignment, student capacity limits, or registration status.</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-pub border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="/coordinator/sections/<?= $section['id'] ?>/edit">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Course Offering <span class="text-danger">*</span></label>
                        <?php $currentOfferingId = $section['course_offering_id'] ?? $section['offering_id'] ?? 0; ?>
                        <select name="course_offering_id" class="form-select" required>
                            <?php foreach ($offerings as $off): ?>
                                <option value="<?= $off['id'] ?>" <?= old('course_offering_id', $currentOfferingId) == $off['id'] ? 'selected' : '' ?>>
                                    <?= e($off['course_code']) ?> &mdash; <?= e($off['course_title']) ?> (<?= e($off['semester_name'] ?? '') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Section Name / Code <span class="text-danger">*</span></label>
                            <input type="text" name="section_name" class="form-control text-uppercase" required maxlength="10" value="<?= e(old('section_name', $section['section_name'])) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Seat Capacity <span class="text-danger">*</span></label>
                            <input type="number" name="capacity" class="form-control" required min="1" max="500" value="<?= e(old('capacity', $section['capacity'])) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Designated Room</label>
                            <?php $currentRoomId = $section['room_id'] ?? null; ?>
                            <select name="room_id" class="form-select">
                                <option value="">-- TBA / To Be Announced --</option>
                                <?php foreach ($rooms as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= old('room_id', $currentRoomId) == $r['id'] ? 'selected' : '' ?>>
                                        <?= e($r['room_number']) ?> &mdash; <?= e($r['building'] ?? 'Academic Complex') ?> (Cap: <?= $r['capacity'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Section Status</label>
                            <select name="status" class="form-select">
                                <option value="open" <?= old('status', $section['status']) === 'open' ? 'selected' : '' ?>>Open</option>
                                <option value="closed" <?= old('status', $section['status']) === 'closed' ? 'selected' : '' ?>>Closed</option>
                                <option value="cancelled" <?= old('status', $section['status']) === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2-circle me-1"></i> Update Section
                        </button>
                        <a href="/coordinator/sections" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
