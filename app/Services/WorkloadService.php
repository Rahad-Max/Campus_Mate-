<?php
namespace App\Services;

use App\Core\Database;
use App\Models\Teacher;

class WorkloadService
{
    private Teacher $teacherModel;
    private Database $db;

    public function __construct()
    {
        $this->teacherModel = new Teacher();
        $this->db = Database::getInstance();
    }

    public function getTeacherWorkload(int $teacherId, int $semesterId): array
    {
        $teacher = $this->teacherModel->find($teacherId);
        $maxLoad = (float)($teacher['max_credit_load'] ?? 15.00);

        $sql = "SELECT COALESCE(SUM(ta.assigned_credits), 0) as total_assigned
                FROM `teacher_assignments` ta
                JOIN `sections` s ON s.id = ta.section_id
                JOIN `course_offerings` co ON co.id = s.course_offering_id
                WHERE ta.teacher_id = :tid AND co.semester_id = :sid";

        $row = $this->db->fetch($sql, [':tid' => $teacherId, ':sid' => $semesterId]);
        $currentLoad = (float)($row['total_assigned'] ?? 0.0);

        return [
            'teacher_id' => $teacherId,
            'current_load' => $currentLoad,
            'max_load' => $maxLoad,
            'available_capacity' => max(0.0, $maxLoad - $currentLoad),
            'is_overloaded' => $currentLoad > $maxLoad
        ];
    }
}
