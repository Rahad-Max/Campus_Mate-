<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\CourseOffering;
use App\Models\Course;
use App\Models\Semester;
use App\Models\Department;

class CourseOfferingController extends Controller
{
    private CourseOffering $offeringModel;
    private Course $courseModel;
    private Semester $semesterModel;
    private Department $departmentModel;

    public function __construct()
    {
        $this->offeringModel = new CourseOffering();
        $this->courseModel = new Course();
        $this->semesterModel = new Semester();
        $this->departmentModel = new Department();
    }

    public function index(): void
    {
        $currentSem = $this->semesterModel->getCurrent();
        $semId = !empty($_GET['semester_id']) ? (int)$_GET['semester_id'] : ($currentSem['id'] ?? 1);
        $deptId = !empty($_GET['department_id']) ? (int)$_GET['department_id'] : null;

        $offerings = $this->offeringModel->getOfferingsBySemester($semId, $deptId);
        $semesters = $this->semesterModel->getAllOrdered();
        $departments = $this->departmentModel->getActive();
        $selectedSem = $this->semesterModel->find($semId);

        $this->render('coordinator/offerings/index', [
            'title' => 'Course Offerings Management — PUB CampusMate',
            'offerings' => $offerings,
            'semesters' => $semesters,
            'departments' => $departments,
            'selectedSem' => $selectedSem,
            'semesterId' => $semId,
            'selectedSemId' => $semId,
            'selectedDeptId' => $deptId
        ]);
    }

    public function create(): void
    {
        $semesters = $this->semesterModel->where('status', 'registration_open');
        if (empty($semesters)) {
            $semesters = $this->semesterModel->getAllOrdered();
        }
        $departments = $this->departmentModel->getActive();
        $courses = $this->courseModel->all('course_code ASC');

        $this->render('coordinator/offerings/create', [
            'title' => 'Offer Course in Semester — PUB CampusMate',
            'semesters' => $semesters,
            'departments' => $departments,
            'courses' => $courses
        ]);
    }

    public function store(): void
    {
        $data = $this->validate($_POST, [
            'semester_id' => 'required|numeric',
            'course_id' => 'required|numeric',
            'department_id' => 'required|numeric'
        ]);

        $semId = (int)$data['semester_id'];
        $courseId = (int)$data['course_id'];
        $deptId = (int)$data['department_id'];
        $coordinatorId = auth_id() ?? 5;

        // Check if course is already offered in this semester
        if ($this->offeringModel->isOffered($semId, $courseId)) {
            $this->setFlash('danger', 'This course is already offered in the selected semester.');
            $this->redirect('/coordinator/course-offerings/create');
            return;
        }

        $id = $this->offeringModel->create([
            'semester_id' => $semId,
            'course_id' => $courseId,
            'department_id' => $deptId,
            'coordinator_id' => $coordinatorId,
            'status' => 'offered'
        ]);

        if ($id) {
            $this->setFlash('success', 'Course offered successfully. You may now create sections.');
            $this->redirect("/coordinator/sections/create?offering_id={$id}");
        } else {
            $this->setFlash('danger', 'Failed to offer course.');
            $this->redirect('/coordinator/course-offerings/create');
        }
    }

    public function edit(string $id): void
    {
        $offering = $this->offeringModel->find((int)$id);
        if (!$offering) {
            $this->setFlash('danger', 'Course offering not found.');
            $this->redirect('/coordinator/course-offerings');
            return;
        }

        $course = $this->courseModel->find((int)$offering['course_id']);
        $semester = $this->semesterModel->find((int)$offering['semester_id']);
        $semesters = $this->semesterModel->getAllOrdered();
        $courses = $this->courseModel->all('course_code ASC');

        $this->render('coordinator/offerings/edit', [
            'title' => "Edit Offering: {$course['course_code']} — PUB CampusMate",
            'offering' => $offering,
            'course' => $course,
            'semester' => $semester,
            'semesters' => $semesters,
            'courses' => $courses
        ]);
    }

    public function update(string $id): void
    {
        $offeringId = (int)$id;
        $offering = $this->offeringModel->find($offeringId);
        if (!$offering) {
            $this->setFlash('danger', 'Course offering not found.');
            $this->redirect('/coordinator/course-offerings');
            return;
        }

        $status = in_array($_POST['status'] ?? '', ['draft', 'offered', 'cancelled', 'completed'])
            ? $_POST['status']
            : $offering['status'];

        if ($status === 'cancelled' && $this->offeringModel->hasEnrolledStudents($offeringId)) {
            $this->setFlash('danger', 'Cannot cancel an offering that has enrolled students.');
            $this->redirect("/coordinator/course-offerings/{$offeringId}/edit");
            return;
        }

        $this->offeringModel->update($offeringId, ['status' => $status]);
        $this->setFlash('success', 'Course offering status updated.');
        $this->redirect('/coordinator/course-offerings');
    }
}
