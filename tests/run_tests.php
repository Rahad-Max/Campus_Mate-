<?php
/**
 * PUB CampusMate — Standalone Automated Test Runner
 * Command line runner: php tests/run_tests.php
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

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = ROOT_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $baseDir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require_once $file;
});

// Helpers
require_once ROOT_PATH . '/app/Helpers/auth.php';
require_once ROOT_PATH . '/app/Helpers/csrf.php';
require_once ROOT_PATH . '/app/Helpers/validation.php';
require_once ROOT_PATH . '/app/Helpers/response.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "========================================================\n";
echo " PUB CampusMate — Automated Foundation Test Suite\n";
echo "========================================================\n\n";

$passed = 0;
$failed = 0;

function assertTest(string $name, bool $condition, string $details = ''): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$name}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$name} - {$details}\n";
        $failed++;
    }
}

// 1. Database Singleton Test
$db1 = \App\Core\Database::getInstance();
$db2 = \App\Core\Database::getInstance();
assertTest("Database::getInstance() returns exact same singleton instance", $db1 === $db2);

// 2. Database PDO Active Connection Test
$pdo = \App\Core\Database::pdo();
assertTest("Database::pdo() returns active PDO connection", $pdo instanceof \PDO);

// 3. 32 Tables Existence Test
$tables = $db1->fetchAll("SHOW TABLES");
assertTest("All 32 database tables present in schema", count($tables) >= 32, "Found: " . count($tables));

// 4. Seed Data Verification Test
$userCount = (int)$db1->fetch("SELECT COUNT(*) as c FROM users")['c'];
assertTest("Demo users present in database", $userCount >= 5, "User count: {$userCount}");

$courseCount = (int)$db1->fetch("SELECT COUNT(*) as c FROM courses")['c'];
assertTest("Curriculum courses present in database", $courseCount >= 10, "Course count: {$courseCount}");

// 5. Password Verification Test
$hash = '$2y$10$mLOb2b5E/43ww.KwJOnhP.gkkPLMBEkkbw5p5zMobz/ziN41AWUjq';
assertTest("Demo password 'Password@123' verifies against bcrypt hash", password_verify('Password@123', $hash));

// 6. Router Registration Test
\App\Core\Router::get('/test-ping-route', fn() => 'pong');
$routes = \App\Core\Router::getRoutes();
$foundRoute = false;
foreach ($routes as $r) {
    if ($r['uri'] === '/test-ping-route') {
        $foundRoute = true;
        break;
    }
}
assertTest("Router registers GET route correctly", $foundRoute);

// 7. Security XSS Escaping Test
$xss = '<script>alert("hacked")</script>';
$escaped = e($xss);
assertTest("Output escaping helper e() prevents script tag injection", !str_contains($escaped, '<script>') && str_contains($escaped, '&lt;script&gt;'));

// 8. CSRF Token Generation Test
$token1 = csrf_token();
$token2 = csrf_token();
assertTest("CSRF token is generated and remains consistent in session", !empty($token1) && $token1 === $token2);

// 9. Credit Limit Service Policy Test
$creditService = new \App\Services\CreditLimitService();
$validCheck = $creditService->validate(1, 15.0);
assertTest("CreditLimitService approves regular 15.0 credit registration", $validCheck['valid'] === true);

$overCheck = $creditService->validate(1, 25.0);
assertTest("CreditLimitService rejects overloaded 25.0 credit registration", $overCheck['valid'] === false);

// 10. Prerequisite Service Test
$prereqService = new \App\Services\PrerequisiteService();
$prereqCheck = $prereqService->check(1, 6);
assertTest("PrerequisiteService verifies student 1 passed CSE-1101 for CSE-2101", $prereqCheck['passed'] === true);

// 11. Seat Availability Service Test
$seatService = new \App\Services\SeatAvailabilityService();
assertTest("SeatAvailabilityService confirms Section 1 has vacancies", $seatService->isAvailable(1));

// 12. Helpdesk RAG Fulltext Search Test
$docModel = new \App\Models\AcademicDocument();
$chunks = $docModel->searchChunks('probation credit limits', 2);
assertTest("AI Helpdesk retrieves grounded document chunks for 'probation credit limits'", !empty($chunks));

echo "\n--------------------------------------------------------\n";
echo "TEST SUMMARY: {$passed} Passed, {$failed} Failed\n";
echo "--------------------------------------------------------\n";

exit($failed > 0 ? 1 : 0);
