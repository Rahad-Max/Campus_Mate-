<?php
namespace App\Core;

use App\Core\View;
use App\Exceptions\AppException;

abstract class Controller
{
    protected function render(string $view, array $data = [], ?string $layout = 'layouts/app'): void
    {
        View::render($view, $data, $layout);
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect(string $url): void
    {
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            $url = url($url);
        }
        header("Location: {$url}");
        exit;
    }

    protected function setFlash(string $type, string $message): void
    {
        ensure_session_started();
        $_SESSION['_flash'][$type] = $message;
    }

    protected function authUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        $sanitized = [];

        foreach ($rules as $field => $fieldRules) {
            $ruleList = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
            $val = $data[$field] ?? null;

            foreach ($ruleList as $rule) {
                if ($rule === 'required' && (is_null($val) || trim((string)$val) === '')) {
                    $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . " is required.";
                }

                if ($rule === 'email' && !empty($val) && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = "Invalid email format.";
                }

                if (str_starts_with($rule, 'min:') && !empty($val)) {
                    $min = (int)substr($rule, 4);
                    if (strlen((string)$val) < $min) {
                        $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min} characters.";
                    }
                }
            }

            $sanitized[$field] = is_string($val) ? trim($val) : $val;
        }

        if (!empty($errors)) {
            if ($this->isAjaxRequest()) {
                $this->json(['success' => false, 'errors' => $errors], 422);
            }
            $_SESSION['_flash']['danger'] = 'Please check the form for errors.';
            $_SESSION['_errors'] = $errors;
            $_SESSION['_old'] = $data;
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }

        return $sanitized;
    }

    protected function isAjaxRequest(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'));
    }
}
