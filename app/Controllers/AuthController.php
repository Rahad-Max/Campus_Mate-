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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!empty($_SESSION['user'])) {
            $role = $_SESSION['user']['role'];
            $this->redirect(match ($role) {
                'student' => '/dashboard/student',
                'teacher' => '/dashboard/teacher',
                'academic_advisor' => '/dashboard/advisor',
                'department_coordinator' => '/dashboard/coordinator',
                default => '/'
            });
        }

        $this->render('auth/login', [
            'title' => 'Sign In — PUB CampusMate'
        ], 'layouts/auth');
    }

    public function login(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $data = $this->validate($_POST, [
            'identifier' => 'required',
            'password' => 'required'
        ]);

        $identifier = $data['identifier'];
        $password = $data['password'];

        $user = $this->userModel->findByCredentials($identifier);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION['_flash']['danger'] = 'Invalid email/username or password.';
            $_SESSION['_old']['identifier'] = $identifier;
            $this->redirect('/login');
            return;
        }

        // Authentication Success
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'avatar_url' => $user['avatar_url']
        ];

        // Update last login
        $this->userModel->update((int)$user['id'], [
            'last_login_at' => date('Y-m-d H:i:s')
        ]);

        // Audit Log
        Database::getInstance()->query(
            "INSERT INTO `audit_logs` (`user_id`, `role`, `ip_address`, `user_agent`, `action`, `entity_type`, `entity_id`)
             VALUES (:uid, :role, :ip, :ua, 'auth.login', 'users', :uid2)",
            [
                ':uid' => $user['id'],
                ':role' => $user['role'],
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                ':ua' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                ':uid2' => $user['id']
            ]
        );

        $_SESSION['_flash']['success'] = "Welcome back, {$user['first_name']}!";

        $redirectUrl = match ($user['role']) {
            'student' => '/dashboard/student',
            'teacher' => '/dashboard/teacher',
            'academic_advisor' => '/dashboard/advisor',
            'department_coordinator' => '/dashboard/coordinator',
            default => '/'
        };

        $this->redirect($redirectUrl);
    }

    public function showForgotPassword(): void
    {
        $this->render('auth/forgot-password', [
            'title' => 'Reset Password — PUB CampusMate'
        ], 'layouts/auth');
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
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

        session_start();
        $_SESSION['_flash']['info'] = 'You have been successfully signed out.';
        $this->redirect('/login');
    }
}
