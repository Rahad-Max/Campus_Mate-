<?php
namespace App\Models;

use App\Core\Model;

class ClassSchedule extends Model
{
    protected string $table = 'class_schedules';
    protected array $fillable = [
        'section_id', 'room_id', 'teacher_id', 'semester_id',
        'day_of_week', 'start_time', 'end_time', 'class_type'
    ];

    public function getStudentTimetable(int $studentId, int $semesterId): array
    {
        $sql = "SELECT cs.*, r.room_number, r.building,
                       s.section_name, c.course_code, c.title as course_title, c.credit,
                       u.first_name as teacher_first_name, u.last_name as teacher_last_name
                FROM `registrations` reg
                JOIN `sections` s ON s.id = reg.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                JOIN `class_schedules` cs ON cs.section_id = s.id
                JOIN `rooms` r ON r.id = cs.room_id
                JOIN `teachers` t ON t.id = cs.teacher_id
                JOIN `users` u ON u.id = t.user_id
                WHERE reg.student_id = :sid AND reg.semester_id = :semid AND reg.status = 'enrolled'
                ORDER BY FIELD(cs.day_of_week, 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), cs.start_time ASC";
        return $this->db->fetchAll($sql, [':sid' => $studentId, ':semid' => $semesterId]);
    }

    public function getTeacherTimetable(int $teacherId, int $semesterId): array
    {
        $sql = "SELECT cs.*, r.room_number, r.building,
                       s.section_name, c.course_code, c.title as course_title, c.credit
                FROM `class_schedules` cs
                JOIN `sections` s ON s.id = cs.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                JOIN `rooms` r ON r.id = cs.room_id
                WHERE cs.teacher_id = :tid AND cs.semester_id = :semid
                ORDER BY FIELD(cs.day_of_week, 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), cs.start_time ASC";
        return $this->db->fetchAll($sql, [':tid' => $teacherId, ':semid' => $semesterId]);
    }

    public function findRoomConflict(int $roomId, int $semesterId, string $day, string $startTime, string $endTime, ?int $ignoreScheduleId = null): ?array
    {
        $sql = "SELECT cs.*, r.room_number, c.course_code, s.section_name
                FROM `class_schedules` cs
                JOIN `rooms` r ON r.id = cs.room_id
                JOIN `sections` s ON s.id = cs.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                WHERE cs.room_id = :rid AND cs.semester_id = :semid AND cs.day_of_week = :day
                  AND (:st < cs.end_time AND :et > cs.start_time)";

        $params = [
            ':rid' => $roomId,
            ':semid' => $semesterId,
            ':day' => $day,
            ':st' => $startTime,
            ':et' => $endTime
        ];

        if ($ignoreScheduleId) {
            $sql .= " AND cs.id != :ign";
            $params[':ign'] = $ignoreScheduleId;
        }

        $sql .= " LIMIT 1";
        return $this->db->fetch($sql, $params);
    }

    public function findTeacherConflict(int $teacherId, int $semesterId, string $day, string $startTime, string $endTime, ?int $ignoreScheduleId = null): ?array
    {
        $sql = "SELECT cs.*, c.course_code, s.section_name
                FROM `class_schedules` cs
                JOIN `sections` s ON s.id = cs.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                WHERE cs.teacher_id = :tid AND cs.semester_id = :semid AND cs.day_of_week = :day
                  AND (:st < cs.end_time AND :et > cs.start_time)";

        $params = [
            ':tid' => $teacherId,
            ':semid' => $semesterId,
            ':day' => $day,
            ':st' => $startTime,
            ':et' => $endTime
        ];

        if ($ignoreScheduleId) {
            $sql .= " AND cs.id != :ign";
            $params[':ign'] = $ignoreScheduleId;
        }

        $sql .= " LIMIT 1";
        return $this->db->fetch($sql, $params);
    }
}
