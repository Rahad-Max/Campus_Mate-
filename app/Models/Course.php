<?php
namespace App\Models;

use App\Core\Model;

class Course extends Model
{
    protected string $table = 'courses';
    protected array $fillable = [
        'department_id', 'course_code', 'title', 'credit',
        'course_type', 'description', 'syllabus_url', 'status'
    ];

    public function findByCode(string $courseCode): ?array
    {
        return $this->findBy('course_code', $courseCode);
    }

    public function getPrerequisites(int $courseId): array
    {
        $sql = "SELECT cp.*, c.course_code, c.title, c.credit
                FROM `course_prerequisites` cp
                JOIN `courses` c ON c.id = cp.prerequisite_course_id
                WHERE cp.course_id = :cid";
        return $this->db->fetchAll($sql, [':cid' => $courseId]);
    }

    public function addPrerequisite(int $courseId, int $prereqCourseId, string $minGrade = 'D', bool $isMandatory = true): bool
    {
        $sql = "INSERT INTO `course_prerequisites` (`course_id`, `prerequisite_course_id`, `min_grade`, `is_mandatory`)
                VALUES (:cid, :pcid, :mg, :im)
                ON DUPLICATE KEY UPDATE `min_grade` = VALUES(`min_grade`), `is_mandatory` = VALUES(`is_mandatory`)";
        return $this->db->execute($sql, [
            ':cid' => $courseId,
            ':pcid' => $prereqCourseId,
            ':mg' => $minGrade,
            ':im' => $isMandatory ? 1 : 0
        ]) > 0;
    }
}
