<?php
namespace App\Models;

use App\Core\Model;

class RegistrationRequest extends Model
{
    protected string $table = 'registration_requests';
    protected array $fillable = [
        'student_id', 'semester_id', 'request_type', 'total_credits',
        'status', 'submission_notes', 'advisor_id', 'advisor_remarks',
        'advisor_action_at', 'coordinator_id', 'coordinator_remarks',
        'coordinator_action_at'
    ];

    public function getStudentRequests(int $studentId, ?int $semesterId = null): array
    {
        $sql = "SELECT rr.*, sem.name as semester_name, sem.code as semester_code
                FROM `registration_requests` rr
                JOIN `semesters` sem ON sem.id = rr.semester_id
                WHERE rr.student_id = :sid";
        $params = [':sid' => $studentId];

        if ($semesterId) {
            $sql .= " AND rr.semester_id = :semid";
            $params[':semid'] = $semesterId;
        }

        $sql .= " ORDER BY rr.id DESC";
        return $this->db->fetchAll($sql, $params);
    }

    public function getItems(int $requestId): array
    {
        $sql = "SELECT rri.*, s.section_name, s.capacity, s.enrolled_count,
                       c.id as course_id, c.course_code, c.title as course_title, c.credit, c.course_type
                FROM `registration_request_items` rri
                JOIN `sections` s ON s.id = rri.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                WHERE rri.request_id = :rid
                ORDER BY c.course_code ASC";
        return $this->db->fetchAll($sql, [':rid' => $requestId]);
    }

    public function getPendingForAdvisor(int $teacherId): array
    {
        $sql = "SELECT rr.*, s.student_id_card, s.batch_no, s.cgpa,
                       u.first_name as student_first_name, u.last_name as student_last_name,
                       sem.name as semester_name
                FROM `registration_requests` rr
                JOIN `students` s ON s.id = rr.student_id
                JOIN `users` u ON u.id = s.user_id
                JOIN `semesters` sem ON sem.id = rr.semester_id
                JOIN `academic_advisors` aa ON aa.student_id = s.id
                WHERE aa.teacher_id = :tid AND rr.status = 'submitted'
                ORDER BY rr.created_at ASC";
        return $this->db->fetchAll($sql, [':tid' => $teacherId]);
    }
}
