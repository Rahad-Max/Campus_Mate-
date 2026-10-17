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
Router::get('/logout', [AuthController::class, 'logout']);

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
Router::get('/notifications', [NotificationController::class, 'index'], ['auth']);
Router::get('/notifications/{id}/read', [NotificationController::class, 'markRead'], ['auth']);
Router::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'], ['auth', 'csrf']);

// AI Helpdesk (Authenticated)
Router::get('/helpdesk', [HelpdeskController::class, 'index'], ['auth']);
Router::post('/helpdesk/ask', [HelpdeskController::class, 'ask'], ['auth', 'csrf']);

// ---------------------------------------------------------------------
// 3. Student Role Routes
// ---------------------------------------------------------------------
Router::group(['prefix' => 'dashboard', 'middleware' => ['auth', 'role:student']], function() {
    Router::get('/student', [DashboardController::class, 'student']);
});

Router::group(['middleware' => ['auth', 'role:student']], function() {
    Router::get('/student/profile', [StudentController::class, 'profile']);
    Router::get('/student/history', [StudentController::class, 'academicHistory']);
    Router::get('/registration', [RegistrationController::class, 'index']);
    Router::post('/registration/submit', [RegistrationController::class, 'submit'], ['csrf']);
    Router::post('/registration/validate-cart', [RegistrationController::class, 'validateCartApi']);
    Router::get('/exams', [ExamController::class, 'index']);
});

// ---------------------------------------------------------------------
// 4. Teacher Role Routes
// ---------------------------------------------------------------------
Router::group(['prefix' => 'dashboard', 'middleware' => ['auth', 'role:teacher']], function() {
    Router::get('/teacher', [DashboardController::class, 'teacher']);
});

Router::group(['middleware' => ['auth', 'role:teacher']], function() {
    Router::get('/teacher/profile', [TeacherController::class, 'profile']);
    Router::get('/teacher/classes', [TeacherController::class, 'classes']);
});

// ---------------------------------------------------------------------
// 5. Academic Advisor Role Routes
// ---------------------------------------------------------------------
Router::group(['prefix' => 'dashboard', 'middleware' => ['auth', 'role:academic_advisor']], function() {
    Router::get('/advisor', [DashboardController::class, 'advisor']);
});

Router::group(['middleware' => ['auth', 'role:academic_advisor']], function() {
    Router::get('/advisor/advisees', [AdvisorController::class, 'advisees']);
    Router::get('/advisor/requests', [AdvisorController::class, 'reviewRequests']);
    Router::post('/advisor/request/{id}/approve', [AdvisorController::class, 'approve'], ['csrf']);
});

// ---------------------------------------------------------------------
// 6. Department Coordinator Role Routes
// ---------------------------------------------------------------------
Router::group(['prefix' => 'dashboard', 'middleware' => ['auth', 'role:department_coordinator']], function() {
    Router::get('/coordinator', [DashboardController::class, 'coordinator']);
});

Router::group(['middleware' => ['auth', 'role:department_coordinator']], function() {
    Router::get('/coordinator/offerings', [CoordinatorController::class, 'offerings']);
    Router::get('/coordinator/curriculum', [CoordinatorController::class, 'curriculum']);
});
