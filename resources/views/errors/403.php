<div class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card card-pub border-0 shadow-sm p-4 p-md-5">
                <div class="mb-3">
                    <span class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bi bi-shield-slash-fill fs-1"></i>
                    </span>
                </div>
                <h1 class="display-5 fw-bold text-dark mb-2">403</h1>
                <h3 class="fw-semibold text-danger mb-3">Access Forbidden</h3>
                <p class="text-muted mb-4">
                    You do not have permission to access this academic resource. Your current authenticated role is 
                    <span class="badge bg-secondary-subtle text-secondary fs-6"><?= ucwords(str_replace('_', ' ', $userRole ?? 'Unknown')) ?></span>.
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="<?= e($dashboardRoute ?? '/dashboard') ?>" class="btn btn-pub px-4">
                        <i class="bi bi-speedometer2 me-1"></i> Return to My Dashboard
                    </a>
                    <a href="/logout" class="btn btn-outline-danger px-4">
                        <i class="bi bi-box-arrow-right me-1"></i> Switch Account
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
