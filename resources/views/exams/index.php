<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-1">Examination Routine</h2>
        <p class="text-muted mb-0">Scheduled midterm, final, and quiz routines for <strong><?= e($semester['name'] ?? 'Summer 2026') ?></strong>.</p>
    </div>
</div>

<div class="card card-pub">
    <div class="card-body p-0">
        <?php if (empty($exams)): ?>
            <div class="text-center py-5">
                <i class="bi bi-file-earmark-check fs-1 text-muted"></i>
                <p class="text-muted mt-2">No examination schedules published yet for your registered courses.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Exam Type</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Course</th>
                            <th>Room</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exams as $ex): ?>
                            <tr>
                                <td><span class="badge bg-warning-subtle text-warning border fw-bold"><?= strtoupper($ex['exam_type']) ?></span></td>
                                <td class="fw-bold"><?= format_date($ex['exam_date']) ?></td>
                                <td><span class="badge bg-light text-dark border font-monospace"><?= format_time($ex['start_time']) ?> - <?= format_time($ex['end_time']) ?></span></td>
                                <td class="fw-bold text-primary"><?= e($ex['course_code']) ?> &bull; <?= e($ex['course_title']) ?></td>
                                <td><?= e($ex['room_number']) ?> (<?= e($ex['building']) ?>)</td>
                                <td><span class="status-pill completed"><?= ucfirst($ex['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
