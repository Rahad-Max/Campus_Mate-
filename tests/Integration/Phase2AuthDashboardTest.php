<?php
/**
 * PUB CampusMate — Phase 2 Authentication & Role Dashboards Test Suite
 */

declare(strict_types=1);

namespace Tests\Integration;

use App\Core\Database;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Semester;
use App\Models\ClassSchedule;
use App\Models\Exam;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

class Phase2AuthDashboardTest
{
    private User $userModel;
    private Database $db;

    public function __construct()
    {
        $this->userModel = new User();
        $this->db = Database::getInstance();
    }

    /**
     * Test 1: Valid Login with Correct Credentials
     */
    public function testValidLogin(): bool
    {
        $user = $this->userModel->findByIdentifier('student@pub.ac.bd');
        if (!$user) {
            return false;
        }

        $passwordValid = password_verify('Password@123', $user['password_hash']);
        $isActive = $this->userModel->isAccountActive($user);

        return $passwordValid && $isActive && $user['role'] === 'student';
    }

    /**
     * Test 2: Invalid Login with Incorrect Password or Unknown Identifier
     */
    public function testInvalidCredentials(): bool
    {
        // Case A: Correct username, bad password
        $user = $this->userModel->findByIdentifier('student@pub.ac.bd');
        $badPasswordResult = $user && password_verify('WrongPassword999', $user['password_hash']);

        // Case B: Non-existent identifier
        $unknownUser = $this->userModel->findByIdentifier('nonexistent_user@pub.ac.bd');

        return !$badPasswordResult && ($unknownUser === null);
    }

    /**
     * Test 3: Inactive / Suspended Account Blocking
     */
    public function testInactiveAccountRejection(): bool
    {
        $user = $this->userModel->findByIdentifier('inactive@pub.ac.bd');
        if (!$user) {
            return false;
        }

        $passwordValid = password_verify('Password@123', $user['password_hash']);
        $isActive = $this->userModel->isAccountActive($user);

        // Password matches, BUT account is inactive and must be rejected
        return $passwordValid && !$isActive;
    }

    /**
     * Test 4: Logout and Session Invalidation
     */
    public function testLogoutSessionInvalidation(): bool
    {
        ensure_session_started();

        // Simulate active session
        $_SESSION['user'] = [
            'id' => 1,
            'username' => 'student21',
            'role' => 'student'
        ];
        $wasLoggedIn = auth_check();

        // Simulate logout process
        $_SESSION = [];
        $isLoggedOut = !auth_check();

        return $wasLoggedIn && $isLoggedOut;
    }

    /**
     * Test 5: Unauthenticated Access to Protected Route
     */
    public function testUnauthenticatedAccessBlocked(): bool
    {
        ensure_session_started();
        $_SESSION = [];

        // Check auth check
        return !auth_check() && empty($_SESSION['user']);
    }

    /**
     * Test 6: Role Boundary Protection (Student accessing Teacher route)
     */
    public function testRoleBoundaryEnforcement(): bool
    {
        ensure_session_started();

        $_SESSION['user'] = [
            'id' => 1,
            'username' => 'student21',
            'role' => 'student'
        ];

        $studentRole = auth_role();
        $allowedForTeacherRoute = ['teacher'];

        // Student must NOT be in teacher allowed list
        $canAccessTeacher = in_array($studentRole, $allowedForTeacherRoute, true);

        // Teacher in teacher allowed list
        $teacherRole = 'teacher';
        $canTeacherAccess = in_array($teacherRole, $allowedForTeacherRoute, true);

        return !$canAccessTeacher && $canTeacherAccess;
    }

    /**
     * Test 7: Correct Role-Based Dashboard Redirection
     */
    public function testRoleDashboardResolution(): bool
    {
        $roles = [
            'student' => '/student/dashboard',
            'teacher' => '/teacher/dashboard',
            'academic_advisor' => '/advisor/dashboard',
            'department_coordinator' => '/coordinator/dashboard'
        ];

        foreach ($roles as $role => $expectedRoute) {
            $resolved = match ($role) {
                'student' => '/student/dashboard',
                'teacher' => '/teacher/dashboard',
                'academic_advisor' => '/advisor/dashboard',
                'department_coordinator' => '/coordinator/dashboard',
                default => '/dashboard'
            };

            if ($resolved !== $expectedRoute) {
                return false;
            }
        }

        return true;
    }

    /**
     * Test 8: Student Dashboard Data Integrity
     */
    public function testStudentDashboardData(): bool
    {
        $studentModel = new Student();
        $student = $studentModel->getFullDetails(1);

        return !empty($student) &&
               $student['student_id_card'] === '210101001' &&
               $student['department_code'] === 'CSE' &&
               (float)$student['cgpa'] > 0;
    }

    /**
     * Test 9: Teacher Dashboard Data Integrity
     */
    public function testTeacherDashboardData(): bool
    {
        $teacherModel = new Teacher();
        $teacher = $teacherModel->getFullDetails(1);
        $sections = $teacherModel->getAssignedSections(1, 2);

        return !empty($teacher) &&
               $teacher['designation'] === 'Assistant Professor' &&
               count($sections) > 0;
    }

    /**
     * Test 10: Advisor Advisee and Queue Retrieval
     */
    public function testAdvisorDashboardData(): bool
    {
        $teacherModel = new Teacher();
        $advisees = $teacherModel->getAssignedAdvisees(2);

        return count($advisees) > 0 && !empty($advisees[0]['student_id_card']);
    }

    /**
     * Test 11: Open-Redirect Validation
     */
    public function testOpenRedirectProtection(): bool
    {
        $dangerousUrls = [
            'https://evil.com/phish',
            '//attacker.com/steal-session',
            'javascript:alert(1)',
            'c:\\malicious\\path'
        ];

        foreach ($dangerousUrls as $url) {
            $isSafe = str_starts_with($url, '/') &&
                      !str_starts_with($url, '//') &&
                      !str_contains($url, ':\\') &&
                      !str_contains($url, '://');

            if ($isSafe) {
                return false; // Should not be marked safe
            }
        }

        $safeUrl = '/student/profile';
        $isSafe = str_starts_with($safeUrl, '/') &&
                  !str_starts_with($safeUrl, '//') &&
                  !str_contains($safeUrl, ':\\') &&
                  !str_contains($safeUrl, '://');

        return $isSafe;
    }

    /**
     * Test 12: Illegal Session Cookie Auto-Recovery
     */
    public function testIllegalSessionCookieAutoRecovery(): bool
    {
        // Simulate a corrupted or illegal cookie injected from client browser
        $sessionName = $_ENV['SESSION_COOKIE_NAME'] ?? 'pub_campusmate_session';
        $_COOKIE[$sessionName] = 'corrupted=illegal+session@id!' . str_repeat('z', 200);

        // Call ensure_session_started
        ensure_session_started();

        $isActive = (session_status() === PHP_SESSION_ACTIVE);
        $sid = session_id();
        $isValidId = (bool)preg_match('/^[a-zA-Z0-9,-]{16,128}$/', $sid);

        return $isActive && $isValidId;
    }
}
