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

echo "Applying Phase 5 database migration...\n";
$db = \App\Core\Database::getInstance();
$pdo = \App\Core\Database::pdo();

try {
    // 1. Check & add class_schedules.status
    $cols = array_column($db->fetchAll("DESCRIBE `class_schedules`"), 'Field');
    if (!in_array('status', $cols, true)) {
        $db->query("ALTER TABLE `class_schedules` ADD COLUMN `status` ENUM('draft', 'published', 'revised', 'cancelled') NOT NULL DEFAULT 'published' AFTER `class_type`, ADD INDEX `idx_cs_status` (`status`)");
        echo "  [+] Added 'status' column to class_schedules table.\n";
    } else {
        echo "  [*] class_schedules.status already exists.\n";
    }

    // 2. Check & add makeup_classes.class_schedule_id
    $mcCols = array_column($db->fetchAll("DESCRIBE `makeup_classes`"), 'Field');
    if (!in_array('class_schedule_id', $mcCols, true)) {
        $db->query("ALTER TABLE `makeup_classes` ADD COLUMN `class_schedule_id` INT NULL AFTER `section_id`, ADD CONSTRAINT `fk_mc_schedule` FOREIGN KEY (`class_schedule_id`) REFERENCES `class_schedules` (`id`) ON DELETE SET NULL ON UPDATE CASCADE");
        echo "  [+] Added 'class_schedule_id' column to makeup_classes table.\n";
    } else {
        echo "  [*] makeup_classes.class_schedule_id already exists.\n";
    }

    // 3. Update exams.status
    $db->query("ALTER TABLE `exams` MODIFY COLUMN `status` ENUM('draft', 'published', 'scheduled', 'in_progress', 'completed', 'postponed', 'cancelled') NOT NULL DEFAULT 'scheduled'");
    echo "  [+] Updated exams.status ENUM definition to support draft and published states.\n";

    echo "Phase 5 database migration completed successfully!\n";
} catch (\Throwable $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
