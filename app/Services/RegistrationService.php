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
    }

    /**
     * Comprehensive validation of a student's course registration cart before submission.
     */
    public function validateCart(int $studentId, int $semesterId, array $sectionIds): array
    {
        $errors = [];
        $totalCredits = 0.0;
        $courseIdsSeen = [];

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
            $errors[] = "Timetable conflict detected between selected course schedules.";
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
            'total_credits' => $totalCredits
        ];
    }

    /**
     * Submit a registration request for advisor review.
     */
    public function submitRequest(int $studentId, int $semesterId, array $sectionIds, string $notes = ''): int
    {
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
            if (!$req || $req['status'] !== 'submitted') {
                throw new AppException("Request not in submittable status for approval.");
            }

            $items = $this->requestModel->getItems($requestId);
            $studentId = (int)$req['student_id'];
            $semesterId = (int)$req['semester_id'];

            foreach ($items as $item) {
                $secId = (int)$item['section_id'];

                // Atomic seat reservation
                $seatAllocated = $this->sectionModel->incrementEnrolled($secId);
                if (!$seatAllocated) {
                    throw new AppException("Failed to approve: Section {$item['course_code']} has reached maximum capacity.");
                }

                // Create confirmed registration
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
                'advisor_remarks' => $remarks,
                'advisor_action_at' => date('Y-m-d H:i:s')
            ]);

            $db->commit();
            return true;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
