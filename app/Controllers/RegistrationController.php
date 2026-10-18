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
        $student = $this->studentModel->findByUserId($user['id']);
        if (!$student) {
            $this->setFlash('danger', 'Student record not found for your account.');
            $this->redirect('/student/dashboard');
            return;
        }
        $studentId = (int)$student['id'];
        $fullStudent = $this->studentModel->getFullDetails($studentId);

        $semester = $this->semesterModel->getCurrent();
        if (!$semester) {
            $semester = $this->semesterModel->find(2) ?: [];
        }
        $semId = (int)($semester['id'] ?? 2);

        // Fetch offerings with detailed sections, schedules, and teacher info
        $offerings = $this->offeringModel->getOfferingsBySemester($semId);
        $sectionModel = new \App\Models\Section();
        $prereqService = new \App\Services\PrerequisiteService();

        foreach ($offerings as &$offering) {
            $offeringId = (int)$offering['id'];
            $courseId = (int)$offering['course_id'];
            
            // Check prerequisites for this course for this student
            $offering['prereq_status'] = $prereqService->check($studentId, $courseId);

            // Fetch sections with schedules
            $sections = $sectionModel->getAllWithDetails($semId, (int)$offering['department_id']);
            $offeringSections = array_values(array_filter($sections, fn($s) => (int)$s['course_offering_id'] === $offeringId));
            
            foreach ($offeringSections as &$sec) {
                $sec['schedules'] = $sectionModel->getSchedules((int)$sec['id']);
                // Lead instructor
                $leadTeacher = (new \App\Models\TeacherAssignment())->getLeadTeacherForSection((int)$sec['id']);
                $sec['lead_teacher'] = $leadTeacher;
            }
            unset($sec);

            $offering['sections'] = $offeringSections;
        }
        unset($offering);

        $enrolled = $this->regModel->getEnrolledCourses($studentId, $semId);
        $requests = $this->requestModel->getStudentRequests($studentId, $semId);
        $hasPending = $this->requestModel->hasPendingRequest($studentId, $semId);

        // Academic credit limits
        $config = require dirname(__DIR__, 2) . '/config/app.php';
        $policy = $config['academic'] ?? [
            'min_credits_per_semester' => 9.0,
            'max_credits_per_semester' => 18.0,
            'probation_max_credits' => 12.0
        ];

        $isProbation = ($student['academic_status'] ?? 'regular') === 'probation' || ((float)($student['cgpa'] ?? 0) > 0 && (float)($student['cgpa'] ?? 0) < 2.50);
        $maxCredits = $isProbation ? (float)$policy['probation_max_credits'] : (float)$policy['max_credits_per_semester'];
        $minCredits = (float)$policy['min_credits_per_semester'];

        $this->render('registration/index', [
            'title' => 'Smart Course Registration — PUB CampusMate',
            'semester' => $semester,
            'student' => $fullStudent ?: $student,
            'offerings' => $offerings,
            'enrolled' => $enrolled,
            'requests' => $requests,
            'hasPending' => $hasPending,
            'minCredits' => $minCredits,
            'maxCredits' => $maxCredits,
            'isProbation' => $isProbation
        ]);
    }

    public function submit(): void
    {
        $user = $this->authUser();
        $student = $this->studentModel->findByUserId($user['id']);
        if (!$student) {
            $this->setFlash('danger', 'Student profile not found.');
            $this->redirect('/student/dashboard');
            return;
        }
        $studentId = (int)$student['id'];

        $semester = $this->semesterModel->getCurrent();
        $semId = (int)($semester['id'] ?? 2);

        $sectionIds = array_map('intval', $_POST['sections'] ?? []);
        $notes = sanitize($_POST['notes'] ?? '');

        if (empty($sectionIds)) {
            $this->setFlash('danger', 'Please select at least one course section to register.');
            $this->redirect('/student/registration');
            return;
        }

        try {
            $requestId = $this->registrationService->submitRequest($studentId, $semId, $sectionIds, $notes);
            $this->setFlash('success', "Registration request #{$requestId} has been submitted for Academic Advisor review.");
            $this->redirect("/student/registration/{$requestId}");
            return;
        } catch (\Throwable $e) {
            $this->setFlash('danger', "Registration submission failed: " . $e->getMessage());
            $this->redirect('/student/registration');
        }
    }

    public function validateCartApi(): void
    {
        $user = $this->authUser();
        $student = $this->studentModel->findByUserId($user['id']);
        $studentId = (int)($student['id'] ?? 0);

        $semester = $this->semesterModel->getCurrent();
        $semId = (int)($semester['id'] ?? 2);

        $sectionIds = array_map('intval', $_POST['sections'] ?? []);
        $result = $this->registrationService->validateCart($studentId, $semId, $sectionIds);

        $this->json($result);
    }

    public function history(): void
    {
        $user = $this->authUser();
        $student = $this->studentModel->findByUserId($user['id']);
        if (!$student) {
            $this->setFlash('danger', 'Student record not found.');
            $this->redirect('/student/dashboard');
            return;
        }
        $studentId = (int)$student['id'];

        $requests = $this->requestModel->getStudentRequests($studentId);
        $fullStudent = $this->studentModel->getFullDetails($studentId);

        $this->render('registration/history', [
            'title' => 'My Course Registration History — PUB CampusMate',
            'requests' => $requests,
            'student' => $fullStudent ?: $student
        ]);
    }

    public function show(string $id): void
    {
        $user = $this->authUser();
        $student = $this->studentModel->findByUserId($user['id']);
        if (!$student) {
            $this->setFlash('danger', 'Student record not found.');
            $this->redirect('/student/dashboard');
            return;
        }
        $studentId = (int)$student['id'];
        $requestId = (int)$id;

        $request = $this->requestModel->getRequestWithDetails($requestId);
        if (!$request) {
            $this->setFlash('danger', "Registration request #{$id} not found.");
            $this->redirect('/student/registration/history');
            return;
        }

        // Ownership authorization check
        if ((int)$request['student_id'] !== $studentId) {
            $this->setFlash('danger', 'Unauthorized access to this registration record.');
            $this->redirect('/student/registration/history');
            return;
        }

        $items = $this->requestModel->getItems($requestId);

        $this->render('registration/show', [
            'title' => "Registration Request #{$requestId} — PUB CampusMate",
            'request' => $request,
            'items' => $items,
            'student' => $student
        ]);
    }

    public function cancel(string $id): void
    {
        $user = $this->authUser();
        $student = $this->studentModel->findByUserId($user['id']);
        if (!$student) {
            $this->setFlash('danger', 'Student record not found.');
            $this->redirect('/student/dashboard');
            return;
        }
        $studentId = (int)$student['id'];
        $requestId = (int)$id;
        $reason = sanitize($_POST['reason'] ?? 'Cancelled by student.');

        try {
            $this->registrationService->cancelRequest($requestId, $studentId, $reason);
            $this->setFlash('success', "Registration request #{$id} has been cancelled successfully.");
        } catch (\Throwable $e) {
            $this->setFlash('danger', "Failed to cancel request: " . $e->getMessage());
        }

        $this->redirect("/student/registration/{$requestId}");
    }
}
