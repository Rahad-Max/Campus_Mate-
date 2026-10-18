<?php
namespace App\Models;

use App\Core\Model;

class ScheduleRevision extends Model
{
    protected string $table = 'schedule_revisions';
    protected array $fillable = [
        'class_schedule_id', 'previous_day', 'previous_start_time', 'previous_end_time', 'previous_room_id',
        'new_day', 'new_start_time', 'new_end_time', 'new_room_id', 'revision_reason', 'revised_by', 'effective_date'
    ];

    /**
     * Get revision audit history for a semester with room and user details
     */
    public function getRevisionHistory(int $semesterId): array
    {
        $sql = "SELECT sr.*,
                       cs.section_id, cs.semester_id,
                       s.section_name,
                       c.course_code, c.title as course_title,
                       r_prev.room_number as prev_room_number, r_prev.building as prev_building,
                       r_new.room_number as new_room_number, r_new.building as new_building,
                       u.first_name as revised_by_first_name, u.last_name as revised_by_last_name
                FROM `schedule_revisions` sr
                JOIN `class_schedules` cs ON cs.id = sr.class_schedule_id
                JOIN `sections` s ON s.id = cs.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                JOIN `rooms` r_prev ON r_prev.id = sr.previous_room_id
                JOIN `rooms` r_new ON r_new.id = sr.new_room_id
                JOIN `users` u ON u.id = sr.revised_by
                WHERE cs.semester_id = :sid
                ORDER BY sr.created_at DESC";
        return $this->db->fetchAll($sql, [':sid' => $semesterId]);
    }

    /**
     * Get revision history for a specific schedule slot
     */
    public function getForSchedule(int $scheduleId): array
    {
        $sql = "SELECT sr.*,
                       r_prev.room_number as prev_room_number,
                       r_new.room_number as new_room_number,
                       u.first_name as revised_by_first_name, u.last_name as revised_by_last_name
                FROM `schedule_revisions` sr
                JOIN `rooms` r_prev ON r_prev.id = sr.previous_room_id
                JOIN `rooms` r_new ON r_new.id = sr.new_room_id
                JOIN `users` u ON u.id = sr.revised_by
                WHERE sr.class_schedule_id = :csid
                ORDER BY sr.created_at DESC";
        return $this->db->fetchAll($sql, [':csid' => $scheduleId]);
    }
}
