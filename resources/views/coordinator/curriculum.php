<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-1">Academic Curriculum Structure</h2>
        <p class="text-muted mb-0"><strong><?= e($curriculum['name'] ?? 'Curriculum') ?></strong> (Version <?= e($curriculum['version'] ?? '1.0') ?> &bull; <?= number_format((float)($curriculum['total_credits_required'] ?? 148), 1) ?> Required Credits)</p>
    </div>
</div>

<div class="card card-pub">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Recommended Semester</th>
                        <th>Course Code</th>
                        <th>Course Title</th>
                        <th>Credits</th>
                        <th>Type</th>
                        <th>Elective</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $c): ?>
                        <tr>
                            <td><span class="badge bg-primary-subtle text-primary">Semester <?= $c['recommended_semester'] ?></span></td>
                            <td class="fw-bold text-primary"><?= e($c['course_code']) ?></td>
                            <td><?= e($c['title']) ?></td>
                            <td><?= number_format((float)$c['credit'], 1) ?> cr</td>
                            <td><span class="badge bg-light text-dark border"><?= ucfirst($c['course_type']) ?></span></td>
                            <td><?= $c['is_elective'] ? 'Yes (' . e($c['elective_group']) . ')' : 'Core Requirement' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
