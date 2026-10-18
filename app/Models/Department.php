<?php
namespace App\Models;

use App\Core\Model;

class Department extends Model
{
    protected string $table = 'departments';
    protected array $fillable = ['code', 'name', 'faculty_name', 'description', 'status'];

    public function findByCode(string $code): ?array
    {
        return $this->findBy('code', strtoupper(trim($code)));
    }

    public function getActive(): array
    {
        return $this->where('status', 'active');
    }

    public function search(string $query): array
    {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `code` LIKE :q OR `name` LIKE :q OR `faculty_name` LIKE :q 
                ORDER BY `name` ASC";
        return $this->db->fetchAll($sql, [':q' => "%{$query}%"]);
    }

    public function isCodeUnique(string $code, ?int $excludeId = null): bool
    {
        $code = strtoupper(trim($code));
        $sql = "SELECT id FROM `{$this->table}` WHERE `code` = :code";
        $params = [':code' => $code];

        if ($excludeId !== null) {
            $sql .= " AND `id` != :id";
            $params[':id'] = $excludeId;
        }

        $row = $this->db->fetch($sql, $params);
        return empty($row);
    }

    /**
     * Check if department has associated students, teachers, courses, or curricula
     */
    public function hasAcademicReferences(int $departmentId): bool
    {
        $counts = [
            'students' => (int)$this->db->fetch("SELECT COUNT(*) as c FROM `students` WHERE `department_id` = :id", [':id' => $departmentId])['c'],
            'teachers' => (int)$this->db->fetch("SELECT COUNT(*) as c FROM `teachers` WHERE `department_id` = :id", [':id' => $departmentId])['c'],
            'courses' => (int)$this->db->fetch("SELECT COUNT(*) as c FROM `courses` WHERE `department_id` = :id", [':id' => $departmentId])['c'],
            'curricula' => (int)$this->db->fetch("SELECT COUNT(*) as c FROM `curricula` WHERE `department_id` = :id", [':id' => $departmentId])['c'],
        ];

        return array_sum($counts) > 0;
    }

    public function getStatistics(int $departmentId): array
    {
        return [
            'total_students' => (int)$this->db->fetch("SELECT COUNT(*) as c FROM `students` WHERE `department_id` = :id", [':id' => $departmentId])['c'],
            'total_teachers' => (int)$this->db->fetch("SELECT COUNT(*) as c FROM `teachers` WHERE `department_id` = :id", [':id' => $departmentId])['c'],
            'total_courses' => (int)$this->db->fetch("SELECT COUNT(*) as c FROM `courses` WHERE `department_id` = :id", [':id' => $departmentId])['c'],
            'total_curricula' => (int)$this->db->fetch("SELECT COUNT(*) as c FROM `curricula` WHERE `department_id` = :id", [':id' => $departmentId])['c'],
        ];
    }
}
