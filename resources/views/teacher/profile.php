<div class="row g-4">
    <div class="col-lg-4">
        <div class="card card-pub text-center p-4">
            <div class="avatar-circle bg-pub-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center fw-bold mb-3" style="width: 84px; height: 84px; font-size: 2rem;">
                <?= strtoupper(substr($teacher['first_name'] ?? 'T', 0, 1)) ?>
            </div>
            <h4 class="fw-bold mb-1"><?= e($teacher['first_name'] . ' ' . $teacher['last_name']) ?></h4>
            <div class="badge-role teacher mb-2"><?= e($teacher['designation']) ?></div>
            <div class="text-muted small mb-3">Teacher ID: <strong><?= e($teacher['teacher_id_card'] ?? '—') ?></strong></div>

            <div class="list-group list-group-flush text-start small border-top pt-3">
                <div class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Office</span>
                    <span class="fw-semibold"><?= e($teacher['office_room'] ?? 'TBA') ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Max Load</span>
                    <span class="fw-semibold"><?= number_format((float)$teacher['max_credit_load'], 1) ?> cr</span>
                </div>
                <div class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Consultation Hours</span>
                    <span class="fw-semibold"><?= e($teacher['office_hours'] ?? 'By Appointment') ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card card-pub mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold">Biography & Academic Background</h5>
            </div>
            <div class="card-body">
                <h6 class="fw-bold text-muted mb-2">About</h6>
                <p class="text-secondary mb-4"><?= e($teacher['bio'] ?? 'No biography submitted.') ?></p>

                <h6 class="fw-bold text-muted mb-2">Education & Credentials</h6>
                <p class="text-secondary mb-4"><?= e($teacher['education'] ?? '—') ?></p>

                <h6 class="fw-bold text-muted mb-2">Research Interests</h6>
                <p class="text-secondary mb-0"><?= e($teacher['research_interests'] ?? '—') ?></p>
            </div>
        </div>
    </div>
</div>
