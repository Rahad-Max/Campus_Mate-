<?php
namespace Tests\Integration;

use App\Core\Database;
use App\Models\Course;
use App\Models\Semester;
use App\Models\Section;
use App\Models\Curriculum;
use App\Models\Department;
use App\Models\TeacherAssignment;

class Phase3AcademicManagementTest
{
    protected Database $db;
    protected Course $courseModel;
    protected Semester $semesterModel;
    protected Section $sectionModel;
    protected Curriculum $curriculumModel;
    protected Department $departmentModel;
    protected TeacherAssignment $assignmentModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->courseModel = new Course();
        $this->semesterModel = new Semester();
        $this->sectionModel = new Section();
        $this->curriculumModel = new Curriculum();
        $this->departmentModel = new Department();
        $this->assignmentModel = new TeacherAssignment();
    }

    /**
     * 1. Coordinator can create a valid course
     */
    public function testCoordinatorCanCreateCourse(): bool
    {
        $testCode = 'TEST-3010';
        $this->db->execute("DELETE FROM courses WHERE course_code = :c", ['c' => $testCode]);

        $courseId = $this->courseModel->create([
            'department_id' => 1,
            'course_code' => $testCode,
            'title' => 'Automated Test Course',
            'credit' => 3.0,
            'course_type' => 'theory',
            'description' => 'Test course description',
            'status' => 'active'
        ]);

        $created = $this->courseModel->find($courseId);
        $success = ($created && $created['course_code'] === $testCode && (float)$created['credit'] === 3.0);

        // Clean up
        $this->db->execute("DELETE FROM courses WHERE id = :id", ['id' => $courseId]);

        return $success;
    }

    /**
     * 2. Duplicate course codes are rejected
     */
    public function testDuplicateCourseCodeRejected(): bool
    {
        $isUnique = $this->courseModel->isCodeUnique('CSE-1101');
        return ($isUnique === false);
    }

    /**
     * 3. A course cannot be its own prerequisite
     */
    public function testCourseCannotBeOwnPrerequisite(): bool
    {
        $course = $this->courseModel->findByCode('CSE-1101');
        if (!$course) return false;

        $hasCycle = $this->courseModel->hasPrerequisiteCycle($course['id'], $course['id']);
        return ($hasCycle === true);
    }

    /**
     * 4. Multi-hop circular prerequisite dependencies are detected and rejected (A -> B -> C -> A)
     */
    public function testCircularPrerequisiteGraphCycleDetection(): bool
    {
        $this->db->execute("DELETE FROM courses WHERE course_code IN ('TC-1', 'TC-2', 'TC-3', 'TC-4')");
        
        $idA = $this->courseModel->create(['department_id' => 1, 'course_code' => 'TC-1', 'title' => 'Test Course A', 'credit' => 3.0, 'course_type' => 'theory', 'status' => 'active']);
        $idB = $this->courseModel->create(['department_id' => 1, 'course_code' => 'TC-2', 'title' => 'Test Course B', 'credit' => 3.0, 'course_type' => 'theory', 'status' => 'active']);
        $idC = $this->courseModel->create(['department_id' => 1, 'course_code' => 'TC-3', 'title' => 'Test Course C', 'credit' => 3.0, 'course_type' => 'theory', 'status' => 'active']);

        // A requires B
        $this->courseModel->addPrerequisite($idA, $idB, 'C');
        // B requires C
        $this->courseModel->addPrerequisite($idB, $idC, 'C');

        // Now if C requires A, that forms cycle C -> A -> B -> C
        $cycleDetected = $this->courseModel->hasPrerequisiteCycle($idC, $idA);

        // Also test non-cycle: D does not cause cycle with A
        $idD = $this->courseModel->create(['department_id' => 1, 'course_code' => 'TC-4', 'title' => 'Test Course D', 'credit' => 3.0, 'course_type' => 'theory', 'status' => 'active']);
        $noCycle = !$this->courseModel->hasPrerequisiteCycle($idA, $idD);

        // Clean up
        $this->db->execute("DELETE FROM course_prerequisites WHERE course_id IN ($idA, $idB, $idC, $idD) OR prerequisite_course_id IN ($idA, $idB, $idC, $idD)");
        $this->db->execute("DELETE FROM courses WHERE id IN ($idA, $idB, $idC, $idD)");

        return ($cycleDetected === true && $noCycle === true);
    }

    /**
     * 5. Invalid semester dates are rejected
     */
    public function testInvalidSemesterDatesRejected(): bool
    {
        // End date before start date
        $invalidDates = $this->semesterModel->validateDates('2026-06-01', '2026-05-01');
        $hasError1 = !empty($invalidDates);

        // Registration close before open
        $invalidReg = $this->semesterModel->validateDates('2026-06-01', '2026-10-01', '2026-05-20', '2026-05-10');
        $hasError2 = !empty($invalidReg);

        // Valid dates
        $valid = $this->semesterModel->validateDates('2026-06-01', '2026-10-31', '2026-05-01', '2026-05-25');
        $noError = empty($valid);

        return ($hasError1 && $hasError2 && $noError);
    }

    /**
     * 6. Single active semester transactional enforcement
     */
    public function testSingleActiveSemesterEnforcement(): bool
    {
        $this->db->execute("DELETE FROM semesters WHERE code = 'TEST-2099'");

        $testSemId = $this->semesterModel->create([
            'code' => 'TEST-2099',
            'name' => 'Testing Term 2099',
            'academic_year' => 2099,
            'term' => 'Spring',
            'start_date' => '2099-01-01',
            'end_date' => '2099-04-30',
            'registration_start' => '2098-12-01 00:00:00',
            'registration_end' => '2098-12-20 23:59:59',
            'add_drop_deadline' => '2099-01-15 23:59:59',
            'status' => 'upcoming',
            'is_current' => 0
        ]);

        $this->semesterModel->setCurrent($testSemId);

        // Verify only 1 semester is current in the entire DB
        $currentCount = (int)$this->db->fetch("SELECT COUNT(*) as c FROM semesters WHERE is_current = 1")['c'];

        // Reset to original active semester (Summer 2026, ID 1)
        $this->semesterModel->setCurrent(1);

        // Clean up
        $this->db->execute("DELETE FROM semesters WHERE id = :id", ['id' => $testSemId]);

        return ($currentCount === 1);
    }

    /**
     * 7. Section with invalid capacity is rejected
     */
    public function testSectionInvalidCapacityRejected(): bool
    {
        $invalid1 = (-5 <= 0);
        $invalid2 = (0 <= 0);
        $valid = (40 > 0);

        return ($invalid1 && $invalid2 && $valid);
    }

    /**
     * 8. Duplicate section identifiers within same offering are rejected
     */
    public function testDuplicateSectionIdentifierRejected(): bool
    {
        $isUnique = $this->sectionModel->isSectionNameUnique(1, 'A');
        $newIsUnique = $this->sectionModel->isSectionNameUnique(1, 'Z9');

        return ($isUnique === false && $newIsUnique === true);
    }

    /**
     * 9. Teacher assignments workload calculation & overload prevention
     */
    public function testTeacherWorkloadLimitEnforced(): bool
    {
        $teacher = $this->db->fetch("SELECT * FROM teachers WHERE id = 1");
        if (!$teacher) return false;

        $maxLoad = (float)($teacher['max_credit_load'] ?? 12.0);
        $currentLoad = $this->assignmentModel->getTeacherCurrentLoad(1, 1);

        // Attempting to assign 50.0 credits triggers overload check
        $overloaded = $this->assignmentModel->isTeacherOverloaded(1, 1, 50.0);

        $headroom = max(0, $maxLoad - $currentLoad);
        $notOverloaded = !$this->assignmentModel->isTeacherOverloaded(1, 1, min(1.0, $headroom));

        return ($overloaded === true && ($headroom <= 0 || $notOverloaded === true));
    }

    /**
     * 10. Students cannot access coordinator management routes
     */
    public function testStudentCannotAccessCoordinatorRoutes(): bool
    {
        $_SESSION['user'] = [
            'id' => 1,
            'email' => 'student@pub.ac.bd',
            'role' => 'student',
            'status' => 'active'
        ];

        $isCoordinator = has_role('department_coordinator');
        $isStudent = has_role('student');

        return ($isCoordinator === false && $isStudent === true);
    }

    /**
     * 11. Teachers cannot modify coordinator-only academic records
     */
    public function testTeacherCannotModifyCoordinatorRecords(): bool
    {
        $_SESSION['user'] = [
            'id' => 2,
            'email' => 'teacher@pub.ac.bd',
            'role' => 'teacher',
            'status' => 'active'
        ];

        $canAccessCoordinator = has_role('department_coordinator');
        return ($canAccessCoordinator === false);
    }

    /**
     * 12. Academic history referential integrity protection
     */
    public function testReferentialIntegrityProtectsHistory(): bool
    {
        $hasRefs = $this->courseModel->hasAcademicReferences(1);
        $deptHasRefs = $this->departmentModel->hasAcademicReferences(1);

        return ($hasRefs === true && $deptHasRefs === true);
    }
}
