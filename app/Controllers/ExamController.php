<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Exam;
use App\Models\Semester;
use App\Models\Student;

class ExamController extends Controller
{
    private Exam $examModel;
    private Semester $semesterModel;

    public function __construct()
    {
        $this->examModel = new Exam();
        $this->semesterModel = new Semester();
    }

    public function index(): void
    {
        $user = $this->authUser();
        $semester = $this->semesterModel->getCurrent();
        $semId = (int)($semester['id'] ?? 2);

        $studentModel = new Student();
        $s = $studentModel->findByUserId($user['id']);
        $exams = $s ? $this->examModel->getExamScheduleForStudent((int)$s['id'], $semId) : [];

        $this->render('exams/index', [
            'title' => 'Examination Routines — PUB CampusMate',
            'semester' => $semester,
            'exams' => $exams
        ]);
    }
}
