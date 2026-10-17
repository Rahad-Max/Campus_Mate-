<div class="auth-brand">
    <div class="brand-logo">
        <i class="bi bi-shield-lock-fill"></i>
    </div>
    <h3 class="fw-bold">Password Recovery</h3>
    <p class="text-muted small">Enter your university email address to receive password reset instructions.</p>
</div>

<form action="/login" method="GET" class="auth-form">
    <div class="mb-3">
        <label for="resetEmail" class="form-label">University Email</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control" id="resetEmail" placeholder="your.name@pub.ac.bd" required>
        </div>
    </div>

    <button type="submit" class="btn btn-pub w-100 py-2 mb-3">
        Send Recovery Link
    </button>

    <div class="text-center">
        <a href="/login" class="text-decoration-none small text-pub-primary">
            <i class="bi bi-arrow-left me-1"></i> Back to Sign In
        </a>
    </div>
</form>
