<div class="row g-4">
    <div class="col-lg-4">
        <div class="card card-pub text-center p-4">
            <div class="avatar-circle bg-pub-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center fw-bold mb-3" style="width: 84px; height: 84px; font-size: 2rem;">
                <?= strtoupper(substr($student['first_name'] ?? 'S', 0, 1)) ?>
            </div>
            <h4 class="fw-bold mb-1"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h4>
            <div class="badge-role student mb-2">Student</div>
            <div class="text-muted small mb-3">ID: <strong><?= e($student['student_id_card'] ?? '—') ?></strong></div>

            <div class="list-group list-group-flush text-start small border-top pt-3">
                <div class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Department</span>
                    <span class="fw-semibold"><?= e($student['department_name'] ?? 'CSE') ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Batch</span>
                    <span class="fw-semibold"><?= e($student['batch_no'] ?? '—') ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Academic Standing</span>
                    <span class="status-pill completed"><?= ucfirst($student['academic_status'] ?? 'regular') ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Academic Advisor</span>
                    <span class="fw-semibold"><?= e($student['advisor_first_name'] ? $student['advisor_first_name'] . ' ' . $student['advisor_last_name'] : 'TBA') ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card card-pub">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold">Academic Transcript Overview</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Course</th>
                                <th>Title</th>
                                <th>Credit</th>
                                <th>Grade</th>
                                <th>Grade Point</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $h): ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?= e($h['course_code']) ?></td>
                                    <td><?= e($h['course_title']) ?></td>
                                    <td><?= number_format((float)$h['credits_earned'], 1) ?></td>
                                    <td><span class="badge bg-light text-dark border fw-bold"><?= e($h['grade_letter']) ?></span></td>
                                    <td><?= number_format((float)$h['grade_point'], 2) ?></td>
                                    <td><span class="status-pill completed"><?= ucfirst($h['status']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
