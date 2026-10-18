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

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = rtrim($_ENV['APP_URL'] ?? '', '/');
        if (empty($base) && isset($_SERVER['SCRIPT_NAME'])) {
            $dir = dirname($_SERVER['SCRIPT_NAME']);
            $base = ($dir === '/' || $dir === '\\') ? '' : rtrim(str_replace('\\', '/', $dir), '/');
        }
        $cleanPath = ltrim($path, '/');
        if (empty($cleanPath)) {
            return $base ?: '/';
        }
        return ($base ? $base . '/' : '/') . $cleanPath;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url($path);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void
    {
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            $url = url($url);
        }
        header("Location: {$url}");
        exit;
    }
}

if (!function_exists('flash')) {
    function flash(?string $type = null): ?string
    {
        ensure_session_started();

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
        ensure_session_started();
        return !empty($_SESSION['_flash'][$type]);
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        ensure_session_started();
        $val = $_SESSION['_old'][$key] ?? $default;
        return $val;
    }
}
