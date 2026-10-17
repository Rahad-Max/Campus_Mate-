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
}
