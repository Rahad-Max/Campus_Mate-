<?php
/**
 * Authentication Helpers
 * PUB CampusMate
 */

if (!function_exists('ensure_session_started')) {
    function ensure_session_started(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $sessionName = $_ENV['SESSION_COOKIE_NAME'] ?? 'pub_campusmate_session';
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $sessionName)) {
            $sessionName = 'pub_campusmate_session';
        }

        if (session_name() !== $sessionName && !headers_sent()) {
            session_name($sessionName);
        }

        // Sanitize incoming session cookies before PHP's session engine inspects them
        foreach ([$sessionName, 'PHPSESSID'] as $cookieKey) {
            if (isset($_COOKIE[$cookieKey])) {
                $rawId = (string)$_COOKIE[$cookieKey];
                // PHP session IDs strictly allow ONLY [a-zA-Z0-9,-] and length 16 to 128
                if (!preg_match('/^[a-zA-Z0-9,-]{16,128}$/', $rawId)) {
                    unset($_COOKIE[$cookieKey]);
                    if (!headers_sent()) {
                        setcookie($cookieKey, '', [
                            'expires' => time() - 3600,
                            'path' => '/',
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ]);
                    }
                }
            }
        }

        // Ensure current session_id() is valid before starting
        $sid = session_id();
        if ($sid !== '' && !preg_match('/^[a-zA-Z0-9,-]{16,128}$/', $sid)) {
            session_id('');
        }

        if (!headers_sent()) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_samesite', 'Lax');
        }

        try {
            @session_start();
        } catch (\Throwable $e) {
            // Silently catch and retry with clean ID
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            if (!headers_sent()) {
                session_id(bin2hex(random_bytes(16)));
            }
            @session_start();
        }
    }
}

if (!function_exists('auth_check')) {
    function auth_check(): bool
    {
        ensure_session_started();
        return !empty($_SESSION['user']['id']);
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): ?array
    {
        ensure_session_started();
        return $_SESSION['user'] ?? null;
    }
}

if (!function_exists('auth_id')) {
    function auth_id(): ?int
    {
        $user = auth_user();
        return $user ? (int)$user['id'] : null;
    }
}

if (!function_exists('auth_role')) {
    function auth_role(): ?string
    {
        $user = auth_user();
        return $user['role'] ?? null;
    }
}

if (!function_exists('has_role')) {
    function has_role(string|array $roles): bool
    {
        $currentRole = auth_role();
        if (!$currentRole) {
            return false;
        }

        $roles = (array)$roles;
        return in_array($currentRole, $roles, true);
    }
}

if (!function_exists('auth_name')) {
    function auth_name(): string
    {
        $user = auth_user();
        if (!$user) {
            return 'Guest';
        }
        return trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
    }
}
