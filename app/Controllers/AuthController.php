<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\User;

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function showLogin(): void
    {
        ensure_session_started();

        if (!empty($_SESSION['user']['id'])) {
            $role = $_SESSION['user']['role'] ?? '';
            $this->redirect(match ($role) {
                'student' => '/student/dashboard',
                'teacher' => '/teacher/dashboard',
                'academic_advisor' => '/advisor/dashboard',
                'department_coordinator' => '/coordinator/dashboard',
                default => '/dashboard'
            });
            return;
        }

        $this->render('auth/login', [
            'title' => 'Sign In — PUB CampusMate'
        ], 'layouts/auth');
    }

    public function login(): void
    {
        ensure_session_started();

        $data = $this->validate($_POST, [
            'identifier' => 'required',
            'password' => 'required'
        ]);

        $identifier = trim((string)($data['identifier'] ?? ''));
        $password = (string)($data['password'] ?? '');

        // 1. Retrieve user by email or username using prepared statements
        $user = $this->userModel->findByIdentifier($identifier);

        // 2. Generic error message for non-existent users or password mismatches
        // Prevents user enumeration / email disclosure
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION['_flash']['danger'] = 'Invalid email/username or password.';
            $_SESSION['_old']['identifier'] = $identifier;
            $this->redirect('/login');
            return;
        }

        // 3. Prevent inactive or suspended accounts from logging in
        if (!$this->userModel->isAccountActive($user)) {
            $_SESSION['_flash']['warning'] = 'Your account has been deactivated or suspended. Please contact the department administration.';
            $_SESSION['_old']['identifier'] = $identifier;
            $this->redirect('/login');
            return;
        }

        // 4. Regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);

        // 5. Store sanitized identity in session
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'avatar_url' => $user['avatar_url']
        ];

        // 6. Update last login timestamp
        $this->userModel->update((int)$user['id'], [
            'last_login_at' => date('Y-m-d H:i:s')
        ]);

        // 7. Secure Audit Log
        try {
            Database::getInstance()->query(
                "INSERT INTO `audit_logs` (`user_id`, `role`, `ip_address`, `user_agent`, `action`, `entity_type`, `entity_id`)
                 VALUES (:uid, :role, :ip, :ua, 'auth.login', 'users', :uid2)",
                [
                    ':uid' => (int)$user['id'],
                    ':role' => $user['role'],
                    ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                    ':ua' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                    ':uid2' => (int)$user['id']
                ]
            );
        } catch (\Throwable $e) {
            error_log("Audit log failed: " . $e->getMessage());
        }

        $_SESSION['_flash']['success'] = "Welcome back, {$user['first_name']}!";

        // 8. Determine target dashboard based on validated role
        $defaultRoute = match ($user['role']) {
            'student' => '/student/dashboard',
            'teacher' => '/teacher/dashboard',
            'academic_advisor' => '/advisor/dashboard',
            'department_coordinator' => '/coordinator/dashboard',
            default => '/dashboard'
        };

        // 9. Open-Redirect Validation
        $redirectUrl = $defaultRoute;
        if (!empty($_SESSION['intended_url'])) {
            $intended = (string)$_SESSION['intended_url'];
            unset($_SESSION['intended_url']);

            // Validate intended URL is a safe local relative path
            if (str_starts_with($intended, '/') &&
                !str_starts_with($intended, '//') &&
                !str_contains($intended, ':\\') &&
                !in_array($intended, ['/login', '/logout', '/forgot-password'], true)) {
                $redirectUrl = $intended;
            }
        }

        $this->redirect($redirectUrl);
    }

    public function showForgotPassword(): void
    {
        $this->render('auth/forgot-password', [
            'title' => 'Reset Password — PUB CampusMate'
        ], 'layouts/auth');
    }

    public function sendPasswordReset(): void
    {
        ensure_session_started();

        $data = $this->validate($_POST, [
            'email' => 'required|email'
        ]);

        $email = trim((string)($data['email'] ?? ''));

        // Always show generic confirmation to prevent user enumeration
        $_SESSION['_flash']['success'] = 'If an account exists with that email address, password reset instructions have been dispatched.';
        $this->redirect('/login');
    }

    public function logout(): void
    {
        ensure_session_started();

        $userId = $_SESSION['user']['id'] ?? null;
        $role = $_SESSION['user']['role'] ?? null;

        if ($userId) {
            try {
                Database::getInstance()->query(
                    "INSERT INTO `audit_logs` (`user_id`, `role`, `ip_address`, `user_agent`, `action`, `entity_type`, `entity_id`)
                     VALUES (:uid, :role, :ip, :ua, 'auth.logout', 'users', :uid2)",
                    [
                        ':uid' => (int)$userId,
                        ':role' => $role ?? 'unknown',
                        ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                        ':ua' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                        ':uid2' => (int)$userId
                    ]
                );
            } catch (\Throwable $e) {
                // Ignore audit failure on logout
            }
        }

        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        // Start fresh session safely to display logout confirmation flash message
        ensure_session_started();
        $_SESSION['_flash']['info'] = 'You have been safely signed out.';
        $this->redirect('/login');
    }
}
