<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Semester;
use App\Models\Registration;
use App\Models\RegistrationRequest;
use App\Models\ClassSchedule;
use App\Models\Notice;
use App\Models\CourseOffering;

class DashboardController extends Controller
{
    public function index(): void
    {
        $user = $this->authUser();
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $this->redirect(match ($user['role']) {
            'student' => '/dashboard/student',
            'teacher' => '/dashboard/teacher',
            'academic_advisor' => '/dashboard/advisor',
            'department_coordinator' => '/dashboard/coordinator',
            default => '/login'
        });
    }

    public function student(): void
    {
        $user = $this->authUser();
        $studentModel = new Student();
        $semesterModel = new Semester();
        $regModel = new Registration();
        $scheduleModel = new ClassSchedule();
        $noticeModel = new Notice();

        $student = $studentModel->getFullDetails($studentModel->findByUserId($user['id'])['id'] ?? 0);
        $currentSemester = $semesterModel->getCurrent();
        $semId = $currentSemester['id'] ?? 2;

        $enrolledCourses = $student ? $regModel->getEnrolledCourses((int)$student['id'], $semId) : [];
        $totalCredits = $student ? $regModel->getTotalRegisteredCredits((int)$student['id'], $semId) : 0.0;
        $timetable = $student ? $scheduleModel->getStudentTimetable((int)$student['id'], $semId) : [];
        $notices = $noticeModel->getActiveNotices('student', $student['department_id'] ?? null);

        $this->render('dashboards/student', [
            'title' => 'Student Dashboard — PUB CampusMate',
            'student' => $student,
            'semester' => $currentSemester,
            'enrolledCourses' => $enrolledCourses,
            'totalCredits' => $totalCredits,
            'timetable' => $timetable,
            'notices' => array_slice($notices, 0, 5)
        ]);
    }

    public function teacher(): void
    {
        $user = $this->authUser();
        $teacherModel = new Teacher();
        $semesterModel = new Semester();
        $scheduleModel = new ClassSchedule();
        $noticeModel = new Notice();

        $teacher = $teacherModel->getFullDetails($teacherModel->findByUserId($user['id'])['id'] ?? 0);
        $currentSemester = $semesterModel->getCurrent();
        $semId = $currentSemester['id'] ?? 2;

        $assignedSections = $teacher ? $teacherModel->getAssignedSections((int)$teacher['id'], $semId) : [];
        $timetable = $teacher ? $scheduleModel->getTeacherTimetable((int)$teacher['id'], $semId) : [];
        $notices = $noticeModel->getActiveNotices('teacher', $teacher['department_id'] ?? null);

        $this->render('dashboards/teacher', [
            'title' => 'Faculty Dashboard — PUB CampusMate',
            'teacher' => $teacher,
            'semester' => $currentSemester,
            'assignedSections' => $assignedSections,
            'timetable' => $timetable,
            'notices' => array_slice($notices, 0, 5)
        ]);
    }

    public function advisor(): void
    {
        $user = $this->authUser();
        $teacherModel = new Teacher();
        $requestModel = new RegistrationRequest();
        $semesterModel = new Semester();

        $teacher = $teacherModel->getFullDetails($teacherModel->findByUserId($user['id'])['id'] ?? 0);
        $advisees = $teacher ? $teacherModel->getAssignedAdvisees((int)$teacher['id']) : [];
        $pendingRequests = $teacher ? $requestModel->getPendingForAdvisor((int)$teacher['id']) : [];
        $currentSemester = $semesterModel->getCurrent();

        $this->render('dashboards/advisor', [
            'title' => 'Academic Advisor Dashboard — PUB CampusMate',
            'advisor' => $teacher,
            'advisees' => $advisees,
            'pendingRequests' => $pendingRequests,
            'semester' => $currentSemester
        ]);
    }

    public function coordinator(): void
    {
        $user = $this->authUser();
        $offeringModel = new CourseOffering();
        $semesterModel = new Semester();
        $currentSemester = $semesterModel->getCurrent();
        $semId = $currentSemester['id'] ?? 2;

        $offerings = $offeringModel->getOfferingsBySemester($semId);

        $db = \App\Core\Database::getInstance();
        $stats = [
            'total_students' => (int)$db->fetch("SELECT COUNT(*) as c FROM students")['c'],
            'total_teachers' => (int)$db->fetch("SELECT COUNT(*) as c FROM teachers")['c'],
            'total_offerings' => count($offerings),
            'total_registrations' => (int)$db->fetch("SELECT COUNT(*) as c FROM registrations WHERE semester_id = :sid AND status = 'enrolled'", [':sid' => $semId])['c']
        ];

        $recentAudits = $db->fetchAll("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 5");

        $this->render('dashboards/coordinator', [
            'title' => 'Coordinator Dashboard — PUB CampusMate',
            'semester' => $currentSemester,
            'offerings' => $offerings,
            'stats' => $stats,
            'recentAudits' => $recentAudits
        ]);
    }
}
