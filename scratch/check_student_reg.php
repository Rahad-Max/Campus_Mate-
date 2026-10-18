<?php
define('ROOT_PATH', dirname(__DIR__));
// Load .env
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim(trim($value), "\"'");
        }
    }
}
require_once ROOT_PATH . '/app/Core/Database.php';
$db = \App\Core\Database::getInstance();
$reqs = $db->fetchAll('SELECT id, student_id, status FROM registration_requests');
echo "Registration Requests:\n";
print_r($reqs);
