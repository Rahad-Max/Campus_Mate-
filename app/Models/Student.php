<?php
namespace App\Models;

use App\Core\Model;

class Student extends Model
{
    protected string $table = 'students';
    protected array $fillable = [
        'user_id', 'department_id', 'student_id_card', 'batch_no',
        'admission_semester_id', 'current_semester_level', 'cgpa',
        'completed_credits', 'academic_status'
    ];

    public function findByUserId(int $userId): ?array
    {
        return $this->findBy('user_id', $userId);
    }

    public function getFullDetails(int $studentId): ?array
    {
        $sql = "SELECT s.*, u.username, u.email, u.first_name, u.last_name, u.phone, u.avatar_url,
                       d.code as department_code, d.name as department_name,
                       sem.name as admission_semester_name,
                       t.id as advisor_teacher_id,
                       adv_u.first_name as advisor_first_name, adv_u.last_name as advisor_last_name,
                       t.designation as advisor_designation, adv_u.email as advisor_email
                FROM `students` s
                JOIN `users` u ON u.id = s.user_id
                JOIN `departments` d ON d.id = s.department_id
                LEFT JOIN `semesters` sem ON sem.id = s.admission_semester_id
                LEFT JOIN `academic_advisors` aa ON aa.student_id = s.id AND aa.status = 'active'
                LEFT JOIN `teachers` t ON t.id = aa.teacher_id
                LEFT JOIN `users` adv_u ON adv_u.id = t.user_id
                WHERE s.id = :id LIMIT 1";
        return $this->db->fetch($sql, [':id' => $studentId]);
    }

    public function getAcademicHistory(int $studentId): array
    {
        $sql = "SELECT sch.*, c.course_code, c.title as course_title, c.credit, c.course_type,
                       sem.name as semester_name, sem.code as semester_code
                FROM `student_course_history` sch
                JOIN `courses` c ON c.id = sch.course_id
                JOIN `semesters` sem ON sem.id = sch.semester_id
                WHERE sch.student_id = :id
                ORDER BY sem.academic_year DESC, sem.id DESC, c.course_code ASC";
        return $this->db->fetchAll($sql, [':id' => $studentId]);
    }

    public function getPassedCourseIds(int $studentId): array
    {
        $sql = "SELECT course_id FROM `student_course_history` 
                WHERE student_id = :id AND status = 'completed' AND grade_point >= 2.00";
        $rows = $this->db->fetchAll($sql, [':id' => $studentId]);
        return array_column($rows, 'course_id');
    }
}
