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
                ORDER BY e.exam_date ASC, e.start_time ASC";
        return $this->db->fetchAll($sql, [':sid' => $studentId, ':semid' => $semesterId]);
    }
}
