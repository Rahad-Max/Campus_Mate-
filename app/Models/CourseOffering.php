<?php
namespace App\Models;

use App\Core\Model;

class CourseOffering extends Model
{
    protected string $table = 'course_offerings';
    protected array $fillable = ['semester_id', 'course_id', 'department_id', 'coordinator_id', 'status'];

    public function isOffered(int $semesterId, int $courseId): bool
    {
        $sql = "SELECT id FROM `{$this->table}` WHERE `semester_id` = :sid AND `course_id` = :cid LIMIT 1";
        $row = $this->db->fetch($sql, [':sid' => $semesterId, ':cid' => $courseId]);
        return !empty($row);
    }

    public function getOfferingsBySemester(int $semesterId, ?int $departmentId = null): array
    {
        $sql = "SELECT co.*, c.course_code, c.title as course_title, c.credit, c.course_type,
                       d.code as department_code, d.name as department_name,
                       sem.name as semester_name, sem.code as semester_code,
                       (SELECT COUNT(*) FROM `sections` s WHERE s.course_offering_id = co.id) as section_count,
                       (SELECT COALESCE(SUM(s.enrolled_count), 0) FROM `sections` s WHERE s.course_offering_id = co.id) as total_enrolled,
                       (SELECT COALESCE(SUM(s.capacity), 0) FROM `sections` s WHERE s.course_offering_id = co.id) as total_capacity
                FROM `course_offerings` co
                JOIN `courses` c ON c.id = co.course_id
                JOIN `departments` d ON d.id = co.department_id
                JOIN `semesters` sem ON sem.id = co.semester_id
                WHERE co.semester_id = :sid";

        $params = [':sid' => $semesterId];
        if ($departmentId) {
            $sql .= " AND co.department_id = :did";
            $params[':did'] = $departmentId;
        }

        $sql .= " ORDER BY c.course_code ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function getSections(int $offeringId): array
    {
        $sql = "SELECT s.*, 
                       (s.capacity - s.enrolled_count) as available_seats,
                       (SELECT COUNT(*) FROM `teacher_assignments` ta WHERE ta.section_id = s.id) as instructor_count
                FROM `sections` s
                WHERE s.course_offering_id = :oid
                ORDER BY s.section_name ASC";
        return $this->db->fetchAll($sql, [':oid' => $offeringId]);
    }

    public function hasEnrolledStudents(int $offeringId): bool
    {
        $sql = "SELECT COALESCE(SUM(enrolled_count), 0) as total FROM `sections` WHERE `course_offering_id` = :oid";
        $row = $this->db->fetch($sql, [':oid' => $offeringId]);
        return (int)($row['total'] ?? 0) > 0;
    }
}
