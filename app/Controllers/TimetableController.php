<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\ClassSchedule;
use App\Models\CourseOffering;
use App\Models\Room;
use App\Models\ScheduleRevision;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Services\TimetableConflictService;
use App\Services\TimetableService;

class TimetableController extends Controller
{
    private ClassSchedule $scheduleModel;
    private Semester $semesterModel;
    private TimetableService $timetableService;
    private TimetableConflictService $conflictService;

    public function __construct()
    {
        $this->scheduleModel = new ClassSchedule();
        $this->semesterModel = new Semester();
        $this->conflictService = new TimetableConflictService();
        $this->timetableService = new TimetableService();
    }

    /**
     * Role-based Timetable Index
     */
    public function index(): void
    {
        $user = $this->authUser();
        $role = $user['role'] ?? 'guest';
        $semester = $this->semesterModel->getCurrent();
        $semId = (int)($semester['id'] ?? 2);

        $timetable = [];

        if ($role === 'student') {
            $studentModel = new Student();
            $s = $studentModel->findByUserId((int)$user['id']);
            $timetable = $s ? $this->scheduleModel->getStudentTimetable((int)$s['id'], $semId) : [];
        } elseif (in_array($role, ['teacher', 'academic_advisor'], true)) {
            $teacherModel = new Teacher();
            $t = $teacherModel->findByUserId((int)$user['id']);
            $timetable = $t ? $this->scheduleModel->getTeacherTimetable((int)$t['id'], $semId) : [];
        } else {
            // Coordinator view: all schedules
            $timetable = $this->scheduleModel->getAllWithDetails($semId, []);
        }

        $this->render('timetable/index', [
            'title' => 'Weekly Academic Timetable — PUB CampusMate',
            'semester' => $semester,
            'timetable' => $timetable,
            'role' => $role
        ]);
    }

    /**
     * Coordinator Master Timetable Management
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
        if (!empty($_GET['day_of_week'])) {
            $filters['day_of_week'] = trim($_GET['day_of_week']);
        }
        if (!empty($_GET['room_id'])) {
            $filters['room_id'] = (int)$_GET['room_id'];
        }
        if (!empty($_GET['teacher_id'])) {
            $filters['teacher_id'] = (int)$_GET['teacher_id'];
        }
        if (!empty($_GET['section_id'])) {
            $filters['section_id'] = (int)$_GET['section_id'];
        }
        if (!empty($_GET['status'])) {
            $filters['status'] = trim($_GET['status']);
        }

        $schedules = $this->scheduleModel->getAllWithDetails($semId, $filters);
        $semesters = $this->semesterModel->all();
        $roomModel = new Room();
        $rooms = $roomModel->all();
        
        $teacherModel = new Teacher();
        $teachers = $teacherModel->getAllWithDetails();

        $sectionModel = new Section();
        $sections = $sectionModel->getAllWithDetails($semId);

        $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        $this->render('coordinator/timetable/index', [
            'title' => 'Class Timetable Management — PUB CampusMate',
            'schedules' => $schedules,
            'semesters' => $semesters,
            'selectedSemesterId' => $semId,
            'rooms' => $rooms,
            'teachers' => $teachers,
            'sections' => $sections,
            'days' => $days,
            'filters' => $filters
        ]);
    }

    /**
     * Show Schedule Creation Form
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

        $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        $this->render('coordinator/timetable/create', [
            'title' => 'Schedule New Class Routine — PUB CampusMate',
            'currentSemester' => $currentSem,
            'offerings' => $offerings,
            'rooms' => $rooms,
            'teachers' => $teachers,
            'days' => $days
        ]);
    }

    /**
     * Store New Schedule Slot
     */
    public function store(): void
    {
        if (!has_role('department_coordinator')) {
            $this->setFlash('danger', 'Unauthorized access.');
            $this->redirect(url('/dashboard'));
            return;
        }

        $user = $this->authUser();

        $data = [
            'semester_id' => (int)($_POST['semester_id'] ?? 0),
            'section_id' => (int)($_POST['section_id'] ?? 0),
            'teacher_id' => (int)($_POST['teacher_id'] ?? 0),
            'room_id' => (int)($_POST['room_id'] ?? 0),
            'day_of_week' => trim($_POST['day_of_week'] ?? ''),
            'start_time' => trim($_POST['start_time'] ?? ''),
            'end_time' => trim($_POST['end_time'] ?? ''),
            'class_type' => trim($_POST['class_type'] ?? 'lecture'),
            'status' => in_array($_POST['status'] ?? '', ['draft', 'published'], true) ? $_POST['status'] : 'draft'
        ];

        try {
            $this->timetableService->createScheduleSlot($data, (int)$user['id']);
            $this->setFlash('success', 'Timetable slot created successfully (' . ucfirst($data['status']) . ').');
            $this->redirect(url('/coordinator/timetable'));
        } catch (\App\Exceptions\AppException $e) {
            $this->setFlash('danger', $e->getMessage());
            $this->redirect(url('/coordinator/timetable/create'));
        }
    }

    /**
     * Show Edit Form for Schedule Slot
     */
    public function edit(string $id): void
    {
        if (!has_role('department_coordinator')) {
            $this->setFlash('danger', 'Unauthorized access.');
            $this->redirect(url('/dashboard'));
            return;
        }

        $scheduleId = (int)$id;
        $schedule = $this->scheduleModel->getScheduleWithDetails($scheduleId);
        if (!$schedule) {
            $this->setFlash('danger', 'Class schedule slot not found.');
            $this->redirect(url('/coordinator/timetable'));
            return;
        }

        $semId = (int)$schedule['semester_id'];
        $offeringModel = new CourseOffering();
        $offerings = $offeringModel->getOfferingsBySemester($semId);

        $sectionModel = new Section();
        $sections = $sectionModel->getAllWithDetails($semId);

        $roomModel = new Room();
        $rooms = $roomModel->getAvailable();

        $teacherModel = new Teacher();
        $teachers = $teacherModel->getAllWithDetails();

        $revisionModel = new ScheduleRevision();
        $revisions = $revisionModel->getForSchedule($scheduleId);

        $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        $this->render('coordinator/timetable/edit', [
            'title' => 'Edit Schedule Slot — PUB CampusMate',
            'schedule' => $schedule,
            'offerings' => $offerings,
            'sections' => $sections,
            'rooms' => $rooms,
            'teachers' => $teachers,
            'revisions' => $revisions,
            'days' => $days
        ]);
    }

    /**
     * Update Schedule Slot
     */
    public function update(string $id): void
    {
        if (!has_role('department_coordinator')) {
            $this->setFlash('danger', 'Unauthorized access.');
            $this->redirect(url('/dashboard'));
            return;
        }

        $scheduleId = (int)$id;
        $user = $this->authUser();

        $data = [
            'section_id' => (int)($_POST['section_id'] ?? 0),
            'teacher_id' => (int)($_POST['teacher_id'] ?? 0),
            'room_id' => (int)($_POST['room_id'] ?? 0),
            'day_of_week' => trim($_POST['day_of_week'] ?? ''),
            'start_time' => trim($_POST['start_time'] ?? ''),
            'end_time' => trim($_POST['end_time'] ?? ''),
            'class_type' => trim($_POST['class_type'] ?? 'lecture'),
            'status' => trim($_POST['status'] ?? 'draft'),
            'revision_reason' => trim($_POST['revision_reason'] ?? '')
        ];

        try {
            $this->timetableService->updateScheduleSlot($scheduleId, $data, (int)$user['id'], $data['revision_reason']);
            $this->setFlash('success', 'Timetable slot updated successfully.');
            $this->redirect(url('/coordinator/timetable'));
        } catch (\App\Exceptions\AppException $e) {
            $this->setFlash('danger', $e->getMessage());
            $this->redirect(url('/coordinator/timetable/' . $scheduleId . '/edit'));
        }
    }

    /**
     * Publish Schedule Slot
     */
    public function publish(string $id): void
    {
        if (!has_role('department_coordinator')) {
            $this->setFlash('danger', 'Unauthorized access.');
            $this->redirect(url('/dashboard'));
            return;
        }

        $scheduleId = (int)$id;
        $user = $this->authUser();

        try {
            $this->timetableService->publishScheduleSlot($scheduleId, (int)$user['id']);
            $this->setFlash('success', 'Schedule published successfully. Notifications sent to enrolled students and instructor.');
        } catch (\App\Exceptions\AppException $e) {
            $this->setFlash('danger', $e->getMessage());
        }

        $this->redirect(url('/coordinator/timetable'));
    }

    /**
     * Cancel Schedule Slot
     */
    public function cancel(string $id): void
    {
        if (!has_role('department_coordinator')) {
            $this->setFlash('danger', 'Unauthorized access.');
            $this->redirect(url('/dashboard'));
            return;
        }

        $scheduleId = (int)$id;
        $user = $this->authUser();

        $reason = trim($_POST['cancel_reason'] ?? 'Class schedule cancelled by coordinator.');

        try {
            $this->timetableService->cancelScheduleSlot($scheduleId, $reason, (int)$user['id']);
            $this->setFlash('success', 'Class schedule cancelled. Affected students and teacher notified.');
        } catch (\App\Exceptions\AppException $e) {
            $this->setFlash('danger', $e->getMessage());
        }

        $this->redirect(url('/coordinator/timetable'));
    }

    /**
     * View Revision History
     */
    public function revisions(): void
    {
        if (!has_role('department_coordinator')) {
            $this->setFlash('danger', 'Unauthorized access.');
            $this->redirect(url('/dashboard'));
            return;
        }

        $currentSem = $this->semesterModel->getCurrent();
        $semId = isset($_GET['semester_id']) ? (int)$_GET['semester_id'] : (int)($currentSem['id'] ?? 2);

        $revisionModel = new ScheduleRevision();
        $revisions = $revisionModel->getRevisionHistory($semId);
        $semesters = $this->semesterModel->all();

        $this->render('coordinator/timetable/revisions', [
            'title' => 'Schedule Revision History & Audit Log — PUB CampusMate',
            'revisions' => $revisions,
            'semesters' => $semesters,
            'selectedSemesterId' => $semId
        ]);
    }

    /**
     * API: Get Sections & Assigned Teachers for a Course Offering
     */
    public function apiOfferingData(): void
    {
        if (!has_role('department_coordinator')) {
            $this->json(['error' => 'Unauthorized'], 403);
            return;
        }

        $offeringId = (int)($_GET['offering_id'] ?? 0);
        if ($offeringId <= 0) {
            $this->json(['sections' => []]);
            return;
        }

        $offeringModel = new CourseOffering();
        $sections = $offeringModel->getSections($offeringId);

        $assignmentModel = new TeacherAssignment();
        foreach ($sections as &$sec) {
            $sec['instructors'] = $assignmentModel->getAssignmentsBySection((int)$sec['id']);
        }

        $this->json(['sections' => $sections]);
    }
}
