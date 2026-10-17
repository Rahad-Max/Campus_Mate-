<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-1">Faculty Directory</h2>
        <p class="text-muted mb-0">Academic profiles, research interests, and office consultation hours for PUB faculty members.</p>
    </div>
</div>

<div class="row g-4">
    <?php foreach ($faculty as $f): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card card-pub h-100">
                <div class="card-body p-4 text-center">
                    <div class="avatar-circle bg-pub-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center fw-bold mb-3" style="width: 72px; height: 72px; font-size: 1.6rem;">
                        <?= strtoupper(substr($f['first_name'], 0, 1)) ?>
                    </div>
                    <h5 class="fw-bold mb-1"><?= e($f['first_name'] . ' ' . $f['last_name']) ?></h5>
                    <div class="text-primary fw-semibold small mb-1"><?= e($f['designation']) ?></div>
                    <div class="text-muted small mb-3"><?= e($f['department_name']) ?></div>

                    <div class="bg-light p-3 rounded text-start small mb-3 border">
                        <div class="mb-1"><strong><i class="bi bi-geo-alt me-1 text-muted"></i>Office:</strong> <?= e($f['office_room'] ?? 'TBA') ?></div>
                        <div class="mb-1"><strong><i class="bi bi-clock me-1 text-muted"></i>Consultation:</strong> <?= e($f['office_hours'] ?? 'By Appointment') ?></div>
                        <div><strong><i class="bi bi-envelope me-1 text-muted"></i>Email:</strong> <a href="mailto:<?= e($f['email']) ?>" class="text-decoration-none"><?= e($f['email']) ?></a></div>
                    </div>

                    <?php if (!empty($f['research_interests'])): ?>
                        <div class="text-start small">
                            <span class="fw-bold text-muted d-block mb-1">Research Areas:</span>
                            <span class="text-secondary"><?= e($f['research_interests']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
