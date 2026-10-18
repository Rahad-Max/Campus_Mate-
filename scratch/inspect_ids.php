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
$data = [
    'students_cols' => $db->fetchAll('DESCRIBE students'),
    'students' => $db->fetchAll('SELECT * FROM students LIMIT 2'),
    'teachers' => $db->fetchAll('SELECT * FROM teachers LIMIT 2'),
    'active_sem' => $db->fetch('SELECT * FROM semesters WHERE is_current=1'),
    'advisors' => $db->fetchAll('SELECT * FROM academic_advisors LIMIT 5'),
    'offerings' => $db->fetchAll('SELECT co.id as offering_id, co.course_id, c.course_code, c.title, c.credit FROM course_offerings co JOIN courses c ON c.id=co.course_id LIMIT 5'),
    'sections' => $db->fetchAll('SELECT s.id, s.course_offering_id, s.section_name, s.capacity, s.enrolled_count FROM sections s LIMIT 5'),
    'prereqs' => $db->fetchAll('SELECT cp.course_id, c1.course_code, cp.prerequisite_course_id, c2.course_code as prereq_code FROM course_prerequisites cp JOIN courses c1 ON c1.id=cp.course_id JOIN courses c2 ON c2.id=cp.prerequisite_course_id LIMIT 5')
];

echo json_encode($data, JSON_PRETTY_PRINT);

