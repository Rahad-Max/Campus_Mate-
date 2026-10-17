<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-1">Academic History & Grades</h2>
        <p class="text-muted mb-0">Official record of completed semester courses and grade points.</p>
    </div>
</div>

<div class="card card-pub">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Term</th>
                        <th>Course Code</th>
                        <th>Title</th>
                        <th>Credits Attempted</th>
                        <th>Credits Earned</th>
                        <th>Grade</th>
                        <th>Grade Point</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No academic history records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($history as $item): ?>
                            <tr>
                                <td><span class="badge bg-light text-dark border"><?= e($item['semester_name']) ?></span></td>
                                <td class="fw-bold text-primary"><?= e($item['course_code']) ?></td>
                                <td><?= e($item['course_title']) ?></td>
                                <td><?= number_format((float)$item['credits_attempted'], 1) ?></td>
                                <td><?= number_format((float)$item['credits_earned'], 1) ?></td>
                                <td><span class="badge bg-primary-subtle text-primary fw-bold"><?= e($item['grade_letter'] ?? '—') ?></span></td>
                                <td class="fw-bold"><?= number_format((float)($item['grade_point'] ?? 0.0), 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
