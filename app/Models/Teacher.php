<?php
namespace App\Models;

use App\Core\Model;

class Teacher extends Model
{
    protected string $table = 'teachers';
    protected array $fillable = [
        'user_id', 'department_id', 'teacher_id_card',
        'designation', 'office_room', 'joining_date',
        'max_credit_load', 'status'
    ];

    public function findByUserId(int $userId): ?array
    {
        return $this->findBy('user_id', $userId);
    }

    public function getFullDetails(int $teacherId): ?array
    {
        $sql = "SELECT t.*, u.username, u.email, u.first_name, u.last_name, u.phone, u.avatar_url,
                       d.code as department_code, d.name as department_name,
                       fp.bio, fp.research_interests, fp.education, fp.publications, fp.office_hours, fp.personal_website
                FROM `teachers` t
                JOIN `users` u ON u.id = t.user_id
                JOIN `departments` d ON d.id = t.department_id
                LEFT JOIN `faculty_profiles` fp ON fp.teacher_id = t.id
                WHERE t.id = :id LIMIT 1";
        return $this->db->fetch($sql, [':id' => $teacherId]);
    }

    public function getAssignedSections(int $teacherId, ?int $semesterId = null): array
    {
        $sql = "SELECT ta.*, s.section_name, s.capacity, s.enrolled_count,
                       co.id as course_offering_id, sem.name as semester_name,
                       c.course_code, c.title as course_title, c.credit, c.course_type
                FROM `teacher_assignments` ta
                JOIN `sections` s ON s.id = ta.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                JOIN `semesters` sem ON sem.id = co.semester_id
                WHERE ta.teacher_id = :tid";

        $params = [':tid' => $teacherId];
        if ($semesterId) {
            $sql .= " AND co.semester_id = :sid";
            $params[':sid'] = $semesterId;
        }

        $sql .= " ORDER BY c.course_code ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function getAssignedAdvisees(int $teacherId): array
    {
        $sql = "SELECT aa.*, s.student_id_card, s.batch_no, s.cgpa, s.completed_credits, s.academic_status,
                       u.first_name, u.last_name, u.email, u.phone,
                       d.code as department_code
                FROM `academic_advisors` aa
                JOIN `students` s ON s.id = aa.student_id
                JOIN `users` u ON u.id = s.user_id
                JOIN `departments` d ON d.id = s.department_id
                WHERE aa.teacher_id = :tid AND aa.status = 'active'
                ORDER BY s.student_id_card ASC";
        return $this->db->fetchAll($sql, [':tid' => $teacherId]);
    }
}
