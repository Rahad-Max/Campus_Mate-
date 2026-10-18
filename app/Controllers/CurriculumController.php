<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Curriculum;
use App\Models\Department;
use App\Models\Course;

class CurriculumController extends Controller
{
    private Curriculum $curriculumModel;
    private Department $departmentModel;
    private Course $courseModel;

    public function __construct()
    {
        $this->curriculumModel = new Curriculum();
        $this->departmentModel = new Department();
        $this->courseModel = new Course();
    }

    public function index(): void
    {
        $curricula = $this->curriculumModel->getAllWithDepartment();

        $this->render('coordinator/curricula/index', [
            'title' => 'Curriculum Management — PUB CampusMate',
            'curricula' => $curricula
        ]);
    }

    public function show(string $id): void
    {
        $curriculum = $this->curriculumModel->find((int)$id);
        if (!$curriculum) {
            $this->setFlash('danger', 'Curriculum not found.');
            $this->redirect('/coordinator/curricula');
            return;
        }

        $department = $this->departmentModel->find((int)$curriculum['department_id']);
        $courses = $this->curriculumModel->getCurriculumCourses((int)$id);
        $totalCredits = $this->curriculumModel->calculateTotalCredits((int)$id);

        // Group courses by recommended semester
        $matrix = [];
        for ($i = 1; $i <= 8; $i++) {
            $matrix[$i] = [];
        }
        foreach ($courses as $c) {
            $sem = (int)$c['recommended_semester'];
            $matrix[$sem][] = $c;
        }

        // Available courses not yet added
        $assignedIds = array_column($courses, 'course_id');
        $allDeptCourses = $this->courseModel->getAllWithDetails((int)$curriculum['department_id']);
        $availableCourses = array_filter($allDeptCourses, fn($c) => !in_array($c['id'], $assignedIds));

        $this->render('coordinator/curricula/show', [
            'title' => "Curriculum Matrix: {$curriculum['name']} — PUB CampusMate",
            'curriculum' => $curriculum,
            'department' => $department,
            'matrix' => $matrix,
            'courses' => $courses,
            'totalCredits' => $totalCredits,
            'availableCourses' => $availableCourses
        ]);
    }

    public function create(): void
    {
        $departments = $this->departmentModel->getActive();

        $this->render('coordinator/curricula/create', [
            'title' => 'Create Curriculum — PUB CampusMate',
            'departments' => $departments
        ]);
    }

    public function store(): void
    {
        $data = $this->validate($_POST, [
            'department_id' => 'required|numeric',
            'name' => 'required',
            'version' => 'required'
        ]);

        $deptId = (int)$data['department_id'];
        $name = trim($data['name'] ?? '');
        $version = trim($data['version'] ?? '');
        $requiredCredits = (float)($_POST['total_credits_required'] ?? 148.0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $id = $this->curriculumModel->create([
            'department_id' => $deptId,
            'name' => $name,
            'version' => $version,
            'total_credits_required' => $requiredCredits,
            'is_active' => $isActive
        ]);

        if ($id) {
            $this->setFlash('success', "Curriculum '{$name} ({$version})' created successfully.");
            $this->redirect("/coordinator/curricula/{$id}");
        } else {
            $this->setFlash('danger', 'Failed to create curriculum.');
            $this->redirect('/coordinator/curricula/create');
        }
    }

    public function edit(string $id): void
    {
        $curriculum = $this->curriculumModel->find((int)$id);
        if (!$curriculum) {
            $this->setFlash('danger', 'Curriculum not found.');
            $this->redirect('/coordinator/curricula');
            return;
        }

        $departments = $this->departmentModel->getActive();

        $this->render('coordinator/curricula/edit', [
            'title' => "Edit Curriculum: {$curriculum['name']} — PUB CampusMate",
            'curriculum' => $curriculum,
            'departments' => $departments
        ]);
    }

    public function update(string $id): void
    {
        $curriculumId = (int)$id;
        $curriculum = $this->curriculumModel->find($curriculumId);
        if (!$curriculum) {
            $this->setFlash('danger', 'Curriculum not found.');
            $this->redirect('/coordinator/curricula');
            return;
        }

        $data = $this->validate($_POST, [
            'name' => 'required',
            'version' => 'required'
        ]);

        $name = trim($data['name'] ?? '');
        $version = trim($data['version'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $this->curriculumModel->update($curriculumId, [
            'name' => $name,
            'version' => $version,
            'is_active' => $isActive
        ]);

        $this->setFlash('success', "Curriculum updated successfully.");
        $this->redirect("/coordinator/curricula/{$curriculumId}");
    }

    public function addCourse(string $id): void
    {
        $curriculumId = (int)$id;
        $courseId = (int)($_POST['course_id'] ?? 0);
        $semester = (int)($_POST['recommended_semester'] ?? 1);
        $isElective = isset($_POST['is_elective']) ? 1 : 0;
        $group = !empty($_POST['elective_group']) ? trim($_POST['elective_group']) : null;

        if (!$courseId || $semester < 1 || $semester > 12) {
            $this->setFlash('danger', 'Please select a valid course and recommended semester (1-12).');
            $this->redirect("/coordinator/curricula/{$curriculumId}");
            return;
        }

        $added = $this->curriculumModel->addCourse($curriculumId, $courseId, $semester, (bool)$isElective, $group);

        if ($added) {
            $this->setFlash('success', 'Course added to curriculum matrix.');
        } else {
            $this->setFlash('danger', 'Failed to assign course to curriculum.');
        }

        $this->redirect("/coordinator/curricula/{$curriculumId}");
    }

    public function removeCourse(string $id, string $courseId): void
    {
        $curriculumId = (int)$id;
        $coid = (int)$courseId;

        $removed = $this->curriculumModel->removeCourse($curriculumId, $coid);

        if ($removed) {
            $this->setFlash('success', 'Course removed from curriculum.');
        } else {
            $this->setFlash('danger', 'Failed to remove course from curriculum.');
        }

        $this->redirect("/coordinator/curricula/{$curriculumId}");
    }
}
