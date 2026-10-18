<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Teacher;
use App\Models\Semester;

class TeacherController extends Controller
{
    private Teacher $teacherModel;
    private Semester $semesterModel;

    public function __construct()
    {
        $this->teacherModel = new Teacher();
        $this->semesterModel = new Semester();
    }

    public function profile(): void
    {
        $user = $this->authUser();
        $teacherRecord = $this->teacherModel->findByUserId($user['id']);
        $teacher = $teacherRecord ? $this->teacherModel->getFullDetails((int)$teacherRecord['id']) : null;

        $this->render('teacher/profile', [
            'title' => 'Faculty Profile — PUB CampusMate',
            'teacher' => $teacher
        ]);
    }

    public function classes(): void
    {
        $user = $this->authUser();
        $teacherRecord = $this->teacherModel->findByUserId($user['id']);
        $semester = $this->semesterModel->getCurrent();
        $semId = $semester['id'] ?? 2;

        $sections = $teacherRecord ? $this->teacherModel->getAssignedSections((int)$teacherRecord['id'], $semId) : [];

        $this->render('teacher/classes', [
            'title' => 'My Assigned Sections — PUB CampusMate',
            'sections' => $sections,
            'semester' => $semester
        ]);
    }

    public function courses(): void
    {
        $user = $this->authUser();
        $teacherRecord = $this->teacherModel->findByUserId($user['id']);
        $deptId = $teacherRecord ? (int)$teacherRecord['department_id'] : 1;

        $courses = (new \App\Models\Course())->getAllWithDetails($deptId);
        $curriculum = (new \App\Models\Curriculum())->getActiveForDepartment($deptId);
        $curriculumCourses = $curriculum ? (new \App\Models\Curriculum())->getCurriculumCourses((int)$curriculum['id']) : [];

        $this->render('teacher/courses', [
            'title' => 'Department Courses & Syllabi — PUB CampusMate',
            'courses' => $courses,
            'curriculum' => $curriculum,
            'curriculumCourses' => $curriculumCourses
        ]);
    }

    public function assignments(): void
    {
        $user = $this->authUser();
        $teacherRecord = $this->teacherModel->findByUserId($user['id']);
        $semester = $this->semesterModel->getCurrent();
        $semId = $semester['id'] ?? 2;

        $assignments = $teacherRecord
            ? (new \App\Models\TeacherAssignment())->getAssignmentsWithDetails($semId, (int)$teacherRecord['department_id'])
            : [];

        $this->render('teacher/assignments', [
            'title' => 'Teaching Workload & Faculty Assignments — PUB CampusMate',
            'assignments' => $assignments,
            'semester' => $semester,
            'teacher' => $teacherRecord
        ]);
    }
}
