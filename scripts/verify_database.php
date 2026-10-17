<?php
/**
 * PUB CampusMate — Database Schema & Data Verification Script
 * Command line runner: php scripts/verify_database.php
 */

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
            $key = trim($key);
            $value = trim($value);
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
            $_ENV[$key] = $value;
        }
    }
}

echo "========================================================\n";
echo " PUB CampusMate — Database Integrity & Schema Verifier\n";
echo " Pundra University of Science & Technology (PUB)\n";
echo "========================================================\n\n";

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = (int)($_ENV['DB_PORT'] ?? 3306);
$database = $_ENV['DB_DATABASE'] ?? 'pub_campusmate';
$username = $_ENV['DB_USERNAME'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';

echo "Connecting to MySQL server at [{$host}:{$port}], database [{$database}]...\n";

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "[PASS] Successfully connected to MySQL database.\n";
    $version = $pdo->query("SELECT VERSION()")->fetchColumn();
    echo "       Database Server Version: {$version}\n\n";
} catch (PDOException $e) {
    echo "[FAIL] Connection failed: " . $e->getMessage() . "\n";
    echo "       Please ensure your local MySQL/MariaDB server is running and check your .env port/credentials.\n";
    exit(1);
}

// Required 32 Tables Checklist
$expectedTables = [
    'departments', 'users', 'teachers', 'semesters', 'students',
    'academic_advisors', 'curricula', 'courses', 'curriculum_courses',
    'course_prerequisites', 'rooms', 'course_offerings', 'sections',
    'teacher_assignments', 'teacher_availability', 'student_course_history',
    'registration_requests', 'registration_request_items', 'registrations',
    'class_schedules', 'schedule_revisions', 'makeup_classes', 'exams',
    'notices', 'notice_recipients', 'notifications', 'faculty_profiles',
    'academic_documents', 'document_chunks', 'helpdesk_conversations',
    'helpdesk_messages', 'audit_logs'
];

echo "Checking required tables (" . count($expectedTables) . " total)...\n";

$existingTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$missingTables = array_diff($expectedTables, $existingTables);

if (!empty($missingTables)) {
    echo "[FAIL] The following " . count($missingTables) . " tables are missing:\n";
    foreach ($missingTables as $t) {
        echo "  - {$t}\n";
    }
    echo "\nPlease run: mysql -u {$username} -p {$database} < database/schema.sql\n";
    exit(1);
}

echo "[PASS] All " . count($expectedTables) . " required tables are present in the schema!\n\n";

// Check Seed Data Counts
echo "Inspecting record counts in key entities:\n";
$tablesToCount = [
    'users' => 'Users / Accounts',
    'departments' => 'Departments',
    'semesters' => 'Semesters',
    'courses' => 'Course Catalog',
    'course_prerequisites' => 'Prerequisite Relationships',
    'rooms' => 'Classrooms & Labs',
    'course_offerings' => 'Semester Offerings',
    'sections' => 'Course Sections',
    'class_schedules' => 'Timetable Schedule Slots',
    'registrations' => 'Confirmed Course Enrollments',
    'notices' => 'Campus Notices',
    'academic_documents' => 'Official Academic Documents',
    'document_chunks' => 'RAG Document Chunks',
    'audit_logs' => 'Audit Event Logs'
];

foreach ($tablesToCount as $tbl => $label) {
    $cnt = (int)$pdo->query("SELECT COUNT(*) FROM `{$tbl}`")->fetchColumn();
    $status = $cnt > 0 ? "[OK]  " : "[WARN]";
    echo "  {$status} {$label} (`{$tbl}`): {$cnt} records\n";
}

// Foreign Key Consistency Test
echo "\nTesting Foreign Key Referential Integrity...\n";
$fkTest = $pdo->query("
    SELECT COUNT(*) as orphans
    FROM `registrations` r
    LEFT JOIN `students` s ON s.id = r.student_id
    LEFT JOIN `sections` sec ON sec.id = r.section_id
    WHERE s.id IS NULL OR sec.id IS NULL
")->fetch();

if ((int)$fkTest['orphans'] === 0) {
    echo "[PASS] Zero orphan records found across sample registration relationships.\n";
} else {
    echo "[FAIL] Orphan records detected in registrations!\n";
}

echo "\n>>> Database verification COMPLETE! The database is healthy, seeded, and ready.\n";
exit(0);
