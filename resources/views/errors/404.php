<div class="text-center py-5">
    <div class="display-1 fw-bold text-pub-primary mb-2">404</div>
    <h3 class="fw-bold mb-3">Page Not Found</h3>
    <p class="text-muted max-w-md mx-auto mb-4" style="max-width: 500px;">
        The requested resource at <code><?= e($uri ?? '') ?></code> was not found on this server. It may have been moved, renamed, or you might not have appropriate role access.
    </p>
    <div class="d-flex justify-content-center gap-2">
        <a href="/dashboard" class="btn btn-pub">
            <i class="bi bi-house-door me-1"></i> Go to Dashboard
        </a>
        <a href="/login" class="btn btn-outline-secondary">
            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
        </a>
    </div>
</div>
