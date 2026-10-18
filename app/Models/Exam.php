<?php
namespace App\Models;

use App\Core\Model;

class Exam extends Model
{
    protected string $table = 'exams';
    protected array $fillable = [
        'course_offering_id', 'section_id', 'exam_type',
        'exam_date', 'start_time', 'end_time', 'room_id',
        'invigilator_teacher_id', 'status', 'instructions'
    ];

    /**
     * Master examination list for Coordinator with filters
     */
    public function getAllWithDetails(int $semesterId, array $filters = []): array
    {
        $sql = "SELECT e.*, c.course_code, c.title as course_title, c.credit,
                       r.room_number, r.building, r.capacity as room_capacity,
                       s.section_name, s.capacity as section_capacity, s.enrolled_count,
                       u_inv.first_name as invigilator_first_name, u_inv.last_name as invigilator_last_name
                FROM `exams` e
                JOIN `course_offerings` co ON co.id = e.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                JOIN `rooms` r ON r.id = e.room_id
                LEFT JOIN `sections` s ON s.id = e.section_id
                LEFT JOIN `teachers` t_inv ON t_inv.id = e.invigilator_teacher_id
                LEFT JOIN `users` u_inv ON u_inv.id = t_inv.user_id
                WHERE co.semester_id = :sid";

        $params = [':sid' => $semesterId];

        if (!empty($filters['exam_type'])) {
            $sql .= " AND e.exam_type = :type";
            $params[':type'] = $filters['exam_type'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND e.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['course_id'])) {
            $sql .= " AND c.id = :cid";
            $params[':cid'] = (int)$filters['course_id'];
        }

        if (!empty($filters['room_id'])) {
            $sql .= " AND e.room_id = :rid";
            $params[':rid'] = (int)$filters['room_id'];
        }

        if (!empty($filters['exam_date'])) {
            $sql .= " AND e.exam_date = :edate";
            $params[':edate'] = $filters['exam_date'];
        }

        $sql .= " ORDER BY e.exam_date ASC, e.start_time ASC";
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get single exam with relational joins
     */
    public function getExamWithDetails(int $id): ?array
    {
        $sql = "SELECT e.*, c.id as course_id, c.course_code, c.title as course_title, c.credit,
                       co.id as course_offering_id, co.semester_id,
                       r.room_number, r.building,
                       s.section_name, s.enrolled_count,
                       t_inv.id as teacher_id,
                       u_inv.first_name as invigilator_first_name, u_inv.last_name as invigilator_last_name
                FROM `exams` e
                JOIN `course_offerings` co ON co.id = e.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                JOIN `rooms` r ON r.id = e.room_id
                LEFT JOIN `sections` s ON s.id = e.section_id
                LEFT JOIN `teachers` t_inv ON t_inv.id = e.invigilator_teacher_id
                LEFT JOIN `users` u_inv ON u_inv.id = t_inv.user_id
                WHERE e.id = :id LIMIT 1";
        return $this->db->fetch($sql, [':id' => $id]);
    }

    /**
     * Examination schedule for an enrolled student (only published/scheduled routines)
     */
    public function getExamScheduleForStudent(int $studentId, int $semesterId): array
    {
        $sql = "SELECT e.*, c.course_code, c.title as course_title, c.credit,
                       r.room_number, r.building,
                       s.section_name
                FROM `registrations` reg
                JOIN `sections` s ON s.id = reg.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                JOIN `exams` e ON e.course_offering_id = co.id AND (e.section_id IS NULL OR e.section_id = s.id)
                JOIN `rooms` r ON r.id = e.room_id
                WHERE reg.student_id = :sid AND reg.semester_id = :semid AND reg.status = 'enrolled'
                  AND e.status IN ('scheduled', 'published')
                ORDER BY e.exam_date ASC, e.start_time ASC";
        return $this->db->fetchAll($sql, [':sid' => $studentId, ':semid' => $semesterId]);
    }

    /**
     * Examination invigilation & course examination schedule for teachers
     */
    public function getExamScheduleForTeacher(int $teacherId, int $semesterId): array
    {
        $sql = "SELECT DISTINCT e.*, c.course_code, c.title as course_title, c.credit,
                       r.room_number, r.building,
                       s.section_name,
                       CASE WHEN e.invigilator_teacher_id = :tid1 THEN 1 ELSE 0 END as is_invigilator
                FROM `exams` e
                JOIN `course_offerings` co ON co.id = e.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                JOIN `rooms` r ON r.id = e.room_id
                LEFT JOIN `sections` s ON s.id = e.section_id
                LEFT JOIN `teacher_assignments` ta ON ta.section_id = s.id OR (e.section_id IS NULL AND ta.teacher_id = :tid2)
                WHERE co.semester_id = :semid
                  AND e.status IN ('scheduled', 'published')
                  AND (e.invigilator_teacher_id = :tid3 OR ta.teacher_id = :tid4)
                ORDER BY e.exam_date ASC, e.start_time ASC";
        return $this->db->fetchAll($sql, [
            ':tid1' => $teacherId,
            ':tid2' => $teacherId,
            ':semid' => $semesterId,
            ':tid3' => $teacherId,
            ':tid4' => $teacherId
        ]);
    }

    /**
     * Conflict Check: Room occupied by another exam
     */
    public function findRoomConflict(int $roomId, string $date, string $startTime, string $endTime, ?int $ignoreId = null): ?array
    {
        $sql = "SELECT e.*, r.room_number, c.course_code, s.section_name
                FROM `exams` e
                JOIN `rooms` r ON r.id = e.room_id
                JOIN `course_offerings` co ON co.id = e.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                LEFT JOIN `sections` s ON s.id = e.section_id
                WHERE e.room_id = :rid AND e.exam_date = :edate
                  AND e.status IN ('scheduled', 'published')
                  AND (:st < e.end_time AND :et > e.start_time)";

        $params = [
            ':rid' => $roomId,
            ':edate' => $date,
            ':st' => $startTime,
            ':et' => $endTime
        ];

        if ($ignoreId) {
            $sql .= " AND e.id != :ign";
            $params[':ign'] = $ignoreId;
        }

        $sql .= " LIMIT 1";
        return $this->db->fetch($sql, $params);
    }

    /**
     * Conflict Check: Invigilator assigned to another exam
     */
    public function findInvigilatorConflict(int $teacherId, string $date, string $startTime, string $endTime, ?int $ignoreId = null): ?array
    {
        $sql = "SELECT e.*, c.course_code, r.room_number
                FROM `exams` e
                JOIN `rooms` r ON r.id = e.room_id
                JOIN `course_offerings` co ON co.id = e.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                WHERE e.invigilator_teacher_id = :tid AND e.exam_date = :edate
                  AND e.status IN ('scheduled', 'published')
                  AND (:st < e.end_time AND :et > e.start_time)";

        $params = [
            ':tid' => $teacherId,
            ':edate' => $date,
            ':st' => $startTime,
            ':et' => $endTime
        ];

        if ($ignoreId) {
            $sql .= " AND e.id != :ign";
            $params[':ign'] = $ignoreId;
        }

        $sql .= " LIMIT 1";
        return $this->db->fetch($sql, $params);
    }

    /**
     * Conflict Check: Overlapping exam for enrolled students in this course/section
     */
    public function findStudentRegisteredConflict(int $courseOfferingId, ?int $sectionId, string $date, string $startTime, string $endTime, ?int $ignoreId = null): ?array
    {
        $sql = "SELECT reg.student_id, stu.student_id_card, u.first_name, u.last_name,
                       c2.course_code as conflicting_course, e2.exam_type, e2.start_time, e2.end_time
                FROM `registrations` reg
                JOIN `sections` s ON s.id = reg.section_id
                JOIN `students` stu ON stu.id = reg.student_id
                JOIN `users` u ON u.id = stu.user_id
                JOIN `registrations` reg2 ON reg2.student_id = reg.student_id AND reg2.semester_id = reg.semester_id AND reg2.status = 'enrolled'
                JOIN `sections` s2 ON s2.id = reg2.section_id AND s2.course_offering_id != s.course_offering_id
                JOIN `exams` e2 ON e2.course_offering_id = s2.course_offering_id AND (e2.section_id IS NULL OR e2.section_id = s2.id)
                JOIN `course_offerings` co2 ON co2.id = e2.course_offering_id
                JOIN `courses` c2 ON c2.id = co2.course_id
                WHERE s.course_offering_id = :coid AND reg.status = 'enrolled'
                  AND e2.exam_date = :edate AND e2.status IN ('scheduled', 'published')
                  AND (:st < e2.end_time AND :et > e2.start_time)";

        $params = [
            ':coid' => $courseOfferingId,
            ':edate' => $date,
            ':st' => $startTime,
            ':et' => $endTime
        ];

        if ($sectionId) {
            $sql .= " AND (e2.section_id IS NULL OR e2.section_id = :secid)";
            $params[':secid'] = $sectionId;
        }

        if ($ignoreId) {
            $sql .= " AND e2.id != :ign";
            $params[':ign'] = $ignoreId;
        }

        $sql .= " LIMIT 1";
        return $this->db->fetch($sql, $params);
    }
}
