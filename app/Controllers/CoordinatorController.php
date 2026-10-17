<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\CourseOffering;
use App\Models\Semester;
use App\Models\Curriculum;

class CoordinatorController extends Controller
{
    private CourseOffering $offeringModel;
    private Semester $semesterModel;
    private Curriculum $curriculumModel;

    public function __construct()
    {
        $this->offeringModel = new CourseOffering();
        $this->semesterModel = new Semester();
        $this->curriculumModel = new Curriculum();
    }

    public function offerings(): void
    {
        $semester = $this->semesterModel->getCurrent();
        $semId = $semester['id'] ?? 2;
        $offerings = $this->offeringModel->getOfferingsBySemester($semId);

        $this->render('coordinator/offerings', [
            'title' => 'Course Offerings Management — PUB CampusMate',
            'offerings' => $offerings,
            'semester' => $semester
        ]);
    }

    public function curriculum(): void
    {
        $curriculum = $this->curriculumModel->getActiveForDepartment(1);
        $courses = $curriculum ? $this->curriculumModel->getCurriculumCourses((int)$curriculum['id']) : [];

        $this->render('coordinator/curriculum', [
            'title' => 'Academic Curriculum — PUB CampusMate',
            'curriculum' => $curriculum,
            'courses' => $courses
        ]);
    }
}
