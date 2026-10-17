<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h2 class="fw-bold mb-1">Course Registration Portal</h2>
        <p class="text-muted mb-0">
            Term: <strong><?= e($semester['name'] ?? 'Summer 2026') ?></strong> &bull; 
            Registration Deadline: <strong><?= format_date($semester['registration_end'] ?? null, 'M d, Y H:i') ?></strong>
        </p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <span class="badge bg-success-subtle text-success border px-3 py-2 fs-6">
            <i class="bi bi-calendar-check me-1"></i> Registration Open
        </span>
    </div>
</div>

<!-- Policy Notice Alert -->
<div class="alert alert-info border d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-info-circle-fill fs-3 text-primary"></i>
    <div class="small">
        <strong>Credit Guidelines:</strong> Standard semester minimum is <strong>9.0 credits</strong> and maximum is <strong>18.0 credits</strong> (Probation limit: 12.0 credits). Selected courses will be submitted to your Academic Advisor for approval before enrollment is confirmed.
    </div>
</div>

<div class="row g-4">
    <!-- Available Offerings Form -->
    <div class="col-lg-8">
        <div class="card card-pub mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-check2-square text-primary me-2"></i>Select Courses & Sections
                </h5>
                <span class="small text-muted">Term Offerings</span>
            </div>
            <div class="card-body p-0">
                <form id="registrationForm" action="/registration/submit" method="POST">
                    <?= csrf_field() ?>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;">Select</th>
                                    <th>Course Code</th>
                                    <th>Course Title</th>
                                    <th>Credit</th>
                                    <th>Section</th>
                                    <th>Available Seats</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $offeringModel = new \App\Models\CourseOffering();
                                foreach ($offerings as $offering):
                                    $sections = $offeringModel->getSections((int)$offering['id']);
                                    foreach ($sections as $sec):
                                        $seatsLeft = (int)$sec['available_seats'];
                                        $isFull = $seatsLeft <= 0;
                                ?>
                                    <tr class="<?= $isFull ? 'table-light text-muted' : '' ?>">
                                        <td class="text-center">
                                            <input type="checkbox" name="sections[]" value="<?= $sec['id'] ?>"
                                                   class="form-check-input course-section-check"
                                                   data-credit="<?= $offering['credit'] ?>"
                                                   <?= $isFull ? 'disabled' : '' ?>>
                                        </td>
                                        <td class="fw-bold text-primary"><?= e($offering['course_code']) ?></td>
                                        <td><?= e($offering['course_title']) ?></td>
                                        <td><?= number_format((float)$offering['credit'], 1) ?> cr</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Sec <?= e($sec['section_name']) ?></span></td>
                                        <td>
                                            <?php if ($isFull): ?>
                                                <span class="badge bg-danger-subtle text-danger">Full (0 seats)</span>
                                            <?php else: ?>
                                                <span class="badge bg-success-subtle text-success"><?= $seatsLeft ?> seats vacant</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php 
                                    endforeach;
                                endforeach; 
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 bg-light border-top">
                        <div class="mb-3">
                            <label for="regNotes" class="form-label small fw-bold">Notes for Academic Advisor (Optional)</label>
                            <textarea class="form-control" id="regNotes" name="notes" rows="2" placeholder="e.g. Requesting regular 3rd semester core courses..."></textarea>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="fw-bold">
                                Selected: <span id="selectedCreditCount" class="text-primary fs-5">0.0</span> Credits
                            </div>
                            <button type="submit" id="btnSubmitRegistration" class="btn btn-pub" disabled>
                                <i class="bi bi-send-check me-1"></i> Submit to Advisor
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Registration Requests History -->
    <div class="col-lg-4">
        <div class="card card-pub mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-clock-history text-primary me-2"></i>Registration Requests
                </h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($requests)): ?>
                    <div class="p-4 text-center text-muted small">
                        No registration requests submitted this semester.
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($requests as $req): ?>
                            <div class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold">Batch #<?= $req['id'] ?></span>
                                    <span class="status-pill <?= $req['status'] === 'advisor_approved' ? 'completed' : 'pending' ?>">
                                        <?= ucwords(str_replace('_', ' ', $req['status'])) ?>
                                    </span>
                                </div>
                                <div class="small text-muted mb-1">
                                    Credits: <?= number_format((float)$req['total_credits'], 1) ?> cr &bull; <?= format_date($req['created_at']) ?>
                                </div>
                                <?php if (!empty($req['advisor_remarks'])): ?>
                                    <div class="small bg-light p-2 rounded border mt-2">
                                        <strong>Advisor:</strong> <?= e($req['advisor_remarks']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
