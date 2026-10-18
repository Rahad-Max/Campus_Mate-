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
        if (!$teacher) {
            $this->setFlash('danger', 'Teacher record not found for your account.');
            $this->redirect('/advisor/dashboard');
            return;
        }

        $teacherId = (int)$teacher['id'];
        $pendingRequests = $this->requestModel->getPendingForAdvisor($teacherId);
        $historyRequests = $this->requestModel->getAdvisorHistory($teacherId);

        $this->render('advisor/requests', [
            'title' => 'Registration Approval Queue — PUB CampusMate',
            'requests' => $pendingRequests,
            'history' => $historyRequests,
            'teacher' => $teacher
        ]);
    }

    public function showRequest(string $id): void
    {
        $user = $this->authUser();
        $teacher = $this->teacherModel->findByUserId($user['id']);
        if (!$teacher) {
            $this->setFlash('danger', 'Teacher record not found.');
            $this->redirect('/advisor/dashboard');
            return;
        }

        $teacherId = (int)$teacher['id'];
        $requestId = (int)$id;
        $request = $this->requestModel->getRequestWithDetails($requestId);

        if (!$request) {
            $this->setFlash('danger', "Registration request #{$id} not found.");
            $this->redirect('/advisor/registration-requests');
            return;
        }

        $studentId = (int)$request['student_id'];

        // Verify advisor boundary
        if (!$this->requestModel->isStudentAssignedToAdvisor($studentId, $teacherId)) {
            $this->setFlash('danger', 'Unauthorized: You are not the assigned Academic Advisor for this student.');
            $this->redirect('/advisor/registration-requests');
            return;
        }

        $items = $this->requestModel->getItems($requestId);
        $studentModel = new \App\Models\Student();
        $academicHistory = $studentModel->getAcademicHistory($studentId);

        // Pre-audit validations
        $prereqService = new \App\Services\PrerequisiteService();
        $creditLimitService = new \App\Services\CreditLimitService();
        $seatService = new \App\Services\SeatAvailabilityService();
        $conflictService = new \App\Services\TimetableConflictService();

        $sectionIds = array_column($items, 'section_id');

        // Audit each item
        $auditResults = [];
        foreach ($items as $item) {
            $cid = (int)$item['course_id'];
            $sid = (int)$item['section_id'];
            $auditResults[$sid] = [
                'prereq' => $prereqService->check($studentId, $cid),
                'seat' => $seatService->checkBatch([$sid])[$sid] ?? []
            ];
        }

        $creditAudit = $creditLimitService->validate($studentId, (float)$request['total_credits']);
        $conflictAudit = $conflictService->detectInternalConflicts($sectionIds);

        $this->render('advisor/show_request', [
            'title' => "Review Registration Request #{$requestId} — PUB CampusMate",
            'request' => $request,
            'items' => $items,
            'history' => $academicHistory,
            'auditResults' => $auditResults,
            'creditAudit' => $creditAudit,
            'conflictAudit' => $conflictAudit,
            'teacher' => $teacher
        ]);
    }

    public function approve(string $id): void
    {
        $user = $this->authUser();
        $teacher = $this->teacherModel->findByUserId($user['id']);
        if (!$teacher) {
            $this->setFlash('danger', 'Teacher record not found.');
            $this->redirect('/advisor/dashboard');
            return;
        }

        $remarks = sanitize($_POST['remarks'] ?? 'Prerequisites, workload, and routine verified. Approved by Academic Advisor.');

        try {
            $this->registrationService->approveRequest((int)$id, (int)$teacher['id'], $remarks);
            $this->setFlash('success', "Registration request #{$id} has been approved and courses enrolled successfully.");
        } catch (\Throwable $e) {
            $this->setFlash('danger', "Approval failed: " . $e->getMessage());
        }

        $this->redirect('/advisor/registration-requests');
    }

    public function reject(string $id): void
    {
        $user = $this->authUser();
        $teacher = $this->teacherModel->findByUserId($user['id']);
        if (!$teacher) {
            $this->setFlash('danger', 'Teacher record not found.');
            $this->redirect('/advisor/dashboard');
            return;
        }

        $reason = sanitize($_POST['rejection_reason'] ?? '');
        if (empty($reason)) {
            $this->setFlash('danger', 'Please provide a clear rejection reason for the advisee.');
            $this->redirect("/advisor/registration-requests/{$id}");
            return;
        }

        try {
            $this->registrationService->rejectRequest((int)$id, (int)$teacher['id'], $reason);
            $this->setFlash('warning', "Registration request #{$id} has been rejected with documented reason.");
        } catch (\Throwable $e) {
            $this->setFlash('danger', "Rejection failed: " . $e->getMessage());
        }

        $this->redirect('/advisor/registration-requests');
    }

    public function courses(): void
    {
        $courses = (new \App\Models\Course())->getAllWithDetails(1);
        $semesters = (new \App\Models\Semester())->getAllOrdered();

        $this->render('advisor/courses', [
            'title' => 'Curriculum Courses & Prerequisites — PUB CampusMate',
            'courses' => $courses,
            'semesters' => $semesters
        ]);
    }
}
