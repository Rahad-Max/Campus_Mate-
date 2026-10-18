<?php
// Student, Teacher & Master Academic Timetable View
$daysOrder = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$groupedTimetable = [];
foreach ($daysOrder as $d) {
    $groupedTimetable[$d] = [];
}
foreach ($timetable as $slot) {
    $day = $slot['day_of_week'] ?? 'Saturday';
    if (!isset($groupedTimetable[$day])) {
        $groupedTimetable[$day] = [];
    }
    $groupedTimetable[$day][] = $slot;
}
$activeDays = array_filter($groupedTimetable, fn($slots) => !empty($slots));
?>

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                <i class="bi bi-calendar-week me-1"></i> Academic Schedule
            </span>
            <span class="text-muted small">&bull; <?= e($semester['name'] ?? 'Summer 2026') ?></span>
        </div>
        <h2 class="fw-bold mb-0 text-dark">Weekly Academic Timetable</h2>
        <p class="text-muted mb-0 small">Official published class routine and hall allocations for this semester.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?= url('/makeup-classes') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm">
            <i class="bi bi-calendar-plus me-1"></i> Make-up Classes
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Routine
        </button>
    </div>
</div>

<?php if (empty($timetable)): ?>
    <div class="card card-pub border-0 shadow-sm bg-white text-center py-5">
        <div class="card-body">
            <div class="p-3 bg-light rounded-circle d-inline-block mb-3">
                <i class="bi bi-calendar-x fs-1 text-muted"></i>
            </div>
            <h5 class="fw-bold text-dark">No Scheduled Classes Found</h5>
            <p class="text-muted small max-w-sm mx-auto mb-3">There are no published class routines available for your registered courses in this semester.</p>
            <?php if ($role === 'student'): ?>
                <a href="<?= url('/student/registration') ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-journal-plus me-1"></i> Complete Course Registration
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <!-- Day by Day Visual Timetable Cards -->
    <div class="row g-4 mb-4">
        <?php foreach ($daysOrder as $day): ?>
            <?php if (!empty($groupedTimetable[$day])): ?>
                <div class="col-12">
                    <div class="card card-pub border-0 shadow-sm bg-white overflow-hidden">
                        <div class="card-header bg-light border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary text-white rounded-pill px-3 py-1 fw-bold"><?= $day ?></span>
                                <span class="text-muted small fw-medium"><?= count($groupedTimetable[$day]) ?> scheduled class<?= count($groupedTimetable[$day]) > 1 ? 'es' : '' ?></span>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <?php foreach ($groupedTimetable[$day] as $slot): ?>
                                    <div class="col-12 col-md-6 col-xl-4">
                                        <div class="p-3 rounded-3 border h-100 transition-all bg-white hover-shadow-sm d-flex flex-column justify-content-between" style="border-left: 4px solid <?= $slot['class_type'] === 'lab' ? '#10b981' : '#4f46e5' ?> !important;">
                                            <div>
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="badge bg-light text-dark border font-monospace small">
                                                        <i class="bi bi-clock me-1 text-muted"></i><?= format_time($slot['start_time']) ?> &ndash; <?= format_time($slot['end_time']) ?>
                                                    </span>
                                                    <span class="badge <?= $slot['class_type'] === 'lab' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' ?> rounded-pill small">
                                                        <?= ucfirst($slot['class_type']) ?>
                                                    </span>
                                                </div>

                                                <h6 class="fw-bold text-dark mb-1">
                                                    <span class="text-primary"><?= e($slot['course_code']) ?></span> &bull; <?= e($slot['course_title']) ?>
                                                </h6>

                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <span class="badge bg-secondary-subtle text-secondary small">
                                                        Sec <?= e($slot['section_name']) ?>
                                                    </span>
                                                    <span class="text-muted small">
                                                        <i class="bi bi-geo-alt text-danger me-1"></i>Room <?= e($slot['room_number']) ?> (<?= e($slot['building']) ?>)
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="pt-2 border-top mt-2 d-flex align-items-center justify-content-between small text-muted">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 0.65rem;">
                                                        <?= strtoupper(substr($slot['teacher_first_name'] ?? 'T', 0, 1)) ?>
                                                    </div>
                                                    <span class="text-truncate" style="max-width: 170px;">
                                                        <?= e(($slot['teacher_first_name'] ?? '') . ' ' . ($slot['teacher_last_name'] ?? '')) ?>
                                                    </span>
                                                </div>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle small">
                                                    Published
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Complete Tabular Matrix Table -->
    <div class="card card-pub border-0 shadow-sm bg-white overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-table text-primary me-2"></i>Full Semester Routine Table
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 12%;">Day</th>
                            <th style="width: 18%;">Time Slot</th>
                            <th>Course</th>
                            <th>Section</th>
                            <th>Room & Building</th>
                            <th>Faculty Member</th>
                            <th>Type</th>
                            <th class="pe-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($timetable as $slot): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-dark"><?= e($slot['day_of_week']) ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">
                                        <?= format_time($slot['start_time']) ?> &ndash; <?= format_time($slot['end_time']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary"><?= e($slot['course_code']) ?></div>
                                    <div class="text-muted small text-truncate" style="max-width: 220px;"><?= e($slot['course_title']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                        Sec <?= e($slot['section_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="bi bi-geo-alt text-muted small"></i>
                                        <span class="fw-semibold text-dark"><?= e($slot['room_number']) ?></span>
                                    </div>
                                    <div class="text-muted small"><?= e($slot['building']) ?></div>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark"><?= e(($slot['teacher_first_name'] ?? '') . ' ' . ($slot['teacher_last_name'] ?? '')) ?></div>
                                </td>
                                <td>
                                    <span class="badge <?= $slot['class_type'] === 'lab' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' ?>">
                                        <?= ucfirst($slot['class_type']) ?>
                                    </span>
                                </td>
                                <td class="pe-3">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        Published
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>
