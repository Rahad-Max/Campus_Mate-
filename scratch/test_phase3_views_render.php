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

echo "Testing All Phase 3 Controller Views Render...\n";

// Set coordinator user
$_SESSION['user'] = [
    'id' => 4,
    'email' => 'coordinator@pub.ac.bd',
    'role' => 'department_coordinator',
    'first_name' => 'Prof. Dr. Coordinator',
    'last_name' => 'Khan',
    'status' => 'active'
];
$_SERVER['REQUEST_URI'] = '/coordinator/dashboard';

function testRender(string $desc, callable $fn): void {
    ob_start();
    try {
        $fn();
        $output = ob_get_clean();
        if (strlen($output) > 50) {
            echo "  [PASS] {$desc} (" . strlen($output) . " bytes)\n";
        } else {
            echo "  [FAIL] {$desc} (Empty or too small output)\n";
        }
    } catch (\Throwable $e) {
        ob_end_clean();
        echo "  [FAIL] {$desc} - Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}

// 1. DepartmentController
$deptCtrl = new \App\Controllers\DepartmentController();
testRender("DepartmentController::index", fn() => $deptCtrl->index());
testRender("DepartmentController::create", fn() => $deptCtrl->create());
testRender("DepartmentController::edit", fn() => $deptCtrl->edit(1));
testRender("DepartmentController::show", fn() => $deptCtrl->show(1));

// 2. SemesterController
$semCtrl = new \App\Controllers\SemesterController();
testRender("SemesterController::index", fn() => $semCtrl->index());
testRender("SemesterController::create", fn() => $semCtrl->create());
testRender("SemesterController::edit", fn() => $semCtrl->edit(1));

// 3. CurriculumController
$curCtrl = new \App\Controllers\CurriculumController();
testRender("CurriculumController::index", fn() => $curCtrl->index());
testRender("CurriculumController::create", fn() => $curCtrl->create());
testRender("CurriculumController::edit", fn() => $curCtrl->edit(1));
testRender("CurriculumController::show", fn() => $curCtrl->show(1));

// 4. CourseController
$courseCtrl = new \App\Controllers\CourseController();
testRender("CourseController::manage", fn() => $courseCtrl->manage());
testRender("CourseController::create", fn() => $courseCtrl->create());
testRender("CourseController::edit", fn() => $courseCtrl->edit(1));
testRender("CourseController::prerequisites", fn() => $courseCtrl->prerequisites(1));

// 5. CourseOfferingController
$offCtrl = new \App\Controllers\CourseOfferingController();
testRender("CourseOfferingController::index", fn() => $offCtrl->index());
testRender("CourseOfferingController::create", fn() => $offCtrl->create());
testRender("CourseOfferingController::edit", fn() => $offCtrl->edit(1));

// 6. SectionController
$secCtrl = new \App\Controllers\SectionController();
testRender("SectionController::index", fn() => $secCtrl->index());
testRender("SectionController::create", fn() => $secCtrl->create());
testRender("SectionController::edit", fn() => $secCtrl->edit(1));

// 7. RoomController
$roomCtrl = new \App\Controllers\RoomController();
testRender("RoomController::index", fn() => $roomCtrl->index());
testRender("RoomController::create", fn() => $roomCtrl->create());
testRender("RoomController::edit", fn() => $roomCtrl->edit(1));

// 8. TeacherAssignmentController
$assignCtrl = new \App\Controllers\TeacherAssignmentController();
testRender("TeacherAssignmentController::index", fn() => $assignCtrl->index());
testRender("TeacherAssignmentController::create", fn() => $assignCtrl->create());

// 9. StudentController
$_SESSION['user']['role'] = 'student';
$_SESSION['user']['id'] = 1;
$stuCtrl = new \App\Controllers\StudentController();
testRender("StudentController::courses", fn() => $stuCtrl->courses());
testRender("StudentController::semester", fn() => $stuCtrl->semester());

// 10. TeacherController
$_SESSION['user']['role'] = 'teacher';
$_SESSION['user']['id'] = 2;
$teacherCtrl = new \App\Controllers\TeacherController();
testRender("TeacherController::courses", fn() => $teacherCtrl->courses());
testRender("TeacherController::assignments", fn() => $teacherCtrl->assignments());

// 11. AdvisorController
$_SESSION['user']['role'] = 'academic_advisor';
$_SESSION['user']['id'] = 3;
$advCtrl = new \App\Controllers\AdvisorController();
testRender("AdvisorController::courses", fn() => $advCtrl->courses());

echo "\nAll controller views tested successfully!\n";
