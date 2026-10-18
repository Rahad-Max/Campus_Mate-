<?php
namespace App\Models;

use App\Core\Model;

class ClassSchedule extends Model
{
    protected string $table = 'class_schedules';
    protected array $fillable = [
        'section_id', 'room_id', 'teacher_id', 'semester_id',
        'day_of_week', 'start_time', 'end_time', 'class_type', 'status'
    ];

    /**
     * Get published class timetable for an enrolled student
     */
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
                  AND cs.status = 'published'
                ORDER BY FIELD(cs.day_of_week, 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), cs.start_time ASC";
        return $this->db->fetchAll($sql, [':sid' => $studentId, ':semid' => $semesterId]);
    }

    /**
     * Get teaching schedule for a faculty member
     */
    public function getTeacherTimetable(int $teacherId, int $semesterId, bool $publishedOnly = false): array
    {
        $sql = "SELECT cs.*, r.room_number, r.building,
                       s.section_name, c.course_code, c.title as course_title, c.credit
                FROM `class_schedules` cs
                JOIN `sections` s ON s.id = cs.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                JOIN `rooms` r ON r.id = cs.room_id
                WHERE cs.teacher_id = :tid AND cs.semester_id = :semid AND cs.status != 'cancelled'";
        
        if ($publishedOnly) {
            $sql .= " AND cs.status = 'published'";
        }

        $sql .= " ORDER BY FIELD(cs.day_of_week, 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), cs.start_time ASC";
        return $this->db->fetchAll($sql, [':tid' => $teacherId, ':semid' => $semesterId]);
    }

    /**
     * Get single schedule with all relational joins
     */
    public function getScheduleWithDetails(int $id): ?array
    {
        $sql = "SELECT cs.*, r.room_number, r.building, r.room_type,
                       s.section_name, s.capacity, s.enrolled_count,
                       co.id as course_offering_id, c.id as course_id, c.course_code, c.title as course_title, c.credit,
                       t.id as teacher_id, t.teacher_id_card,
                       u.first_name as teacher_first_name, u.last_name as teacher_last_name,
                       sem.name as semester_name, sem.code as semester_code
                FROM `class_schedules` cs
                JOIN `sections` s ON s.id = cs.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                JOIN `rooms` r ON r.id = cs.room_id
                JOIN `teachers` t ON t.id = cs.teacher_id
                JOIN `users` u ON u.id = t.user_id
                JOIN `semesters` sem ON sem.id = cs.semester_id
                WHERE cs.id = :id LIMIT 1";
        return $this->db->fetch($sql, [':id' => $id]);
    }

    /**
     * Master schedule listing for Department Coordinator with multi-criteria filtering
     */
    public function getAllWithDetails(int $semesterId, array $filters = []): array
    {
        $sql = "SELECT cs.*, r.room_number, r.building,
                       s.section_name, s.capacity, s.enrolled_count,
                       c.course_code, c.title as course_title, c.credit,
                       u.first_name as teacher_first_name, u.last_name as teacher_last_name,
                       (SELECT COUNT(*) FROM `schedule_revisions` sr WHERE sr.class_schedule_id = cs.id) as revision_count
                FROM `class_schedules` cs
                JOIN `sections` s ON s.id = cs.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                JOIN `rooms` r ON r.id = cs.room_id
                JOIN `teachers` t ON t.id = cs.teacher_id
                JOIN `users` u ON u.id = t.user_id
                WHERE cs.semester_id = :sid";
        
        $params = [':sid' => $semesterId];

        if (!empty($filters['day_of_week'])) {
            $sql .= " AND cs.day_of_week = :day";
            $params[':day'] = $filters['day_of_week'];
        }

        if (!empty($filters['room_id'])) {
            $sql .= " AND cs.room_id = :rid";
            $params[':rid'] = (int)$filters['room_id'];
        }

        if (!empty($filters['teacher_id'])) {
            $sql .= " AND cs.teacher_id = :tid";
            $params[':tid'] = (int)$filters['teacher_id'];
        }

        if (!empty($filters['course_id'])) {
            $sql .= " AND c.id = :cid";
            $params[':cid'] = (int)$filters['course_id'];
        }

        if (!empty($filters['section_id'])) {
            $sql .= " AND cs.section_id = :secid";
            $params[':secid'] = (int)$filters['section_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND cs.status = :status";
            $params[':status'] = $filters['status'];
        } else {
            // Default exclude cancelled unless explicitly requested
            $sql .= " AND cs.status != 'cancelled'";
        }

        $sql .= " ORDER BY FIELD(cs.day_of_week, 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), cs.start_time ASC";
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Check if a room is occupied by another active schedule slot
     */
    public function findRoomConflict(int $roomId, int $semesterId, string $day, string $startTime, string $endTime, ?int $ignoreScheduleId = null): ?array
    {
        $sql = "SELECT cs.*, r.room_number, c.course_code, s.section_name
                FROM `class_schedules` cs
                JOIN `rooms` r ON r.id = cs.room_id
                JOIN `sections` s ON s.id = cs.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                WHERE cs.room_id = :rid AND cs.semester_id = :semid AND cs.day_of_week = :day
                  AND cs.status != 'cancelled'
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

    /**
     * Check if a teacher has another overlapping active class
     */
    public function findTeacherConflict(int $teacherId, int $semesterId, string $day, string $startTime, string $endTime, ?int $ignoreScheduleId = null): ?array
    {
        $sql = "SELECT cs.*, c.course_code, s.section_name, u.first_name, u.last_name
                FROM `class_schedules` cs
                JOIN `sections` s ON s.id = cs.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                JOIN `teachers` t ON t.id = cs.teacher_id
                JOIN `users` u ON u.id = t.user_id
                WHERE cs.teacher_id = :tid AND cs.semester_id = :semid AND cs.day_of_week = :day
                  AND cs.status != 'cancelled'
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

    /**
     * Check if a section already has a scheduled class at the specified time
     */
    public function findSectionConflict(int $sectionId, int $semesterId, string $day, string $startTime, string $endTime, ?int $ignoreScheduleId = null): ?array
    {
        $sql = "SELECT cs.*, c.course_code, s.section_name, r.room_number
                FROM `class_schedules` cs
                JOIN `sections` s ON s.id = cs.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                JOIN `rooms` r ON r.id = cs.room_id
                WHERE cs.section_id = :secid AND cs.semester_id = :semid AND cs.day_of_week = :day
                  AND cs.status != 'cancelled'
                  AND (:st < cs.end_time AND :et > cs.start_time)";

        $params = [
            ':secid' => $sectionId,
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

    /**
     * Check if any enrolled students in this section have a timetable conflict with another course
     */
    public function findStudentRegisteredConflict(int $sectionId, int $semesterId, string $day, string $startTime, string $endTime, ?int $ignoreScheduleId = null): ?array
    {
        $sql = "SELECT reg.student_id, stu.student_id_card, u.first_name, u.last_name,
                       c2.course_code as conflicting_course, s2.section_name as conflicting_section,
                       cs2.start_time, cs2.end_time
                FROM `registrations` reg
                JOIN `students` stu ON stu.id = reg.student_id
                JOIN `users` u ON u.id = stu.user_id
                JOIN `registrations` reg2 ON reg2.student_id = reg.student_id AND reg2.semester_id = reg.semester_id AND reg2.section_id != reg.section_id AND reg2.status = 'enrolled'
                JOIN `class_schedules` cs2 ON cs2.section_id = reg2.section_id AND cs2.semester_id = :semid AND cs2.day_of_week = :day AND cs2.status = 'published'
                JOIN `sections` s2 ON s2.id = reg2.section_id
                JOIN `course_offerings` co2 ON co2.id = s2.course_offering_id
                JOIN `courses` c2 ON c2.id = co2.course_id
                WHERE reg.section_id = :secid AND reg.status = 'enrolled'
                  AND (:st < cs2.end_time AND :et > cs2.start_time)";

        $params = [
            ':secid' => $sectionId,
            ':semid' => $semesterId,
            ':day' => $day,
            ':st' => $startTime,
            ':et' => $endTime
        ];

        if ($ignoreScheduleId) {
            $sql .= " AND cs2.id != :ign";
            $params[':ign'] = $ignoreScheduleId;
        }

        $sql .= " LIMIT 1";
        return $this->db->fetch($sql, $params);
    }

    public function getTodayClassesForStudent(int $studentId, int $semesterId, ?string $dayOfWeek = null): array
    {
        $day = $dayOfWeek ?? date('l');
        $all = $this->getStudentTimetable($studentId, $semesterId);
        return array_values(array_filter($all, fn($item) => strcasecmp($item['day_of_week'], $day) === 0));
    }

    public function getTodayClassesForTeacher(int $teacherId, int $semesterId, ?string $dayOfWeek = null): array
    {
        $day = $dayOfWeek ?? date('l');
        $all = $this->getTeacherTimetable($teacherId, $semesterId);
        return array_values(array_filter($all, fn($item) => strcasecmp($item['day_of_week'], $day) === 0));
    }
}
