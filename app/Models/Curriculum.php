<?php
namespace App\Models;

use App\Core\Model;

class Curriculum extends Model
{
    protected string $table = 'curricula';
    protected array $fillable = ['department_id', 'name', 'version', 'total_credits_required', 'is_active'];

    public function getActiveForDepartment(int $departmentId): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE department_id = :dept_id AND is_active = 1 LIMIT 1";
        return $this->db->fetch($sql, [':dept_id' => $departmentId]);
    }

    public function getCurriculumCourses(int $curriculumId): array
    {
        $sql = "SELECT cc.*, c.course_code, c.title, c.credit, c.course_type, c.description
                FROM `curriculum_courses` cc
                JOIN `courses` c ON c.id = cc.course_id
                WHERE cc.curriculum_id = :cid
                ORDER BY cc.recommended_semester ASC, c.course_code ASC";
        return $this->db->fetchAll($sql, [':cid' => $curriculumId]);
    }
}
