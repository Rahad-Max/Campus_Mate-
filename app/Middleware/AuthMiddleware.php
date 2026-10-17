<?php
namespace App\Middleware;

class AuthMiddleware
{
    public function handle(?string $param = null): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user']) || empty($_SESSION['user']['id'])) {
            if ($this->isAjaxRequest()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
                exit;
            }

            $_SESSION['_flash']['warning'] = 'Authentication required. Please sign in to continue.';
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? '/';
            
            // Redirect to login
            $baseUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
            header("Location: /login");
            exit;
        }
    }

    private function isAjaxRequest(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'));
    }
}
