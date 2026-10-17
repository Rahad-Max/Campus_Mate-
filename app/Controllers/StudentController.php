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
}
