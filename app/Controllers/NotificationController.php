<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
    private Notification $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new Notification();
    }

    public function index(): void
    {
        $user = $this->authUser();
        $notifications = $this->notificationModel->getUserNotifications((int)$user['id']);

        $this->render('notifications/index', [
            'title' => 'Notifications — PUB CampusMate',
            'notifications' => $notifications
        ]);
    }

    public function markRead(string $id): void
    {
        $user = $this->authUser();
        $this->notificationModel->markAsRead((int)$id, (int)$user['id']);

        if ($this->isAjaxRequest()) {
            $this->json(['success' => true]);
        }
        $this->redirect('/notifications');
    }

    public function markAllRead(): void
    {
        $user = $this->authUser();
        $this->notificationModel->markAllAsRead((int)$user['id']);

        if ($this->isAjaxRequest()) {
            $this->json(['success' => true]);
        }
        $this->setFlash('success', 'All notifications marked as read.');
        $this->redirect('/notifications');
    }
}
