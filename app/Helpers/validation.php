<?php
/**
 * Validation & Formatting Helpers
 * PUB CampusMate
 */

if (!function_exists('sanitize')) {
    function sanitize(mixed $data): mixed
    {
        if (is_array($data)) {
            return array_map('sanitize', $data);
        }
        if (is_string($data)) {
            return trim(strip_tags($data));
        }
        return $data;
    }
}

if (!function_exists('is_valid_email')) {
    function is_valid_email(string $email): bool
    {
        return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date, string $format = 'M d, Y'): string
    {
        if (empty($date)) {
            return '—';
        }
        try {
            return (new \DateTime($date))->format($format);
        } catch (\Exception) {
            return $date;
        }
    }
}

if (!function_exists('format_time')) {
    function format_time(?string $time, string $format = 'g:i A'): string
    {
        if (empty($time)) {
            return '—';
        }
        try {
            return (new \DateTime($time))->format($format);
        } catch (\Exception) {
            return $time;
        }
    }
}
