<?php
namespace App\Middleware;

class RoleMiddleware
{
    public function handle(?string $requiredRoles = null): void
    {
        ensure_session_started();

        if (empty($_SESSION['user'])) {
            header("Location: /login");
            exit;
        }

        $userRole = $_SESSION['user']['role'] ?? '';

        if (!empty($requiredRoles)) {
            $allowed = array_map('trim', explode(',', $requiredRoles));

            if (!in_array($userRole, $allowed, true)) {
                http_response_code(403);

                if ($this->isAjaxRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'code' => 403,
                        'message' => 'Access Denied: Insufficient role permissions for this resource.'
                    ]);
                    exit;
                }

                $dashboardRoute = match ($userRole) {
                    'student' => '/student/dashboard',
                    'teacher' => '/teacher/dashboard',
                    'academic_advisor' => '/advisor/dashboard',
                    'department_coordinator' => '/coordinator/dashboard',
                    default => '/dashboard'
                };

                \App\Core\View::render('errors/403', [
                    'title' => '403 Forbidden — Access Denied',
                    'userRole' => $userRole,
                    'allowedRoles' => $allowed,
                    'dashboardRoute' => $dashboardRoute
                ], 'layouts/app');
                exit;
            }
        }
    }

    private function isAjaxRequest(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'));
    }
}
