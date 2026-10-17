<?php
namespace App\Models;

use App\Core\Model;

class CourseOffering extends Model
{
    protected string $table = 'course_offerings';
    protected array $fillable = ['semester_id', 'course_id', 'department_id', 'coordinator_id', 'status'];

    public function getOfferingsBySemester(int $semesterId, ?int $departmentId = null): array
    {
        $sql = "SELECT co.*, c.course_code, c.title as course_title, c.credit, c.course_type,
                       d.code as department_code, d.name as department_name,
                       (SELECT COUNT(*) FROM `sections` s WHERE s.course_offering_id = co.id) as section_count
                FROM `course_offerings` co
                JOIN `courses` c ON c.id = co.course_id
                JOIN `departments` d ON d.id = co.department_id
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
                       (s.capacity - s.enrolled_count) as available_seats
                FROM `sections` s
                WHERE s.course_offering_id = :oid
                ORDER BY s.section_name ASC";
        return $this->db->fetchAll($sql, [':oid' => $offeringId]);
    }
}
