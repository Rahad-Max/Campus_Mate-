<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Semester;

class SemesterController extends Controller
{
    private Semester $semesterModel;

    public function __construct()
    {
        $this->semesterModel = new Semester();
    }

    public function index(): void
    {
        $semesters = $this->semesterModel->getAllOrdered();
        $current = $this->semesterModel->getCurrent();

        $this->render('coordinator/semesters/index', [
            'title' => 'Semester & Academic Terms Management — PUB CampusMate',
            'semesters' => $semesters,
            'current' => $current
        ]);
    }

    public function create(): void
    {
        $this->render('coordinator/semesters/create', [
            'title' => 'Create Academic Semester — PUB CampusMate'
        ]);
    }

    public function store(): void
    {
        $data = $this->validate($_POST, [
            'code' => 'required',
            'name' => 'required',
            'academic_year' => 'required|numeric',
            'term' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'registration_start' => 'required',
            'registration_end' => 'required',
            'add_drop_deadline' => 'required'
        ]);

        $code = strtoupper(trim($data['code'] ?? ''));
        $name = trim($data['name'] ?? '');
        $year = (int)($data['academic_year'] ?? date('Y'));
        $term = in_array($data['term'] ?? '', ['Spring', 'Summer', 'Fall']) ? $data['term'] : 'Summer';

        $startDate = trim($data['start_date'] ?? '');
        $endDate = trim($data['end_date'] ?? '');
        $regStart = trim($data['registration_start'] ?? '');
        $regEnd = trim($data['registration_end'] ?? '');
        $addDrop = trim($data['add_drop_deadline'] ?? '');

        // 1. Uniqueness check
        if (!$this->semesterModel->isCodeUnique($code)) {
            $this->setFlash('danger', "Semester code '{$code}' already exists.");
            $this->redirect('/coordinator/semesters/create');
            return;
        }

        // 2. Date validation
        $dateErrors = Semester::validateDates($startDate, $endDate, $regStart, $regEnd, $addDrop);
        if (!empty($dateErrors)) {
            $this->setFlash('danger', implode(' ', $dateErrors));
            $this->redirect('/coordinator/semesters/create');
            return;
        }

        $id = $this->semesterModel->create([
            'code' => $code,
            'name' => $name,
            'academic_year' => $year,
            'term' => $term,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'registration_start' => $regStart,
            'registration_end' => $regEnd,
            'add_drop_deadline' => $addDrop,
            'is_current' => 0,
            'status' => 'upcoming'
        ]);

        if ($id) {
            $this->setFlash('success', "Semester '{$name}' created successfully.");
            $this->redirect('/coordinator/semesters');
        } else {
            $this->setFlash('danger', 'Failed to save semester record.');
            $this->redirect('/coordinator/semesters/create');
        }
    }

    public function edit(string $id): void
    {
        $semester = $this->semesterModel->find((int)$id);
        if (!$semester) {
            $this->setFlash('danger', 'Semester not found.');
            $this->redirect('/coordinator/semesters');
            return;
        }

        $this->render('coordinator/semesters/edit', [
            'title' => "Edit Semester: {$semester['name']} — PUB CampusMate",
            'semester' => $semester
        ]);
    }

    public function update(string $id): void
    {
        $semId = (int)$id;
        $semester = $this->semesterModel->find($semId);
        if (!$semester) {
            $this->setFlash('danger', 'Semester not found.');
            $this->redirect('/coordinator/semesters');
            return;
        }

        $data = $this->validate($_POST, [
            'code' => 'required',
            'name' => 'required',
            'academic_year' => 'required|numeric',
            'term' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'registration_start' => 'required',
            'registration_end' => 'required',
            'add_drop_deadline' => 'required'
        ]);

        $code = strtoupper(trim($data['code'] ?? ''));
        $name = trim($data['name'] ?? '');
        $year = (int)($data['academic_year'] ?? date('Y'));
        $term = in_array($data['term'] ?? '', ['Spring', 'Summer', 'Fall']) ? $data['term'] : 'Summer';

        $startDate = trim($data['start_date'] ?? '');
        $endDate = trim($data['end_date'] ?? '');
        $regStart = trim($data['registration_start'] ?? '');
        $regEnd = trim($data['registration_end'] ?? '');
        $addDrop = trim($data['add_drop_deadline'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['upcoming', 'registration_open', 'active', 'grading', 'closed'])
            ? $_POST['status']
            : $semester['status'];

        if (!$this->semesterModel->isCodeUnique($code, $semId)) {
            $this->setFlash('danger', "Semester code '{$code}' is already used by another semester.");
            $this->redirect("/coordinator/semesters/{$semId}/edit");
            return;
        }

        $dateErrors = Semester::validateDates($startDate, $endDate, $regStart, $regEnd, $addDrop);
        if (!empty($dateErrors)) {
            $this->setFlash('danger', implode(' ', $dateErrors));
            $this->redirect("/coordinator/semesters/{$semId}/edit");
            return;
        }

        $this->semesterModel->update($semId, [
            'code' => $code,
            'name' => $name,
            'academic_year' => $year,
            'term' => $term,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'registration_start' => $regStart,
            'registration_end' => $regEnd,
            'add_drop_deadline' => $addDrop,
            'status' => $status
        ]);

        $this->setFlash('success', "Semester '{$name}' updated successfully.");
        $this->redirect('/coordinator/semesters');
    }

    public function setCurrent(string $id): void
    {
        $semId = (int)$id;
        $semester = $this->semesterModel->find($semId);
        if (!$semester) {
            $this->setFlash('danger', 'Semester not found.');
            $this->redirect('/coordinator/semesters');
            return;
        }

        $success = $this->semesterModel->setCurrent($semId);
        if ($success) {
            $this->setFlash('success', "Semester '{$semester['name']}' is now set as the active current term.");
        } else {
            $this->setFlash('danger', 'Failed to update current semester.');
        }

        $this->redirect('/coordinator/semesters');
    }

    public function close(string $id): void
    {
        $semId = (int)$id;
        $semester = $this->semesterModel->find($semId);
        if (!$semester) {
            $this->setFlash('danger', 'Semester not found.');
            $this->redirect('/coordinator/semesters');
            return;
        }

        $this->semesterModel->update($semId, [
            'status' => 'closed',
            'is_current' => 0
        ]);

        $this->setFlash('info', "Semester '{$semester['name']}' has been officially closed and archived.");
        $this->redirect('/coordinator/semesters');
    }
}
