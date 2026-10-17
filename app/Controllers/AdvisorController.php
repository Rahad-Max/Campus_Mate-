<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Teacher;
use App\Models\RegistrationRequest;
use App\Services\RegistrationService;

class AdvisorController extends Controller
{
    private Teacher $teacherModel;
    private RegistrationRequest $requestModel;
    private RegistrationService $registrationService;

    public function __construct()
    {
        $this->teacherModel = new Teacher();
        $this->requestModel = new RegistrationRequest();
        $this->registrationService = new RegistrationService();
    }

    public function advisees(): void
    {
        $user = $this->authUser();
        $teacher = $this->teacherModel->findByUserId($user['id']);
        $advisees = $teacher ? $this->teacherModel->getAssignedAdvisees((int)$teacher['id']) : [];

        $this->render('advisor/advisees', [
            'title' => 'My Assigned Advisees — PUB CampusMate',
            'advisees' => $advisees
        ]);
    }

    public function reviewRequests(): void
    {
        $user = $this->authUser();
        $teacher = $this->teacherModel->findByUserId($user['id']);
        $requests = $teacher ? $this->requestModel->getPendingForAdvisor((int)$teacher['id']) : [];

        $this->render('advisor/requests', [
            'title' => 'Review Registration Requests — PUB CampusMate',
            'requests' => $requests
        ]);
    }

    public function approve(string $id): void
    {
        $user = $this->authUser();
        $teacher = $this->teacherModel->findByUserId($user['id']);
        $remarks = sanitize($_POST['remarks'] ?? 'Approved by Academic Advisor.');

        try {
            $this->registrationService->approveRequest((int)$id, (int)$teacher['id'], $remarks);
            $this->setFlash('success', "Registration request #{$id} has been approved and courses enrolled successfully.");
        } catch (\Throwable $e) {
            $this->setFlash('danger', "Approval failed: " . $e->getMessage());
        }

        $this->redirect('/advisor/requests');
    }
}
