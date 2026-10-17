<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Course;

class CourseController extends Controller
{
    private Course $courseModel;

    public function __construct()
    {
        $this->courseModel = new Course();
    }

    public function index(): void
    {
        $courses = $this->courseModel->all('course_code ASC');

        $this->render('courses/index', [
            'title' => 'Course Catalog — PUB CampusMate',
            'courses' => $courses
        ]);
    }

    public function show(string $id): void
    {
        $course = $this->courseModel->find((int)$id);
        if (!$course) {
            $this->redirect('/courses');
            return;
        }

        $prereqs = $this->courseModel->getPrerequisites((int)$id);

        $this->render('courses/show', [
            'title' => "{$course['course_code']}: {$course['title']} — PUB CampusMate",
            'course' => $course,
            'prerequisites' => $prereqs
        ]);
    }
}
