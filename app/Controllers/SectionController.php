<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Section;
use App\Models\CourseOffering;
use App\Models\Semester;

class SectionController extends Controller
{
    private Section $sectionModel;
    private CourseOffering $offeringModel;
    private Semester $semesterModel;

    public function __construct()
    {
        $this->sectionModel = new Section();
        $this->offeringModel = new CourseOffering();
        $this->semesterModel = new Semester();
    }

    public function index(): void
    {
        $currentSem = $this->semesterModel->getCurrent();
        $semId = !empty($_GET['semester_id']) ? (int)$_GET['semester_id'] : ($currentSem['id'] ?? 1);

        $sections = $this->sectionModel->getAllWithDetails($semId);
        $semesters = $this->semesterModel->getAllOrdered();

        $this->render('coordinator/sections/index', [
            'title' => 'Course Sections Management — PUB CampusMate',
            'sections' => $sections,
            'semesters' => $semesters,
            'semesterId' => $semId,
            'selectedSemId' => $semId
        ]);
    }

    public function create(): void
    {
        $offeringId = (int)($_GET['offering_id'] ?? 0);
        $currentSem = $this->semesterModel->getCurrent();
        $semId = $currentSem['id'] ?? 1;

        $offerings = $this->offeringModel->getOfferingsBySemester($semId);
        $selectedOffering = $offeringId ? $this->offeringModel->find($offeringId) : null;

        $rooms = (new \App\Models\Room())->getAvailable();

        $this->render('coordinator/sections/create', [
            'title' => 'Create Course Section — PUB CampusMate',
            'offerings' => $offerings,
            'rooms' => $rooms,
            'selectedOfferingId' => $offeringId,
            'selectedOffering' => $selectedOffering
        ]);
    }

    public function store(): void
    {
        $data = $this->validate($_POST, [
            'course_offering_id' => 'required|numeric',
            'section_name' => 'required',
            'capacity' => 'required|numeric'
        ]);

        $offeringId = (int)$data['course_offering_id'];
        $sectionName = strtoupper(trim($data['section_name'] ?? ''));
        $capacity = (int)($data['capacity'] ?? 0);

        // 1. Capacity must be positive
        if ($capacity <= 0) {
            $this->setFlash('danger', 'Seat capacity must be a positive integer greater than zero.');
            $this->redirect("/coordinator/sections/create?offering_id={$offeringId}");
            return;
        }

        // 2. Prevent duplicate section name within the same offering
        if (!$this->sectionModel->isSectionNameUnique($offeringId, $sectionName)) {
            $this->setFlash('danger', "Section '{$sectionName}' already exists for this course offering.");
            $this->redirect("/coordinator/sections/create?offering_id={$offeringId}");
            return;
        }

        $id = $this->sectionModel->create([
            'course_offering_id' => $offeringId,
            'section_name' => $sectionName,
            'capacity' => $capacity,
            'enrolled_count' => 0,
            'status' => 'open'
        ]);

        if ($id) {
            $this->setFlash('success', "Section '{$sectionName}' created with capacity {$capacity}.");
            $this->redirect('/coordinator/sections');
        } else {
            $this->setFlash('danger', 'Failed to create section.');
            $this->redirect("/coordinator/sections/create?offering_id={$offeringId}");
        }
    }

    public function edit(string $id): void
    {
        $section = $this->sectionModel->getDetailedInfo((int)$id);
        if (!$section) {
            $this->setFlash('danger', 'Section not found.');
            $this->redirect('/coordinator/sections');
            return;
        }

        $offeringId = (int)($section['course_offering_id'] ?? $section['offering_id'] ?? 1);
        $offering = $this->offeringModel->find($offeringId);
        $offerings = $this->offeringModel->getOfferingsBySemester((int)($offering['semester_id'] ?? 1));
        $rooms = (new \App\Models\Room())->getAvailable();

        $this->render('coordinator/sections/edit', [
            'title' => "Edit Section {$section['section_name']} ({$section['course_code']}) — PUB CampusMate",
            'section' => $section,
            'offerings' => $offerings,
            'rooms' => $rooms
        ]);
    }

    public function update(string $id): void
    {
        $sectionId = (int)$id;
        $section = $this->sectionModel->find($sectionId);
        if (!$section) {
            $this->setFlash('danger', 'Section not found.');
            $this->redirect('/coordinator/sections');
            return;
        }

        $data = $this->validate($_POST, [
            'section_name' => 'required',
            'capacity' => 'required|numeric'
        ]);

        $sectionName = strtoupper(trim($data['section_name'] ?? ''));
        $capacity = (int)($data['capacity'] ?? 0);
        $status = in_array($_POST['status'] ?? '', ['open', 'closed', 'cancelled']) ? $_POST['status'] : $section['status'];

        if ($capacity <= 0) {
            $this->setFlash('danger', 'Seat capacity must be positive.');
            $this->redirect("/coordinator/sections/{$sectionId}/edit");
            return;
        }

        if ($capacity < (int)$section['enrolled_count']) {
            $this->setFlash('danger', "Capacity cannot be less than currently enrolled students ({$section['enrolled_count']}).");
            $this->redirect("/coordinator/sections/{$sectionId}/edit");
            return;
        }

        if (!$this->sectionModel->isSectionNameUnique((int)$section['course_offering_id'], $sectionName, $sectionId)) {
            $this->setFlash('danger', "Section '{$sectionName}' already exists for this offering.");
            $this->redirect("/coordinator/sections/{$sectionId}/edit");
            return;
        }

        $this->sectionModel->update($sectionId, [
            'section_name' => $sectionName,
            'capacity' => $capacity,
            'status' => $status
        ]);

        $this->setFlash('success', "Section '{$sectionName}' updated successfully.");
        $this->redirect('/coordinator/sections');
    }

    public function toggleStatus(string $id): void
    {
        $sectionId = (int)$id;
        $section = $this->sectionModel->find($sectionId);
        if (!$section) {
            $this->setFlash('danger', 'Section not found.');
            $this->redirect('/coordinator/sections');
            return;
        }

        $newStatus = ($section['status'] === 'open') ? 'closed' : 'open';
        $this->sectionModel->update($sectionId, ['status' => $newStatus]);

        $this->setFlash('success', "Section {$section['section_name']} status set to {$newStatus}.");
        $this->redirect('/coordinator/sections');
    }
}
