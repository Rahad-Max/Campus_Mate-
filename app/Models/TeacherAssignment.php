<?php
namespace App\Models;

use App\Core\Model;

class TeacherAssignment extends Model
{
    protected string $table = 'teacher_assignments';
    protected array $fillable = [
        'section_id', 'teacher_id', 'role', 'assigned_credits', 'assigned_by'
    ];

    public function findBySectionAndTeacher(int $sectionId, int $teacherId): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `section_id` = :sid AND `teacher_id` = :tid LIMIT 1";
        return $this->db->fetch($sql, [':sid' => $sectionId, ':tid' => $teacherId]);
    }

    public function isTeacherAssignedToSection(int $teacherId, int $sectionId): bool
    {
        return $this->findBySectionAndTeacher($sectionId, $teacherId) !== null;
    }

    public function getAssignmentsBySection(int $sectionId): array
    {
        $sql = "SELECT ta.*, t.teacher_id_card, t.designation, u.first_name, u.last_name, u.email
                FROM `teacher_assignments` ta
                JOIN `teachers` t ON t.id = ta.teacher_id
                JOIN `users` u ON u.id = t.user_id
                WHERE ta.section_id = :sid
                ORDER BY ta.role ASC";
        return $this->db->fetchAll($sql, [':sid' => $sectionId]);
    }

    public function getLeadTeacherForSection(int $sectionId): ?array
    {
        $sql = "SELECT ta.*, t.teacher_id_card, t.designation, u.first_name, u.last_name, u.email
                FROM `teacher_assignments` ta
                JOIN `teachers` t ON t.id = ta.teacher_id
                JOIN `users` u ON u.id = t.user_id
                WHERE ta.section_id = :sid AND ta.role = 'lead_instructor'
                LIMIT 1";
        return $this->db->fetch($sql, [':sid' => $sectionId]);
    }

    public function getAssignmentsWithDetails(?int $semesterId = null, ?int $departmentId = null): array
    {
        $sql = "SELECT ta.*, 
                       t.teacher_id_card, t.designation, t.max_credit_load,
                       u.first_name, u.last_name,
                       s.section_name, s.capacity, s.enrolled_count,
                       c.id as course_id, c.course_code, c.title as course_title, c.credit as course_credit,
                       sem.id as semester_id, sem.name as semester_name, sem.code as semester_code,
                       d.id as department_id, d.code as department_code, d.name as department_name
                FROM `teacher_assignments` ta
                JOIN `teachers` t ON t.id = ta.teacher_id
                JOIN `users` u ON u.id = t.user_id
                JOIN `sections` s ON s.id = ta.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                JOIN `semesters` sem ON sem.id = co.semester_id
                JOIN `departments` d ON d.id = co.department_id
                WHERE 1=1";

        $params = [];
        if ($semesterId) {
            $sql .= " AND co.semester_id = :sid";
            $params[':sid'] = $semesterId;
        }
        if ($departmentId) {
            $sql .= " AND co.department_id = :did";
            $params[':did'] = $departmentId;
        }

        $sql .= " ORDER BY sem.id DESC, c.course_code ASC, s.section_name ASC";
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Calculate teacher's total assigned credits in a given semester
     */
    public function getTeacherCurrentLoad(int $teacherId, int $semesterId): float
    {
        $sql = "SELECT COALESCE(SUM(ta.assigned_credits), 0) as total_load
                FROM `teacher_assignments` ta
                JOIN `sections` s ON s.id = ta.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                WHERE ta.teacher_id = :tid AND co.semester_id = :sid";
        $row = $this->db->fetch($sql, [':tid' => $teacherId, ':sid' => $semesterId]);
        return (float)($row['total_load'] ?? 0.0);
    }

    /**
     * Check if assigning additional credits exceeds teacher's max allowed load
     */
    public function isTeacherOverloaded(int $teacherId, int $semesterId, float $additionalCredits = 0.0): bool
    {
        $teacher = (new Teacher())->find($teacherId);
        if (!$teacher) {
            return false;
        }

        $maxLoad = (float)$teacher['max_credit_load'];
        $currentLoad = $this->getTeacherCurrentLoad($teacherId, $semesterId);

        return ($currentLoad + $additionalCredits) > $maxLoad;
    }
}
