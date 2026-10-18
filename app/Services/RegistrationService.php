<?php
namespace App\Services;

use App\Core\Database;
use App\Models\Section;
use App\Models\Registration;
use App\Models\RegistrationRequest;
use App\Models\CourseOffering;
use App\Exceptions\AppException;

class RegistrationService
{
    private PrerequisiteService $prereqService;
    private CreditLimitService $creditLimitService;
    private SeatAvailabilityService $seatService;
    private TimetableConflictService $conflictService;
    private NotificationService $notifService;
    private RegistrationRequest $requestModel;
    private Registration $regModel;
    private Section $sectionModel;

    public function __construct()
    {
        $this->prereqService = new PrerequisiteService();
        $this->creditLimitService = new CreditLimitService();
        $this->seatService = new SeatAvailabilityService();
        $this->conflictService = new TimetableConflictService();
        $this->requestModel = new RegistrationRequest();
        $this->regModel = new Registration();
        $this->sectionModel = new Section();
        $this->notifService = new NotificationService();
    }

    /**
     * Comprehensive validation of a student's course registration cart before submission.
     */
    public function validateCart(int $studentId, int $semesterId, array $sectionIds): array
    {
        $errors = [];
        $totalCredits = 0.0;
        $courseIdsSeen = [];
        $validatedSections = [];

        if (empty($sectionIds)) {
            return [
                'valid' => false,
                'errors' => ['Please select at least one course section to register.'],
                'total_credits' => 0.0,
                'sections' => []
            ];
        }

        foreach ($sectionIds as $secId) {
            $sec = $this->sectionModel->getDetailedInfo($secId);
            if (!$sec) {
                $errors[] = "Section ID #{$secId} not found.";
                continue;
            }

            $courseId = (int)$sec['course_id'];
            $courseCode = $sec['course_code'];
            $credit = (float)$sec['credit'];
            $totalCredits += $credit;
            $validatedSections[] = $sec;

            // 1. Prevent duplicate course in cart
            if (in_array($courseId, $courseIdsSeen, true)) {
                $errors[] = "Duplicate course in selection: {$courseCode} selected more than once.";
            }
            $courseIdsSeen[] = $courseId;

            // 2. Prevent duplicate course already enrolled
            if ($this->regModel->isCourseAlreadyEnrolled($studentId, $semesterId, $courseId)) {
                $errors[] = "You are already enrolled in {$courseCode} for this semester.";
            }

            // 3. Prerequisite check
            $prereqResult = $this->prereqService->check($studentId, $courseId);
            if (!$prereqResult['passed']) {
                $missingList = array_map(fn($m) => "{$m['course_code']} ({$m['title']})", $prereqResult['missing']);
                $errors[] = "Unsatisfied prerequisites for {$courseCode}: Missing " . implode(', ', $missingList) . ".";
            }

            // 4. Seat availability check
            if (!$this->seatService->isAvailable($secId)) {
                $errors[] = "No available seats in {$courseCode} (Section {$sec['section_name']}).";
            }
        }

        // 5. Timetable conflict check within cart
        $conflictResult = $this->conflictService->detectInternalConflicts($sectionIds);
        if ($conflictResult['has_conflict']) {
            $conflictMsgs = [];
            foreach ($conflictResult['conflicts'] as $c) {
                $conflictMsgs[] = "Clash on {$c['day']} between {$c['time_a']} and {$c['time_b']}";
            }
            $errors[] = "Timetable conflict detected: " . implode('; ', $conflictMsgs) . ".";
        }

        // 6. Timetable conflict against already enrolled courses
        $enrolledConflictResult = $this->conflictService->detectEnrolledConflicts($studentId, $semesterId, $sectionIds);
        if ($enrolledConflictResult['has_conflict']) {
            $errors[] = "Selected courses clash with your currently enrolled semester routine.";
        }

        // 7. Credit limit check
        $creditCheck = $this->creditLimitService->validate($studentId, $totalCredits);
        if (!$creditCheck['valid']) {
            $errors[] = $creditCheck['message'];
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'total_credits' => $totalCredits,
            'sections' => $validatedSections
        ];
    }

    /**
     * Submit a registration request for advisor review.
     */
    public function submitRequest(int $studentId, int $semesterId, array $sectionIds, string $notes = ''): int
    {
        // Check for existing pending request
        if ($this->requestModel->hasPendingRequest($studentId, $semesterId)) {
            throw new AppException("You already have a pending registration request awaiting Academic Advisor review for this semester. You cannot submit multiple concurrent requests.");
        }

        $validation = $this->validateCart($studentId, $semesterId, $sectionIds);
        if (!$validation['valid']) {
            throw new AppException(implode(' | ', $validation['errors']));
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $requestId = $this->requestModel->create([
                'student_id' => $studentId,
                'semester_id' => $semesterId,
                'request_type' => 'regular',
                'total_credits' => $validation['total_credits'],
                'status' => 'submitted',
                'submission_notes' => $notes
            ]);

            foreach ($sectionIds as $secId) {
                $db->query(
                    "INSERT INTO `registration_request_items` (`request_id`, `section_id`, `action_type`, `status`) VALUES (:rid, :sid, 'add', 'pending')",
                    [':rid' => $requestId, ':sid' => $secId]
                );
            }

            $db->commit();

            // Notify Student
            $student = (new \App\Models\Student())->getFullDetails($studentId);
            if ($student && !empty($student['user_id'])) {
                $this->notifService->notify(
                    (int)$student['user_id'],
                    'Registration Request Submitted',
                    "Your registration request #{$requestId} ({$validation['total_credits']} credits) has been submitted for Academic Advisor review.",
                    'registration',
                    "/student/registration/{$requestId}"
                );
            }

            // Notify Assigned Advisor if mapped
            if ($student && !empty($student['advisor_teacher_id'])) {
                $advTeacher = (new \App\Models\Teacher())->find((int)$student['advisor_teacher_id']);
                if ($advTeacher && !empty($advTeacher['user_id'])) {
                    $this->notifService->notify(
                        (int)$advTeacher['user_id'],
                        'New Registration Request Awaiting Review',
                        "Advisee {$student['first_name']} {$student['last_name']} ({$student['student_id_card']}) submitted registration request #{$requestId} ({$validation['total_credits']} credits).",
                        'registration',
                        "/advisor/registration-requests/{$requestId}"
                    );
                }
            }

            return $requestId;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Advisor approves request and enrolls student into confirmed sections with transactional seat allocation.
     */
    public function approveRequest(int $requestId, int $advisorTeacherId, string $remarks = ''): bool
    {
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $req = $this->requestModel->find($requestId);
            if (!$req) {
                throw new AppException("Registration request #{$requestId} not found.");
            }

            if ($req['status'] !== 'submitted') {
                throw new AppException("Request cannot be approved: current status is " . ucwords(str_replace('_', ' ', $req['status'])) . ".");
            }

            $studentId = (int)$req['student_id'];
            $semesterId = (int)$req['semester_id'];

            // Verify advisor authorization
            if (!$this->requestModel->isStudentAssignedToAdvisor($studentId, $advisorTeacherId)) {
                throw new AppException("Unauthorized: You are not the assigned Academic Advisor for this student.");
            }

            $items = $this->requestModel->getItems($requestId);
            if (empty($items)) {
                throw new AppException("Registration request has no course items.");
            }

            foreach ($items as $item) {
                $secId = (int)$item['section_id'];

                // Atomic row lock & seat capacity verification
                $lockedSec = $this->sectionModel->lockForUpdate($secId);
                if (!$lockedSec) {
                    throw new AppException("Section #{$secId} no longer exists.");
                }

                if ((int)$lockedSec['enrolled_count'] >= (int)$lockedSec['capacity']) {
                    throw new AppException("Failed to approve: Section {$item['course_code']} (Section {$item['section_name']}) is full (Capacity: {$lockedSec['capacity']}).");
                }

                // Increment seat count atomically
                $seatAllocated = $this->sectionModel->incrementEnrolled($secId);
                if (!$seatAllocated) {
                    throw new AppException("Failed to allocate seat in {$item['course_code']} (Section {$item['section_name']}).");
                }

                // Create confirmed registration record
                $this->regModel->create([
                    'student_id' => $studentId,
                    'section_id' => $secId,
                    'semester_id' => $semesterId,
                    'registration_request_id' => $requestId,
                    'status' => 'enrolled'
                ]);

                // Update item status
                $db->query(
                    "UPDATE `registration_request_items` SET `status` = 'approved' WHERE `id` = :id",
                    [':id' => $item['id']]
                );
            }

            // Update request header
            $this->requestModel->update($requestId, [
                'status' => 'advisor_approved',
                'advisor_id' => $advisorTeacherId,
                'advisor_remarks' => $remarks ?: 'Approved by Academic Advisor.',
                'advisor_action_at' => date('Y-m-d H:i:s')
            ]);

            $db->commit();

            // Notify student
            $student = (new \App\Models\Student())->getFullDetails($studentId);
            if ($student && !empty($student['user_id'])) {
                $this->notifService->notify(
                    (int)$student['user_id'],
                    'Registration Request Approved!',
                    "Your Summer 2026 course registration (Request #{$requestId}) has been approved by your Academic Advisor. Your courses are now officially enrolled.",
                    'success',
                    "/student/registration/{$requestId}"
                );
            }

            return true;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Advisor rejects request with documented reason.
     */
    public function rejectRequest(int $requestId, int $advisorTeacherId, string $rejectionReason): bool
    {
        $reason = trim($rejectionReason);
        if (empty($reason)) {
            throw new AppException("A specific rejection reason is required to reject a registration request.");
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $req = $this->requestModel->find($requestId);
            if (!$req) {
                throw new AppException("Registration request #{$requestId} not found.");
            }

            if ($req['status'] !== 'submitted') {
                throw new AppException("Request cannot be rejected: current status is " . ucwords(str_replace('_', ' ', $req['status'])) . ".");
            }

            $studentId = (int)$req['student_id'];

            // Verify advisor authorization
            if (!$this->requestModel->isStudentAssignedToAdvisor($studentId, $advisorTeacherId)) {
                throw new AppException("Unauthorized: You are not the assigned Academic Advisor for this student.");
            }

            // Update request header
            $this->requestModel->update($requestId, [
                'status' => 'advisor_rejected',
                'advisor_id' => $advisorTeacherId,
                'advisor_remarks' => $reason,
                'advisor_action_at' => date('Y-m-d H:i:s')
            ]);

            // Update all items to rejected with reason
            $db->query(
                "UPDATE `registration_request_items` SET `status` = 'rejected', `rejection_reason` = :reason WHERE `request_id` = :rid",
                [':reason' => $reason, ':rid' => $requestId]
            );

            $db->commit();

            // Notify student
            $student = (new \App\Models\Student())->getFullDetails($studentId);
            if ($student && !empty($student['user_id'])) {
                $this->notifService->notify(
                    (int)$student['user_id'],
                    'Registration Request Rejected',
                    "Your registration request #{$requestId} was rejected by your Academic Advisor. Reason: {$reason}",
                    'danger',
                    "/student/registration/{$requestId}"
                );
            }

            return true;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Student cancels a pending registration request.
     */
    public function cancelRequest(int $requestId, int $studentId, string $reason = ''): bool
    {
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $req = $this->requestModel->find($requestId);
            if (!$req) {
                throw new AppException("Registration request #{$requestId} not found.");
            }

            if ((int)$req['student_id'] !== $studentId) {
                throw new AppException("Unauthorized: You do not own this registration request.");
            }

            if (!in_array($req['status'], ['submitted', 'draft'], true)) {
                throw new AppException("Cannot cancel request: The request is already " . ucwords(str_replace('_', ' ', $req['status'])) . ".");
            }

            $this->requestModel->update($requestId, [
                'status' => 'cancelled',
                'submission_notes' => trim(($req['submission_notes'] ?? '') . "\n[Cancelled by Student]: " . ($reason ?: 'Cancelled by student before advisor review.'))
            ]);

            $db->commit();

            // Notify student
            $student = (new \App\Models\Student())->getFullDetails($studentId);
            if ($student && !empty($student['user_id'])) {
                $this->notifService->notify(
                    (int)$student['user_id'],
                    'Registration Request Cancelled',
                    "Your registration request #{$requestId} has been cancelled successfully.",
                    'warning',
                    "/student/registration/{$requestId}"
                );
            }

            return true;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
