<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Course;
use App\Models\Department;
use App\Models\Semester;

class CourseController extends Controller
{
    private Course $courseModel;
    private Department $departmentModel;

    public function __construct()
    {
        $this->courseModel = new Course();
        $this->departmentModel = new Department();
    }

    /**
     * Shared Read-only Course Catalog
     */
    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');
        $deptId = !empty($_GET['dept']) ? (int)$_GET['dept'] : null;

        $courses = $this->courseModel->getAllWithDetails($deptId, $search);
        $departments = $this->departmentModel->getActive();

        $this->render('courses/index', [
            'title' => 'Course Catalog — PUB CampusMate',
            'courses' => $courses,
            'departments' => $departments,
            'search' => $search,
            'selectedDept' => $deptId
        ]);
    }

    /**
     * Shared Read-only Course Details
     */
    public function show(string $id): void
    {
        $course = $this->courseModel->find((int)$id);
        if (!$course) {
            $this->setFlash('danger', 'Course not found.');
            $this->redirect('/courses');
            return;
        }

        $department = $this->departmentModel->find((int)$course['department_id']);
        $prereqs = $this->courseModel->getPrerequisites((int)$id);

        $this->render('courses/show', [
            'title' => "{$course['course_code']}: {$course['title']} — PUB CampusMate",
            'course' => $course,
            'department' => $department,
            'prerequisites' => $prereqs
        ]);
    }

    /**
     * Coordinator: Course Administration Management Page
     */
    public function manage(): void
    {
        $search = trim($_GET['q'] ?? '');
        $deptId = !empty($_GET['dept']) ? (int)$_GET['dept'] : null;

        $courses = $this->courseModel->getAllWithDetails($deptId, $search);
        $departments = $this->departmentModel->getActive();

        $this->render('coordinator/courses/index', [
            'title' => 'Course Catalog Management — PUB CampusMate',
            'courses' => $courses,
            'departments' => $departments,
            'search' => $search,
            'selectedDept' => $deptId
        ]);
    }

    public function create(): void
    {
        $departments = $this->departmentModel->getActive();

        $this->render('coordinator/courses/create', [
            'title' => 'Add New Course — PUB CampusMate',
            'departments' => $departments
        ]);
    }

    public function store(): void
    {
        $data = $this->validate($_POST, [
            'course_code' => 'required',
            'title' => 'required',
            'credit' => 'required|numeric',
            'department_id' => 'required|numeric'
        ]);

        $code = strtoupper(trim($data['course_code'] ?? ''));
        $title = trim($data['title'] ?? '');
        $credit = (float)($data['credit'] ?? 0.0);
        $deptId = (int)$data['department_id'];
        $type = in_array($_POST['course_type'] ?? '', ['theory', 'lab', 'project', 'thesis', 'internship'])
            ? $_POST['course_type']
            : 'theory';
        $desc = trim($_POST['description'] ?? '');

        // 1. Uniqueness check
        if (!$this->courseModel->isCodeUnique($code)) {
            $this->setFlash('danger', "Course code '{$code}' already exists in catalog.");
            $this->redirect('/coordinator/courses/create');
            return;
        }

        // 2. Positive credit limits check (0.5 to 6.0)
        if ($credit < 0.5 || $credit > 6.0) {
            $this->setFlash('danger', 'Credit value must be between 0.5 and 6.0 credit hours.');
            $this->redirect('/coordinator/courses/create');
            return;
        }

        $id = $this->courseModel->create([
            'department_id' => $deptId,
            'course_code' => $code,
            'title' => $title,
            'credit' => $credit,
            'course_type' => $type,
            'description' => $desc,
            'status' => 'active'
        ]);

        if ($id) {
            $this->setFlash('success', "Course '{$code}: {$title}' added successfully.");
            $this->redirect('/coordinator/courses');
        } else {
            $this->setFlash('danger', 'Failed to create course.');
            $this->redirect('/coordinator/courses/create');
        }
    }

    public function edit(string $id): void
    {
        $course = $this->courseModel->find((int)$id);
        if (!$course) {
            $this->setFlash('danger', 'Course not found.');
            $this->redirect('/coordinator/courses');
            return;
        }

        $departments = $this->departmentModel->getActive();

        $this->render('coordinator/courses/edit', [
            'title' => "Edit Course: {$course['course_code']} — PUB CampusMate",
            'course' => $course,
            'departments' => $departments
        ]);
    }

    public function update(string $id): void
    {
        $courseId = (int)$id;
        $course = $this->courseModel->find($courseId);
        if (!$course) {
            $this->setFlash('danger', 'Course not found.');
            $this->redirect('/coordinator/courses');
            return;
        }

        $data = $this->validate($_POST, [
            'course_code' => 'required',
            'title' => 'required',
            'credit' => 'required|numeric',
            'department_id' => 'required|numeric'
        ]);

        $code = strtoupper(trim($data['course_code'] ?? ''));
        $title = trim($data['title'] ?? '');
        $credit = (float)($data['credit'] ?? 0.0);
        $deptId = (int)$data['department_id'];
        $type = in_array($_POST['course_type'] ?? '', ['theory', 'lab', 'project', 'thesis', 'internship'])
            ? $_POST['course_type']
            : $course['course_type'];
        $status = in_array($_POST['status'] ?? '', ['active', 'inactive', 'archived'])
            ? $_POST['status']
            : $course['status'];
        $desc = trim($_POST['description'] ?? '');

        if (!$this->courseModel->isCodeUnique($code, $courseId)) {
            $this->setFlash('danger', "Course code '{$code}' is already assigned to another course.");
            $this->redirect("/coordinator/courses/{$courseId}/edit");
            return;
        }

        if ($credit < 0.5 || $credit > 6.0) {
            $this->setFlash('danger', 'Credit value must be between 0.5 and 6.0 credit hours.');
            $this->redirect("/coordinator/courses/{$courseId}/edit");
            return;
        }

        $this->courseModel->update($courseId, [
            'department_id' => $deptId,
            'course_code' => $code,
            'title' => $title,
            'credit' => $credit,
            'course_type' => $type,
            'description' => $desc,
            'status' => $status
        ]);

        $this->setFlash('success', "Course '{$code}' updated successfully.");
        $this->redirect('/coordinator/courses');
    }

    public function prerequisites(string $id): void
    {
        $courseId = (int)$id;
        $course = $this->courseModel->find($courseId);
        if (!$course) {
            $this->setFlash('danger', 'Course not found.');
            $this->redirect('/coordinator/courses');
            return;
        }

        $prereqs = $this->courseModel->getPrerequisites($courseId);
        $allCourses = $this->courseModel->all('course_code ASC');

        // Filter out the course itself and already assigned prerequisites
        $assignedIds = array_column($prereqs, 'prerequisite_course_id');
        $availablePrereqs = array_filter($allCourses, function($c) use ($courseId, $assignedIds) {
            return (int)$c['id'] !== $courseId && !in_array((int)$c['id'], $assignedIds);
        });

        $this->render('coordinator/courses/prerequisites', [
            'title' => "Prerequisites for {$course['course_code']} — PUB CampusMate",
            'course' => $course,
            'prerequisites' => $prereqs,
            'availableCourses' => $availablePrereqs
        ]);
    }

    public function addPrerequisite(string $id): void
    {
        $courseId = (int)$id;
        $prereqId = (int)($_POST['prerequisite_course_id'] ?? 0);
        $minGrade = trim($_POST['min_grade'] ?? 'D');
        $isMandatory = isset($_POST['is_mandatory']) ? true : false;

        if (!$prereqId) {
            $this->setFlash('danger', 'Please select a prerequisite course.');
            $this->redirect("/coordinator/courses/{$courseId}/prerequisites");
            return;
        }

        // Rule 1: A course cannot be its own prerequisite
        if ($courseId === $prereqId) {
            $this->setFlash('danger', 'A course cannot be its own prerequisite.');
            $this->redirect("/coordinator/courses/{$courseId}/prerequisites");
            return;
        }

        // Rule 2: Prerequisite cycle detection
        if ($this->courseModel->hasPrerequisiteCycle($courseId, $prereqId)) {
            $this->setFlash('danger', 'Cannot add prerequisite: Circular prerequisite dependency detected.');
            $this->redirect("/coordinator/courses/{$courseId}/prerequisites");
            return;
        }

        $added = $this->courseModel->addPrerequisite($courseId, $prereqId, $minGrade, $isMandatory);

        if ($added) {
            $this->setFlash('success', 'Prerequisite course linked successfully.');
        } else {
            $this->setFlash('danger', 'Failed to add prerequisite relationship.');
        }

        $this->redirect("/coordinator/courses/{$courseId}/prerequisites");
    }

    public function removePrerequisite(string $id, string $prereqId): void
    {
        $courseId = (int)$id;
        $pid = (int)$prereqId;

        $removed = $this->courseModel->removePrerequisite($courseId, $pid);

        if ($removed) {
            $this->setFlash('success', 'Prerequisite link removed.');
        } else {
            $this->setFlash('danger', 'Failed to remove prerequisite.');
        }

        $this->redirect("/coordinator/courses/{$courseId}/prerequisites");
    }
}
