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

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = ROOT_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $baseDir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require_once $file;
});

$db = \App\Core\Database::getInstance();
$tables = ['class_schedules', 'makeup_classes', 'exams', 'schedule_revisions'];
$info = [];
foreach ($tables as $t) {
    $info[$t] = array_map(fn($col) => $col['Field'] . ' (' . $col['Type'] . ')', $db->fetchAll("DESCRIBE `{$t}`"));
}

echo json_encode($info, JSON_PRETTY_PRINT);
