<?php
/**
 * PUB CampusMate — Demonstration Accounts Seeder
 * Safe, idempotent seeder ensuring all demo accounts exist.
 * Command: php scripts/seed_demo_accounts.php
 */

declare(strict_types=1);

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

// Built-in Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = ROOT_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require $file;
});

use App\Core\Database;

echo "========================================================\n";
echo " PUB CampusMate — Demonstration Accounts Seeder\n";
echo "========================================================\n";

$db = Database::getInstance();
$passwordHash = password_hash('Password@123', PASSWORD_BCRYPT, ['cost' => 10]);

// Seed / Update Accounts
$accounts = [
    [
        'id' => 1,
        'username' => 'student21',
        'email' => 'student@pub.ac.bd',
        'role' => 'student',
        'first_name' => 'Rahat',
        'last_name' => 'Rahman',
        'phone' => '+8801710000001',
        'avatar_url' => 'assets/images/student.png',
        'is_active' => 1
    ],
    [
        'id' => 2,
        'username' => 'tamanna22',
        'email' => 'tamanna@pub.ac.bd',
        'role' => 'student',
        'first_name' => 'Tamanna',
        'last_name' => 'Akter',
        'phone' => '+8801710000002',
        'avatar_url' => 'assets/images/student2.png',
        'is_active' => 1
    ],
    [
        'id' => 3,
        'username' => 'prof_tanvir',
        'email' => 'teacher@pub.ac.bd',
        'role' => 'teacher',
        'first_name' => 'Tanvir',
        'last_name' => 'Ahmed',
        'phone' => '+8801720000001',
        'avatar_url' => 'assets/images/teacher.png',
        'is_active' => 1
    ],
    [
        'id' => 4,
        'username' => 'advisor_kazi',
        'email' => 'advisor@pub.ac.bd',
        'role' => 'academic_advisor',
        'first_name' => 'Dr. Kazi',
        'last_name' => 'Nahid',
        'phone' => '+8801730000001',
        'avatar_url' => 'assets/images/advisor.png',
        'is_active' => 1
    ],
    [
        'id' => 5,
        'username' => 'coord_hasan',
        'email' => 'coordinator@pub.ac.bd',
        'role' => 'department_coordinator',
        'first_name' => 'Dr. Mahmudul',
        'last_name' => 'Hasan',
        'phone' => '+8801740000001',
        'avatar_url' => 'assets/images/coordinator.png',
        'is_active' => 1
    ],
    [
        'id' => 6,
        'username' => 'inactive_demo',
        'email' => 'inactive@pub.ac.bd',
        'role' => 'student',
        'first_name' => 'Inactive',
        'last_name' => 'DemoAccount',
        'phone' => '+8801710000099',
        'avatar_url' => 'assets/images/student.png',
        'is_active' => 0
    ]
];

foreach ($accounts as $acc) {
    $existing = $db->fetch("SELECT id FROM users WHERE id = :id OR email = :email OR username = :username", [
        ':id' => $acc['id'],
        ':email' => $acc['email'],
        ':username' => $acc['username']
    ]);

    if ($existing) {
        $db->execute(
            "UPDATE users SET password_hash = :pw, role = :role, first_name = :fn, last_name = :ln, is_active = :act WHERE id = :id",
            [
                ':pw' => $passwordHash,
                ':role' => $acc['role'],
                ':fn' => $acc['first_name'],
                ':ln' => $acc['last_name'],
                ':act' => $acc['is_active'],
                ':id' => $existing['id']
            ]
        );
        echo " [OK] Verified user '{$acc['username']}' ({$acc['role']}, active=" . $acc['is_active'] . ")\n";
    } else {
        $db->execute(
            "INSERT INTO users (id, username, email, password_hash, role, first_name, last_name, phone, avatar_url, is_active)
             VALUES (:id, :username, :email, :pw, :role, :fn, :ln, :phone, :avatar, :act)",
            [
                ':id' => $acc['id'],
                ':username' => $acc['username'],
                ':email' => $acc['email'],
                ':pw' => $passwordHash,
                ':role' => $acc['role'],
                ':fn' => $acc['first_name'],
                ':ln' => $acc['last_name'],
                ':phone' => $acc['phone'],
                ':avatar' => $acc['avatar_url'],
                ':act' => $acc['is_active']
            ]
        );
        echo " [OK] Created user '{$acc['username']}' ({$acc['role']}, active=" . $acc['is_active'] . ")\n";
    }
}

// Ensure Inactive Student record exists
$existingStudent = $db->fetch("SELECT id FROM students WHERE user_id = 6");
if (!$existingStudent) {
    $db->execute(
        "INSERT INTO students (id, user_id, department_id, student_id_card, batch_no, admission_semester_id, current_semester_level, cgpa, completed_credits, academic_status)
         VALUES (3, 6, 1, '200101099', 'Batch 20', 1, 4, 2.10, 54.00, 'suspended')"
    );
    echo " [OK] Created inactive student record for User #6\n";
} else {
    echo " [OK] Inactive student record already exists for User #6\n";
}

echo "\nResult: All demo accounts verified and ready for local development!\n";
