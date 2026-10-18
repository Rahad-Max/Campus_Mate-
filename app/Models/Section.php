<?php
namespace App\Models;

use App\Core\Model;

class Section extends Model
{
    protected string $table = 'sections';
    protected array $fillable = ['course_offering_id', 'section_name', 'capacity', 'enrolled_count', 'status'];

    public function isSectionNameUnique(int $offeringId, string $sectionName, ?int $excludeId = null): bool
    {
        $name = strtoupper(trim($sectionName));
        $sql = "SELECT id FROM `{$this->table}` WHERE `course_offering_id` = :oid AND `section_name` = :sname";
        $params = [':oid' => $offeringId, ':sname' => $name];

        if ($excludeId !== null) {
            $sql .= " AND `id` != :id";
            $params[':id'] = $excludeId;
        }

        $row = $this->db->fetch($sql, $params);
        return empty($row);
    }

    public function getDetailedInfo(int $sectionId): ?array
    {
        $sql = "SELECT s.*, 
                       GREATEST(0, (s.capacity - s.enrolled_count)) as available_seats,
                       co.semester_id, co.status as offering_status, co.department_id,
                       c.id as course_id, c.course_code, c.title as course_title, c.credit, c.course_type,
                       sem.name as semester_name, sem.code as semester_code
                FROM `sections` s
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                JOIN `semesters` sem ON sem.id = co.semester_id
                WHERE s.id = :id LIMIT 1";
        return $this->db->fetch($sql, [':id' => $sectionId]);
    }

    public function getAllWithDetails(?int $semesterId = null, ?int $departmentId = null): array
    {
        $sql = "SELECT s.*, 
                       GREATEST(0, (s.capacity - s.enrolled_count)) as available_seats,
                       co.id as offering_id, co.status as offering_status,
                       c.id as course_id, c.course_code, c.title as course_title, c.credit as course_credit,
                       sem.id as semester_id, sem.name as semester_name, sem.code as semester_code,
                       d.id as department_id, d.code as department_code,
                       (SELECT COUNT(*) FROM `teacher_assignments` ta WHERE ta.section_id = s.id) as teacher_count
                FROM `sections` s
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

    public function hasAvailableSeats(int $sectionId): bool
    {
        $sql = "SELECT capacity, enrolled_count FROM `sections` WHERE id = :id";
        $row = $this->db->fetch($sql, [':id' => $sectionId]);
        if (!$row) {
            return false;
        }
        return (int)$row['enrolled_count'] < (int)$row['capacity'];
    }

    public function lockForUpdate(int $sectionId): ?array
    {
        $sql = "SELECT id, course_offering_id, section_name, capacity, enrolled_count, status 
                FROM `sections` WHERE id = :id FOR UPDATE";
        return $this->db->fetch($sql, [':id' => $sectionId]);
    }

    public function incrementEnrolled(int $sectionId): bool
    {
        $sql = "UPDATE `sections` SET `enrolled_count` = `enrolled_count` + 1 
                WHERE `id` = :id AND `enrolled_count` < `capacity`";
        return $this->db->execute($sql, [':id' => $sectionId]) > 0;
    }

    public function decrementEnrolled(int $sectionId): bool
    {
        $sql = "UPDATE `sections` SET `enrolled_count` = GREATEST(0, `enrolled_count` - 1) WHERE `id` = :id";
        return $this->db->execute($sql, [':id' => $sectionId]) > 0;
    }

    public function getSchedules(int $sectionId): array
    {
        $sql = "SELECT cs.*, r.room_number, r.building,
                       u.first_name as teacher_first_name, u.last_name as teacher_last_name, t.designation
                FROM `class_schedules` cs
                JOIN `rooms` r ON r.id = cs.room_id
                JOIN `teachers` t ON t.id = cs.teacher_id
                JOIN `users` u ON u.id = t.user_id
                WHERE cs.section_id = :sid
                ORDER BY FIELD(cs.day_of_week, 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), cs.start_time ASC";
        return $this->db->fetchAll($sql, [':sid' => $sectionId]);
    }
}
