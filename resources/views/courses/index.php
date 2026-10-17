<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-1">Master Course Catalog</h2>
        <p class="text-muted mb-0">Browse approved academic courses, credit hours, and syllabus outlines for PUB programs.</p>
    </div>
</div>

<div class="card card-pub">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Course Title</th>
                        <th>Credits</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th class="text-end">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $c): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= e($c['course_code']) ?></td>
                            <td>
                                <div class="fw-semibold"><?= e($c['title']) ?></div>
                                <div class="text-muted small text-truncate" style="max-width: 400px;"><?= e($c['description'] ?? 'No description available.') ?></div>
                            </td>
                            <td><span class="badge bg-primary-subtle text-primary"><?= number_format((float)$c['credit'], 1) ?> cr</span></td>
                            <td><span class="badge bg-light text-dark border"><?= ucfirst($c['course_type']) ?></span></td>
                            <td><span class="status-pill completed"><?= ucfirst($c['status']) ?></span></td>
                            <td class="text-end">
                                <a href="/courses/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
