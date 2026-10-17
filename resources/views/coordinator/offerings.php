<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-1">Course Offerings & Capacity</h2>
        <p class="text-muted mb-0">Manage active courses and section allotments for <strong><?= e($semester['name'] ?? 'Summer 2026') ?></strong>.</p>
    </div>
</div>

<div class="card card-pub">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Title</th>
                        <th>Credit</th>
                        <th>Type</th>
                        <th>Section Count</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($offerings as $offering): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= e($offering['course_code']) ?></td>
                            <td><?= e($offering['course_title']) ?></td>
                            <td><?= number_format((float)$offering['credit'], 1) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= $offering['section_count'] ?> sections</span></td>
                            <td><span class="status-pill completed"><?= ucfirst($offering['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
