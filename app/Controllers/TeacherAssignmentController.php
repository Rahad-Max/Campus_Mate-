<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\TeacherAssignment;
use App\Models\Teacher;
use App\Models\Section;
use App\Models\Semester;

class TeacherAssignmentController extends Controller
{
    private TeacherAssignment $assignmentModel;
    private Teacher $teacherModel;
    private Section $sectionModel;
    private Semester $semesterModel;

    public function __construct()
    {
        $this->assignmentModel = new TeacherAssignment();
        $this->teacherModel = new Teacher();
        $this->sectionModel = new Section();
        $this->semesterModel = new Semester();
    }

    public function index(): void
    {
        $currentSem = $this->semesterModel->getCurrent();
        $semId = !empty($_GET['semester_id']) ? (int)$_GET['semester_id'] : ($currentSem['id'] ?? 1);

        $assignments = $this->assignmentModel->getAssignmentsWithDetails($semId);
        $semesters = $this->semesterModel->getAllOrdered();

        // Calculate teaching loads for all teachers in this semester
        $teachers = $this->teacherModel->getAllWithDetails();
        foreach ($teachers as &$t) {
            $t['assigned_credits'] = $this->assignmentModel->getTeacherCurrentLoad((int)$t['id'], $semId);
            $t['workload_percentage'] = $t['max_credit_load'] > 0
                ? min(100, round(($t['assigned_credits'] / $t['max_credit_load']) * 100))
                : 0;
        }

        $this->render('coordinator/assignments/index', [
            'title' => 'Faculty Course Assignments — PUB CampusMate',
            'assignments' => $assignments,
            'semesters' => $semesters,
            'teachers' => $teachers,
            'semesterId' => $semId,
            'selectedSemId' => $semId
        ]);
    }

    public function create(): void
    {
        $currentSem = $this->semesterModel->getCurrent();
        $semId = !empty($_GET['semester_id']) ? (int)$_GET['semester_id'] : ($currentSem['id'] ?? 1);

        $sections = $this->sectionModel->getAllWithDetails($semId);
        $teachers = $this->teacherModel->getAllWithDetails();
        $semesters = $this->semesterModel->getAllOrdered();

        foreach ($teachers as &$t) {
            $t['assigned_credits'] = $this->assignmentModel->getTeacherCurrentLoad((int)$t['id'], $semId);
            $t['available_capacity'] = max(0, (float)$t['max_credit_load'] - $t['assigned_credits']);
        }

        $this->render('coordinator/assignments/create', [
            'title' => 'Assign Teacher to Section — PUB CampusMate',
            'sections' => $sections,
            'teachers' => $teachers,
            'semesters' => $semesters,
            'selectedSemId' => $semId
        ]);
    }

    public function store(): void
    {
        $data = $this->validate($_POST, [
            'section_id' => 'required|numeric',
            'teacher_id' => 'required|numeric',
            'role' => 'required',
            'assigned_credits' => 'required|numeric'
        ]);

        $sectionId = (int)$data['section_id'];
        $teacherId = (int)$data['teacher_id'];
        $role = in_array($data['role'] ?? '', ['lead_instructor', 'co_instructor', 'lab_instructor', 'teaching_assistant'])
            ? $data['role']
            : 'lead_instructor';
        $assignedCredits = (float)($data['assigned_credits'] ?? 3.0);
        $coordinatorId = auth_id() ?? 5;

        // 1. Prevent duplicate assignment
        if ($this->assignmentModel->findBySectionAndTeacher($sectionId, $teacherId)) {
            $this->setFlash('danger', 'This teacher is already assigned to this course section.');
            $this->redirect('/coordinator/teacher-assignments/create');
            return;
        }

        // 2. Workload limit enforcement
        $section = $this->sectionModel->getDetailedInfo($sectionId);
        $semId = $section ? (int)$section['semester_id'] : 1;

        if ($this->assignmentModel->isTeacherOverloaded($teacherId, $semId, $assignedCredits)) {
            $teacher = $this->teacherModel->find($teacherId);
            $currentLoad = $this->assignmentModel->getTeacherCurrentLoad($teacherId, $semId);
            $this->setFlash('danger', "Assignment rejected: Adding {$assignedCredits} credits will exceed instructor maximum workload limit ({$currentLoad} + {$assignedCredits} > {$teacher['max_credit_load']} credits).");
            $this->redirect('/coordinator/teacher-assignments/create');
            return;
        }

        $id = $this->assignmentModel->create([
            'section_id' => $sectionId,
            'teacher_id' => $teacherId,
            'role' => $role,
            'assigned_credits' => $assignedCredits,
            'assigned_by' => $coordinatorId
        ]);

        if ($id) {
            $this->setFlash('success', 'Instructor assigned to course section successfully.');
            $this->redirect('/coordinator/teacher-assignments');
        } else {
            $this->setFlash('danger', 'Failed to create teacher assignment.');
            $this->redirect('/coordinator/teacher-assignments/create');
        }
    }

    public function delete(string $id): void
    {
        $assignmentId = (int)$id;
        $assignment = $this->assignmentModel->find($assignmentId);
        if (!$assignment) {
            $this->setFlash('danger', 'Assignment record not found.');
            $this->redirect('/coordinator/teacher-assignments');
            return;
        }

        $this->assignmentModel->delete($assignmentId);
        $this->setFlash('success', 'Instructor assignment removed.');
        $this->redirect('/coordinator/teacher-assignments');
    }
}
