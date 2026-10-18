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
        return $this->findBy('course_code', strtoupper(trim($courseCode)));
    }

    public function isCodeUnique(string $code, ?int $excludeId = null): bool
    {
        $code = strtoupper(trim($code));
        $sql = "SELECT id FROM `{$this->table}` WHERE `course_code` = :code";
        $params = [':code' => $code];

        if ($excludeId !== null) {
            $sql .= " AND `id` != :id";
            $params[':id'] = $excludeId;
        }

        $row = $this->db->fetch($sql, $params);
        return empty($row);
    }

    public function getAllWithDetails(?int $departmentId = null, ?string $search = null): array
    {
        $sql = "SELECT c.*, d.code as department_code, d.name as department_name,
                       (SELECT COUNT(*) FROM `course_prerequisites` cp WHERE cp.course_id = c.id) as prereq_count,
                       (SELECT COUNT(*) FROM `course_offerings` co WHERE co.course_id = c.id) as offering_count
                FROM `courses` c
                JOIN `departments` d ON d.id = c.department_id
                WHERE 1=1";

        $params = [];
        if ($departmentId) {
            $sql .= " AND c.department_id = :did";
            $params[':did'] = $departmentId;
        }
        if (!empty($search)) {
            $sql .= " AND (c.course_code LIKE :q OR c.title LIKE :q)";
            $params[':q'] = "%{$search}%";
        }

        $sql .= " ORDER BY c.course_code ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function getPrerequisites(int $courseId): array
    {
        $sql = "SELECT cp.*, c.course_code, c.title, c.credit, c.course_type
                FROM `course_prerequisites` cp
                JOIN `courses` c ON c.id = cp.prerequisite_course_id
                WHERE cp.course_id = :cid
                ORDER BY c.course_code ASC";
        return $this->db->fetchAll($sql, [':cid' => $courseId]);
    }

    /**
     * Detect if adding prerequisite candidatePrereqId to courseId creates a circular dependency
     */
    public function hasPrerequisiteCycle(int $courseId, int $candidatePrereqId): bool
    {
        if ($courseId === $candidatePrereqId) {
            return true;
        }

        $visited = [];
        $queue = [$candidatePrereqId];

        while (!empty($queue)) {
            $current = array_shift($queue);
            if ($current === $courseId) {
                return true;
            }

            if (isset($visited[$current])) {
                continue;
            }
            $visited[$current] = true;

            $nextPrereqs = $this->db->fetchAll(
                "SELECT prerequisite_course_id FROM `course_prerequisites` WHERE course_id = :cid",
                [':cid' => $current]
            );

            foreach ($nextPrereqs as $row) {
                $pid = (int)$row['prerequisite_course_id'];
                if (!isset($visited[$pid])) {
                    $queue[] = $pid;
                }
            }
        }

        return false;
    }

    public function addPrerequisite(
        int $courseId,
        int $prereqCourseId,
        string $minGrade = 'D',
        bool $isMandatory = true
    ): bool {
        if ($courseId === $prereqCourseId) {
            return false;
        }

        if ($this->hasPrerequisiteCycle($courseId, $prereqCourseId)) {
            return false;
        }

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

    public function removePrerequisite(int $courseId, int $prereqCourseId): bool
    {
        $sql = "DELETE FROM `course_prerequisites` WHERE `course_id` = :cid AND `prerequisite_course_id` = :pcid";
        return $this->db->execute($sql, [':cid' => $courseId, ':pcid' => $prereqCourseId]) > 0;
    }

    /**
     * Safety check: prevent deleting or breaking courses referenced in transcripts or active offerings
     */
    public function hasAcademicReferences(int $courseId): bool
    {
        $historyCount = (int)$this->db->fetch(
            "SELECT COUNT(*) as c FROM `student_course_history` WHERE `course_id` = :cid",
            [':cid' => $courseId]
        )['c'];

        $offeringCount = (int)$this->db->fetch(
            "SELECT COUNT(*) as c FROM `course_offerings` WHERE `course_id` = :cid",
            [':cid' => $courseId]
        )['c'];

        return ($historyCount + $offeringCount) > 0;
    }
}
