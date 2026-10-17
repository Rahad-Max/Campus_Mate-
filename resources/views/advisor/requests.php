<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-1">Registration Approval Queue</h2>
        <p class="text-muted mb-0">Review prerequisite compliance, credit workloads, and approve advisee registration requests.</p>
    </div>
</div>

<div class="card card-pub">
    <div class="card-body p-0">
        <?php if (empty($requests)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 text-success"></i>
                <h5 class="fw-bold mt-2">All Caught Up!</h5>
                <p>No registration requests currently pending your approval.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Batch ID</th>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Standing</th>
                            <th>Requested Credits</th>
                            <th>Submitted At</th>
                            <th>Notes</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $req): ?>
                            <tr>
                                <td class="fw-bold">#<?= $req['id'] ?></td>
                                <td class="fw-bold text-primary"><?= e($req['student_id_card']) ?></td>
                                <td><?= e($req['student_first_name'] . ' ' . $req['student_last_name']) ?></td>
                                <td><span class="badge bg-light text-dark border">CGPA <?= number_format((float)$req['cgpa'], 2) ?></span></td>
                                <td><strong><?= number_format((float)$req['total_credits'], 1) ?></strong> cr</td>
                                <td><?= format_date($req['created_at']) ?></td>
                                <td><small class="text-muted"><?= e($req['submission_notes'] ?? 'None') ?></small></td>
                                <td class="text-end">
                                    <form action="/advisor/request/<?= $req['id'] ?>/approve" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="remarks" value="Prerequisites and workload verified. Approved.">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-check-lg me-1"></i> Approve
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
