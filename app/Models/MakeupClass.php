<?php
namespace App\Models;

use App\Core\Model;

class MakeupClass extends Model
{
    protected string $table = 'makeup_classes';
    protected array $fillable = [
        'section_id', 'class_schedule_id', 'teacher_id', 'room_id',
        'makeup_date', 'start_time', 'end_time', 'reason', 'status', 'scheduled_by'
    ];

    /**
     * Get makeup classes for coordinator view with full joins
     */
    public function getAllWithDetails(int $semesterId, array $filters = []): array
    {
        $sql = "SELECT mc.*, s.section_name, s.capacity, s.enrolled_count,
                       c.course_code, c.title as course_title, c.credit,
                       r.room_number, r.building,
                       u_t.first_name as teacher_first_name, u_t.last_name as teacher_last_name,
                       u_s.first_name as creator_first_name, u_s.last_name as creator_last_name,
                       cs.day_of_week as original_day, cs.start_time as original_start_time, cs.end_time as original_end_time
                FROM `makeup_classes` mc
                JOIN `sections` s ON s.id = mc.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                JOIN `rooms` r ON r.id = mc.room_id
                JOIN `teachers` t ON t.id = mc.teacher_id
                JOIN `users` u_t ON u_t.id = t.user_id
                JOIN `users` u_s ON u_s.id = mc.scheduled_by
                LEFT JOIN `class_schedules` cs ON cs.id = mc.class_schedule_id
                WHERE co.semester_id = :sid";

        $params = [':sid' => $semesterId];

        if (!empty($filters['status'])) {
            $sql .= " AND mc.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['section_id'])) {
            $sql .= " AND mc.section_id = :secid";
            $params[':secid'] = (int)$filters['section_id'];
        }

        if (!empty($filters['teacher_id'])) {
            $sql .= " AND mc.teacher_id = :tid";
            $params[':tid'] = (int)$filters['teacher_id'];
        }

        if (!empty($filters['room_id'])) {
            $sql .= " AND mc.room_id = :rid";
            $params[':rid'] = (int)$filters['room_id'];
        }

        $sql .= " ORDER BY mc.makeup_date DESC, mc.start_time ASC";
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get upcoming makeup classes for an enrolled student
     */
    public function getForStudent(int $studentId, int $semesterId): array
    {
        $sql = "SELECT mc.*, s.section_name,
                       c.course_code, c.title as course_title,
                       r.room_number, r.building,
                       u_t.first_name as teacher_first_name, u_t.last_name as teacher_last_name
                FROM `registrations` reg
                JOIN `sections` s ON s.id = reg.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                JOIN `makeup_classes` mc ON mc.section_id = s.id
                JOIN `rooms` r ON r.id = mc.room_id
                JOIN `teachers` t ON t.id = mc.teacher_id
                JOIN `users` u_t ON u_t.id = t.user_id
                WHERE reg.student_id = :sid AND reg.semester_id = :semid AND reg.status = 'enrolled'
                  AND mc.status = 'scheduled'
                ORDER BY mc.makeup_date ASC, mc.start_time ASC";
        return $this->db->fetchAll($sql, [':sid' => $studentId, ':semid' => $semesterId]);
    }

    /**
     * Get upcoming makeup classes for a teacher
     */
    public function getForTeacher(int $teacherId, int $semesterId): array
    {
        $sql = "SELECT mc.*, s.section_name,
                       c.course_code, c.title as course_title,
                       r.room_number, r.building
                FROM `makeup_classes` mc
                JOIN `sections` s ON s.id = mc.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                JOIN `rooms` r ON r.id = mc.room_id
                WHERE mc.teacher_id = :tid AND co.semester_id = :semid
                  AND mc.status = 'scheduled'
                ORDER BY mc.makeup_date ASC, mc.start_time ASC";
        return $this->db->fetchAll($sql, [':tid' => $teacherId, ':semid' => $semesterId]);
    }

    /**
     * Check if a room is occupied by a scheduled makeup class on a specific date/time
     */
    public function findRoomConflict(int $roomId, string $date, string $startTime, string $endTime, ?int $ignoreId = null): ?array
    {
        $sql = "SELECT mc.*, r.room_number, c.course_code, s.section_name
                FROM `makeup_classes` mc
                JOIN `rooms` r ON r.id = mc.room_id
                JOIN `sections` s ON s.id = mc.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                WHERE mc.room_id = :rid AND mc.makeup_date = :mdate AND mc.status = 'scheduled'
                  AND (:st < mc.end_time AND :et > mc.start_time)";

        $params = [
            ':rid' => $roomId,
            ':mdate' => $date,
            ':st' => $startTime,
            ':et' => $endTime
        ];

        if ($ignoreId) {
            $sql .= " AND mc.id != :ign";
            $params[':ign'] = $ignoreId;
        }

        $sql .= " LIMIT 1";
        return $this->db->fetch($sql, $params);
    }

    /**
     * Check if a teacher is scheduled for another makeup class on a specific date/time
     */
    public function findTeacherConflict(int $teacherId, string $date, string $startTime, string $endTime, ?int $ignoreId = null): ?array
    {
        $sql = "SELECT mc.*, c.course_code, s.section_name
                FROM `makeup_classes` mc
                JOIN `sections` s ON s.id = mc.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                WHERE mc.teacher_id = :tid AND mc.makeup_date = :mdate AND mc.status = 'scheduled'
                  AND (:st < mc.end_time AND :et > mc.start_time)";

        $params = [
            ':tid' => $teacherId,
            ':mdate' => $date,
            ':st' => $startTime,
            ':et' => $endTime
        ];

        if ($ignoreId) {
            $sql .= " AND mc.id != :ign";
            $params[':ign'] = $ignoreId;
        }

        $sql .= " LIMIT 1";
        return $this->db->fetch($sql, $params);
    }
}
