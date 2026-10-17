<?php
namespace App\Models;

use App\Core\Model;

class Notice extends Model
{
    protected string $table = 'notices';
    protected array $fillable = [
        'title', 'content', 'category', 'department_id',
        'semester_id', 'target_role', 'attachment_url',
        'published_by', 'is_pinned', 'publish_date',
        'expiry_date', 'status'
    ];

    public function getActiveNotices(?string $userRole = null, ?int $departmentId = null): array
    {
        $sql = "SELECT n.*, d.code as department_code,
                       u.first_name as author_first_name, u.last_name as author_last_name
                FROM `notices` n
                LEFT JOIN `departments` d ON d.id = n.department_id
                JOIN `users` u ON u.id = n.published_by
                WHERE n.status = 'published'
                  AND (n.expiry_date IS NULL OR n.expiry_date >= NOW())";

        $params = [];

        if ($userRole) {
            $sql .= " AND (n.target_role = 'all' OR n.target_role = :role)";
            $params[':role'] = $userRole;
        }

        if ($departmentId) {
            $sql .= " AND (n.department_id IS NULL OR n.department_id = :did)";
            $params[':did'] = $departmentId;
        }

        $sql .= " ORDER BY n.is_pinned DESC, n.publish_date DESC LIMIT 50";
        return $this->db->fetchAll($sql, $params);
    }
}
