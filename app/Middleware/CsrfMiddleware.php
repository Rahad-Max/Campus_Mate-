<?php
namespace App\Middleware;

class CsrfMiddleware
{
    public function handle(?string $param = null): void
    {
        ensure_session_started();

        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            $submittedToken = $_POST['_csrf_token'] 
                ?? $_SERVER['HTTP_X_CSRF_TOKEN'] 
                ?? null;

            $sessionToken = $_SESSION['_csrf_token'] ?? null;

            if (empty($sessionToken) || empty($submittedToken) || !hash_equals($sessionToken, $submittedToken)) {
                if ($this->isAjaxRequest()) {
                    http_response_code(419);
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'CSRF Token Mismatch or Expired Session.']);
                    exit;
                }

                $_SESSION['_flash']['danger'] = 'Security validation failed (CSRF expired). Please refresh and try again.';
                header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));
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
