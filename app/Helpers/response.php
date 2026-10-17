<?php
/**
 * Response, View, and URL Helpers
 * PUB CampusMate
 */

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $base = rtrim($_ENV['APP_URL'] ?? '', '/');
        $cleanPath = ltrim($path, '/');
        if (empty($base)) {
            return '/' . $cleanPath;
        }
        return $base . '/' . $cleanPath;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = rtrim($_ENV['APP_URL'] ?? '', '/');
        $cleanPath = ltrim($path, '/');
        if (empty($path)) {
            return $base ?: '/';
        }
        return ($base ?: '') . '/' . $cleanPath;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
}

if (!function_exists('flash')) {
    function flash(?string $type = null): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($type === null) {
            if (!empty($_SESSION['_flash'])) {
                $flashes = $_SESSION['_flash'];
                unset($_SESSION['_flash']);
                return json_encode($flashes);
            }
            return null;
        }

        if (isset($_SESSION['_flash'][$type])) {
            $msg = $_SESSION['_flash'][$type];
            unset($_SESSION['_flash'][$type]);
            return $msg;
        }

        return null;
    }
}

if (!function_exists('has_flash')) {
    function has_flash(string $type): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return !empty($_SESSION['_flash'][$type]);
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $val = $_SESSION['_old'][$key] ?? $default;
        return $val;
    }
}
