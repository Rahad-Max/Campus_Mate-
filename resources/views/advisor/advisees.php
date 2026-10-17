<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-1">My Assigned Advisees</h2>
        <p class="text-muted mb-0">Undergraduate students assigned for academic mentoring and course registration approval.</p>
    </div>
</div>

<div class="card card-pub">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Batch</th>
                        <th>CGPA</th>
                        <th>Completed Credits</th>
                        <th>Academic Status</th>
                        <th>Contact</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($advisees)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No advisees assigned.</td></tr>
                    <?php else: ?>
                        <?php foreach ($advisees as $adv): ?>
                            <tr>
                                <td class="fw-bold text-primary"><?= e($adv['student_id_card']) ?></td>
                                <td><?= e($adv['first_name'] . ' ' . $adv['last_name']) ?></td>
                                <td><?= e($adv['batch_no']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= number_format((float)$adv['cgpa'], 2) ?></span></td>
                                <td><?= number_format((float)$adv['completed_credits'], 1) ?> cr</td>
                                <td><span class="status-pill completed"><?= ucfirst($adv['academic_status']) ?></span></td>
                                <td><a href="mailto:<?= e($adv['email']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-envelope"></i> Email</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
