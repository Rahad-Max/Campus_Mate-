<?php
/**
 * Application Web Routes
 * PUB CampusMate — Pundra University of Science & Technology
 */

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\StudentController;
use App\Controllers\TeacherController;
use App\Controllers\AdvisorController;
use App\Controllers\CoordinatorController;
use App\Controllers\CourseController;
use App\Controllers\RegistrationController;
use App\Controllers\TimetableController;
use App\Controllers\ExamController;
use App\Controllers\NoticeController;
use App\Controllers\FacultyController;
use App\Controllers\NotificationController;
use App\Controllers\HelpdeskController;
use App\Controllers\DepartmentController;
use App\Controllers\SemesterController;
use App\Controllers\CurriculumController;
use App\Controllers\CourseOfferingController;
use App\Controllers\SectionController;
use App\Controllers\RoomController;
use App\Controllers\TeacherAssignmentController;
use App\Controllers\MakeupClassController;

// ---------------------------------------------------------------------
// 1. Public & Authentication Routes
// ---------------------------------------------------------------------
Router::get('/', function() {
    if (auth_check()) {
        (new DashboardController())->index();
    } else {
        (new AuthController())->showLogin();
    }
});

Router::get('/login', [AuthController::class, 'showLogin']);
Router::post('/login', [AuthController::class, 'login'], ['csrf']);
Router::get('/forgot-password', [AuthController::class, 'showForgotPassword']);
Router::post('/forgot-password', [AuthController::class, 'sendPasswordReset'], ['csrf']);
Router::get('/logout', [AuthController::class, 'logout']);
Router::post('/logout', [AuthController::class, 'logout']);

// Public directory & catalog
Router::get('/faculty', [FacultyController::class, 'index']);
Router::get('/courses', [CourseController::class, 'index']);
Router::get('/courses/{id}', [CourseController::class, 'show']);
Router::get('/notices', [NoticeController::class, 'index']);
Router::get('/notices/{id}', [NoticeController::class, 'show']);

// ---------------------------------------------------------------------
// 2. Base Authenticated Routes
// ---------------------------------------------------------------------
Router::get('/dashboard', [DashboardController::class, 'index'], ['auth']);
Router::get('/timetable', [TimetableController::class, 'index'], ['auth']);
Router::get('/makeup-classes', [MakeupClassController::class, 'studentAndTeacherView'], ['auth']);
Router::get('/notifications', [NotificationController::class, 'index'], ['auth']);
Router::get('/notifications/{id}/read', [NotificationController::class, 'markRead'], ['auth']);
Router::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'], ['auth', 'csrf']);

// AI Helpdesk (Authenticated)
Router::get('/helpdesk', [HelpdeskController::class, 'index'], ['auth']);
Router::post('/helpdesk/ask', [HelpdeskController::class, 'ask'], ['auth', 'csrf']);

// ---------------------------------------------------------------------
// 3. Student Role Routes
// ---------------------------------------------------------------------
Router::group(['middleware' => ['auth', 'role:student']], function() {
    Router::get('/student/dashboard', [DashboardController::class, 'student']);
    Router::get('/dashboard/student', [DashboardController::class, 'student']);
    Router::get('/student/profile', [StudentController::class, 'profile']);
    Router::get('/student/history', [StudentController::class, 'academicHistory']);
    Router::get('/student/courses', [StudentController::class, 'courses']);
    Router::get('/student/semester', [StudentController::class, 'semester']);

    // Phase 4 Course Registration (Student)
    Router::get('/student/registration', [RegistrationController::class, 'index']);
    Router::get('/registration', [RegistrationController::class, 'index']); // Legacy alias
    Router::post('/student/registration/validate', [RegistrationController::class, 'validateCartApi']);
    Router::post('/registration/validate-cart', [RegistrationController::class, 'validateCartApi']); // Legacy alias
    Router::post('/student/registration/submit', [RegistrationController::class, 'submit'], ['csrf']);
    Router::post('/registration/submit', [RegistrationController::class, 'submit'], ['csrf']); // Legacy alias
    Router::get('/student/registration/history', [RegistrationController::class, 'history']);
    Router::get('/student/registration/{id}', [RegistrationController::class, 'show']);
    Router::post('/student/registration/{id}/cancel', [RegistrationController::class, 'cancel'], ['csrf']);

    Router::get('/exams', [ExamController::class, 'index']);
});

// ---------------------------------------------------------------------
// 4. Teacher Role Routes
// ---------------------------------------------------------------------
Router::group(['middleware' => ['auth', 'role:teacher']], function() {
    Router::get('/teacher/dashboard', [DashboardController::class, 'teacher']);
    Router::get('/dashboard/teacher', [DashboardController::class, 'teacher']);
    Router::get('/teacher/profile', [TeacherController::class, 'profile']);
    Router::get('/teacher/classes', [TeacherController::class, 'classes']);
    Router::get('/teacher/courses', [TeacherController::class, 'courses']);
    Router::get('/teacher/assignments', [TeacherController::class, 'assignments']);
});

// ---------------------------------------------------------------------
// 5. Academic Advisor Role Routes
// ---------------------------------------------------------------------
Router::group(['middleware' => ['auth', 'role:academic_advisor']], function() {
    Router::get('/advisor/dashboard', [DashboardController::class, 'advisor']);
    Router::get('/dashboard/advisor', [DashboardController::class, 'advisor']);
    Router::get('/advisor/advisees', [AdvisorController::class, 'advisees']);

    // Phase 4 Course Registration Approvals (Advisor)
    Router::get('/advisor/registration-requests', [AdvisorController::class, 'reviewRequests']);
    Router::get('/advisor/requests', [AdvisorController::class, 'reviewRequests']); // Legacy alias
    Router::get('/advisor/registration-requests/{id}', [AdvisorController::class, 'showRequest']);
    Router::post('/advisor/registration-requests/{id}/approve', [AdvisorController::class, 'approve'], ['csrf']);
    Router::post('/advisor/request/{id}/approve', [AdvisorController::class, 'approve'], ['csrf']); // Legacy alias
    Router::post('/advisor/registration-requests/{id}/reject', [AdvisorController::class, 'reject'], ['csrf']);

    Router::get('/advisor/courses', [AdvisorController::class, 'courses']);
});

// ---------------------------------------------------------------------
// 6. Department Coordinator Academic Management Routes
// ---------------------------------------------------------------------
Router::group(['middleware' => ['auth', 'role:department_coordinator']], function() {
    Router::get('/coordinator/dashboard', [DashboardController::class, 'coordinator']);
    Router::get('/dashboard/coordinator', [DashboardController::class, 'coordinator']);

    // 6.1 Department Management
    Router::get('/coordinator/departments', [DepartmentController::class, 'index']);
    Router::get('/coordinator/departments/create', [DepartmentController::class, 'create']);
    Router::post('/coordinator/departments', [DepartmentController::class, 'store'], ['csrf']);
    Router::get('/coordinator/departments/{id}', [DepartmentController::class, 'show']);
    Router::get('/coordinator/departments/{id}/edit', [DepartmentController::class, 'edit']);
    Router::post('/coordinator/departments/{id}/edit', [DepartmentController::class, 'update'], ['csrf']);
    Router::post('/coordinator/departments/{id}/toggle-status', [DepartmentController::class, 'toggleStatus'], ['csrf']);

    // 6.2 Semester / Academic Term Management
    Router::get('/coordinator/semesters', [SemesterController::class, 'index']);
    Router::get('/coordinator/semesters/create', [SemesterController::class, 'create']);
    Router::post('/coordinator/semesters', [SemesterController::class, 'store'], ['csrf']);
    Router::get('/coordinator/semesters/{id}/edit', [SemesterController::class, 'edit']);
    Router::post('/coordinator/semesters/{id}/edit', [SemesterController::class, 'update'], ['csrf']);
    Router::post('/coordinator/semesters/{id}/set-current', [SemesterController::class, 'setCurrent'], ['csrf']);
    Router::post('/coordinator/semesters/{id}/close', [SemesterController::class, 'close'], ['csrf']);

    // 6.3 Curriculum Management
    Router::get('/coordinator/curricula', [CurriculumController::class, 'index']);
    Router::get('/coordinator/curricula/create', [CurriculumController::class, 'create']);
    Router::post('/coordinator/curricula', [CurriculumController::class, 'store'], ['csrf']);
    Router::get('/coordinator/curricula/{id}', [CurriculumController::class, 'show']);
    Router::get('/coordinator/curricula/{id}/edit', [CurriculumController::class, 'edit']);
    Router::post('/coordinator/curricula/{id}/edit', [CurriculumController::class, 'update'], ['csrf']);
    Router::post('/coordinator/curricula/{id}/add-course', [CurriculumController::class, 'addCourse'], ['csrf']);
    Router::post('/coordinator/curricula/{id}/remove-course/{courseId}', [CurriculumController::class, 'removeCourse'], ['csrf']);
    Router::get('/coordinator/curriculum', [CoordinatorController::class, 'curriculum']);

    // 6.4 Course Catalog & Prerequisites Management
    Router::get('/coordinator/courses', [CourseController::class, 'manage']);
    Router::get('/coordinator/courses/create', [CourseController::class, 'create']);
    Router::post('/coordinator/courses', [CourseController::class, 'store'], ['csrf']);
    Router::get('/coordinator/courses/{id}/edit', [CourseController::class, 'edit']);
    Router::post('/coordinator/courses/{id}/edit', [CourseController::class, 'update'], ['csrf']);
    Router::get('/coordinator/courses/{id}/prerequisites', [CourseController::class, 'prerequisites']);
    Router::post('/coordinator/courses/{id}/add-prerequisite', [CourseController::class, 'addPrerequisite'], ['csrf']);
    Router::post('/coordinator/courses/{id}/remove-prerequisite/{prereqId}', [CourseController::class, 'removePrerequisite'], ['csrf']);

    // 6.5 Course Offerings Management
    Router::get('/coordinator/course-offerings', [CourseOfferingController::class, 'index']);
    Router::get('/coordinator/course-offerings/create', [CourseOfferingController::class, 'create']);
    Router::post('/coordinator/course-offerings', [CourseOfferingController::class, 'store'], ['csrf']);
    Router::get('/coordinator/course-offerings/{id}/edit', [CourseOfferingController::class, 'edit']);
    Router::post('/coordinator/course-offerings/{id}/edit', [CourseOfferingController::class, 'update'], ['csrf']);
    Router::get('/coordinator/offerings', [CoordinatorController::class, 'offerings']);

    // 6.6 Section Management
    Router::get('/coordinator/sections', [SectionController::class, 'index']);
    Router::get('/coordinator/sections/create', [SectionController::class, 'create']);
    Router::post('/coordinator/sections', [SectionController::class, 'store'], ['csrf']);
    Router::get('/coordinator/sections/{id}/edit', [SectionController::class, 'edit']);
    Router::post('/coordinator/sections/{id}/edit', [SectionController::class, 'update'], ['csrf']);
    Router::post('/coordinator/sections/{id}/toggle-status', [SectionController::class, 'toggleStatus'], ['csrf']);

    // 6.7 Room & Facility Management
    Router::get('/coordinator/rooms', [RoomController::class, 'index']);
    Router::get('/coordinator/rooms/create', [RoomController::class, 'create']);
    Router::post('/coordinator/rooms', [RoomController::class, 'store'], ['csrf']);
    Router::get('/coordinator/rooms/{id}/edit', [RoomController::class, 'edit']);
    Router::post('/coordinator/rooms/{id}/edit', [RoomController::class, 'update'], ['csrf']);
    Router::post('/coordinator/rooms/{id}/toggle-status', [RoomController::class, 'toggleStatus'], ['csrf']);
    Router::post('/coordinator/rooms/{id}/delete', [RoomController::class, 'delete'], ['csrf']);

    // 6.8 Teacher Assignment & Workload Management
    Router::get('/coordinator/teacher-assignments', [TeacherAssignmentController::class, 'index']);
    Router::get('/coordinator/teacher-assignments/create', [TeacherAssignmentController::class, 'create']);
    Router::post('/coordinator/teacher-assignments', [TeacherAssignmentController::class, 'store'], ['csrf']);
    Router::post('/coordinator/teacher-assignments/{id}/delete', [TeacherAssignmentController::class, 'delete'], ['csrf']);

    // 6.9 Timetable & Class Routine Management
    Router::get('/coordinator/timetable', [TimetableController::class, 'coordinatorIndex']);
    Router::get('/coordinator/timetable/create', [TimetableController::class, 'create']);
    Router::post('/coordinator/timetable', [TimetableController::class, 'store'], ['csrf']);
    Router::get('/coordinator/timetable/{id}/edit', [TimetableController::class, 'edit']);
    Router::post('/coordinator/timetable/{id}/edit', [TimetableController::class, 'update'], ['csrf']);
    Router::post('/coordinator/timetable/{id}/publish', [TimetableController::class, 'publish'], ['csrf']);
    Router::post('/coordinator/timetable/{id}/cancel', [TimetableController::class, 'cancel'], ['csrf']);
    Router::get('/coordinator/timetable/revisions', [TimetableController::class, 'revisions']);
    Router::get('/coordinator/api/offering-data', [TimetableController::class, 'apiOfferingData']);

    // 6.10 Examination Scheduling & Invigilation Management
    Router::get('/coordinator/exams', [ExamController::class, 'coordinatorIndex']);
    Router::get('/coordinator/exams/create', [ExamController::class, 'create']);
    Router::post('/coordinator/exams', [ExamController::class, 'store'], ['csrf']);
    Router::get('/coordinator/exams/{id}/edit', [ExamController::class, 'edit']);
    Router::post('/coordinator/exams/{id}/edit', [ExamController::class, 'update'], ['csrf']);
    Router::post('/coordinator/exams/{id}/publish', [ExamController::class, 'publish'], ['csrf']);
    Router::post('/coordinator/exams/{id}/cancel', [ExamController::class, 'cancel'], ['csrf']);

    // 6.11 Make-up Class Scheduling & Routine Adjustments
    Router::get('/coordinator/makeup-classes', [MakeupClassController::class, 'index']);
    Router::get('/coordinator/makeup-classes/create', [MakeupClassController::class, 'create']);
    Router::post('/coordinator/makeup-classes', [MakeupClassController::class, 'store'], ['csrf']);
    Router::post('/coordinator/makeup-classes/{id}/cancel', [MakeupClassController::class, 'cancel'], ['csrf']);
});
