<?php
namespace App\Models;

use App\Core\Model;

class Notification extends Model
{
    protected string $table = 'notifications';
    protected array $fillable = [
        'user_id', 'title', 'message', 'type',
        'action_url', 'is_read', 'read_at'
    ];

    public function getUserNotifications(int $userId, bool $unreadOnly = false): array
    {
        $sql = "SELECT * FROM `notifications` WHERE user_id = :uid";
        $params = [':uid' => $userId];

        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }

        $sql .= " ORDER BY created_at DESC LIMIT 30";
        return $this->db->fetchAll($sql, $params);
    }

    public function markAsRead(int $notificationId, int $userId): bool
    {
        $sql = "UPDATE `notifications` SET `is_read` = 1, `read_at` = NOW() 
                WHERE `id` = :id AND `user_id` = :uid";
        return $this->db->execute($sql, [':id' => $notificationId, ':uid' => $userId]) > 0;
    }

    public function markAllAsRead(int $userId): bool
    {
        $sql = "UPDATE `notifications` SET `is_read` = 1, `read_at` = NOW() 
                WHERE `user_id` = :uid AND `is_read` = 0";
        return $this->db->execute($sql, [':uid' => $userId]) >= 0;
    }

    public function getUnreadCount(int $userId): int
    {
        $sql = "SELECT COUNT(*) as unread FROM `notifications` WHERE user_id = :uid AND is_read = 0";
        $row = $this->db->fetch($sql, [':uid' => $userId]);
        return (int)($row['unread'] ?? 0);
    }
}
