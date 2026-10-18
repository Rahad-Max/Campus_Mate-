<div class="auth-brand">
    <div class="brand-logo">
        <i class="bi bi-shield-lock-fill"></i>
    </div>
    <h3 class="fw-bold">Password Recovery</h3>
    <p class="text-muted small">Enter your university email address to receive password reset instructions.</p>
</div>

<form action="/forgot-password" method="POST" class="auth-form">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="resetEmail" class="form-label">University Email</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control" id="resetEmail" name="email" placeholder="your.name@pub.ac.bd" required autofocus>
        </div>
        <div class="form-text small text-muted">A password reset verification link will be dispatched to this registered email.</div>
    </div>

    <button type="submit" class="btn btn-pub w-100 py-2 mb-3">
        <i class="bi bi-send me-1"></i> Send Recovery Link
    </button>

    <div class="text-center">
        <a href="/login" class="text-decoration-none small text-pub-primary">
            <i class="bi bi-arrow-left me-1"></i> Back to Sign In
        </a>
    </div>
</form>
