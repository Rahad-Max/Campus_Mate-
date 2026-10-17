<?php
namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    private Notification $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new Notification();
    }

    public function notify(int $userId, string $title, string $message, string $type = 'info', ?string $actionUrl = null): int
    {
        return $this->notificationModel->create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'action_url' => $actionUrl,
            'is_read' => 0
        ]);
    }

    public function notifyRole(string $role, string $title, string $message, string $type = 'info', ?string $actionUrl = null): int
    {
        $sql = "SELECT id FROM `users` WHERE role = :role AND is_active = 1";
        $users = \App\Core\Database::getInstance()->fetchAll($sql, [':role' => $role]);
        $count = 0;

        foreach ($users as $u) {
            $this->notify((int)$u['id'], $title, $message, $type, $actionUrl);
            $count++;
        }

        return $count;
    }
}
