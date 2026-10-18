<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Exceptions\AppException;
use App\Models\CourseOffering;
use App\Models\Exam;
use App\Models\Notification;
use App\Models\Room;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\TimetableConflictService;

class ExamController extends Controller
{
    private Exam $examModel;
    private Semester $semesterModel;
    private TimetableConflictService $conflictService;

    public function __construct()
    {
        $this->examModel = new Exam();
        $this->semesterModel = new Semester();
        $this->conflictService = new TimetableConflictService();
    }

    /**
     * Student & Teacher Examination Routine View
     */
    public function index(): void
    {
        $user = $this->authUser();
        $role = $user['role'] ?? 'guest';
        $semester = $this->semesterModel->getCurrent();
        $semId = (int)($semester['id'] ?? 2);

        $exams = [];

        if ($role === 'student') {
            $studentModel = new Student();
            $s = $studentModel->findByUserId((int)$user['id']);
            $exams = $s ? $this->examModel->getExamScheduleForStudent((int)$s['id'], $semId) : [];
        } elseif (in_array($role, ['teacher', 'academic_advisor'], true)) {
            $teacherModel = new Teacher();
            $t = $teacherModel->findByUserId((int)$user['id']);
            $exams = $t ? $this->examModel->getExamScheduleForTeacher((int)$t['id'], $semId) : [];
        } else {
            // Coordinator: redirect to coordinator exams management
            $this->redirect(url('/coordinator/exams'));
            return;
        }

        $this->render('exams/index', [
            'title' => 'Examination Routine — PUB CampusMate',
            'semester' => $semester,
            'exams' => $exams,
            'role' => $role
        ]);
    }

    /**
     * Coordinator Master Exam Schedule List
     */
    public function coordinatorIndex(): void
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
        if (!empty($_GET['exam_type'])) {
            $filters['exam_type'] = trim($_GET['exam_type']);
        }
        if (!empty($_GET['status'])) {
            $filters['status'] = trim($_GET['status']);
        }
        if (!empty($_GET['course_id'])) {
            $filters['course_id'] = (int)$_GET['course_id'];
        }
        if (!empty($_GET['room_id'])) {
            $filters['room_id'] = (int)$_GET['room_id'];
        }
        if (!empty($_GET['exam_date'])) {
            $filters['exam_date'] = trim($_GET['exam_date']);
        }

        $exams = $this->examModel->getAllWithDetails($semId, $filters);
        $semesters = $this->semesterModel->all();
        $roomModel = new Room();
        $rooms = $roomModel->all();

        $this->render('coordinator/exams/index', [
            'title' => 'Examination Routines Management — PUB CampusMate',
            'exams' => $exams,
            'semesters' => $semesters,
            'selectedSemesterId' => $semId,
            'rooms' => $rooms,
            'filters' => $filters
        ]);
    }

    /**
     * Show Create Exam Form
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

        $offeringModel = new CourseOffering();
        $offerings = $offeringModel->getOfferingsBySemester($semId);

        $roomModel = new Room();
        $rooms = $roomModel->getAvailable();

        $teacherModel = new Teacher();
        $teachers = $teacherModel->getAllWithDetails();

        $this->render('coordinator/exams/create', [
            'title' => 'Schedule New Examination — PUB CampusMate',
            'currentSemester' => $currentSem,
            'offerings' => $offerings,
            'rooms' => $rooms,
            'teachers' => $teachers
        ]);
    }

    /**
     * Store New Exam
     */
    public function store(): void
    {
        if (!has_role('department_coordinator')) {
            $this->setFlash('danger', 'Unauthorized access.');
            $this->redirect(url('/dashboard'));
            return;
        }

        $offeringId = (int)($_POST['course_offering_id'] ?? 0);
        $sectionId = !empty($_POST['section_id']) ? (int)$_POST['section_id'] : null;
        $examType = trim($_POST['exam_type'] ?? 'final');
        $examDate = trim($_POST['exam_date'] ?? '');
        $startTime = trim($_POST['start_time'] ?? '');
        $endTime = trim($_POST['end_time'] ?? '');
        $roomId = (int)($_POST['room_id'] ?? 0);
        $invigilatorId = !empty($_POST['invigilator_teacher_id']) ? (int)$_POST['invigilator_teacher_id'] : null;
        $status = in_array($_POST['status'] ?? '', ['draft', 'published', 'scheduled'], true) ? $_POST['status'] : 'draft';
        $instructions = trim($_POST['instructions'] ?? '');

        // Validation
        $errors = [];
        if ($offeringId <= 0) $errors[] = 'Please select a valid course offering.';
        if (empty($examDate)) $errors[] = 'Exam date is required.';
        if (empty($startTime) || empty($endTime)) $errors[] = 'Exam start and end times are required.';
        if ($roomId <= 0) $errors[] = 'Please assign an examination room.';

        if (empty($errors)) {
            try {
                $this->conflictService->validateExamSlot(
                    $offeringId,
                    $sectionId,
                    $roomId,
                    $invigilatorId,
                    $examDate,
                    $startTime,
                    $endTime
                );
            } catch (AppException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            $this->setFlash('danger', implode(' ', $errors));
            $this->redirect(url('/coordinator/exams/create'));
            return;
        }

        $examData = [
            'course_offering_id' => $offeringId,
            'section_id' => $sectionId,
            'exam_type' => $examType,
            'exam_date' => $examDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'room_id' => $roomId,
            'invigilator_teacher_id' => $invigilatorId,
            'status' => $status,
            'instructions' => $instructions
        ];

        $examId = $this->examModel->create($examData);

        if (!$examId) {
            $this->setFlash('danger', 'Database error: Could not schedule examination.');
            $this->redirect(url('/coordinator/exams/create'));
            return;
        }

        if ($status === 'published' || $status === 'scheduled') {
            $this->notifyExamParticipants((int)$examId, 'New Examination Scheduled', 'A new ' . strtoupper($examType) . ' exam has been scheduled for ' . format_date($examDate) . '.');
        }

        $this->setFlash('success', 'Examination routine created successfully (' . ucfirst($status) . ').');
        $this->redirect(url('/coordinator/exams'));
    }

    /**
     * Show Edit Form for Exam
     */
    public function edit(string $id): void
    {
        if (!has_role('department_coordinator')) {
            $this->setFlash('danger', 'Unauthorized access.');
            $this->redirect(url('/dashboard'));
            return;
        }

        $examId = (int)$id;
        $exam = $this->examModel->getExamWithDetails($examId);
        if (!$exam) {
            $this->setFlash('danger', 'Examination record not found.');
            $this->redirect(url('/coordinator/exams'));
            return;
        }

        $semId = (int)$exam['semester_id'];
        $offeringModel = new CourseOffering();
        $offerings = $offeringModel->getOfferingsBySemester($semId);
        $sections = $offeringModel->getSections((int)$exam['course_offering_id']);

        $roomModel = new Room();
        $rooms = $roomModel->getAvailable();

        $teacherModel = new Teacher();
        $teachers = $teacherModel->getAllWithDetails();

        $this->render('coordinator/exams/edit', [
            'title' => 'Edit Examination Routine — PUB CampusMate',
            'exam' => $exam,
            'offerings' => $offerings,
            'sections' => $sections,
            'rooms' => $rooms,
            'teachers' => $teachers
        ]);
    }

    /**
     * Update Existing Exam
     */
    public function update(string $id): void
    {
        if (!has_role('department_coordinator')) {
            $this->setFlash('danger', 'Unauthorized access.');
            $this->redirect(url('/dashboard'));
            return;
        }

        $examId = (int)$id;
        $exam = $this->examModel->getExamWithDetails($examId);
        if (!$exam) {
            $this->setFlash('danger', 'Examination record not found.');
            $this->redirect(url('/coordinator/exams'));
            return;
        }

        $offeringId = (int)($_POST['course_offering_id'] ?? $exam['course_offering_id']);
        $sectionId = !empty($_POST['section_id']) ? (int)$_POST['section_id'] : null;
        $examType = trim($_POST['exam_type'] ?? $exam['exam_type']);
        $examDate = trim($_POST['exam_date'] ?? $exam['exam_date']);
        $startTime = trim($_POST['start_time'] ?? $exam['start_time']);
        $endTime = trim($_POST['end_time'] ?? $exam['end_time']);
        $roomId = (int)($_POST['room_id'] ?? $exam['room_id']);
        $invigilatorId = !empty($_POST['invigilator_teacher_id']) ? (int)$_POST['invigilator_teacher_id'] : null;
        $status = trim($_POST['status'] ?? $exam['status']);
        $instructions = trim($_POST['instructions'] ?? $exam['instructions']);

        $errors = [];
        if (empty($examDate)) $errors[] = 'Exam date is required.';
        if (empty($startTime) || empty($endTime)) $errors[] = 'Start and end times are required.';

        if (empty($errors)) {
            try {
                $this->conflictService->validateExamSlot(
                    $offeringId,
                    $sectionId,
                    $roomId,
                    $invigilatorId,
                    $examDate,
                    $startTime,
                    $endTime,
                    $examId
                );
            } catch (AppException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            $this->setFlash('danger', implode(' ', $errors));
            $this->redirect(url('/coordinator/exams/' . $examId . '/edit'));
            return;
        }

        $updateData = [
            'course_offering_id' => $offeringId,
            'section_id' => $sectionId,
            'exam_type' => $examType,
            'exam_date' => $examDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'room_id' => $roomId,
            'invigilator_teacher_id' => $invigilatorId,
            'status' => $status,
            'instructions' => $instructions
        ];

        $this->examModel->update($examId, $updateData);

        if ($status === 'published' || $status === 'scheduled') {
            $this->notifyExamParticipants($examId, 'Examination Routine Updated', 'The examination routine for ' . ($exam['course_code'] ?? 'course') . ' on ' . format_date($examDate) . ' has been updated.');
        }

        $this->setFlash('success', 'Examination routine updated successfully.');
        $this->redirect(url('/coordinator/exams'));
    }

    /**
     * Publish Examination Routine
     */
    public function publish(string $id): void
    {
        if (!has_role('department_coordinator')) {
            $this->setFlash('danger', 'Unauthorized access.');
            $this->redirect(url('/dashboard'));
            return;
        }

        $examId = (int)$id;
        $exam = $this->examModel->getExamWithDetails($examId);
        if (!$exam) {
            $this->setFlash('danger', 'Exam not found.');
            $this->redirect(url('/coordinator/exams'));
            return;
        }

        $this->examModel->update($examId, ['status' => 'published']);
        $this->notifyExamParticipants($examId, 'Examination Schedule Published', 'Official examination schedule for ' . ($exam['course_code'] ?? 'your course') . ' is published.');

        $this->setFlash('success', 'Examination schedule published and participants notified.');
        $this->redirect(url('/coordinator/exams'));
    }

    /**
     * Cancel Examination
     */
    public function cancel(string $id): void
    {
        if (!has_role('department_coordinator')) {
            $this->setFlash('danger', 'Unauthorized access.');
            $this->redirect(url('/dashboard'));
            return;
        }

        $examId = (int)$id;
        $exam = $this->examModel->getExamWithDetails($examId);
        if (!$exam) {
            $this->setFlash('danger', 'Exam not found.');
            $this->redirect(url('/coordinator/exams'));
            return;
        }

        $this->examModel->update($examId, ['status' => 'cancelled']);
        $this->notifyExamParticipants($examId, 'Examination Cancelled', 'The scheduled examination for ' . ($exam['course_code'] ?? 'your course') . ' on ' . format_date($exam['exam_date']) . ' has been cancelled.');

        $this->setFlash('success', 'Examination routine cancelled.');
        $this->redirect(url('/coordinator/exams'));
    }

    /**
     * Internal helper to dispatch notifications to affected students and invigilator
     */
    private function notifyExamParticipants(int $examId, string $title, string $message): void
    {
        $exam = $this->examModel->getExamWithDetails($examId);
        if (!$exam) return;

        $db = Database::getInstance();

        // 1. Enrolled students
        $studentSql = "SELECT DISTINCT u.id as user_id
                       FROM `registrations` reg
                       JOIN `sections` s ON s.id = reg.section_id
                       JOIN `students` stu ON stu.id = reg.student_id
                       JOIN `users` u ON u.id = stu.user_id
                       WHERE s.course_offering_id = :coid AND reg.status = 'enrolled'";
        $params = [':coid' => $exam['course_offering_id']];

        if (!empty($exam['section_id'])) {
            $studentSql .= " AND reg.section_id = :secid";
            $params[':secid'] = $exam['section_id'];
        }

        $students = $db->fetchAll($studentSql, $params);
        foreach ($students as $stu) {
            $db->execute(
                "INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `action_url`, `is_read`, `created_at`)
                 VALUES (:uid, :title, :msg, 'academic', '/exams', 0, NOW())",
                [
                    ':uid' => $stu['user_id'],
                    ':title' => $title,
                    ':msg' => $message
                ]
            );
        }

        // 2. Invigilator
        if (!empty($exam['invigilator_teacher_id'])) {
            $teacher = (new Teacher())->find((int)$exam['invigilator_teacher_id']);
            if ($teacher && !empty($teacher['user_id'])) {
                $db->execute(
                    "INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `action_url`, `is_read`, `created_at`)
                     VALUES (:uid, :title, :msg, 'academic', '/exams', 0, NOW())",
                    [
                        ':uid' => $teacher['user_id'],
                        ':title' => $title,
                        ':msg' => $message . ' (You are assigned as Invigilator)'
                    ]
                );
            }
        }
    }
}
