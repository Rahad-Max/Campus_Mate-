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

ensure_session_started();

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

// =====================================================================
// PHASE 2: AUTHENTICATION & ROLE-BASED DASHBOARD TESTS
// =====================================================================
echo "\n--- Phase 2: Authentication & Role Dashboards Verification ---\n";
require_once ROOT_PATH . '/tests/Integration/Phase2AuthDashboardTest.php';
$phase2 = new \Tests\Integration\Phase2AuthDashboardTest();

assertTest("Phase 2 [1/12]: Valid login with correct credentials verifies password & role", $phase2->testValidLogin());
assertTest("Phase 2 [2/12]: Invalid credentials correctly rejected (bad password / unknown user)", $phase2->testInvalidCredentials());
assertTest("Phase 2 [3/12]: Inactive / suspended accounts blocked even with correct password", $phase2->testInactiveAccountRejection());
assertTest("Phase 2 [4/12]: Logout invalidates session and clears authentication state", $phase2->testLogoutSessionInvalidation());
assertTest("Phase 2 [5/12]: Unauthenticated access to protected routes blocked", $phase2->testUnauthenticatedAccessBlocked());
assertTest("Phase 2 [6/12]: Role boundary enforcement prevents student from accessing teacher route", $phase2->testRoleBoundaryEnforcement());
assertTest("Phase 2 [7/12]: Role dashboard redirection resolves correct route for all 4 roles", $phase2->testRoleDashboardResolution());
assertTest("Phase 2 [8/12]: Student dashboard data model loads real academic metrics & CGPA", $phase2->testStudentDashboardData());
assertTest("Phase 2 [9/12]: Teacher dashboard data model loads assigned sections & load limits", $phase2->testTeacherDashboardData());
assertTest("Phase 2 [10/12]: Advisor dashboard data model loads assigned advisees portfolio", $phase2->testAdvisorDashboardData());
assertTest("Phase 2 [11/12]: Open-redirect protection sanitizes external/malicious URLs", $phase2->testOpenRedirectProtection());
assertTest("Phase 2 [12/12]: Illegal/corrupted browser session cookie auto-recovery & sanitization", $phase2->testIllegalSessionCookieAutoRecovery());

// =====================================================================
// PHASE 3: ACADEMIC MANAGEMENT MODULE TESTS
// =====================================================================
echo "\n--- Phase 3: Academic Management Module Verification ---\n";
require_once ROOT_PATH . '/tests/Integration/Phase3AcademicManagementTest.php';
$phase3 = new \Tests\Integration\Phase3AcademicManagementTest();

assertTest("Phase 3 [1/12]: Coordinator can create a valid course with positive credits", $phase3->testCoordinatorCanCreateCourse());
assertTest("Phase 3 [2/12]: Duplicate course codes are rejected by uniqueness rule", $phase3->testDuplicateCourseCodeRejected());
assertTest("Phase 3 [3/12]: A course cannot be its own prerequisite requirement", $phase3->testCourseCannotBeOwnPrerequisite());
assertTest("Phase 3 [4/12]: Multi-hop circular prerequisite graph cycles detected & rejected", $phase3->testCircularPrerequisiteGraphCycleDetection());
assertTest("Phase 3 [5/12]: Invalid semester date ranges and registration windows rejected", $phase3->testInvalidSemesterDatesRejected());
assertTest("Phase 3 [6/12]: Single active semester transactional enforcement across university", $phase3->testSingleActiveSemesterEnforcement());
assertTest("Phase 3 [7/12]: Course section with invalid / non-positive capacity rejected", $phase3->testSectionInvalidCapacityRejected());
assertTest("Phase 3 [8/12]: Duplicate section identifiers within same offering rejected", $phase3->testDuplicateSectionIdentifierRejected());
assertTest("Phase 3 [9/12]: Teacher assignment workload calculation & overload prevention", $phase3->testTeacherWorkloadLimitEnforced());
assertTest("Phase 3 [10/12]: Students blocked from coordinator academic management routes", $phase3->testStudentCannotAccessCoordinatorRoutes());
assertTest("Phase 3 [11/12]: Teachers blocked from modifying coordinator academic records", $phase3->testTeacherCannotModifyCoordinatorRecords());
assertTest("Phase 3 [12/12]: Referential integrity protects academic history from orphan deletion", $phase3->testReferentialIntegrityProtectsHistory());

// =====================================================================
// PHASE 4: SMART COURSE REGISTRATION & ADVISOR APPROVAL TESTS
// =====================================================================
echo "\n--- Phase 4: Smart Course Registration & Advisor Approval Verification ---\n";
require_once ROOT_PATH . '/tests/Integration/Phase4RegistrationTest.php';
$phase4 = new \Tests\Integration\Phase4RegistrationTest();

assertTest("Phase 4 [1/12]: Valid student can submit eligible course registration request", $phase4->testValidStudentCanSubmitEligibleRequest());
assertTest("Phase 4 [2/12]: Student without prerequisite is rejected with explicit course error", $phase4->testStudentWithoutPrerequisiteRejected());
assertTest("Phase 4 [3/12]: Workload exceeding configured minimum/maximum credit limits rejected", $phase4->testCreditLimitValidationExceededRejected());
assertTest("Phase 4 [4/12]: Full course section capacity enforced and rejected atomically", $phase4->testFullSectionCapacityRejected());
assertTest("Phase 4 [5/12]: Duplicate course offering selection in same cart detected & prevented", $phase4->testDuplicateCourseInCartPrevented());
assertTest("Phase 4 [6/12]: Timetable schedule clashes between cart courses detected & flagged", $phase4->testOverlappingCourseSchedulesDetected());
assertTest("Phase 4 [7/12]: Student request ownership isolation prevents cross-student access", $phase4->testUnauthorizedStudentCannotAccessAnotherStudentRequest());
assertTest("Phase 4 [8/12]: Unassigned academic advisor cannot approve another teacher's advisee", $phase4->testAdvisorCannotApproveUnassignedStudentRequest());
assertTest("Phase 4 [9/12]: Assigned advisor can approve request, reserving seats & enrolling student", $phase4->testAdvisorCanApproveRequestAndEnrollsStudent());
assertTest("Phase 4 [10/12]: Idempotent approval handling prevents double-enrollment & duplicate seats", $phase4->testIdempotentApprovalCannotApproveTwice());
assertTest("Phase 4 [11/12]: Academic advisor can reject request with documented academic reason", $phase4->testAdvisorCanRejectWithReason());
assertTest("Phase 4 [12/12]: Student can cancel pending submission; cancelled requests cannot be approved", $phase4->testStudentCanCancelPendingRequest());

// =====================================================================
// PHASE 5: SMART TIMETABLE, EXAMINATION & INTEGRATION TESTS
// =====================================================================
echo "\n--- Phase 5: Smart Timetable, Examination & Integration Verification ---\n";
require_once ROOT_PATH . '/tests/Integration/Phase5TimetableExamTest.php';
$phase5 = new \Tests\Integration\Phase5TimetableExamTest();

assertTest("Phase 5 [1/15]: Coordinator can create a valid timetable routine entry", $phase5->testCreateValidTimetableEntry());
assertTest("Phase 5 [2/15]: Timetable conflict engine detects & rejects teacher overlaps", $phase5->testDetectTeacherConflict());
assertTest("Phase 5 [3/15]: Timetable conflict engine detects & rejects classroom overlaps", $phase5->testDetectRoomConflict());
assertTest("Phase 5 [4/15]: Timetable conflict engine detects & rejects section overlaps", $phase5->testDetectSectionConflict());
assertTest("Phase 5 [5/15]: Invalid routine time ranges and short durations rejected", $phase5->testRejectInvalidTimeRange());
assertTest("Phase 5 [6/15]: Coordinator can create a valid examination routine schedule", $phase5->testCreateValidExaminationSchedule());
assertTest("Phase 5 [7/15]: Exam conflict engine detects room & invigilator assignment clashes", $phase5->testDetectExaminationRoomAndInvigilatorConflicts());
assertTest("Phase 5 [8/15]: Exam conflict engine detects enrolled student examination clashes", $phase5->testDetectStudentRegisteredExamClashes());
assertTest("Phase 5 [9/15]: Make-up class created without modifying/overwriting original routine", $phase5->testCreateMakeupClassWithoutOverwritingOriginalSchedule());
assertTest("Phase 5 [10/15]: Published routine changes require & record schedule revision audit", $phase5->testRecordRevisionWhenPublishedScheduleChanges());
assertTest("Phase 5 [11/15]: Targeted notifications dispatched to affected students & teachers", $phase5->testCreateNotificationsForAffectedRecipients());
assertTest("Phase 5 [12/15]: Students strictly blocked from coordinator timetable operations", $phase5->testBlockStudentsFromCoordinatorOperations());
assertTest("Phase 5 [13/15]: Unpublished/draft routine slots invisible to student timetable", $phase5->testBlockAccessToUnpublishedSchedulesForStudents());
assertTest("Phase 5 [14/15]: Academic management & Phase 4 registration records fully preserved", $phase5->testPreservePhase4RegistrationAndEnrollment());
assertTest("Phase 5 [15/15]: Full integration check: all 4 role dashboards render cleanly", $phase5->testAllRoleDashboardsRenderCleanly());

echo "\n--------------------------------------------------------\n";
echo "TEST SUMMARY: {$passed} Passed, {$failed} Failed\n";
echo "--------------------------------------------------------\n";

exit($failed > 0 ? 1 : 0);

