<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-1">Weekly Academic Timetable</h2>
        <p class="text-muted mb-0">Scheduled class routines for <strong><?= e($semester['name'] ?? 'Summer 2026') ?></strong>.</p>
    </div>
    <button type="button" id="btnPrintTimetable" class="btn btn-outline-secondary">
        <i class="bi bi-printer me-1"></i> Print Routine
    </button>
</div>

<div class="card card-pub mb-4">
    <div class="card-body p-0">
        <?php if (empty($timetable)): ?>
            <div class="text-center py-5">
                <i class="bi bi-calendar-x fs-1 text-muted"></i>
                <p class="text-muted mt-2">No class schedules found for the active semester.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 text-center">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%;">Day</th>
                            <th style="width: 20%;">Time Slot</th>
                            <th>Course</th>
                            <th>Section</th>
                            <th>Room</th>
                            <th>Instructor</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($timetable as $slot): ?>
                            <tr>
                                <td class="fw-bold text-dark bg-light"><?= e($slot['day_of_week']) ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">
                                        <?= format_time($slot['start_time']) ?> - <?= format_time($slot['end_time']) ?>
                                    </span>
                                </td>
                                <td class="fw-bold text-primary"><?= e($slot['course_code']) ?> &bull; <?= e($slot['course_title']) ?></td>
                                <td><span class="badge bg-secondary-subtle text-secondary">Sec <?= e($slot['section_name']) ?></span></td>
                                <td><span class="badge bg-light text-dark border"><i class="bi bi-geo-alt me-1"></i><?= e($slot['room_number']) ?></span></td>
                                <td><?= e(($slot['teacher_first_name'] ?? '') . ' ' . ($slot['teacher_last_name'] ?? '')) ?></td>
                                <td>
                                    <span class="badge <?= $slot['class_type'] === 'lab' ? 'bg-teal-subtle text-success border' : 'bg-primary-subtle text-primary border' ?>">
                                        <?= ucfirst($slot['class_type']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
