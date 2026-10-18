<?php
namespace App\Models;

use App\Core\Model;

class Curriculum extends Model
{
    protected string $table = 'curricula';
    protected array $fillable = ['department_id', 'name', 'version', 'total_credits_required', 'is_active'];

    public function getAllWithDepartment(): array
    {
        $sql = "SELECT c.*, d.name as department_name, d.code as department_code,
                       (SELECT COUNT(*) FROM `curriculum_courses` cc WHERE cc.curriculum_id = c.id) as course_count,
                       (SELECT COALESCE(SUM(co.credit), 0) FROM `curriculum_courses` cc JOIN `courses` co ON co.id = cc.course_id WHERE cc.curriculum_id = c.id) as calculated_credits
                FROM `curricula` c
                JOIN `departments` d ON d.id = c.department_id
                ORDER BY d.code ASC, c.is_active DESC, c.version DESC";
        return $this->db->fetchAll($sql);
    }

    public function getActiveForDepartment(int $departmentId): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE department_id = :dept_id AND is_active = 1 LIMIT 1";
        return $this->db->fetch($sql, [':dept_id' => $departmentId]);
    }

    public function getCurriculumCourses(int $curriculumId): array
    {
        $sql = "SELECT cc.*, c.course_code, c.title, c.credit, c.course_type, c.description, c.status as course_status
                FROM `curriculum_courses` cc
                JOIN `courses` c ON c.id = cc.course_id
                WHERE cc.curriculum_id = :cid
                ORDER BY cc.recommended_semester ASC, c.course_code ASC";
        return $this->db->fetchAll($sql, [':cid' => $curriculumId]);
    }

    public function calculateTotalCredits(int $curriculumId): float
    {
        $sql = "SELECT COALESCE(SUM(c.credit), 0) as total
                FROM `curriculum_courses` cc
                JOIN `courses` c ON c.id = cc.course_id
                WHERE cc.curriculum_id = :cid";
        $row = $this->db->fetch($sql, [':cid' => $curriculumId]);
        return (float)($row['total'] ?? 0.0);
    }

    public function addCourse(
        int $curriculumId,
        int $courseId,
        int $recommendedSemester,
        bool $isElective = false,
        ?string $electiveGroup = null
    ): bool {
        $sql = "INSERT INTO `curriculum_courses` (`curriculum_id`, `course_id`, `recommended_semester`, `is_elective`, `elective_group`)
                VALUES (:cid, :coid, :rec, :elec, :egrp)
                ON DUPLICATE KEY UPDATE `recommended_semester` = VALUES(`recommended_semester`),
                                        `is_elective` = VALUES(`is_elective`),
                                        `elective_group` = VALUES(`elective_group`)";

        $res = $this->db->execute($sql, [
            ':cid' => $curriculumId,
            ':coid' => $courseId,
            ':rec' => $recommendedSemester,
            ':elec' => $isElective ? 1 : 0,
            ':egrp' => $electiveGroup
        ]);

        $this->syncTotalCredits($curriculumId);
        return $res > 0;
    }

    public function removeCourse(int $curriculumId, int $courseId): bool
    {
        $sql = "DELETE FROM `curriculum_courses` WHERE `curriculum_id` = :cid AND `course_id` = :coid";
        $res = $this->db->execute($sql, [':cid' => $curriculumId, ':coid' => $courseId]);
        $this->syncTotalCredits($curriculumId);
        return $res > 0;
    }

    public function syncTotalCredits(int $curriculumId): void
    {
        $total = $this->calculateTotalCredits($curriculumId);
        $this->db->execute(
            "UPDATE `curricula` SET `total_credits_required` = :tot WHERE `id` = :id",
            [':tot' => $total, ':id' => $curriculumId]
        );
    }
}
