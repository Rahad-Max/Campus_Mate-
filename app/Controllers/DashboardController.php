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
use App\Models\Exam;
use App\Models\Notification;

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
            'student' => '/student/dashboard',
            'teacher' => '/teacher/dashboard',
            'academic_advisor' => '/advisor/dashboard',
            'department_coordinator' => '/coordinator/dashboard',
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
        $examModel = new Exam();
        $notifModel = new Notification();

        $student = $studentModel->getFullDetails($studentModel->findByUserId($user['id'])['id'] ?? 0);
        $currentSemester = $semesterModel->getCurrent();
        $semId = (int)($currentSemester['id'] ?? 2);

        $studentId = $student ? (int)$student['id'] : 0;

        $enrolledCourses = $studentId ? $regModel->getEnrolledCourses($studentId, $semId) : [];
        $totalCredits = $studentId ? $regModel->getTotalRegisteredCredits($studentId, $semId) : 0.0;
        $timetable = $studentId ? $scheduleModel->getStudentTimetable($studentId, $semId) : [];
        $todayClasses = $studentId ? $scheduleModel->getTodayClassesForStudent($studentId, $semId) : [];
        $upcomingExams = $studentId ? $examModel->getExamScheduleForStudent($studentId, $semId) : [];
        $notices = $noticeModel->getActiveNotices('student', $student['department_id'] ?? null);
        $notifications = $notifModel->getUserNotifications((int)$user['id']);

        $this->render('dashboards/student', [
            'title' => 'Student Dashboard — PUB CampusMate',
            'student' => $student,
            'semester' => $currentSemester,
            'enrolledCourses' => $enrolledCourses,
            'totalCredits' => $totalCredits,
            'timetable' => $timetable,
            'todayClasses' => $todayClasses,
            'upcomingExams' => $upcomingExams,
            'notices' => array_slice($notices, 0, 5),
            'notifications' => array_slice($notifications, 0, 5)
        ]);
    }

    public function teacher(): void
    {
        $user = $this->authUser();
        $teacherModel = new Teacher();
        $semesterModel = new Semester();
        $scheduleModel = new ClassSchedule();
        $noticeModel = new Notice();
        $examModel = new Exam();
        $notifModel = new Notification();

        $teacher = $teacherModel->getFullDetails($teacherModel->findByUserId($user['id'])['id'] ?? 0);
        $currentSemester = $semesterModel->getCurrent();
        $semId = (int)($currentSemester['id'] ?? 2);

        $teacherId = $teacher ? (int)$teacher['id'] : 0;

        $assignedSections = $teacherId ? $teacherModel->getAssignedSections($teacherId, $semId) : [];
        $timetable = $teacherId ? $scheduleModel->getTeacherTimetable($teacherId, $semId) : [];
        $todayClasses = $teacherId ? $scheduleModel->getTodayClassesForTeacher($teacherId, $semId) : [];
        $upcomingExams = $teacherId ? $examModel->getExamScheduleForTeacher($teacherId, $semId) : [];
        $notices = $noticeModel->getActiveNotices('teacher', $teacher['department_id'] ?? null);
        $notifications = $notifModel->getUserNotifications((int)$user['id']);

        $this->render('dashboards/teacher', [
            'title' => 'Faculty Dashboard — PUB CampusMate',
            'teacher' => $teacher,
            'semester' => $currentSemester,
            'assignedSections' => $assignedSections,
            'timetable' => $timetable,
            'todayClasses' => $todayClasses,
            'upcomingExams' => $upcomingExams,
            'notices' => array_slice($notices, 0, 5),
            'notifications' => array_slice($notifications, 0, 5)
        ]);
    }

    public function advisor(): void
    {
        $user = $this->authUser();
        $teacherModel = new Teacher();
        $requestModel = new RegistrationRequest();
        $semesterModel = new Semester();
        $noticeModel = new Notice();
        $notifModel = new Notification();

        $teacher = $teacherModel->getFullDetails($teacherModel->findByUserId($user['id'])['id'] ?? 0);
        $teacherId = $teacher ? (int)$teacher['id'] : 0;

        $advisees = $teacherId ? $teacherModel->getAssignedAdvisees($teacherId) : [];
        $pendingRequests = $teacherId ? $requestModel->getPendingForAdvisor($teacherId) : [];
        $currentSemester = $semesterModel->getCurrent();
        $notices = $noticeModel->getActiveNotices('teacher', $teacher['department_id'] ?? null);
        $notifications = $notifModel->getUserNotifications((int)$user['id']);

        $this->render('dashboards/advisor', [
            'title' => 'Academic Advisor Dashboard — PUB CampusMate',
            'advisor' => $teacher,
            'advisees' => $advisees,
            'pendingRequests' => $pendingRequests,
            'semester' => $currentSemester,
            'notices' => array_slice($notices, 0, 5),
            'notifications' => array_slice($notifications, 0, 5)
        ]);
    }

    public function coordinator(): void
    {
        $user = $this->authUser();
        $offeringModel = new CourseOffering();
        $semesterModel = new Semester();
        $noticeModel = new Notice();
        $db = \App\Core\Database::getInstance();

        $currentSemester = $semesterModel->getCurrent();
        $semId = (int)($currentSemester['id'] ?? 2);

        $offerings = $offeringModel->getOfferingsBySemester($semId);

        // Department-level statistics directly from database
        $totalStudents = (int)$db->fetch("SELECT COUNT(*) as c FROM `students`")['c'];
        $totalTeachers = (int)$db->fetch("SELECT COUNT(*) as c FROM `teachers`")['c'];
        $totalSections = (int)$db->fetch(
            "SELECT COUNT(s.id) as c FROM `sections` s 
             JOIN `course_offerings` co ON co.id = s.course_offering_id 
             WHERE co.semester_id = :sid",
            [':sid' => $semId]
        )['c'];
        $totalRegistrations = (int)$db->fetch(
            "SELECT COUNT(*) as c FROM `registrations` 
             WHERE semester_id = :sid AND status = 'enrolled'",
            [':sid' => $semId]
        )['c'];
        $pendingTasks = (int)$db->fetch(
            "SELECT COUNT(*) as c FROM `registration_requests` 
             WHERE semester_id = :sid AND status IN ('submitted', 'advisor_approved')",
            [':sid' => $semId]
        )['c'];

        $stats = [
            'total_students' => $totalStudents,
            'total_teachers' => $totalTeachers,
            'total_offerings' => count($offerings),
            'total_sections' => $totalSections,
            'total_registrations' => $totalRegistrations,
            'pending_tasks' => $pendingTasks
        ];

        // Recent schedule updates / revisions
        $recentRevisions = $db->fetchAll(
            "SELECT sr.*, cs.day_of_week as current_day, c.course_code, sec.section_name, u.first_name, u.last_name
             FROM `schedule_revisions` sr
             JOIN `class_schedules` cs ON cs.id = sr.class_schedule_id
             JOIN `sections` sec ON sec.id = cs.section_id
             JOIN `course_offerings` co ON co.id = sec.course_offering_id
             JOIN `courses` c ON c.id = co.course_id
             JOIN `users` u ON u.id = sr.revised_by
             ORDER BY sr.created_at DESC LIMIT 5"
        );

        $recentAudits = $db->fetchAll("SELECT * FROM `audit_logs` ORDER BY created_at DESC LIMIT 6");
        $notices = $noticeModel->getActiveNotices(null, 1);

        $this->render('dashboards/coordinator', [
            'title' => 'Department Coordinator Dashboard — PUB CampusMate',
            'semester' => $currentSemester,
            'offerings' => $offerings,
            'stats' => $stats,
            'recentRevisions' => $recentRevisions,
            'recentAudits' => $recentAudits,
            'notices' => array_slice($notices, 0, 5)
        ]);
    }
}
