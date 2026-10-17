<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Student;
use App\Models\Semester;
use App\Models\CourseOffering;
use App\Models\Registration;
use App\Models\RegistrationRequest;
use App\Services\RegistrationService;

class RegistrationController extends Controller
{
    private Student $studentModel;
    private Semester $semesterModel;
    private CourseOffering $offeringModel;
    private Registration $regModel;
    private RegistrationRequest $requestModel;
    private RegistrationService $registrationService;

    public function __construct()
    {
        $this->studentModel = new Student();
        $this->semesterModel = new Semester();
        $this->offeringModel = new CourseOffering();
        $this->regModel = new Registration();
        $this->requestModel = new RegistrationRequest();
        $this->registrationService = new RegistrationService();
    }

    public function index(): void
    {
        $user = $this->authUser();
        $studentRecord = $this->studentModel->findByUserId($user['id']);
        $studentId = (int)($studentRecord['id'] ?? 0);

        $semester = $this->semesterModel->getCurrent();
        $semId = $semester['id'] ?? 2;

        $offerings = $this->offeringModel->getOfferingsBySemester($semId);
        $enrolled = $this->regModel->getEnrolledCourses($studentId, $semId);
        $requests = $this->requestModel->getStudentRequests($studentId, $semId);

        $this->render('registration/index', [
            'title' => 'Course Registration — PUB CampusMate',
            'semester' => $semester,
            'offerings' => $offerings,
            'enrolled' => $enrolled,
            'requests' => $requests,
            'student' => $studentRecord
        ]);
    }

    public function submit(): void
    {
        $user = $this->authUser();
        $studentRecord = $this->studentModel->findByUserId($user['id']);
        $studentId = (int)($studentRecord['id'] ?? 0);

        $semester = $this->semesterModel->getCurrent();
        $semId = (int)($semester['id'] ?? 2);

        $sectionIds = array_map('intval', $_POST['sections'] ?? []);
        $notes = sanitize($_POST['notes'] ?? '');

        if (empty($sectionIds)) {
            $this->setFlash('danger', 'Please select at least one course section.');
            $this->redirect('/registration');
            return;
        }

        try {
            $requestId = $this->registrationService->submitRequest($studentId, $semId, $sectionIds, $notes);
            $this->setFlash('success', "Registration request #{$requestId} submitted successfully. Awaiting Academic Advisor approval.");
        } catch (\Throwable $e) {
            $this->setFlash('danger', "Registration submission failed: " . $e->getMessage());
        }

        $this->redirect('/registration');
    }

    public function validateCartApi(): void
    {
        $user = $this->authUser();
        $studentRecord = $this->studentModel->findByUserId($user['id']);
        $studentId = (int)($studentRecord['id'] ?? 0);

        $semester = $this->semesterModel->getCurrent();
        $semId = (int)($semester['id'] ?? 2);

        $sectionIds = array_map('intval', $_POST['sections'] ?? []);
        $result = $this->registrationService->validateCart($studentId, $semId, $sectionIds);

        $this->json($result);
    }
}
