<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Department;

class DepartmentController extends Controller
{
    private Department $departmentModel;

    public function __construct()
    {
        $this->departmentModel = new Department();
    }

    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');
        if (!empty($search)) {
            $departments = $this->departmentModel->search($search);
        } else {
            $departments = $this->departmentModel->all('code ASC');
        }

        $this->render('coordinator/departments/index', [
            'title' => 'Departments Management — PUB CampusMate',
            'departments' => $departments,
            'search' => $search
        ]);
    }

    public function show(string $id): void
    {
        $department = $this->departmentModel->find((int)$id);
        if (!$department) {
            $this->setFlash('danger', 'Department not found.');
            $this->redirect('/coordinator/departments');
            return;
        }

        $stats = $this->departmentModel->getStatistics((int)$id);

        $this->render('coordinator/departments/show', [
            'title' => "{$department['name']} ({$department['code']}) — PUB CampusMate",
            'department' => $department,
            'stats' => $stats
        ]);
    }

    public function create(): void
    {
        $this->render('coordinator/departments/create', [
            'title' => 'Add New Department — PUB CampusMate'
        ]);
    }

    public function store(): void
    {
        $data = $this->validate($_POST, [
            'code' => 'required',
            'name' => 'required',
            'faculty_name' => 'required'
        ]);

        $code = strtoupper(trim($data['code'] ?? ''));
        $name = trim($data['name'] ?? '');
        $faculty = trim($data['faculty_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (!$this->departmentModel->isCodeUnique($code)) {
            $this->setFlash('danger', "Department code '{$code}' is already registered.");
            $this->redirect('/coordinator/departments/create');
            return;
        }

        $id = $this->departmentModel->create([
            'code' => $code,
            'name' => $name,
            'faculty_name' => $faculty,
            'description' => $description,
            'status' => 'active'
        ]);

        if ($id) {
            $this->setFlash('success', "Department '{$code}' created successfully.");
            $this->redirect('/coordinator/departments');
        } else {
            $this->setFlash('danger', 'Failed to create department. Please try again.');
            $this->redirect('/coordinator/departments/create');
        }
    }

    public function edit(string $id): void
    {
        $dept = $this->departmentModel->find((int)$id);
        if (!$dept) {
            $this->setFlash('danger', 'Department not found.');
            $this->redirect('/coordinator/departments');
            return;
        }

        $this->render('coordinator/departments/edit', [
            'title' => "Edit Department: {$dept['code']} — PUB CampusMate",
            'department' => $dept
        ]);
    }

    public function update(string $id): void
    {
        $deptId = (int)$id;
        $dept = $this->departmentModel->find($deptId);
        if (!$dept) {
            $this->setFlash('danger', 'Department not found.');
            $this->redirect('/coordinator/departments');
            return;
        }

        $data = $this->validate($_POST, [
            'code' => 'required',
            'name' => 'required',
            'faculty_name' => 'required'
        ]);

        $code = strtoupper(trim($data['code'] ?? ''));
        $name = trim($data['name'] ?? '');
        $faculty = trim($data['faculty_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';

        if (!$this->departmentModel->isCodeUnique($code, $deptId)) {
            $this->setFlash('danger', "Department code '{$code}' is already assigned to another department.");
            $this->redirect("/coordinator/departments/{$deptId}/edit");
            return;
        }

        if ($status === 'inactive' && $this->departmentModel->hasAcademicReferences($deptId)) {
            $this->setFlash('warning', 'Cannot deactivate department with active students, faculty, or curriculum courses.');
            $status = 'active';
        }

        $this->departmentModel->update($deptId, [
            'code' => $code,
            'name' => $name,
            'faculty_name' => $faculty,
            'description' => $description,
            'status' => $status
        ]);

        $this->setFlash('success', "Department '{$code}' updated successfully.");
        $this->redirect('/coordinator/departments');
    }

    public function toggleStatus(string $id): void
    {
        $deptId = (int)$id;
        $dept = $this->departmentModel->find($deptId);
        if (!$dept) {
            $this->setFlash('danger', 'Department not found.');
            $this->redirect('/coordinator/departments');
            return;
        }

        $newStatus = ($dept['status'] === 'active') ? 'inactive' : 'active';

        if ($newStatus === 'inactive' && $this->departmentModel->hasAcademicReferences($deptId)) {
            $this->setFlash('danger', 'Cannot deactivate department with active student records, faculty, or courses.');
            $this->redirect('/coordinator/departments');
            return;
        }

        $this->departmentModel->update($deptId, ['status' => $newStatus]);
        $this->setFlash('success', "Department '{$dept['code']}' status changed to {$newStatus}.");
        $this->redirect('/coordinator/departments');
    }
}
