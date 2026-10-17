<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-1">My Assigned Classes</h2>
        <p class="text-muted mb-0">Active section assignments and student rosters for <strong><?= e($semester['name'] ?? 'Summer 2026') ?></strong>.</p>
    </div>
</div>

<div class="card card-pub">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Course Code</th>
                        <th>Course Title</th>
                        <th>Credit</th>
                        <th>Section</th>
                        <th>Role</th>
                        <th>Capacity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sections)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No sections assigned yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($sections as $s): ?>
                            <tr>
                                <td class="fw-bold text-primary"><?= e($s['course_code']) ?></td>
                                <td><?= e($s['course_title']) ?></td>
                                <td><?= number_format((float)$s['credit'], 1) ?></td>
                                <td><span class="badge bg-secondary-subtle text-secondary">Sec <?= e($s['section_name']) ?></span></td>
                                <td><span class="badge bg-info-subtle text-info border"><?= ucwords(str_replace('_', ' ', $s['role'])) ?></span></td>
                                <td><?= (int)$s['enrolled_count'] ?> / <?= (int)$s['capacity'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
