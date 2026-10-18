<?php
namespace App\Models;

use App\Core\Model;

class Registration extends Model
{
    protected string $table = 'registrations';
    protected array $fillable = [
        'student_id', 'section_id', 'semester_id',
        'registration_request_id', 'status',
        'enrolled_at', 'dropped_at'
    ];

    public function getEnrolledCourses(int $studentId, int $semesterId): array
    {
        $sql = "SELECT reg.*, s.section_name, s.capacity,
                       c.id as course_id, c.course_code, c.title as course_title, c.credit, c.course_type,
                       t.id as teacher_id, u.first_name as teacher_first_name, u.last_name as teacher_last_name
                FROM `registrations` reg
                JOIN `sections` s ON s.id = reg.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                LEFT JOIN `teacher_assignments` ta ON ta.section_id = s.id AND ta.role = 'lead_instructor'
                LEFT JOIN `teachers` t ON t.id = ta.teacher_id
                LEFT JOIN `users` u ON u.id = t.user_id
                WHERE reg.student_id = :sid AND reg.semester_id = :semid AND reg.status = 'enrolled'
                ORDER BY c.course_code ASC";
        $items = $this->db->fetchAll($sql, [':sid' => $studentId, ':semid' => $semesterId]);
        $sectionModel = new Section();
        foreach ($items as &$item) {
            $item['schedules'] = $sectionModel->getSchedules((int)$item['section_id']);
        }
        unset($item);
        return $items;
    }

    public function getTotalRegisteredCredits(int $studentId, int $semesterId): float
    {
        $sql = "SELECT COALESCE(SUM(c.credit), 0) as total_credits
                FROM `registrations` reg
                JOIN `sections` s ON s.id = reg.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                WHERE reg.student_id = :sid AND reg.semester_id = :semid AND reg.status = 'enrolled'";
        $row = $this->db->fetch($sql, [':sid' => $studentId, ':semid' => $semesterId]);
        return (float)($row['total_credits'] ?? 0.0);
    }

    public function isCourseAlreadyEnrolled(int $studentId, int $semesterId, int $courseId): bool
    {
        $sql = "SELECT reg.id
                FROM `registrations` reg
                JOIN `sections` s ON s.id = reg.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                WHERE reg.student_id = :sid AND reg.semester_id = :semid 
                  AND co.course_id = :cid AND reg.status = 'enrolled'
                LIMIT 1";
        return (bool)$this->db->fetch($sql, [':sid' => $studentId, ':semid' => $semesterId, ':cid' => $courseId]);
    }

    public function getEnrolledStudentsForSection(int $sectionId): array
    {
        $sql = "SELECT reg.*, s.student_id_card, s.batch_no, s.cgpa,
                       u.first_name, u.last_name, u.email
                FROM `registrations` reg
                JOIN `students` s ON s.id = reg.student_id
                JOIN `users` u ON u.id = s.user_id
                WHERE reg.section_id = :sid AND reg.status = 'enrolled'
                ORDER BY s.student_id_card ASC";
        return $this->db->fetchAll($sql, [':sid' => $sectionId]);
    }
}
