<?php
namespace Tests\Integration;

use App\Core\Database;
use App\Models\RegistrationRequest;
use App\Models\Registration;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\RegistrationService;

/**
 * Phase 4: Smart Course Registration & Advisor Approval Automated Tests
 * Tests all 12 core requirements specified in Phase 4 prompt.
 */
class Phase4RegistrationTest
{
    protected Database $db;
    protected RegistrationService $regService;
    protected RegistrationRequest $requestModel;
    protected Registration $registrationModel;
    protected Section $sectionModel;
    protected Semester $semesterModel;
    protected Student $studentModel;
    protected Teacher $teacherModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->regService = new RegistrationService();
        $this->requestModel = new RegistrationRequest();
        $this->registrationModel = new Registration();
        $this->sectionModel = new Section();
        $this->semesterModel = new Semester();
        $this->studentModel = new Student();
        $this->teacherModel = new Teacher();
    }

    /**
     * Clean up any temporary test registration records created for a student
     */
    protected function cleanupStudentRegistrations(int $studentId, int $semesterId): void
    {
        // Delete notifications created for tests
        $studentUser = $this->db->fetch("SELECT user_id FROM students WHERE id = :id", ['id' => $studentId]);
        if ($studentUser) {
            $this->db->execute("DELETE FROM notifications WHERE user_id = :uid AND title LIKE '%Registration%'", ['uid' => $studentUser['user_id']]);
        }

        // Get test request IDs
        $requests = $this->db->fetchAll("SELECT id FROM registration_requests WHERE student_id = :sid AND semester_id = :sem AND submission_notes LIKE 'TEST_%'", [
            'sid' => $studentId,
            'sem' => $semesterId
        ]);

        foreach ($requests as $r) {
            $reqId = (int)$r['id'];
            $this->db->execute("DELETE FROM registrations WHERE registration_request_id = :rid", ['rid' => $reqId]);
            $this->db->execute("DELETE FROM registration_request_items WHERE request_id = :rid", ['rid' => $reqId]);
            $this->db->execute("DELETE FROM registration_requests WHERE id = :id", ['id' => $reqId]);
        }

        // Reset section seat counts to standard seed counts
        $this->db->execute("UPDATE sections SET enrolled_count = 28 WHERE id = 1");
        $this->db->execute("UPDATE sections SET enrolled_count = 22 WHERE id = 2");
        $this->db->execute("UPDATE sections SET enrolled_count = 28 WHERE id = 3");
        $this->db->execute("UPDATE sections SET enrolled_count = 30 WHERE id = 4");
        $this->db->execute("UPDATE sections SET enrolled_count = 30 WHERE id = 5");
        $this->db->execute("UPDATE sections SET enrolled_count = 35 WHERE id = 6");
    }

    /**
     * 1. A valid student can submit an eligible registration request.
     */
    public function testValidStudentCanSubmitEligibleRequest(): bool
    {
        $studentId = 2; // Tamanna (completed CSE-1101, CSE-1102)
        $semesterId = 2; // Summer 2026 (active)
        $this->cleanupStudentRegistrations($studentId, $semesterId);

        // Sections: Sec 1 (CSE-2101, 3 cr), Sec 3 (CSE-2102, 1.5 cr), Sec 5 (CSE-3102, 1.5 cr), Sec 6 (MATH-1101, 3 cr) = 9.0 cr
        $sectionIds = [1, 3, 5, 6];

        try {
            $requestId = $this->regService->submitRequest($studentId, $semesterId, $sectionIds, 'TEST_Valid registration submission');
        } catch (\Throwable $e) {
            $this->cleanupStudentRegistrations($studentId, $semesterId);
            return false;
        }

        $request = $this->requestModel->find($requestId);
        $items = $this->requestModel->getItems($requestId);

        $success = ($request &&
                    $request['status'] === 'submitted' &&
                    (float)$request['total_credits'] === 9.0 &&
                    count($items) === 4);

        $this->cleanupStudentRegistrations($studentId, $semesterId);
        return $success;
    }

    /**
     * 2. A student without a required prerequisite is rejected.
     */
    public function testStudentWithoutPrerequisiteRejected(): bool
    {
        $studentId = 2; // Tamanna (has NOT completed CSE-2101 Data Structures)
        $semesterId = 2;

        // Section 4 is CSE-3101 DBMS, which requires CSE-2101 Data Structures
        $validation = $this->regService->validateCart($studentId, $semesterId, [4]);

        $failedPrereq = false;
        foreach ($validation['errors'] as $err) {
            if (str_contains($err, 'prerequisite') || str_contains($err, 'Prerequisite') || str_contains($err, 'CSE-2101')) {
                $failedPrereq = true;
                break;
            }
        }

        // Also test submission rejection via exception
        $submitBlocked = false;
        try {
            $this->regService->submitRequest($studentId, $semesterId, [4], 'TEST_Prereq fail');
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'prerequisite') || str_contains($e->getMessage(), 'CSE-2101')) {
                $submitBlocked = true;
            }
        }

        return ($validation['valid'] === false && $failedPrereq && $submitBlocked);
    }

    /**
     * 3. A request exceeding the configured credit limit is rejected.
     */
    public function testCreditLimitValidationExceededRejected(): bool
    {
        $studentId = 2;
        $semesterId = 2;

        // Regular min is 9.0 credits. Let's test below minimum (Sec 3 only = 1.5 credits)
        $minCheck = $this->regService->validateCart($studentId, $semesterId, [3]);
        $minFailed = false;
        foreach ($minCheck['errors'] as $err) {
            if (str_contains($err, 'credits') || str_contains($err, 'Credit')) {
                $minFailed = true;
                break;
            }
        }

        // Test with mock high credit requirement
        $creditService = new \App\Services\CreditLimitService();
        $exceeded = $creditService->validate($studentId, 24.0);

        return ($minCheck['valid'] === false && $minFailed && $exceeded['valid'] === false);
    }

    /**
     * 4. A full section cannot accept additional registrations.
     */
    public function testFullSectionCapacityRejected(): bool
    {
        $studentId = 2;
        $semesterId = 2;

        // Temporarily set Section 6 (MATH-1101) enrolled_count equal to capacity (45/45)
        $original = $this->sectionModel->find(6);
        $this->db->execute("UPDATE sections SET enrolled_count = capacity WHERE id = 6");

        $validation = $this->regService->validateCart($studentId, $semesterId, [1, 3, 5, 6]);

        $capacityError = false;
        foreach ($validation['errors'] as $err) {
            if (str_contains($err, 'full') || str_contains($err, 'capacity') || str_contains($err, 'MATH-1101')) {
                $capacityError = true;
                break;
            }
        }

        // Restore original enrolled_count
        $this->db->execute("UPDATE sections SET enrolled_count = :enc WHERE id = 6", ['enc' => $original['enrolled_count']]);

        return ($validation['valid'] === false && $capacityError);
    }

    /**
     * 5. Duplicate course registration in cart is prevented.
     */
    public function testDuplicateCourseInCartPrevented(): bool
    {
        $studentId = 2;
        $semesterId = 2;

        // Section 1 is CSE-2101 Section A; Section 2 is CSE-2101 Section B
        // Selecting both sections of the SAME course offering should be rejected
        $validation = $this->regService->validateCart($studentId, $semesterId, [1, 2]);

        $duplicateDetected = false;
        foreach ($validation['errors'] as $err) {
            if (str_contains($err, 'Duplicate') || str_contains($err, 'duplicate') || str_contains($err, 'offering')) {
                $duplicateDetected = true;
                break;
            }
        }

        return ($validation['valid'] === false && $duplicateDetected);
    }

    /**
     * 6. Overlapping course schedules are detected.
     */
    public function testOverlappingCourseSchedulesDetected(): bool
    {
        $studentId = 2;
        $semesterId = 2;

        // Section 1 has Sunday 09:00:00 - 10:30:00.
        // Let's temporarily add a conflicting schedule row to Section 5 on Sunday 09:30:00 - 11:00:00
        $this->db->execute(
            "INSERT INTO class_schedules (section_id, room_id, teacher_id, semester_id, day_of_week, start_time, end_time, class_type)
             VALUES (5, 1, 2, 2, 'Sunday', '09:30:00', '11:00:00', 'lecture')"
        );
        $schedId = (int)$this->db->lastInsertId();

        $validation = $this->regService->validateCart($studentId, $semesterId, [1, 5]);

        $clashDetected = false;
        foreach ($validation['errors'] as $err) {
            if (str_contains($err, 'conflict') || str_contains($err, 'Schedule') || str_contains($err, 'overlap')) {
                $clashDetected = true;
                break;
            }
        }

        // Clean up temporary schedule row
        $this->db->execute("DELETE FROM class_schedules WHERE id = :id", ['id' => $schedId]);

        return ($validation['valid'] === false && $clashDetected);
    }

    /**
     * 7. An unauthorized student cannot access another student's request.
     */
    public function testUnauthorizedStudentCannotAccessAnotherStudentRequest(): bool
    {
        // Request 1 belongs to student 1 (Rahat)
        // Student 2 (Tamanna) tries to access Request 1
        $request = $this->requestModel->getRequestWithDetails(1);
        $thisRequestStudentId = (int)$request['student_id'];

        $tamannaStudentId = 2;
        // Verify ownership mismatch check
        $canAccess = ($thisRequestStudentId === $tamannaStudentId);

        // Also test cancel action authorization via exception
        $cancelBlocked = false;
        try {
            $this->regService->cancelRequest(1, $tamannaStudentId);
        } catch (\Throwable $e) {
            $cancelBlocked = true;
        }

        return (!$canAccess && $cancelBlocked);
    }

    /**
     * 8. An advisor cannot approve a request outside their assignment.
     */
    public function testAdvisorCannotApproveUnassignedStudentRequest(): bool
    {
        $studentId = 2;
        $semesterId = 2;
        $this->cleanupStudentRegistrations($studentId, $semesterId);

        // Submit a valid request for student 2
        try {
            $requestId = $this->regService->submitRequest($studentId, $semesterId, [1, 3, 5, 6], 'TEST_Unassigned advisor check');
        } catch (\Throwable $e) {
            return false;
        }

        // Teacher 1 (Tanvir Ahmed) is NOT the advisor of student 2 (Dr. Kazi Nahid is)
        $unassignedAdvisorTeacherId = 1;
        $isBlocked = false;
        try {
            $this->regService->approveRequest($requestId, $unassignedAdvisorTeacherId, 'Attempting unauthorized approval');
        } catch (\Throwable $e) {
            if (stripos($e->getMessage(), 'authorized') !== false || stripos($e->getMessage(), 'assigned') !== false) {
                $isBlocked = true;
            }
        }

        $this->cleanupStudentRegistrations($studentId, $semesterId);
        return $isBlocked;
    }

    /**
     * 9. An assigned advisor can approve a request and enrollment is confirmed.
     */
    public function testAdvisorCanApproveRequestAndEnrollsStudent(): bool
    {
        $studentId = 2;
        $semesterId = 2;
        $this->cleanupStudentRegistrations($studentId, $semesterId);

        // Check starting enrolled_count for section 1
        $sec1Before = (int)$this->sectionModel->find(1)['enrolled_count'];

        // Submit request
        try {
            $requestId = $this->regService->submitRequest($studentId, $semesterId, [1, 3, 5, 6], 'TEST_Approval flow');
        } catch (\Throwable $e) {
            return false;
        }

        // Teacher 2 (Dr. Kazi Nahid) is the authorized advisor for Student 2
        $assignedAdvisorTeacherId = 2;
        try {
            $approved = $this->regService->approveRequest($requestId, $assignedAdvisorTeacherId, 'All requirements verified.');
        } catch (\Throwable $e) {
            $this->cleanupStudentRegistrations($studentId, $semesterId);
            return false;
        }

        // Verify request updated
        $req = $this->requestModel->find($requestId);
        $isApproved = ($req['status'] === 'advisor_approved' && (int)$req['advisor_id'] === $assignedAdvisorTeacherId);

        // Verify registrations records created
        $enrolled = $this->registrationModel->getEnrolledCourses($studentId, $semesterId);
        $enrolledCount = count($enrolled);

        // Verify section enrolled_count incremented
        $sec1After = (int)$this->sectionModel->find(1)['enrolled_count'];
        $countIncremented = ($sec1After === $sec1Before + 1);

        $this->cleanupStudentRegistrations($studentId, $semesterId);
        // Restore seat count
        $this->db->execute("UPDATE sections SET enrolled_count = :c WHERE id = 1", ['c' => $sec1Before]);

        return ($approved && $isApproved && $enrolledCount >= 4 && $countIncremented);
    }

    /**
     * 10. An approved request cannot be approved twice (Idempotency check).
     */
    public function testIdempotentApprovalCannotApproveTwice(): bool
    {
        $studentId = 2;
        $semesterId = 2;
        $this->cleanupStudentRegistrations($studentId, $semesterId);

        $requestId = $this->regService->submitRequest($studentId, $semesterId, [1, 3, 5, 6], 'TEST_Double approval check');

        // First approval
        $firstOk = $this->regService->approveRequest($requestId, 2, 'Approved 1st time');

        // Second approval attempt should throw exception
        $secondBlocked = false;
        try {
            $this->regService->approveRequest($requestId, 2, 'Approved 2nd time');
        } catch (\Throwable $e) {
            $secondBlocked = true;
        }

        $this->cleanupStudentRegistrations($studentId, $semesterId);
        // Restore section 1 seat count
        $this->db->execute("UPDATE sections SET enrolled_count = 28 WHERE id = 1");

        return ($firstOk && $secondBlocked);
    }

    /**
     * 11. Advisor can reject a request with mandatory reason.
     */
    public function testAdvisorCanRejectWithReason(): bool
    {
        $studentId = 2;
        $semesterId = 2;
        $this->cleanupStudentRegistrations($studentId, $semesterId);

        $requestId = $this->regService->submitRequest($studentId, $semesterId, [1, 3, 5, 6], 'TEST_Rejection test');

        // Empty reason should fail
        $emptyReasonBlocked = false;
        try {
            $this->regService->rejectRequest($requestId, 2, '   ');
        } catch (\Throwable $e) {
            $emptyReasonBlocked = true;
        }

        // Valid reason should succeed
        $validRejection = false;
        try {
            $validRejection = $this->regService->rejectRequest($requestId, 2, 'Course load needs readjustment due to work schedule.');
        } catch (\Throwable $e) {
            $validRejection = false;
        }

        $req = $this->requestModel->find($requestId);
        $isRejected = ($req['status'] === 'advisor_rejected' && str_contains($req['advisor_remarks'], 'readjustment'));

        // Ensure NO records were created in registrations table
        $regs = $this->db->fetchAll("SELECT id FROM registrations WHERE registration_request_id = :rid", ['rid' => $requestId]);

        $this->cleanupStudentRegistrations($studentId, $semesterId);

        return ($emptyReasonBlocked && $validRejection && $isRejected && empty($regs));
    }

    /**
     * 12. Student can cancel a pending request, and transaction integrity is maintained.
     */
    public function testStudentCanCancelPendingRequest(): bool
    {
        $studentId = 2;
        $semesterId = 2;
        $this->cleanupStudentRegistrations($studentId, $semesterId);

        $requestId = $this->regService->submitRequest($studentId, $semesterId, [1, 3, 5, 6], 'TEST_Cancellation test');

        $cancelOk = $this->regService->cancelRequest($requestId, $studentId);
        $req = $this->requestModel->find($requestId);

        $cancelled = ($cancelOk && $req['status'] === 'cancelled');

        // Cancelled request cannot be approved by advisor
        $approveBlocked = false;
        try {
            $this->regService->approveRequest($requestId, 2, 'Trying to approve cancelled');
        } catch (\Throwable $e) {
            $approveBlocked = true;
        }

        $this->cleanupStudentRegistrations($studentId, $semesterId);

        return ($cancelled && $approveBlocked);
    }
}
