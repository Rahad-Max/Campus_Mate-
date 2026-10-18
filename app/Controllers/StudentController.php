<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Student;
use App\Models\Semester;

class StudentController extends Controller
{
    private Student $studentModel;
    private Semester $semesterModel;

    public function __construct()
    {
        $this->studentModel = new Student();
        $this->semesterModel = new Semester();
    }

    public function profile(): void
    {
        $user = $this->authUser();
        $studentRecord = $this->studentModel->findByUserId($user['id']);
        $student = $studentRecord ? $this->studentModel->getFullDetails((int)$studentRecord['id']) : null;
        $history = $studentRecord ? $this->studentModel->getAcademicHistory((int)$studentRecord['id']) : [];

        $this->render('student/profile', [
            'title' => 'My Academic Profile — PUB CampusMate',
            'student' => $student,
            'history' => $history
        ]);
    }

    public function academicHistory(): void
    {
        $user = $this->authUser();
        $studentRecord = $this->studentModel->findByUserId($user['id']);
        $history = $studentRecord ? $this->studentModel->getAcademicHistory((int)$studentRecord['id']) : [];

        $this->render('student/history', [
            'title' => 'Academic History & Transcripts — PUB CampusMate',
            'history' => $history
        ]);
    }

    public function courses(): void
    {
        $user = $this->authUser();
        $studentRecord = $this->studentModel->findByUserId($user['id']);
        $deptId = $studentRecord ? (int)$studentRecord['department_id'] : 1;

        $courses = (new \App\Models\Course())->getAllWithDetails($deptId);
        $curriculum = (new \App\Models\Curriculum())->getActiveForDepartment($deptId);
        $curriculumCourses = $curriculum ? (new \App\Models\Curriculum())->getCurriculumCourses((int)$curriculum['id']) : [];

        $departments = (new \App\Models\Department())->getActive();

        $this->render('student/courses', [
            'title' => 'My Academic Courses & Curriculum — PUB CampusMate',
            'courses' => $courses,
            'curriculum' => $curriculum,
            'curriculumCourses' => $curriculumCourses,
            'departments' => $departments,
            'departmentId' => $deptId
        ]);
    }

    public function semester(): void
    {
        $current = $this->semesterModel->getCurrent();
        $semesters = $this->semesterModel->getAllOrdered();

        $this->render('student/semester', [
            'title' => 'Academic Calendar & Semester Info — PUB CampusMate',
            'current' => $current,
            'semesters' => $semesters
        ]);
    }
}
