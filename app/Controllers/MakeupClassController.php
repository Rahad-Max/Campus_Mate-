<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Exceptions\AppException;
use App\Models\ClassSchedule;
use App\Models\CourseOffering;
use App\Models\MakeupClass;
use App\Models\Room;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\TimetableConflictService;

class MakeupClassController extends Controller
{
    private MakeupClass $makeupModel;
    private Semester $semesterModel;
    private TimetableConflictService $conflictService;

    public function __construct()
    {
        $this->makeupModel = new MakeupClass();
        $this->semesterModel = new Semester();
        $this->conflictService = new TimetableConflictService();
    }

    /**
     * Public/Authenticated Index for Students & Teachers
     */
    public function studentAndTeacherView(): void
    {
        $user = $this->authUser();
        $role = $user['role'] ?? 'guest';
        $semester = $this->semesterModel->getCurrent();
        $semId = (int)($semester['id'] ?? 2);

        $makeupClasses = [];

        if ($role === 'student') {
            $student = (new Student())->findByUserId((int)$user['id']);
            $makeupClasses = $student ? $this->makeupModel->getForStudent((int)$student['id'], $semId) : [];
        } elseif (in_array($role, ['teacher', 'academic_advisor'], true)) {
            $teacher = (new Teacher())->findByUserId((int)$user['id']);
            $makeupClasses = $teacher ? $this->makeupModel->getForTeacher((int)$teacher['id'], $semId) : [];
        } else {
            $this->redirect(url('/coordinator/makeup-classes'));
            return;
        }

        $this->render('makeup_classes/index', [
            'title' => 'Make-up Classes — PUB CampusMate',
            'semester' => $semester,
            'makeupClasses' => $makeupClasses,
            'role' => $role
        ]);
    }

    /**
     * Coordinator Index: Master List of Make-up Classes
     */
    public function index(): void
    {
        if (!has_role('department_coordinator')) {
            $this->setFlash('danger', 'Unauthorized access.');
            $this->redirect(url('/dashboard'));
            return;
        }

        $currentSem = $this->semesterModel->getCurrent();
        $semId = isset($_GET['semester_id']) && $_GET['semester_id'] !== ''
            ? (int)$_GET['semester_id']
            : (int)($currentSem['id'] ?? 2);

        $filters = [];
        if (!empty($_GET['status'])) {
            $filters['status'] = trim($_GET['status']);
        }
        if (!empty($_GET['teacher_id'])) {
            $filters['teacher_id'] = (int)$_GET['teacher_id'];
        }
        if (!empty($_GET['room_id'])) {
            $filters['room_id'] = (int)$_GET['room_id'];
        }

        $makeupClasses = $this->makeupModel->getAllWithDetails($semId, $filters);
        $semesters = $this->semesterModel->all();
        $teachers = (new Teacher())->getAllWithDetails();
        $rooms = (new Room())->all();

        $this->render('coordinator/makeup_classes/index', [
            'title' => 'Make-up Classes Management — PUB CampusMate',
            'makeupClasses' => $makeupClasses,
            'semesters' => $semesters,
            'selectedSemesterId' => $semId,
            'teachers' => $teachers,
            'rooms' => $rooms,
            'filters' => $filters
        ]);
    }

    /**
     * Show Schedule Make-up Class Form
     */
    public function create(): void
    {
        if (!has_role('department_coordinator')) {
            $this->setFlash('danger', 'Unauthorized access.');
            $this->redirect(url('/dashboard'));
            return;
        }

        $currentSem = $this->semesterModel->getCurrent();
        $semId = (int)($currentSem['id'] ?? 2);

        $scheduleId = !empty($_GET['schedule_id']) ? (int)$_GET['schedule_id'] : null;
        $originalSchedule = null;
        if ($scheduleId) {
            $originalSchedule = (new ClassSchedule())->getScheduleWithDetails($scheduleId);
        }

        $offeringModel = new CourseOffering();
        $offerings = $offeringModel->getOfferingsBySemester($semId);

        $sectionModel = new Section();
        $sections = $sectionModel->getAllWithDetails($semId);

        $rooms = (new Room())->getAvailable();
        $teachers = (new Teacher())->getAllWithDetails();

        $this->render('coordinator/makeup_classes/create', [
            'title' => 'Schedule Make-up Class — PUB CampusMate',
            'currentSemester' => $currentSem,
            'originalSchedule' => $originalSchedule,
            'offerings' => $offerings,
            'sections' => $sections,
            'rooms' => $rooms,
            'teachers' => $teachers
        ]);
    }

    /**
     * Store Make-up Class
     */
    public function store(): void
    {
        if (!has_role('department_coordinator')) {
            $this->setFlash('danger', 'Unauthorized access.');
            $this->redirect(url('/dashboard'));
            return;
        }

        $user = $this->authUser();

        $sectionId = (int)($_POST['section_id'] ?? 0);
        $classScheduleId = !empty($_POST['class_schedule_id']) ? (int)$_POST['class_schedule_id'] : null;
        $teacherId = (int)($_POST['teacher_id'] ?? 0);
        $roomId = (int)($_POST['room_id'] ?? 0);
        $makeupDate = trim($_POST['makeup_date'] ?? '');
        $startTime = trim($_POST['start_time'] ?? '');
        $endTime = trim($_POST['end_time'] ?? '');
        $reason = trim($_POST['reason'] ?? '');

        $errors = [];
        if ($sectionId <= 0) $errors[] = 'Please select a valid section.';
        if ($teacherId <= 0) $errors[] = 'Please assign an instructor.';
        if ($roomId <= 0) $errors[] = 'Please select a classroom.';
        if (empty($makeupDate)) $errors[] = 'Replacement date is required.';
        if (empty($startTime) || empty($endTime)) $errors[] = 'Start and end times are required.';
        if (empty($reason)) $errors[] = 'Please provide a reason for the make-up class.';

        $sec = (new Section())->getDetailedInfo($sectionId);
        $semesterId = (int)($sec['semester_id'] ?? 2);

        if (empty($errors)) {
            try {
                $this->conflictService->validateMakeupClassSlot(
                    $sectionId,
                    $roomId,
                    $teacherId,
                    $semesterId,
                    $makeupDate,
                    $startTime,
                    $endTime
                );
            } catch (AppException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            $this->setFlash('danger', implode(' ', $errors));
            $this->redirect(url('/coordinator/makeup-classes/create' . ($classScheduleId ? '?schedule_id=' . $classScheduleId : '')));
            return;
        }

        $data = [
            'section_id' => $sectionId,
            'class_schedule_id' => $classScheduleId,
            'teacher_id' => $teacherId,
            'room_id' => $roomId,
            'makeup_date' => $makeupDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'reason' => $reason,
            'status' => 'scheduled',
            'scheduled_by' => (int)$user['id']
        ];

        $makeupId = $this->makeupModel->create($data);

        if (!$makeupId) {
            $this->setFlash('danger', 'Failed to schedule make-up class in database.');
            $this->redirect(url('/coordinator/makeup-classes/create'));
            return;
        }

        // Notify affected users
        $this->notifyMakeupParticipants($sectionId, $teacherId, $makeupDate, $startTime, $endTime, $reason);

        $this->setFlash('success', 'Make-up class scheduled successfully. Affected students and instructor have been notified.');
        $this->redirect(url('/coordinator/makeup-classes'));
    }

    /**
     * Cancel Make-up Class
     */
    public function cancel(string $id): void
    {
        if (!has_role('department_coordinator')) {
            $this->setFlash('danger', 'Unauthorized access.');
            $this->redirect(url('/dashboard'));
            return;
        }

        $makeupId = (int)$id;
        $makeup = $this->makeupModel->find($makeupId);
        if (!$makeup) {
            $this->setFlash('danger', 'Make-up class record not found.');
            $this->redirect(url('/coordinator/makeup-classes'));
            return;
        }

        $this->makeupModel->update($makeupId, ['status' => 'cancelled']);

        // Notify participants
        $this->notifyMakeupCancellation((int)$makeup['section_id'], (int)$makeup['teacher_id'], $makeup['makeup_date']);

        $this->setFlash('success', 'Make-up class cancelled and notifications sent.');
        $this->redirect(url('/coordinator/makeup-classes'));
    }

    /**
     * Send notifications when make-up class is scheduled
     */
    private function notifyMakeupParticipants(int $sectionId, int $teacherId, string $date, string $startTime, string $endTime, string $reason): void
    {
        $db = Database::getInstance();

        $students = $db->fetchAll(
            "SELECT u.id as user_id, c.course_code, s.section_name
             FROM `registrations` reg
             JOIN `sections` s ON s.id = reg.section_id
             JOIN `course_offerings` co ON co.id = s.course_offering_id
             JOIN `courses` c ON c.id = co.course_id
             JOIN `students` stu ON stu.id = reg.student_id
             JOIN `users` u ON u.id = stu.user_id
             WHERE reg.section_id = :secid AND reg.status = 'enrolled'",
            [':secid' => $sectionId]
        );

        $courseLabel = !empty($students[0]['course_code']) ? $students[0]['course_code'] . ' (Sec ' . $students[0]['section_name'] . ')' : 'Course';
        $title = "Make-up Class Scheduled: {$courseLabel}";
        $msg = "A make-up class has been scheduled for {$date} from " . format_time($startTime) . " to " . format_time($endTime) . ". Reason: {$reason}";

        foreach ($students as $stu) {
            $db->execute(
                "INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `action_url`, `is_read`, `created_at`)
                 VALUES (:uid, :title, :msg, 'academic', '/makeup-classes', 0, NOW())",
                [':uid' => $stu['user_id'], ':title' => $title, ':msg' => $msg]
            );
        }

        $teacher = (new Teacher())->find($teacherId);
        if ($teacher && !empty($teacher['user_id'])) {
            $db->execute(
                "INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `action_url`, `is_read`, `created_at`)
                 VALUES (:uid, :title, :msg, 'academic', '/makeup-classes', 0, NOW())",
                [':uid' => $teacher['user_id'], ':title' => $title, ':msg' => $msg]
            );
        }
    }

    /**
     * Send notifications when make-up class is cancelled
     */
    private function notifyMakeupCancellation(int $sectionId, int $teacherId, string $date): void
    {
        $db = Database::getInstance();

        $students = $db->fetchAll(
            "SELECT u.id as user_id, c.course_code, s.section_name
             FROM `registrations` reg
             JOIN `sections` s ON s.id = reg.section_id
             JOIN `course_offerings` co ON co.id = s.course_offering_id
             JOIN `courses` c ON c.id = co.course_id
             JOIN `students` stu ON stu.id = reg.student_id
             JOIN `users` u ON u.id = stu.user_id
             WHERE reg.section_id = :secid AND reg.status = 'enrolled'",
            [':secid' => $sectionId]
        );

        $courseLabel = !empty($students[0]['course_code']) ? $students[0]['course_code'] . ' (Sec ' . $students[0]['section_name'] . ')' : 'Course';
        $title = "Make-up Class Cancelled: {$courseLabel}";
        $msg = "The make-up class scheduled for {$date} has been cancelled by the department coordinator.";

        foreach ($students as $stu) {
            $db->execute(
                "INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `action_url`, `is_read`, `created_at`)
                 VALUES (:uid, :title, :msg, 'academic', '/makeup-classes', 0, NOW())",
                [':uid' => $stu['user_id'], ':title' => $title, ':msg' => $msg]
            );
        }

        $teacher = (new Teacher())->find($teacherId);
        if ($teacher && !empty($teacher['user_id'])) {
            $db->execute(
                "INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `action_url`, `is_read`, `created_at`)
                 VALUES (:uid, :title, :msg, 'academic', '/makeup-classes', 0, NOW())",
                [':uid' => $teacher['user_id'], ':title' => $title, ':msg' => $msg]
            );
        }
    }
}
