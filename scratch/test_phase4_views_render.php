<?php
define('ROOT_PATH', dirname(__DIR__));
require ROOT_PATH . '/app/Helpers/auth.php';
require ROOT_PATH . '/app/Helpers/csrf.php';
require ROOT_PATH . '/app/Helpers/validation.php';
require ROOT_PATH . '/app/Helpers/response.php';
require ROOT_PATH . '/app/Core/Database.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "Testing Phase 4 view renderings...\n";

// Helper to render view into buffer
function testRenderView($viewPath, $data = []) {
    extract($data);
    ob_start();
    try {
        include ROOT_PATH . '/resources/views/' . $viewPath . '.php';
        $out = ob_get_clean();
        echo "  [PASS] {$viewPath} rendered cleanly (" . strlen($out) . " bytes)\n";
        return true;
    } catch (\Throwable $e) {
        ob_end_clean();
        echo "  [FAIL] {$viewPath}: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
        return false;
    }
}

// 1. registration/index
$sem = ['id' => 2, 'name' => 'Summer 2026', 'registration_end' => '2026-05-18 23:59:00'];
$student = [
    'id' => 1, 'first_name' => 'Rahat', 'last_name' => 'Rahman',
    'student_id_card' => '210101001', 'batch_no' => 'Batch 21',
    'cgpa' => 3.65, 'completed_credits' => 36.0, 'academic_status' => 'regular',
    'advisor_first_name' => 'Dr. Kazi', 'advisor_last_name' => 'Nahid',
    'advisor_designation' => 'Associate Professor'
];
testRenderView('registration/index', [
    'semester' => $sem,
    'student' => $student,
    'offerings' => [],
    'enrolled' => [],
    'requests' => [],
    'hasPending' => false,
    'minCredits' => 9.0,
    'maxCredits' => 18.0,
    'isProbation' => false
]);

// 2. registration/history
testRenderView('registration/history', [
    'requests' => [
        [
            'id' => 1, 'semester_name' => 'Summer 2026', 'semester_code' => 'SUMMER-2026',
            'request_type' => 'regular', 'total_credits' => 9.0, 'status' => 'advisor_approved',
            'created_at' => '2026-05-12 11:30:00', 'advisor_remarks' => 'Approved', 'advisor_action_at' => '2026-05-12 12:00:00'
        ]
    ],
    'student' => $student
]);

// 3. registration/show
testRenderView('registration/show', [
    'request' => [
        'id' => 1, 'semester_name' => 'Summer 2026', 'created_at' => '2026-05-12 11:30:00',
        'status' => 'submitted', 'total_credits' => 9.0, 'submission_notes' => 'Test notes',
        'advisor_first_name' => 'Dr. Kazi', 'advisor_last_name' => 'Nahid',
        'advisor_action_at' => null, 'advisor_remarks' => null
    ],
    'items' => [
        [
            'course_code' => 'CSE-2101', 'course_title' => 'Data Structures', 'credit' => 3.0,
            'course_type' => 'theory', 'section_name' => 'A', 'schedules' => [],
            'teacher_first_name' => 'Tanvir', 'teacher_last_name' => 'Ahmed',
            'status' => 'pending', 'rejection_reason' => null
        ]
    ],
    'student' => $student
]);

// 4. advisor/requests
testRenderView('advisor/requests', [
    'requests' => [
        [
            'id' => 1, 'student_id_card' => '210101001', 'batch_no' => 'Batch 21',
            'student_first_name' => 'Rahat', 'student_last_name' => 'Rahman',
            'academic_status' => 'regular', 'cgpa' => 3.65, 'course_count' => 3,
            'total_credits' => 9.0, 'created_at' => '2026-05-12 11:30:00'
        ]
    ],
    'history' => [],
    'teacher' => ['id' => 2, 'user_id' => 4]
]);

// 5. advisor/show_request
testRenderView('advisor/show_request', [
    'request' => [
        'id' => 1, 'student_first_name' => 'Rahat', 'student_last_name' => 'Rahman',
        'student_id_card' => '210101001', 'batch_no' => 'Batch 21', 'department_name' => 'CSE',
        'department_code' => 'CSE', 'academic_status' => 'regular', 'cgpa' => 3.65,
        'completed_credits' => 36.0, 'total_credits' => 9.0, 'semester_name' => 'Summer 2026',
        'status' => 'submitted', 'submission_notes' => 'Notes', 'advisor_action_at' => null,
        'advisor_remarks' => null
    ],
    'items' => [
        [
            'section_id' => 1, 'course_code' => 'CSE-2101', 'course_title' => 'Data Structures',
            'credit' => 3.0, 'course_type' => 'theory', 'section_name' => 'A',
            'schedules' => [], 'teacher_first_name' => 'Tanvir', 'teacher_last_name' => 'Ahmed',
            'capacity' => 40
        ]
    ],
    'history' => [],
    'auditResults' => [
        1 => ['prereq' => ['passed' => true, 'missing' => []], 'seat' => ['available' => true, 'remaining' => 12, 'capacity' => 40]]
    ],
    'creditAudit' => ['valid' => true],
    'conflictAudit' => ['has_conflict' => false],
    'teacher' => ['id' => 2]
]);

echo "All Phase 4 view tests completed.\n";
