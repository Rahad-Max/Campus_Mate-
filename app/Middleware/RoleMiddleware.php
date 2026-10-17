<?php
namespace App\Middleware;

class RoleMiddleware
{
    public function handle(?string $requiredRoles = null): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user'])) {
            header("Location: /login");
            exit;
        }

        $userRole = $_SESSION['user']['role'] ?? '';

        if (!empty($requiredRoles)) {
            $allowed = array_map('trim', explode(',', $requiredRoles));

            if (!in_array($userRole, $allowed, true)) {
                if ($this->isAjaxRequest()) {
                    http_response_code(403);
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Access Denied: Insufficient role permissions for this resource.'
                    ]);
                    exit;
                }

                $_SESSION['_flash']['danger'] = "Access Denied: You do not have permission to access that section. (Current role: " . ucwords(str_replace('_', ' ', $userRole)) . ")";
                
                // Redirect user to their own valid dashboard
                $dashboardRoute = match ($userRole) {
                    'student' => '/dashboard/student',
                    'teacher' => '/dashboard/teacher',
                    'academic_advisor' => '/dashboard/advisor',
                    'department_coordinator' => '/dashboard/coordinator',
                    default => '/'
                };

                header("Location: {$dashboardRoute}");
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
