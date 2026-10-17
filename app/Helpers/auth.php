<?php
/**
 * Authentication Helpers
 * PUB CampusMate
 */

if (!function_exists('auth_check')) {
    function auth_check(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return !empty($_SESSION['user']['id']);
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
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
