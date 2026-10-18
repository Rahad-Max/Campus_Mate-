<div class="auth-brand">
    <div class="brand-logo">
        <i class="bi bi-mortarboard-fill"></i>
    </div>
    <h3 class="fw-bold">PUB CampusMate</h3>
    <p class="text-muted small">Smart Campus Companion & Academic Planning<br><span class="badge bg-light text-primary border">Pundra University of Science & Technology</span></p>
</div>

<form action="<?= url('login') ?>" method="POST" class="auth-form" autocomplete="off" id="loginForm">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="identifier" class="form-label">Email Address or Username</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control" id="identifier" name="identifier" 
                   value="<?= e(old('identifier')) ?>" 
                   placeholder="e.g. student@pub.ac.bd or student21" required autofocus>
        </div>
    </div>

    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <label for="password" class="form-label">Password</label>
            <a href="<?= url('forgot-password') ?>" class="text-decoration-none small text-pub-primary">Forgot Password?</a>
        </div>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
            <button class="btn btn-outline-secondary" type="button" id="togglePasswordBtn" title="Toggle password visibility">
                <i class="bi bi-eye" id="togglePasswordIcon"></i>
            </button>
        </div>
    </div>

    <div class="mb-4 form-check">
        <input type="checkbox" class="form-check-input" id="rememberMe">
        <label class="form-check-label small text-muted" for="rememberMe">Remember my session on this device</label>
    </div>

    <button type="submit" class="btn btn-pub w-100 py-2">
        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to CampusMate
    </button>
</form>

<!-- Demo Credentials Helper Card -->
<div class="demo-credentials-card">
    <div class="fw-bold text-dark mb-2 d-flex align-items-center justify-content-between">
        <span><i class="bi bi-key-fill text-warning me-1"></i> One-Click Demo Accounts</span>
        <span class="badge bg-secondary-subtle text-secondary">Password@123</span>
    </div>
    <div class="d-flex flex-wrap gap-1 mb-2">
        <button type="button" class="btn btn-sm btn-outline-primary demo-fill-btn" data-user="student@pub.ac.bd">
            <i class="bi bi-person-fill"></i> Student
        </button>
        <button type="button" class="btn btn-sm btn-outline-success demo-fill-btn" data-user="teacher@pub.ac.bd">
            <i class="bi bi-person-workspace"></i> Teacher
        </button>
        <button type="button" class="btn btn-sm btn-outline-warning demo-fill-btn" data-user="advisor@pub.ac.bd">
            <i class="bi bi-person-check-fill"></i> Advisor
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary demo-fill-btn" data-user="coordinator@pub.ac.bd">
            <i class="bi bi-gear-wide-connected"></i> Coordinator
        </button>
    </div>
    <div class="border-top pt-2 mt-2">
        <button type="button" class="btn btn-xs btn-outline-danger demo-fill-btn w-100" data-user="inactive@pub.ac.bd" style="font-size: 0.75rem;">
            <i class="bi bi-slash-circle me-1"></i> Test Inactive Account (Verify Rejection)
        </button>
    </div>
</div>

<script>
document.querySelectorAll('.demo-fill-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('identifier').value = btn.dataset.user;
        document.getElementById('password').value = 'Password@123';
    });
});

const togglePasswordBtn = document.getElementById('togglePasswordBtn');
if (togglePasswordBtn) {
    togglePasswordBtn.addEventListener('click', function () {
        const passwordInput = document.getElementById('password');
        const icon = document.getElementById('togglePasswordIcon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });
}
</script>
