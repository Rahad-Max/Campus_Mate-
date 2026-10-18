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

    public function hasPendingRequest(int $studentId, int $semesterId): bool
    {
        $sql = "SELECT id FROM `registration_requests` 
                WHERE student_id = :sid AND semester_id = :semid AND status = 'submitted' 
                LIMIT 1";
        return !empty($this->db->fetch($sql, [':sid' => $studentId, ':semid' => $semesterId]));
    }

    public function getRequestWithDetails(int $requestId): ?array
    {
        $sql = "SELECT rr.*, 
                       s.student_id_card, s.batch_no, s.cgpa, s.completed_credits, s.academic_status,
                       u.first_name as student_first_name, u.last_name as student_last_name, u.email as student_email,
                       d.code as department_code, d.name as department_name,
                       sem.name as semester_name, sem.code as semester_code,
                       adv_u.first_name as advisor_first_name, adv_u.last_name as advisor_last_name
                FROM `registration_requests` rr
                JOIN `students` s ON s.id = rr.student_id
                JOIN `users` u ON u.id = s.user_id
                JOIN `departments` d ON d.id = s.department_id
                JOIN `semesters` sem ON sem.id = rr.semester_id
                LEFT JOIN `teachers` adv_t ON adv_t.id = rr.advisor_id
                LEFT JOIN `users` adv_u ON adv_u.id = adv_t.user_id
                WHERE rr.id = :id LIMIT 1";
        return $this->db->fetch($sql, [':id' => $requestId]);
    }

    public function getItems(int $requestId): array
    {
        $sql = "SELECT rri.*, s.section_name, s.capacity, s.enrolled_count,
                       c.id as course_id, c.course_code, c.title as course_title, c.credit, c.course_type,
                       t.id as teacher_id, u.first_name as teacher_first_name, u.last_name as teacher_last_name
                FROM `registration_request_items` rri
                JOIN `sections` s ON s.id = rri.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                JOIN `courses` c ON c.id = co.course_id
                LEFT JOIN `teacher_assignments` ta ON ta.section_id = s.id AND ta.role = 'lead_instructor'
                LEFT JOIN `teachers` t ON t.id = ta.teacher_id
                LEFT JOIN `users` u ON u.id = t.user_id
                WHERE rri.request_id = :rid
                ORDER BY c.course_code ASC";
        $items = $this->db->fetchAll($sql, [':rid' => $requestId]);
        
        // Attach schedules for each item
        $scheduleModel = new ClassSchedule();
        foreach ($items as &$item) {
            $item['schedules'] = (new Section())->getSchedules((int)$item['section_id']);
        }
        unset($item);

        return $items;
    }

    public function isStudentAssignedToAdvisor(int $studentId, int $teacherId): bool
    {
        $sql = "SELECT id FROM `academic_advisors` 
                WHERE student_id = :sid AND teacher_id = :tid AND status = 'active' 
                LIMIT 1";
        return !empty($this->db->fetch($sql, [':sid' => $studentId, ':tid' => $teacherId]));
    }

    public function getPendingForAdvisor(int $teacherId): array
    {
        $sql = "SELECT rr.*, s.student_id_card, s.batch_no, s.cgpa, s.academic_status,
                       u.first_name as student_first_name, u.last_name as student_last_name,
                       sem.name as semester_name, sem.code as semester_code,
                       (SELECT COUNT(*) FROM `registration_request_items` rri WHERE rri.request_id = rr.id) as course_count
                FROM `registration_requests` rr
                JOIN `students` s ON s.id = rr.student_id
                JOIN `users` u ON u.id = s.user_id
                JOIN `semesters` sem ON sem.id = rr.semester_id
                JOIN `academic_advisors` aa ON aa.student_id = s.id AND aa.status = 'active'
                WHERE aa.teacher_id = :tid AND rr.status = 'submitted'
                ORDER BY rr.created_at ASC";
        return $this->db->fetchAll($sql, [':tid' => $teacherId]);
    }

    public function getAdvisorHistory(int $teacherId): array
    {
        $sql = "SELECT rr.*, s.student_id_card, s.batch_no, s.cgpa,
                       u.first_name as student_first_name, u.last_name as student_last_name,
                       sem.name as semester_name, sem.code as semester_code
                FROM `registration_requests` rr
                JOIN `students` s ON s.id = rr.student_id
                JOIN `users` u ON u.id = s.user_id
                JOIN `semesters` sem ON sem.id = rr.semester_id
                WHERE rr.advisor_id = :tid AND rr.status IN ('advisor_approved', 'advisor_rejected')
                ORDER BY rr.advisor_action_at DESC LIMIT 50";
        return $this->db->fetchAll($sql, [':tid' => $teacherId]);
    }
}
